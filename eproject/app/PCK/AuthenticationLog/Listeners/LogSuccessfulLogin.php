<?php

namespace PCK\AuthenticationLog\Listeners;

use Illuminate\Http\Request;
use Carbon\Carbon;
use PCK\AuthenticationLog\AuthenticationLog;
use PCK\Users\User;

class LogSuccessfulLogin
{
    /**
     * The request.
     *
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * Create the event listener.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
    public function handle(User $user)
    {
        $ip = $this->request->ip();
        $userAgent = $this->request->header('User-Agent');
        $known = $user->authenticationLogs()->whereIpAddress($ip)->whereUserAgent($userAgent)->first();
        $newUser = Carbon::parse($user->{$user->getCreatedAtColumn()})->diffInMinutes(Carbon::now()) < 1;

        $authenticationLog = new AuthenticationLog([
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'login_at' => Carbon::now(),
        ]);

        $user->authenticationLogs()->save($authenticationLog);
    }
}