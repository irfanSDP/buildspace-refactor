<?php
namespace PCK\ExternalApplication;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ExternalApplication\ClientModule;
use PCK\ExternalApplication\Identifier;

class Attribute extends Model
{
    protected $table = 'external_application_attributes';
    
    public function clientModule()
    {
        return $this->belongsTo(ClientModule::class, 'client_module_id');
    }
}