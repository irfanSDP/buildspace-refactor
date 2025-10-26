<?php

use Illuminate\Support\Facades\DB;
use PCK\AccessLog\AccessLog;
use PCK\Users\User;
use Carbon\Carbon;

class AccessLogController extends \BaseController
{
    public function index()
    {
        return View::make('access_log.index');
    }

    public function list()
    {
        $limit = Input::get('size', 1);
        $page = Input::get('page', 1);

        $conditions = [];

        if(Input::has('filters'))
        {
            foreach(Input::get('filters', []) as $filters)
            {
                $val = trim($filters['value']);
                $field = trim(strtolower($filters['field']));

                switch($field)
                {
                    case 'username':
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $conditions[] = " AND u.{$field} ILIKE '%{$val}%' ";
                        }
                        break;
                    case 'ip_address':
                    case 'user_agent':
                    case 'http_method':
                        if(strlen($val) > 0)
                        {
                            $conditions[] = " AND log.{$field} ILIKE '%{$val}%' ";
                        }
                        break;
                    case 'url':
                    case 'url_path':
                        if(strlen($val) > 0)
                        {
                            foreach(explode('*', $val) as $string)
                            {
                                $conditions[] = " AND log.{$field} ILIKE '%{$string}%' ";
                            }
                        }
                        break;
                    case 'params':
                        if(strlen($val) > 0)
                        {
                            $conditions[] = " AND log.{$field}::TEXT ILIKE '%{$val}%' ";
                        }
                        break;
                    case 'created_at':
                        $timestamps = explode('~', $val);

                        if(count($timestamps) !== 2) break;

                        foreach($timestamps as $timestamp)
                        {
                            if(!strtotime($timestamp)) break 2;
                        }

                        $conditions[] = " AND log.{$field} BETWEEN '{$timestamps[0]}' AND '{$timestamps[1]}'";

                        break;
                }
            }
        }

        $conditionString = implode('', $conditions);

        $query = "WITH access_log_cte AS (
                      SELECT log.id, log.ip_address, log.user_agent, log.http_method, log.url, log.url_path, log.params, log.created_at, log.user_id, u.name, u.username 
                      FROM access_log log
                      INNER JOIN users u ON u.id = log.user_id 
                      WHERE TRUE 
                      {$conditionString}
                      ORDER BY log.created_at DESC
                  )";

        $rowCountResult = DB::select(DB::raw($query . " SELECT count(*) AS row_count FROM access_log_cte;"));
        $rowCount = $rowCountResult[0]->row_count;

        $offset = $limit * ($page - 1);

        $records = DB::select(DB::raw("{$query} SELECT * FROM access_log_cte LIMIT {$limit} OFFSET {$offset};"));

        $data = [];

        foreach($records as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'          => $record->id,
                'counter'     => $counter,
                'name'        => $record->name,
                'username'    => $record->username,
                'ip_address'  => $record->ip_address,
                'user_agent'  => $record->user_agent,
                'http_method' => $record->http_method,
                'url'         => $record->url,
                'url_path'    => $record->url_path,
                'params'      => $record->params,
                'created_at'  => Carbon::parse($record->created_at)->format(\Config::get('dates.timestamp')),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function list_old()
    {
        $request = Request::instance();

        $limit = Input::get('size', 1);
        $page = Input::get('page', 1);

        // $model = AuthenticationLog::select("authentication_logs.id AS id", "authentication_logs.ip_address", "authentication_logs.user_agent",
        // "authentication_logs.login_at", "authentication_logs.logout_at", "users.id AS user_id", "users.name", "users.username")
        // ->join('users', 'authentication_logs.user_id', '=', 'users.id');

        $model = AccessLog::select(
            "access_log.id",
            "access_log.ip_address",
            "access_log.user_agent",
            "access_log.http_method",
            "access_log.url",
            "access_log.url_path",
            "access_log.params",
            "access_log.created_at",
            "users.id AS user_id",
            "users.name",
            "users.username"
        )
        ->join('users', 'access_log.user_id', '=', 'users.id');

        if(Input::has('filters'))
        {
            foreach(Input::get('filters', []) as $filters)
            {
                $val = trim($filters['value']);
                switch(trim(strtolower($filters['field'])))
                {
                    case 'username':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.username', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'ip_address':
                        if(strlen($val) > 0)
                        {
                            $model->where('access_log.ip_address', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'user_agent':
                        if(strlen($val) > 0)
                        {
                            $model->where('access_log.user_agent', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'http_method':
                        if(strlen($val) > 0)
                        {
                            $model->where('access_log.http_method', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'url':
                        if(strlen($val) > 0)
                        {
                            foreach(explode('*', $val) as $string)
                            {
                                $model->where('access_log.url', 'ILIKE', '%'.$string.'%');
                            }
                        }
                        break;
                    case 'url_path':
                        if(strlen($val) > 0)
                        {
                            foreach(explode('*', $val) as $string)
                            {
                                $model->where('access_log.url_path', 'ILIKE', '%'.$string.'%');
                            }
                        }
                        break;
                    case 'params':
                        if(strlen($val) > 0)
                        {
                            $model->where(\DB::raw('access_log.params::text'), 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'created_at':
                        $timestamps = explode('~', $val);

                        if(count($timestamps) !== 2) break;

                        foreach($timestamps as $timestamp)
                        {
                            if(!strtotime($timestamp)) break 2;
                        }

                        $model->where(\DB::raw('access_log.created_at'), 'BETWEEN', \DB::raw("'{$timestamps[0]}' AND '{$timestamps[1]}'"));

                        break;
                }
            }
        }

        $model->orderBy('access_log.id', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'          => $record->id,
                'counter'     => $counter,
                'name'        => $record->name,
                'username'    => $record->username,
                'ip_address'  => $record->ip_address,
                'user_agent'  => $record->user_agent,
                'http_method' => $record->http_method,
                'url'         => $record->url,
                'url_path'    => $record->url_path,
                'params'      => $record->params,
                'created_at'  => Carbon::parse($record->created_at)->format(\Config::get('dates.timestamp')),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }
}