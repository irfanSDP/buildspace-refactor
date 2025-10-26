<?php
namespace PCK\ExternalApplication\Module;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use PCK\ExternalApplication\Client;
use PCK\ExternalApplication\ClientModule;
use PCK\ExternalApplication\OutboundAuthorization;
use PCK\ExternalApplication\OutboundLog;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;

class Base
{
    public $clientModule;

    const ATTRIBUTE_TYPE_STRING = 1;
    const ATTRIBUTE_TYPE_INTEGER = 2;
    const ATTRIBUTE_TYPE_DOUBLE = 4;
    const ATTRIBUTE_TYPE_BOOLEAN = 8;
    const ATTRIBUTE_TYPE_JSON = 16;

    const ATTRIBUTE_TYPE_STRING_TXT = 'String';
    const ATTRIBUTE_TYPE_INTEGER_TXT = 'Integer';
    const ATTRIBUTE_TYPE_DOUBLE_TXT = 'Double';
    const ATTRIBUTE_TYPE_BOOLEAN_TXT = 'Boolean';
    const ATTRIBUTE_TYPE_JSON_TXT = 'JSON';

    protected static $className = '';
    protected static $internalAttributes = [];
    protected $attributeMaps = [];

    public function __construct(ClientModule $clientModule)
    {
        $this->clientModule = $clientModule;

        foreach($this->clientModule->attributes as $attribute)
        {
            $this->attributeMaps[$attribute->internal_attribute] = $attribute->external_attribute;
        }
    }

    public static function getClassName()
    {
        return static::$className;
    }
    
    public static function getInternalAttributes()
    {
        return static::$internalAttributes;
    }

    public static function getAttributeTypeText($type)
    {
        switch($type)
        {
            case self::ATTRIBUTE_TYPE_STRING:
                return self::ATTRIBUTE_TYPE_STRING_TXT;
            case self::ATTRIBUTE_TYPE_INTEGER:
                return self::ATTRIBUTE_TYPE_INTEGER_TXT;
            case self::ATTRIBUTE_TYPE_DOUBLE:
                return self::ATTRIBUTE_TYPE_DOUBLE_TXT;
            case self::ATTRIBUTE_TYPE_BOOLEAN:
                return self::ATTRIBUTE_TYPE_BOOLEAN_TXT;
            case self::ATTRIBUTE_TYPE_JSON:
                return self::ATTRIBUTE_TYPE_JSON_TXT;
            default:
                throw new \Exception('invalid attribute type');
        }
    }

    public function create(array $data)
    {
        throw new UnauthorizedHttpException('Bearer', 'Not authorized to perform this action');
    }

    public function update($id, array $data)
    {
        throw new UnauthorizedHttpException('Bearer', 'Not authorized to perform this action');
    }

    public function retrieve($id)
    {
        throw new UnauthorizedHttpException('Bearer', 'Not authorized to perform this action');
    }

    public function delete($id)
    {
        throw new UnauthorizedHttpException('Bearer', 'Not authorized to perform this action');
    }

    public function list(Request $request)
    {
        throw new UnauthorizedHttpException('Bearer', 'Not authorized to perform this action');
    }

    public function createdRecords(Request $request)
    {
        $totalPages = 0;
        $data = [];
        
        return [$totalPages, $data];
    }

    public function outboundLogs(Request $request)
    {
        $totalPages = 0;
        $data = [];
        
        return [$totalPages, $data];
    }

    public function oAuthTwoPost(array $data)
    {
        $outboundAuth = OutboundAuthorization::find($this->clientModule->client_id);

        if(!$outboundAuth || $outboundAuth->type != OutboundAuthorization::TYPE_OAUTH_TWO)
        {
            return false;
        }

        $options = json_decode($outboundAuth->options, true);

        $parsedURL = parse_url($options['access_token_url']);

        $log = [
            'data' => $data,
            'status_code' => 'UNKNOWN',
            'response_contents' => ''
        ];

        try
        {
            $client = new GuzzleClient([
                'verify'   => false,
                'base_uri' => $parsedURL['scheme'].'://'.$parsedURL['host']
            ]);

            $response = $client->post($parsedURL['path'], [
                'multipart' => [[
                    'name'     => 'client_id',
                    'contents' => $options['client_id']
                ],[
                    'name'     => 'client_secret',
                    'contents' => $options['client_secret']
                ],[
                    'name'     => 'scope',
                    'contents' => (array_key_exists('scope', $options)) ? $options['scope'] : ''
                ],[
                    'name'     => 'grant_type',
                    'contents' => $options['grant_type']
                ]]
            ]);

            $token = json_decode($response->getBody());
            
            if(isset($token->access_token))
            {
                $headerParams = json_decode($options['header_params'], true);

                $headerAuthPrefix = (array_key_exists('header_prefix', $options) && strlen($options['header_prefix'])) ? $options['header_prefix']." " : '';

                $headerParams['Authorization'] = $headerAuthPrefix.$token->access_token;
                $headerParams['system'] = 'BS';//NEED TO MOVE THIS INTO options

                $clientApi = new GuzzleClient([
                    'verify'   => false,
                    'base_uri' => ltrim($outboundAuth->url, '/'),
                    'headers' => $headerParams
                ]);

                $res = $clientApi->post($this->clientModule->outbound_url_path,[
                    GuzzleRequestOptions::JSON => $data
                ]);

                $log['status_code'] = $res->getStatusCode();
                $log['response_contents'] = (string)$res->getBody()->getContents();
            }
        }
        catch(\GuzzleHttp\Exception\ClientException $e)
        {
            $log['status_code'] = $e->getResponse()->getStatusCode();
            $log['response_contents'] = (string) $e->getResponse()->getBody()->getContents();
        } catch(\GuzzleHttp\Exception\RequestException $e)
        {
            $log['status_code'] = $e->getResponse()->getStatusCode();
            $log['response_contents'] = (string) $e->getResponse()->getBody()->getContents();
        } catch (\Exception $e)
        {
            $log['response_contents'] = (string) $e->getMessage();
        }

        $outboundLog = new OutboundLog;
        $outboundLog->client_id = $this->clientModule->client_id;
        $outboundLog->module = get_class($this);
        $outboundLog->data = json_encode($log['data']);
        $outboundLog->status_code = $log['status_code'];
        $outboundLog->response_contents = $log['response_contents'];

        $outboundLog->save();
    }
}
