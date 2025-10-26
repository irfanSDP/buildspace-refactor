<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use PCK\AuthenticationLog\AuthenticationLog;
use PCK\Users\User;
use PCK\Helpers\StringOperations;

use Carbon\Carbon;

class AuthenticationLogController extends \BaseController
{
    public function index()
    {
        return View::make('authentication_log.index');
    }

    public function list()
    {
        $request = Request::instance();

        $limit = Input::get('size', 1);
        $page = Input::get('page', 1);

        $model = AuthenticationLog::select("authentication_logs.id AS id", "authentication_logs.ip_address", "authentication_logs.user_agent",
        "authentication_logs.login_at", "authentication_logs.logout_at", "users.id AS user_id", "users.name", "users.username")
        ->join('users', 'authentication_logs.user_id', '=', 'users.id');

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
                }
            }
        }

        $model->whereNotNull('authentication_logs.login_at')->orderBy('authentication_logs.login_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'         => $record->id,
                'counter'    => $counter,
                'name'       => mb_strtoupper($record->name),
                'username'   => $record->username,
                'ip'         => $record->ip_address,
                'user_agent' => $record->platform()." (".$record->browser()." ".$record->device().") - ".$record->user_agent,
                'login_at'   => $record->login_at ? date('d/m/Y H:i:s', strtotime($record->login_at)) : "",
                'logout_at'  => $record->logout_at ? date('d/m/Y H:i:s', strtotime($record->logout_at)) : ""
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function exportExcel()
    {
        $request = Request::instance();

        $model = AuthenticationLog::select("authentication_logs.id AS id", "authentication_logs.ip_address", "authentication_logs.user_agent",
        "authentication_logs.login_at", "authentication_logs.logout_at", "users.id AS user_id", "users.name", "users.username")
        ->join('users', 'authentication_logs.user_id', '=', 'users.id');

        if(Input::has('username'))
        {
            $val = trim(Input::get('username'));
            if(strlen($val) > 0)
            {
                $model->where('users.username', 'ILIKE', '%'.$val.'%');
            }
        }

        if(Input::has('name'))
        {
            $val = trim(Input::get('name'));
            if(strlen($val) > 0)
            {
                $model->where('users.name', 'ILIKE', '%'.$val.'%');
            }
        }

        $records = $model->whereNotNull('authentication_logs.login_at')->orderBy('authentication_logs.login_at', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator("Buildspace");

        $activeSheet = $spreadsheet->getActiveSheet();
        $activeSheet->setTitle("User Authentication Logs");

        $activeSheet->setAutoFilter('A1:F1');

        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'ffffff']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '187bcd']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER
            ]
        ];

        $headers = [
            'username'   => "Username",
            'name'       => "Name",
            'ip_address' => "IP Address",
            'user_agent' => "User Agent",
            'login_at'   => "Login At",
            'logout_at'  => "Logout At"
        ];

        $headerCount = 1;
        foreach($headers as $key => $val)
        {
            $cell = StringOperations::numberToAlphabet($headerCount)."1";
            $activeSheet->setCellValue($cell, $val);
            $activeSheet->getStyle($cell)->applyFromArray($headerStyle);

            $activeSheet->getColumnDimension(StringOperations::numberToAlphabet($headerCount))->setAutoSize(true);
            
            $headerCount++;
        }
        
        $data = [];
        foreach($records as $record)
        {
            $data[] = [
                $record->username,
                mb_strtoupper($record->name),
                $record->ip_address,
                $record->platform()." (".$record->browser()." ".$record->device().") - ".$record->user_agent,
                ($record->login_at) ? date('d/m/Y H:i:s', strtotime($record->login_at)) : null,
                ($record->logout_at) ? date('d/m/Y H:i:s', strtotime($record->logout_at)) : null
            ];
        }

        $activeSheet->fromArray($data, null, 'A2');

        $writer = new Xlsx($spreadsheet);

        $filepath = \PCK\Helpers\Files::getTmpFileUri();

        $writer->save($filepath);

        $filename = 'User_Authentication_Logs-'.date("dmYHis");

        return \PCK\Helpers\Files::download($filepath, "{$filename}.".\PCK\Helpers\Files::EXTENSION_EXCEL);
    }
}