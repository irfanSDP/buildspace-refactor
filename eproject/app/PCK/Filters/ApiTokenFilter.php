<?php namespace PCK\Filters;

use Illuminate\Http\Request;
use Response;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use PCK\ExternalApplication\Client;
use PCK\ExternalApplication\ClientModule;

class ApiTokenFilter {
    private $request;

    private $response;

    private $header = 'authorization';

    private $prefix = 'bearer';

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function auth(Route $route)
    {
        $header = $this->request->header('Content-type');
        if (!\Str::contains($header, 'application/json'))
        {
            return $this->response->make('Only JSON requests are allowed', 406);
        }

        $token = $this->parseToken();

        if(empty($token))
        {
            return Response::json([
                'error' => 'Token not provided'
            ], 401);
        }

        $user = null;

        if($route->getPrefix() == 'api/v2')
        {
            try
            {
                $routeModuleName = $route->parameter('module');
                $client = Client::validate($token, $routeModuleName);
                $user = $client->user;
            }
            catch(UnauthorizedHttpException $e)
            {
                return Response::json([
                    'error' => $e->getMessage()
                ], 401);
            }
            catch(NotFoundHttpException $e)
            {
                return Response::json([
                    'error' => $e->getMessage()
                ], 404);
            }
        }

        if(!$user)
        {
            return Response::json([
                'error' => 'Invalid user'
            ], 401);
        }

        \Auth::onceUsingId($user->id);
    }

    private function parseToken()
    {
        $request = $this->request;

        $header = $request->headers->get($this->header);

        if ($header !== null)
        {
            $position = strripos($header, $this->prefix);

            if ($position !== false) {
                $header = substr($header, $position + strlen($this->prefix));

                return trim(
                    strpos($header, ',') !== false ? strstr($header, ',', true) : $header
                );
            }
        }

        return null;
    }
}