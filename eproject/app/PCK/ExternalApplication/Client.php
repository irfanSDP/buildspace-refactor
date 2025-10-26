<?php
namespace PCK\ExternalApplication;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use PCK\Users\User;
use PCK\Companies\Company;
use PCK\ContractGroupCategory\ContractGroupCategory;

use PCK\ExternalApplication\ClientModule;
use PCK\ExternalApplication\OutboundAuthorization;
use PCK\ExternalApplication\OutboundLog;

class Client extends Model
{
    protected $table = 'external_application_clients';

    protected static function boot()
    {
        parent::boot();

        self::creating(function(self $model)
        {
            $model->name  = mb_strtoupper(trim($model->name));
            $model->token = md5(uniqid($model->name, true));

            $ref = uniqid($model->name);

            $contractGroup = ContractGroupCategory::where('name', '=', ContractGroupCategory::API_DEFAULT_NAME)->first();

            if(!$contractGroup)
            {
                $contractGroup = new ContractGroupCategory;
                $contractGroup->name = ContractGroupCategory::API_DEFAULT_NAME;
                $contractGroup->editable = false;
                $contractGroup->default_buildspace_access = false;
                $contractGroup->code = uniqid(ContractGroupCategory::API_DEFAULT_NAME);
                $contractGroup->hidden = true;
                $contractGroup->type = ContractGroupCategory::TYPE_INTERNAL;
                $contractGroup->vendor_type = ContractGroupCategory::VENDOR_TYPE_DEFAULT;
                $contractGroup->save();
            }

            /* 
             * we use raw sql to create company to escape company boot callbacks.
             * Company for client is needed to create certain entity with a company id. This company cannot and won't be treated as normal company in eproject.
             * Same reasons for user creation in the API clients down below
             */
            $insertCompany = \DB::select("INSERT INTO companies
                (name, address, main_contact, email, telephone_number, reference_no, reference_id, contract_group_category_id, confirmed, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id, reference_no", [
                $model->name,
                "-",
                "-",
                "-",
                "-",
                $ref,
                str_random(Company::REFERENCE_ID_LENGTH),
                $contractGroup->id,
                true,
                date("Y-m-d H:i:s"),
                date("Y-m-d H:i:s")
            ]);

            if(!empty($insertCompany))
            {
                $company = $insertCompany[0];

                $insertUser = \DB::select("INSERT INTO users
                    (name, contact_number, username, email, password, confirmation_code, confirmed, company_id, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id", [
                    $model->name,
                    "-",
                    mb_strtolower($model->name),
                    mb_strtolower($model->name),
                    \Hash::make($model->token),
                    $ref,
                    true,
                    $company->id,
                    date("Y-m-d H:i:s"),
                    date("Y-m-d H:i:s")
                ]);

                if(!empty($insertUser))
                {
                    $user = $insertUser[0];

                    $model->user_id = $user->id;
                }
            }
        });

        self::deleting(function(self $model)
        {
            if(!$model->canBeDeleted())
            {
                throw new \Exception($model->name.' cannot be deleted');
            }

            foreach($model->clientModules as $clientModule)
            {
                $clientModule->delete();
            }
        });

        self::deleted(function(self $model)
        {
            $company = Company::find($model->user->company_id);
            $model->user->delete();
            $company->delete();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function clientModules()
    {
        return $this->hasMany(ClientModule::class, 'client_id');
    }

    public function outboundAuthorization()
    {
        return $this->hasOne(OutboundAuthorization::class, 'client_id');
    }

    public function outboundLogs()
    {
        return $this->hasMany(OutboundLog::class, 'client_id');
    }

    public function createByModule($moduleName, array $data)
    {
        $clientModule = ClientModule::where("client_id", "=", $this->id)
            ->where('module', "=", $moduleName)
            ->first();
        
        if($clientModule)
        {
            return $clientModule->createEntity($data);
        }
    }

    public function canBeDeleted()
    {
        $count = ClientModule::join('external_application_identifiers', 'external_application_identifiers.client_module_id', '=', 'external_application_client_modules.id')
        ->where("external_application_client_modules.client_id", "=", $this->id)
        ->count();

        return (!$count);
    }

    public function updateByModule($moduleName, $extAppId, array $data)
    {
        $clientModule = ClientModule::where("client_id", "=", $this->id)
            ->where('module', "=", $moduleName)
            ->first();
        
        if($clientModule)
        {
            return $clientModule->updateEntity($extAppId, $data);
        }
    }

    public function retrieveByModule($moduleName, $extAppId)
    {
        $clientModule = ClientModule::where("client_id", "=", $this->id)
            ->where('module', "=", $moduleName)
            ->first();
        
        if($clientModule)
        {
            return $clientModule->retrieveEntity($extAppId);
        }
    }

    public function deleteByModule($moduleName, $extAppId)
    {
        $clientModule = ClientModule::where("client_id", "=", $this->id)
            ->where('module', "=", $moduleName)
            ->first();
        
        if($clientModule)
        {
            $clientModule->deleteEntity($extAppId);
        }
    }

    public function listByModule($moduleName, Request $request)
    {
        $clientModule = ClientModule::where("client_id", "=", $this->id)
            ->where('module', "=", $moduleName)
            ->first();
        
        if($clientModule)
        {
            return $clientModule->listEntities($request);
        }
    }

    public static function validate($token, $routeModuleName)
    {
        if(!array_key_exists($routeModuleName, ClientModule::API_ROUTES))
        {
            throw new NotFoundHttpException('Invalid uri');
        }

        $client = Client::where('token', '=', $token)->first();

        if(!$client)
        {
            throw new UnauthorizedHttpException('Bearer', 'Invalid token');
        }

        return $client;
    }
}