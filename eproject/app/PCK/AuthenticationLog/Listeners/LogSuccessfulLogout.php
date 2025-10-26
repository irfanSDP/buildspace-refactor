<?php

namespace PCK\AuthenticationLog\Listeners;

use Illuminate\Http\Request;
use Carbon\Carbon;
use PCK\AuthenticationLog\AuthenticationLog;
use PCK\Users\User;

class LogSuccessfulLogout
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
    
    public function handle(User $user=null)
    {
        if ($user)
        {
            $ip = $this->request->ip();
            $userAgent = $this->request->header('User-Agent');
            $authenticationLog = $user->authenticationLogs()
                ->whereIpAddress($ip)
                ->whereUserAgent($userAgent)
                ->whereNull('authentication_logs.logout_at')
                ->orderBy('login_at', 'desc')
                ->first();

            if (! $authenticationLog)
            {
                $authenticationLog = new AuthenticationLog([
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'login_at'   => Carbon::now()
                ]);
            }

            $authenticationLog->logout_at = Carbon::now();

            $user->authenticationLogs()->save($authenticationLog);
        }
    }
}