<?php
namespace ExternalApplications;

use Carbon\Carbon;

use Illuminate\Support\Facades\Input;
use PCK\ExternalApplication\Client;
use PCK\ExternalApplication\ClientModule;
use PCK\ExternalApplication\OutboundAuthorization;
use PCK\ExternalApplication\OutboundLog;

use PCK\Forms\ExternalApplications\ClientForm;
use PCK\Forms\ExternalApplications\ClientModuleSettingsForm;
use PCK\Forms\ExternalApplications\OutboundAuthorizationForm;

use GuzzleHttp\Client as GuzzleClient;

class ClientController extends \BaseController
{
    private $clientForm;
    private $moduleSettingsForm;
    private $outboundAuthForm;

    public function __construct(ClientForm $clientForm, ClientModuleSettingsForm $moduleSettingsForm, OutboundAuthorizationForm $outboundAuthForm)
    {
        $this->clientForm = $clientForm;
        $this->moduleSettingsForm = $moduleSettingsForm;
        $this->outboundAuthForm = $outboundAuthForm;
    }

    public function index()
    {
        return \View::make('external_applications.clients.index');
    }

    public function list()
    {
        $request = \Request::instance();

        $limit = Input::get('size', 100);
        $page = Input::get('page', 1);

        $model = Client::select("external_application_clients.id AS id", "external_application_clients.name",
        "external_application_clients.remarks", "external_application_clients.created_at");

        if(Input::has('filters'))
        {
            foreach(Input::get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('external_application_clients.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('external_application_clients.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();
        
        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'           => $record->id,
                'counter'      => $counter,
                'name'         => trim($record->name),
                'remarks'      => trim($record->remarks),
                'created_at'   => Carbon::parse($record->created_at)->format('d/m/Y'),
                'route:show'   => route('api.v2.clients.show', [$record->id]),
                'route:delete' => route('api.v2.clients.delete', [$record->id]),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return \Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function store()
    {
        $input = \Input::all();

        $data = [];

        try
        {
            $this->clientForm->validate($input);

            $user = \Confide::user();

            $client = Client::find($input['id']);

            if(!$client)
            {
                $client = new Client();

                $client->created_by = $user->id;
            }

            $client->name       = mb_strtoupper(trim($input['name']));
            $client->remarks    = $input['remarks'];
            $client->updated_by = $user->id;

            $client->save();

            $data = [
                'status' => 'success',
                'client' => [
                    'name'    => $client->name,
                    'remarks' => $client->remarks
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

    public function show(Client $client, $module=null)
    {
        $user = \Confide::user();

        $modules = [
            ClientModule::MODULE_SUBSIDIARY,
            ClientModule::MODULE_AWARDED_CONSULTANT,
            ClientModule::MODULE_AWARDED_CONTRACTOR,
            ClientModule::MODULE_CONTRACTOR_VARIATION_ORDER,
            ClientModule::MODULE_PROGRESS_CLAIM,
        ];

        $selectedModule = null;
        $downstreamPermissions = [];
        $outboundStatuses = [
            ClientModule::OUTBOUND_STATUS_DISABLED => ClientModule::OUTBOUND_STATUS_DISABLED_TXT,
            ClientModule::OUTBOUND_STATUS_ENABLED => ClientModule::OUTBOUND_STATUS_ENABLED_TXT,
        ];
        $attributes = [];

        if($module && in_array($module, $modules))
        {
            $selectedModule = ClientModule::where("external_application_client_modules.client_id", "=", $client->id)
            ->where('external_application_client_modules.module', "=", $module)
            ->first();

            if(!$selectedModule)
            {
                $selectedModule = new ClientModule;
                $selectedModule->client_id = $client->id;
                $selectedModule->module = $module;
                $selectedModule->downstream_permission = ClientModule::DOWNSTREAM_PERMISSION_DISABLED;
                $selectedModule->save();
            }

            $attributes = call_user_func('PCK\\ExternalApplication\\Module\\'.$module.'::getInternalAttributes');

            $downstreamPermissions = [
                ClientModule::DOWNSTREAM_PERMISSION_ALL => ClientModule::DOWNSTREAM_PERMISSION_ALL_TXT,
                ClientModule::DOWNSTREAM_PERMISSION_CLIENT => ClientModule::DOWNSTREAM_PERMISSION_CLIENT_TXT,
                ClientModule::DOWNSTREAM_PERMISSION_DISABLED => ClientModule::DOWNSTREAM_PERMISSION_DISABLED_TXT
            ];
        }

        $tabView = 'module';

        return \View::make('external_applications.clients.show', compact('client', 'modules', 'selectedModule', 'downstreamPermissions', 'outboundStatuses', 'attributes', 'user', 'tabView'));
    }

    public function outboundShow(Client $client, $selectedAuthType=null)
    {
        $user = \Confide::user();

        $authTypes = [
            OutboundAuthorization::TYPE_BEARER_TOKEN => OutboundAuthorization::TYPE_BEARER_TOKEN_TXT,
            OutboundAuthorization::TYPE_OAUTH_TWO => OutboundAuthorization::TYPE_OAUTH_TWO_TXT
        ];

        $tabView = 'outbound';
        $outboundAuth = null;
        $authOptions = [];
        $grantTypes = [];

        $outboundAuth = OutboundAuthorization::find($client->id);

        $selectedAuthType = ($outboundAuth && !$selectedAuthType) ? $outboundAuth->type : $selectedAuthType;

        if($selectedAuthType)
        {
            switch($selectedAuthType)
            {
                case OutboundAuthorization::TYPE_BEARER_TOKEN:
                    if($outboundAuth && $outboundAuth->type == OutboundAuthorization::TYPE_BEARER_TOKEN)
                    {
                        $authOptions = json_decode($outboundAuth->options);
                    }
                    break;
                case OutboundAuthorization::TYPE_OAUTH_TWO:
                    if($outboundAuth && $outboundAuth->type == OutboundAuthorization::TYPE_OAUTH_TWO)
                    {
                        $authOptions = json_decode($outboundAuth->options);
                    }

                    $grantTypes = [
                        'client_credentials' => 'Client Credentials'
                    ];
                    break;
            }
        }

        return \View::make('external_applications.clients.show', compact('client', 'authTypes', 'selectedAuthType', 'outboundAuth', 'authOptions', 'grantTypes', 'user', 'tabView'));
    }

    public function outboundAuthStore(Client $client)
    {
        $input = \Input::all();
        $this->outboundAuthForm->validate($input);

        $outboundAuth = OutboundAuthorization::find($client->id);

        if(!$outboundAuth)
        {
            $outboundAuth = new OutboundAuthorization;
            $outboundAuth->client_id = $client->id;
        }

        $outboundAuth->url  = trim($input['url']);
        $outboundAuth->type = (int)$input['type'];

        $outboundAuth->optionsUpdate($input);

        $outboundAuth->save();

        return \Redirect::route('api.v2.clients.outbound.type.show', [$client->id, $outboundAuth->type]);
    }

    public function delete(Client $client)
    {
        $user = \Confide::user();
        $clientId = $client->id;
        $clientName = $client->name;

        try
        {
            $client->delete();

            \Log::info("Delete external application Client [client id: {$clientId}]][client name: {$clientName}][user id:{$user->id}]");
        }
        catch(\Exception $e)
        {
            \Flash::error($e->getMessage());
        }
        
        return \Redirect::route('api.v2.clients.index');
    }

    public function moduleSettingsStore(ClientModule $clientModule)
    {
        $input = \Input::all();

        $input['id'] = $clientModule->id;//add for form validation
        $this->moduleSettingsForm->validate($input);

        $clientModule->downstream_permission = $input['downstream_permission'];

        $clientModule->save();

        $attrPrefix = "external_attribute_";

        $attributeInputs = [];

        foreach($input as $key => $value)
        {
            if(substr($key, 0, strlen($attrPrefix)) === $attrPrefix)
            {
                $key = substr($key, strpos($key, $attrPrefix) + strlen($attrPrefix));

                $attributeInputs[$key] = $value;
            }
        }

        $clientModule->storeAttributes($attributeInputs);

        return \Redirect::route('api.v2.clients.module.show', [$clientModule->client_id, $clientModule->module]);
    }

    public function outboundModuleStore(ClientModule $clientModule)
    {
        $input = \Input::all();
        
        $clientModule->outbound_status           = (int)$input['outbound_status'];
        $clientModule->outbound_only_same_source = array_key_exists('outbound_only_same_source', $input);
        $clientModule->outbound_url_path         = ((int)$input['outbound_status'] == ClientModule::OUTBOUND_STATUS_ENABLED) ? trim($input['outbound_url_path']) : null;

        $clientModule->save();

        return \Redirect::route('api.v2.clients.module.show', [$clientModule->client_id, $clientModule->module]);
    }

    public function moduleRecords(ClientModule $clientModule)
    {
        $request = \Request::instance();

        list($totalPages, $data) = $clientModule->getCreatedRecords($request);

        return \Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function outboundModuleLogs(ClientModule $clientModule)
    {
        $request = \Request::instance();

        list($totalPages, $data) = $clientModule->getOutboundLogs($request);

        return \Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function resyncOutboundModuleLogs(ClientModule $clientModule)
    {
        $input = \Input::all();

        $outboundLog = OutboundLog::findOrFail($input['id']);

        $outboundLog->resync();

        return \Redirect::route('api.v2.clients.module.show', [$clientModule->client_id, $clientModule->module]);
    }
}