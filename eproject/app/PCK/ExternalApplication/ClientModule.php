<?php
namespace PCK\ExternalApplication;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ExternalApplication\Client;
use PCK\ExternalApplication\Attribute;
use PCK\ExternalApplication\Identifier;

class ClientModule extends Model
{
    protected $table = 'external_application_client_modules';

    const MODULE_SUBSIDIARY = 'Subsidiary';
    const MODULE_AWARDED_CONSULTANT = 'AwardedConsultant';
    const MODULE_AWARDED_CONTRACTOR = 'AwardedContractor';
    const MODULE_CONTRACTOR_VARIATION_ORDER = 'ContractorVariationOrder';
    const MODULE_PROGRESS_CLAIM = 'ProgressClaim';

    const DOWNSTREAM_PERMISSION_ALL = 1;
    const DOWNSTREAM_PERMISSION_CLIENT = 2;
    const DOWNSTREAM_PERMISSION_DISABLED = 4;

    const DOWNSTREAM_PERMISSION_ALL_TXT = "ALL";
    const DOWNSTREAM_PERMISSION_CLIENT_TXT = "CLIENT";
    const DOWNSTREAM_PERMISSION_DISABLED_TXT = "DISABLED";

    const OUTBOUND_STATUS_DISABLED = 1;
    const OUTBOUND_STATUS_ENABLED = 2;

    const OUTBOUND_STATUS_DISABLED_TXT = 'DISABLED';
    const OUTBOUND_STATUS_ENABLED_TXT = 'ENABLED';

    const API_ROUTES = [
        'subsidiaries' => self::MODULE_SUBSIDIARY,
        'awarded-consultant' => self::MODULE_AWARDED_CONSULTANT,
        'awarded-contractor' => self::MODULE_AWARDED_CONTRACTOR,
        'contractor-variation-order' => self::MODULE_CONTRACTOR_VARIATION_ORDER,
        'progress-claim' => self::MODULE_PROGRESS_CLAIM,
    ];

    protected static function boot()
    {
        parent::boot();

        self::deleting(function(self $model)
        {
            if(!$model->canBeDeleted())
            {
                throw new \Exception('Client module with id '.$model->id.' cannot be deleted');
            }

            $model->attributes()->delete();

            $model->identifiers()->delete();
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function attributes()
    {
        return $this->hasMany(Attribute::class, 'client_module_id')->orderBy('internal_attribute', 'asc');
    }

    public function identifiers()
    {
        return $this->hasMany(Identifier::class, 'client_module_id')->orderBy('internal_identifier', 'asc');
    }

    public function storeAttributes(array $inputs)
    {
        $internalAttributes = call_user_func('PCK\\ExternalApplication\\Module\\'.$this->module.'::getInternalAttributes');

        $attributes = [];

        $this->attributes()->delete();

        foreach($inputs as $key => $val)
        {
            if(strlen($val))
            {
                $attribute = new Attribute;
                $attribute->client_module_id = $this->id;
                $attribute->internal_attribute = $key;
                $attribute->external_attribute = $val;
                $attribute->is_identifier = (array_key_exists($key, $internalAttributes) && array_key_exists('is_identifier', $internalAttributes[$key]) && $internalAttributes[$key]['is_identifier']);

                $attribute->save();
            }
        }
    }

    public function canBeDeleted()
    {
        return (!$this->identifiers->count());
    }

    public function getAttributeByInternalAttribute(int $internalAttribute)
    {
        return Attribute::where('client_module_id', '=', $this->id)
        ->where('internal_attribute', '=', $internalAttribute)
        ->first();
    }

    public function createEntity(array $data)
    {
        $moduleName = "PCK\\ExternalApplication\\Module\\".$this->module;
        $module = new $moduleName($this);

        return $module->create($data);
    }

    public function updateEntity($extIdentifier, array $data)
    {
        $moduleName = "PCK\\ExternalApplication\\Module\\".$this->module;
        $module = new $moduleName($this);
        
        return $module->update($extIdentifier, $data);
    }

    public function retrieveEntity($extIdentifier)
    {
        $moduleName = "PCK\\ExternalApplication\\Module\\".$this->module;
        $module = new $moduleName($this);
        
        return $module->retrieve($extIdentifier);
    }

    public function deleteEntity($extIdentifier)
    {
        $moduleName = "PCK\\ExternalApplication\\Module\\".$this->module;
        $module = new $moduleName($this);
        
        $module->delete($extIdentifier);
    }

    public function listEntities(Request $request)
    {
        $moduleName = "PCK\\ExternalApplication\\Module\\".$this->module;
        $module = new $moduleName($this);
        
        return $module->list($request);
    }

    public function getCreatedRecords(Request $request)
    {
        $moduleName = "PCK\\ExternalApplication\\Module\\".$this->module;
        $module = new $moduleName($this);
        
        return $module->createdRecords($request);
    }

    public function getOutboundLogs(Request $request)
    {
        $moduleName = "PCK\\ExternalApplication\\Module\\".$this->module;
        $module = new $moduleName($this);
        
        return $module->outboundLogs($request);
    }
}