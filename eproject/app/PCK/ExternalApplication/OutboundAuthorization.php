<?php
namespace PCK\ExternalApplication;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use PCK\ExternalApplication\Client;

class OutboundAuthorization extends Model
{
    protected $table = 'external_application_client_outbound_authorizations';
    protected $primaryKey = 'client_id';
    protected $fillable = ['client_id', 'url', 'type', 'options'];

    public $incrementing = false;

    const TYPE_BEARER_TOKEN = 1;
    const TYPE_OAUTH_TWO = 2;

    const TYPE_BEARER_TOKEN_TXT = 'Bearer Token';
    const TYPE_OAUTH_TWO_TXT = 'OAuth 2.0';

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

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function optionsUpdate(array $data)
    {
        $options = [];

        $headerParams = explode(',', $data['header_params']);

        if(is_array($headerParams) && !empty($headerParams))
        {
            $params = [];
            foreach($headerParams as $headerParam)
            {
                $param = explode(':', $headerParam);
                if(is_array($param) && count($param) == 2)
                    $params[$param[0]] = trim($param[1]);
            }

            if($params)
                $options['header_params'] = json_encode($params);
        }

        switch($this->type)
        {
            case self::TYPE_BEARER_TOKEN:
                $options['token'] = trim($data['token']);
                break;
            case self::TYPE_OAUTH_TWO:
                $options['header_prefix'] = trim($data['header_prefix']);
                $options['access_token_url'] = trim($data['access_token_url']);
                $options['client_id'] = trim($data['client_id']);
                $options['client_secret'] = trim($data['client_secret']);
                $options['grant_type'] = trim($data['grant_type']);
                $options['scope'] = trim($data['scope']);
                break;
        }

        $this->options = json_encode($options);
    }
}