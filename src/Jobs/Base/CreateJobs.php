<?php


namespace Common\Jobs\Base;

use Common\Helpers\LoggerHelper;
use Common\Jobs\JobsEvent;
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
            // Установите ключ кэша
            $self->setCacheKeyQueue();

            // Поместите задание в очередь и верните результат
            return $self->push();
        } catch (Exception $e) {
            LoggerHelper::getLogger('create-jobs-' . __FUNCTION__)->error($e);
            return false;
        }
    }

    /**
     * @param string $jobClass
     * @param $data
     * @param string $priority
     * @param string $connection
     *
     * @return false|string
     */
    public static function createNotUniq(string $jobClass, $data, string $priority = 'default', string $connection = '')
    {
        if (!is_array($data)) {
            $data = [$data];
        }

        $data['options']['cache_check'] = false;
        return self::create($jobClass, $data, $priority, $connection);
    }

    /**
     * Помещает задание в очередь.
     *
     * @return string UUID отправленного задания в случае успеха, в противном случае — false.
     */
    public function push(): ?string
    {
        // Поместите задание в очередь с указанным приоритетом и данными.
        $queue = Queue::connection($this->getConnectionName())
            ->push($this->getJobClass(), $this->addDataParams(), $this->getPriority());

        // Если задание было успешно отправлено, создайте запись в журнале и верните UUID.
        if ($queue) {
            $this->createLogParse();
            return $this->createEvent($queue);
        }

        // Если задание не удалось отправить, верните false
        return $this->createEvent();
    }

    /**
     * Создает евент, что бы дальше по сокеты можно было кинуть fail, если потребуется
     *
     * @param string|null $jobId
     *
     * @return string
     */
    public function createEvent(?string $jobId = null): ?string
    {
        $data = $this->getData();

        if (isset($data['job_type']) && $jobId) {
            $event = JobsEvent::create($this->getUserId(), $jobId, $data['job_type']);
            $event->pending();

            return $jobId;
        }

        return $jobId;
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
        $data = $this->getData();

        // Если данные это массив, то обработаем их
        if (is_array($data)) {
            $newData = [];

            // Сохранить только ключи, которые находится в массиве с ключем options
            foreach ($data as $key => $value) {
                if (is_array($value) && $key == 'options') {
                    $newData[$key] = $value;
                } else {
                    $newData[] = $value; // Сбрасываем ключи для остальных данных
                }
            }

            $data = $newData;
        } else {
            // Если данные не массив, конвертируем их в массив
            $data = [$data];
        }

        return $this->addOptionsDataParams($data);
    }

    /**
     * Добавление необходимых опций для массива джобы
     * и перемещение в конец массива, если уже что-то было записано
     *
     * @param $data
     *
     * @return array
     */
    public function addOptionsDataParams($data): array
    {
        // Добавляем параметры в 'options'
        $data['options']['uuid'] = $this->getUuid();
        $data['options']['cache_key'] = $this->getCacheKeyQueue();
        $data['options']['cache_check'] = $data['options']['cache_check'] ?? true;

        // Перемещаем 'options' в конец массива
        $options = $data['options'];
        unset($data['options']);
        $data['options'] = $options;

        return $data;
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