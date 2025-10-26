<?php namespace PCK\Notifications;

use Carbon\Carbon;
use PCK\Users\User;
use Fenos\Notifynder\Models\Notification;

class NotificationRepository {

	protected $notification;

	public function __construct(Notification $notification)
	{
		$this->notification = $notification;
	}

	public function getAllNotificationsByDates(User $user, $limit = 30)
	{
		$notifications = $this->notification
			->with('from')
			->wherePolymorphic('to_id', 'to_type', $user->id, null)
			->orderBy('id', 'DESC')
			->paginate($limit);

		return $this->reorderNotificationsData($notifications);
	}

	public function markReadAllNotifications(User $user)
	{
		return \Notifynder::readAll($user->id);
	}

	private function reorderNotificationsData($notifications)
	{
		$data = array();

		foreach ( $notifications as $notification )
		{
			$date = Carbon::parse($notification['created_at'])->toDateString();

			$data[$date][] = $notification;

			unset( $date );
		}

		$notifications['data'] = $data;

		return $notifications;
	}

}