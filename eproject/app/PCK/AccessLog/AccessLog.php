<?php namespace PCK\AccessLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use PCK\Users\User;

class AccessLog extends Model
{
    protected $table = 'access_log';

    public $timestamps = false;

    protected $fillable = [
        'ip_address',
        'user_agent',
        'user_id',
        'http_method',
        'url',
        'url_path',
        'params',
        'created_at',
    ];

    protected static $ignoredHttpMethods = ['GET'];

    public static function log(Request $request)
    {
        if(in_array(strtoupper($request->method()), self::$ignoredHttpMethods)) return;

        self::create([
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->header('User-Agent'),
            'user_id'      => \Auth::id(),
            'http_method'  => $request->method(),
            'url'          => $request->fullUrl(),
            'url_path'     => $request->path(),
            'params'       => json_encode($request->all()),
            'created_at'   => \Carbon\Carbon::now(),
        ]);
    }
}