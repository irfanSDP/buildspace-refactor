<?php
namespace PCK\ExternalApplication;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use PCK\ExternalApplication\ClientModule;

class Identifier extends Model
{
    protected $table = 'external_application_identifiers';
    protected $primaryKey = ['client_module_id', 'class_name', 'internal_identifier'];
    protected $fillable = ['client_module_id', 'class_name', 'internal_identifier', 'external_identifier'];

    public $incrementing = false;

    protected function setKeysForSaveQuery(Builder $query)
    {
        $keys = $this->getKeyName();
        if(!is_array($keys)){
            return parent::setKeysForSaveQuery($query);
        }

        foreach($keys as $keyName){
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }

        return $query;
    }

    protected function getKeyForSaveQuery($keyName = null)
    {
        if(is_null($keyName)){
            $keyName = $this->getKeyName();
        }

        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }

        return $this->getAttribute($keyName);
    }

    public function clientModule()
    {
        return $this->belongsTo(ClientModule::class, 'client_module_id');
    }
}