<?php
use Carbon\Carbon;

use PCK\Users\User;

use PCK\Buildspace\AccountGroup;
use PCK\Buildspace\AccountCode;

use PCK\Forms\Buildspace\AccountGroupForm;
use PCK\Forms\Buildspace\AccountCodeForm;

class AccountGroupController extends \BaseController
{
    private $accountGroupForm;
    private $accountCodeForm;

    public function __construct(AccountGroupForm $accountGroupForm, AccountCodeForm $accountCodeForm)
    {
        $this->accountGroupForm = $accountGroupForm;
        $this->accountCodeForm = $accountCodeForm;
    }

    public function index()
    {
        $user = \Confide::user();

        $accountCodeTypes = [
            AccountCode::ACCOUNT_TYPE_PIV => "PIV",
            AccountCode::ACCOUNT_TYPE_PCN => "PCN",
            AccountCode::ACCOUNT_TYPE_PDN => "PDN"
        ];

        $accountGroups = AccountGroup::orderBy('priority', 'asc')->get();

        return View::make('account_groups.index', compact('user', 'accountCodeTypes', 'accountGroups'));
    }

    public function accountGroupInfo($id)
    {
        $accountGroup = AccountGroup::findOrFail((int)$id);

        return \Response::json([
            'id' => $accountGroup->id,
            'name' => $accountGroup->name
        ]);
    }

    public function accountGroupStore()
    {
        $input = \Input::all();

        $data = [];

        try
        {
            $this->accountGroupForm->validate($input);

            $user = \Confide::user();

            $accountGroup = AccountGroup::find($input['id']);

            if(!$accountGroup)
            {
                $lastRecord = AccountGroup::selectRaw('MAX(priority) as max_priority')->whereNull("deleted_at")
                ->first();

                $accountGroup = new AccountGroup();

                $accountGroup->priority   = ($lastRecord) ? $lastRecord->max_priority+1 : 0;
                $accountGroup->created_by = ($user->getBsUser()) ? $user->getBsUser()->id : null;
                $accountGroup->created_at = date('Y-m-d H:i:s');
            }

            $accountGroup->name       = trim($input['name']);
            $accountGroup->updated_by = ($user->getBsUser()) ? $user->getBsUser()->id : null;

            $accountGroup->save();

            $data = [
                'status' => 'success',
                'account_group' => [
                    'name'    => $accountGroup->name
                ]
            ];
        }
        catch(\Laracasts\Validation\FormValidationException $e)
        {
            $errors = [];
            foreach($e->getErrors()->getMessages() as $key => $msg)
            {
                $errors[$key] = $msg[0];
            }

            $data = [
                'status' => 'error',
                'errors' => $errors
            ];
        }
        catch(\Exception $e)
        {
            $data = [
                'status' => 'error',
                'errors' => [$e->getMessage()]
            ];
        }

        return \Response::json($data);
    }

    public function accountCodeStore($accountGroupId)
    {
        $accountGroup = AccountGroup::findOrFail((int)$accountGroupId);

        $input = \Input::all();

        $data = [];

        try
        {
            $this->accountCodeForm->validate($input);

            $user = \Confide::user();

            $accountCode = AccountCode::find($input['id']);

            if(!$accountCode)
            {
                $lastRecord = AccountCode::selectRaw('MAX(priority) as max_priority')
                ->where('account_group_id', $accountGroup->id)
                ->whereNull("deleted_at")
                ->first();

                $accountCode = new AccountCode();

                $accountCode->account_group_id = $accountGroup->id;
                $accountCode->priority         = ($lastRecord) ? $lastRecord->max_priority+1 : 0;
                $accountCode->created_by       = ($user->getBsUser()) ? $user->getBsUser()->id : null;
                $accountCode->created_at       = date('Y-m-d H:i:s');
            }

            $accountCode->code        = trim($input['code']);
            $accountCode->description = trim($input['description']);
            $accountCode->tax_code    = trim($input['tax_code']);
            $accountCode->type        = (int)$input['type'];
            $accountCode->updated_by  = ($user->getBsUser()) ? $user->getBsUser()->id : null;

            $accountCode->save();

            $data = [
                'status' => 'success',
                'account_code' => [
                    'code'     => $accountCode->code,
                    'tax_code' => $accountCode->tax_code,
                    'type'     => $accountCode->code
                ]
            ];
        }
        catch(\Laracasts\Validation\FormValidationException $e)
        {
            $errors = [];
            foreach($e->getErrors()->getMessages() as $key => $msg)
            {
                $errors[$key] = $msg[0];
            }

            $data = [
                'status' => 'error',
                'errors' => $errors
            ];
        }
        catch(\Exception $e)
        {
            $data = [
                'status' => 'error',
                'errors' => [$e->getMessage()]
            ];
        }

        return \Response::json($data);
    }

    public function accountCodeList($accountGroupId)
    {
        $accountGroup = AccountGroup::findOrFail($accountGroupId);
        $user = \Confide::user();

        $request = Request::instance();

        $limit = Input::get('size', 1);
        $page = Input::get('page', 1);

        $model = AccountCode::select("id", "code", "description", "tax_code", "type")
        ->where('account_group_id', '=', $accountGroup->id)
        ->whereNull('deleted_at');

        //tabulator filters
        if(Input::has('filters'))
        {
            foreach(Input::get('filters', []) as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'code':
                        if(strlen($val) > 0)
                        {
                            $model->where('code', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                    case 'tax_code':
                        if(strlen($val) > 0)
                        {
                            $model->where('tax_code', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('priority', 'asc');

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
                'code'        => $record->code,
                'description' => $record->description,
                'tax_code'    => $record->tax_code,
                'type'        => $record->type,
                'type_txt'    => AccountCode::getTypeText($record->type),
                'route:delete' => route('account.group.account.codes.delete', [$accountGroup->id, $record->id])
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function accountCodeInfo($accountGroupId, $id)
    {
        $accountGroup = AccountGroup::findOrFail($accountGroupId);
        $accountCode = AccountCode::where('account_group_id', '=', $accountGroup->id)
        ->where('id', $id)
        ->whereNull('deleted_at')
        ->first();

        return \Response::json([
            'id'          => ($accountCode) ? $accountCode->id : '-1',
            'code'        => ($accountCode) ? $accountCode->code : '',
            'description' => ($accountCode) ? $accountCode->description : '',
            'tax_code'    => ($accountCode) ? $accountCode->tax_code : '',
            'type'        => ($accountCode) ? $accountCode->type : ''
        ]);
    }

    public function accountCodeDelete($accountGroupId, $id)
    {
        $accountGroup = AccountGroup::findOrFail($accountGroupId);
        $accountCode = AccountCode::where('account_group_id', '=', $accountGroup->id)
        ->where('id', $id)
        ->whereNull('deleted_at')
        ->first();

        try
        {
            if($accountCode)
            {
                $user = \Confide::user();
                $id   = $accountCode->id;
                $code = $accountCode->code;

                $accountCode->delete();

                \Log::info("Delete Account Code [id: {$id}]][code: {$code}][user id:{$user->id}]");
            }
        }
        catch(\Exception $e)
        {
            \Flash::error($e->getMessage());
        }
        
        return \Redirect::route('account.group.index');
    }
}