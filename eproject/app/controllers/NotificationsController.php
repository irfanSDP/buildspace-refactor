<?php

use PCK\Notifications\NotificationRepository;

class NotificationsController extends \BaseController {

	private $notificationRepo;

	public function __construct(NotificationRepository $notificationRepo)
	{
		$this->notificationRepo = $notificationRepo;
	}

	/**
	 * Display a listing of the resource.
	 * GET /notifications
	 *
	 * @return Response
	 */
	public function index()
	{
		$user = \Confide::user();

		$notifications = $this->notificationRepo->getAllNotificationsByDates($user);

		// will mark all notifications that have been read
		$this->notificationRepo->markReadAllNotifications($user);

		return View::make('notifications.index', compact('user', 'notifications'));
	}

}