<?php namespace PCK\Helpers;

use PCK\Users\User;

class Queue {

    public static function mail($queue, $view, User $recipient, $subject, $viewData = array())
    {
        \Queue::push(function () use ($view, $recipient, $subject, $viewData)
        {
            \Mail::send(
                $view,
                $viewData,
                function ($message) use ($subject, $recipient)
                {
                    $message->to($recipient->email, $recipient->name)
                        ->subject($subject);
                }
            );
        }, null, $queue);
    }
}