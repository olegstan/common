<?php

namespace Common\Controllers\Common\ExtendProfileController;
use App\Api\V1\Controllers\Common\ProfileController;
use Common\Models\Users\Collective\CollectiveGroup;
use Auth;
use Common\Models\BaseModel;
use Common\Models\Users\Notification\UserNotification;
use Common\Models\Users\Notification\UserNotificationRelation;
use Common\Models\Users\User;
use Exception;
use LaravelRest\Http\Response\Response;

class CollectGroupController extends ProfileController
{
    public const MESSAGE_NOTIFICATION = 'Уведомление о вступлении в группу';

    protected User $user;
    protected User $invite_user;
    protected int $type;

    /**
     * @param $request
     * @return Response
     * @throws Exception
     */
    public static function addAssistUser($request): Response
    {
        $selfClass = self::selfCreate($request);
        $selfClass->type = CollectiveGroup::ASSISTANT;

        if (!$selfClass->setData($request)) {
            return $selfClass->error('Такого пользователя не существует');
        }

        $invite = $selfClass->createCollectGroup();
        return $selfClass->success('Пользователь добавлен');
    }

    /**
     * Подтверждение приглашения в группу
     *
     * @param $request
     * @return Response
     * @throws Exception
     */
    public static function acceptInviteGroup($request): Response
    {
        $selfClass = self::selfCreate($request);

        $notification = UserNotificationRelation::find($request->input('notification_id'));
        $notification->is_confirmed = true;

        return $selfClass->success('Приглашение принято');
    }

    /**
     * Отклонение приглашения в группу
     *
     * @param $request
     * @return Response
     * @throws Exception
     */
    public static function declineInviteGroup($request): Response
    {
        $selfClass = self::selfCreate($request);

        $notification = UserNotificationRelation::find($request->input('notification_id'));
        CollectiveGroup::where('id', $notification->post_id)->delete();

        return $selfClass->success('Вы отказались от приглашения');
    }

    /**
     * Отправка приглашения в группу
     *
     * @param $request
     * @return Response
     * @throws Exception
     */
    public static function sendInviteGroup($request): Response
    {
        $selfClass = self::selfCreate($request);
        $selfClass->type = CollectiveGroup::FAMILY;

        if (!$selfClass->setData($request)) {
            return $selfClass->error('Такого пользователя не существует');
        }

        $invite = $selfClass->createCollectGroup();
        $notification = $selfClass->createNotificationInvite();
        $relation = $selfClass->createNotificationRelationInvite($notification, $invite);

        if ($relation->is_confirmed) {
            return $selfClass->success('Приглашение уже было отправлено');
        }

        return $selfClass->success('Приглашение отправлено');
    }

    /**
     * Создание записи в коллективной группе
     *
     * @return BaseModel
     */
    public function createCollectGroup()
    {
        $userId = $this->user->id;
        $inviteUserId = $this->invite_user->id;
        $type = $this->type;

        return CollectiveGroup::where('user_id', $userId)
            ->where('union_user_id', $inviteUserId)
            ->where('type_id', $type)
            ->firstOrCreate([
                'user_id' => $userId,
                'union_user_id' => $inviteUserId,
                'type_id' => $type,
            ]);
    }

    /**
     * Создание уведомления о приглашении
     *
     * @return BaseModel|UserNotification
     */
    public function createNotificationInvite()
    {
        return UserNotification::where('content', self::MESSAGE_NOTIFICATION)
            ->where('user_id', $this->user->id)
            ->where('status', UserNotification::CREATED)
            ->where('action_id', UserNotification::INVITE_GROUP)
            ->firstOrCreate([
                'content' => self::MESSAGE_NOTIFICATION,
                'user_id' => $this->user->id,
                'status' => UserNotification::CREATED,
                'action_id' => UserNotification::INVITE_GROUP,
            ]);
    }

    /**
     * Создание связи уведомления и записи в коллективной группе
     *
     * @param UserNotification $notification
     * @param BaseModel|CollectiveGroup $invite
     * @return BaseModel|UserNotificationRelation
     */
    public function createNotificationRelationInvite(UserNotification $notification, CollectiveGroup $invite)
    {
        return UserNotificationRelation::where('notification_id', $notification->id)
            ->where('post_type', $invite->getMorphClass())
            ->where('post_id', $invite->getId())
            ->firstOrCreate([
                'notification_id' => $notification->id,
                'post_type' => $invite->getMorphClass(),
                'post_id' => $invite->getId(),
            ]);
    }

    /**
     * Установка данных пользователя
     *
     * @param $request
     * @return bool
     */
    public function setData($request): bool
    {
        $inviteUser = User::firstWhere('email', $request->input('email'));

        if (!$inviteUser) {
            return false;
        }

        $this->user = Auth::user();
        $this->invite_user = $inviteUser;

        return true;
    }

    /**
     * Создание экземпляра класса
     *
     * @param $request
     * @return CollectGroupController
     * @throws Exception
     */
    public static function selfCreate($request): CollectGroupController
    {
        return new CollectGroupController($request);
    }

    /**
     * Возврат ошибки
     *
     * @param $text
     * @return Response
     */
    public function error($text): Response
    {
        return $this->response()->error($text);
    }

    /**
     * Возврат успешного ответа
     *
     * @param $text
     * @return Response
     */
    public function success($text): Response
    {
        return $this->response()->success($text);
    }
}