<?php

namespace Common\Jobs\Base;

use Common\Helpers\LoggerHelper;
use Common\Jobs\LogJob\LogJobParser;
use Common\Jobs\Traits\CreateJobs\CreateJobsGetTrait;
use Common\Jobs\Traits\CreateJobs\CreateJobsSetTrait;
use Common\Models\BaseModel;
use Exception;
use Illuminate\Support\Facades\Queue;

class CreateJobs
{
    use CreateJobsGetTrait;
    use CreateJobsSetTrait;

    /**
     * Путь джобы
     *
     * @var string
     */
    private string $job_class;

    /**
     * Тип джобы
     * Указывается, если вызывается джоба из другого проекта
     * И не можем определить фактический тип из файла
     *
     * @var int
     */
    private int $job_type;

    /**
     * Переданные данные
     *
     * @var
     */
    private $data;

    /**
     * Приоритет джобы | название очереди
     *
     * @var string
     */
    private string $priority = 'default';

    /**
     * Путь к какому-либо файлу
     * Обычно требуется для файлов Атон
     *
     * @var string
     */
    private string $path;

    /**
     * Уникальный идентификатор джобы
     *
     * @var string
     */
    private string $uuid;

    /**
     * Идентификатор пользователя (или рандомное число, если его нет)
     *
     * @var int
     */
    private int $user_id;

    /**
     * Содержится название подключения из конфига, когда это требуется
     *
     * @var string
     */
    private string $connection_name;

    /**
     * Ключ кэша, для проверки на дублирование в RabbitMQ
     * Если обнаружатся 2 одинаковых ключа, второй раз отправки в очередь не будет
     *
     * @var string
     */
    private string $cache_key_queue;

    /**
     * Ключ проверки кэша на онлайн пользователя
     *
     * @var string
     */
    public const PREFIX_ONLINE = 'last_online.';

    /**
     * Создает новый экземпляр класса.
     *
     * @param string $jobClass Имя класса задания.
     * @param mixed $data Данные, которые будут переданы в задание.
     * @param string $connection Название подключения очереди.
     */
    public function __construct(string $jobClass, $data, string $connection)
    {
        // Установить класс работы
        $this->setJobClass($jobClass);
        // Установите данные
        $this->setData($data);
        // Установить идентификатор пользователя
        $this->setUserId($data);
        // Установить путь до файла
        $this->setPath($data);
        // Установите UUID
        $this->setUuid();
        // Установите название подключения очереди
        $this->setConnectionName($connection);
    }

    /**
     * Создайте новое задание и поместите его в очередь.
     *
     * @param string $jobClass Название класса работы.
     * @param mixed $data Данные, которые будут переданы в задание.
     * @param string $priority Приоритет задания (по умолчанию: «по умолчанию»).
     * @param string $connection Название подключения из конфига queue
     *
     * @return false|string Возвращает false, если тип задания не существует, в противном случае возвращает результат
     *     помещения задания в очередь.
     */
    public static function create(string $jobClass, $data, string $priority = 'default', string $connection = '')
    {
        try {
            // Создайте новый экземпляр задания
            $self = new self($jobClass, $data, $connection);
            // Установить приоритет
            $self->setPriority($priority);
            // Установите тип вызываемой джобы
            $self->setJobType();
            // Установите ключ кэша
            $self->setCacheKeyQueue();

            // Проверьте, существует ли тип задания
            // Поместите задание в очередь и верните результат
            return $self->existsTypeJob() ? $self->push() : false;
        } catch (Exception $e) {
            LoggerHelper::getLogger('create-jobs-' . __FUNCTION__)->error($e);
            return false;
        }
    }

    /**
     * Помещает задание в очередь.
     *
     * @return false|string UUID отправленного задания в случае успеха, в противном случае — false.
     */
    public function push()
    {
        // Поместите задание в очередь с указанным приоритетом и данными.
        $queue = Queue::connection($this->getConnectionName())
            ->push($this->getJobClass(), $this->addDataParams(), $this->getPriority());

        // Если задание было успешно отправлено, создайте запись в журнале и верните UUID.
        if ($queue) {
            $this->createLogParse();
            return $this->getUuid();
        }

        // Если задание не удалось отправить, верните false
        return false;
    }

    /**
     * Добавляет параметры данных в существующий массив данных.
     *
     * Этот метод удаляет все ключи из существующего массива данных перед добавлением ключей «cache_key» и «uuid».
     * «Cache_key» получается из метода getCacheKeyQueue(), а «uuid» — из метода getUuid().
     *
     * @return array Обновленный массив данных с добавленными параметрами.
     */
    public function addDataParams(): array
    {
        // Если данные джобы это массив, сбросим ключи
        // В противном случае, нам надо сделать массив, что бы дальше дополнить данными
        $data = is_array($this->getData()) ? array_values($this->getData()) : [$this->getData()];
        // Добавьте параметр uuid
        $data[] = $this->getUuid();
        // Добавьте параметр «cache_key»
        $data['cache_key'] = $this->getCacheKeyQueue();

        return $data;
    }

    /**
     * Проверьте, существует ли тип задания.
     *
     * Этот метод проверяет, определен ли тип задания для данного класса заданий.
     * Если тип задания не определен, оно регистрирует сообщение об ошибке и возвращает false.
     * В противном случае он возвращает true.
     *
     * @return bool Возвращает true, если тип задания существует, в противном случае — false.
     */
    public function existsTypeJob(): bool
    {
        // Проверьте, определен ли тип задания
        if ($this->getJobType() === 0) {
            // Зарегистрировать сообщение об ошибке
            LoggerHelper::getLogger('add-queue-' . $this->getPriority())
                ->error('Для класса такой очереди не определен тип (' . $this->getJobClass() . ')');

            return false;
        }

        return true;
    }

    /**
     *  Создание лога парсера
     *  что бы записывать время когда пользователь создаст джобу на парс файла или токена брокера
     *
     * @return bool|LogJobParser|BaseModel
     */
    public function createLogParse()
    {
        try {
            $config = 'create-jobs.parse_jobs';
            $checkPriority = $this->getPriority() === 'parse';
            $configHas = config()->has($config);

            if ($checkPriority || ($configHas && in_array($this->getJobClass(), config($config)))) {
                return LogJobParser::create([
                    'user_id' => $this->getUserId(),
                    'job_name' => $this->getJobClass(),
                    'path_file' => $this->getPath(),
                ]);
            }

            return true;
        } catch (Exception $e) {
            LoggerHelper::getLogger(class_basename($this) . '_' . __FUNCTION__)->error($e);
            return false;
        }
    }
}