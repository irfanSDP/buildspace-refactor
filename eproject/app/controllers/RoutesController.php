<?php

class RoutesController extends \BaseController
{
    public function generateRoutes()
    {
        $success      = false;
        $errorMessage = null;
        $routes       = [];

        try
        {
            foreach(Input::get('routes') ?? [] as $routeName => $routeParams)
            {
                $routes[$routeName] = route($routeName, $routeParams);
            }

            $success = true;
        }
        catch(\Exception $e)
        {
            $errorMessage = $e->getMessage();
        }

        return [
            'success'      => true,
            'errorMessage' => $errorMessage,
            'routes'       => $routes,
        ];
    }
}