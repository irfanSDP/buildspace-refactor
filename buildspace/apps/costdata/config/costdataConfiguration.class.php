<?php

class costdataConfiguration extends sfApplicationConfiguration
{
    protected $costDataRouting = null;

    public function generateCostDataUrl($name, $parameters = array(), $absolute = false)
    {
        return sfConfig::get('app_site_url') . $this->getCostDataRouting()->generate($name, $parameters, $absolute);
    }

    public function getCostDataRouting()
    {
        if( ! $this->costDataRouting )
        {
            $this->costDataRouting = new sfPatternRouting(new sfEventDispatcher());

            $config = new sfRoutingConfigHandler();
            $routes = $config->evaluate(array( sfConfig::get('sf_apps_dir') . '/costdata/config/routing.yml' ));

            $this->costDataRouting->setRoutes($routes);
        }

        return $this->costDataRouting;
    }

    public function configure()
    {
    }
}
