<?php
namespace PCK\ExternalApplication;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

use PCK\ExternalApplication\Client;
use PCK\ExternalApplication\ClientModule;
use PCK\ExternalApplication\OutboundAuthorization;

class OutboundLog extends Model
{
    protected $table = 'external_application_client_outbound_logs';
    protected $fillable = ['client_id', 'module', 'data', 'status_code', 'response_contents'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function resync()
    {
        $reflect = new \ReflectionClass($this->module);
        $clientModule = ClientModule::where('client_id', '=', $this->client_id)
        ->where('module', '=', $reflect->getShortName())
        ->first();

        $className = $this->module;
        $module = new $className($clientModule);

        $outboundAuth = $this->client->outboundAuthorization;
        
        if($outboundAuth)
        {
            switch($outboundAuth->type)
            {
                case OutboundAuthorization::TYPE_OAUTH_TWO:
                    $module->oAuthTwoPost(json_decode($this->data, true));
                    break;
                default:
                    break;
            }
        }
    }
}