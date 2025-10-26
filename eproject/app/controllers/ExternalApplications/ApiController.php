<?php namespace ExternalApplications;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Response;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use PCK\Exceptions\ValidationException;

use PCK\ExternalApplication\Client;
use PCK\ExternalApplication\ClientModule;
use PCK\ExternalApplication\Attribute;

class ApiController extends \BaseController
{
    private $routeModuleName;
    private $request;
    private $response;
    private $extAppClient;

    public function __construct(Response $response, Request $request)
    {
        $this->routeModuleName = \Route::getCurrentRoute()->parameter('module');
        $this->request = $request;
        $this->response = $response;

        $user = \Confide::user();
        $this->extAppClient = $user->extAppClient;
    }

    public function list()
    {
        $request = \Request::instance();
        $content = $request->getContent();

        try
        {
            $data = $this->extAppClient->listByModule(ClientModule::API_ROUTES[$this->routeModuleName], $request);
            $statusCode = 200;
        }
        catch(UnauthorizedHttpException $e)
        {
            $statusCode = 401;
            $data = [
                'error' => $e->getMessage()
            ];
        }
        catch(\Exception $e)
        {
            $statusCode = 500;
            $data = [
                'error' => $e->getMessage()
            ];
        }

        return Response::json($data, $statusCode);
    }

    public function create()
    {
        try
        {
            $request = \Request::instance();
            $content = $request->getContent();

            $records = json_decode(trim($content), true, 512);
            $records = is_array($records) ? $records : [];

            $createdRecords = $this->extAppClient->createByModule(ClientModule::API_ROUTES[$this->routeModuleName], $records);
            
            $msg = !empty($createdRecords) ? 'Successfully created records' : 'No record was created';

            $statusCode = 200;
            $data = [
                'success'  => $msg,
                'entities' => $createdRecords
            ];
        }
        catch(\Exception $e)
        {
            $statusCode = 422;
            $data = [
                'error' => $e->getMessage()
            ];
        }

        return Response::json($data, $statusCode);
    }

    public function update($moduleName, $extIdentifier)
    {
        try
        {
            $request = \Request::instance();
            $content = $request->getContent();

            $records = json_decode(trim($content), true, 512);
            $records = is_array($records) ? $records : [];

            $data = $this->extAppClient->updateByModule(ClientModule::API_ROUTES[$this->routeModuleName], $extIdentifier, $records);

            $statusCode = 200;
        }
        catch(ModelNotFoundException $e)
        {
            $statusCode = 404;
            $data = [
                'error' => $e->getMessage()
            ];
        }
        catch(UnauthorizedHttpException $e)
        {
            $statusCode = 401;
            $data = [
                'error' => $e->getMessage()
            ];
        }
        catch(\Exception $e)
        {
            $statusCode = 500;
            $data = [
                'error' => $e->getMessage()
            ];
        }

        return Response::json($data, $statusCode);
    }

    public function retrieve($moduleName, $extIdentifier)
    {
        try
        {
            $data = $this->extAppClient->retrieveByModule(ClientModule::API_ROUTES[$this->routeModuleName], $extIdentifier);

            $statusCode = 200;
        }
        catch(ModelNotFoundException $e)
        {
            $statusCode = 404;
            $data = [
                'error' => $e->getMessage()
            ];
        }
        catch(UnauthorizedHttpException $e)
        {
            $statusCode = 401;
            $data = [
                'error' => $e->getMessage()
            ];
        }
        catch(\Exception $e)
        {
            $statusCode = 500;
            $data = [
                'error' => $e->getMessage()
            ];
        }

        return Response::json($data, $statusCode);
    }

    public function delete($moduleName, $extIdentifier)
    {
        try
        {
            $this->extAppClient->deleteByModule(ClientModule::API_ROUTES[$this->routeModuleName], $extIdentifier);

            $statusCode = 200;
            $data = [
                'success' => 'Successfully deleted entity with id: '.$extIdentifier
            ];
        }
        catch(ModelNotFoundException $e)
        {
            $statusCode = 404;
            $data = [
                'error' => $e->getMessage()
            ];
        }
        catch(ValidationException $e)
        {
            $statusCode = 422;
            $data = [
                'error' => $e->getMessage()
            ];
        }
        catch(\Exception $e)
        {
            $statusCode = 500;
            $data = [
                'error' => $e->getMessage()
            ];
        }

        return Response::json($data, $statusCode);
    }
}