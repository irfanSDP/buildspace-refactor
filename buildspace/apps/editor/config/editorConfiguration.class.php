<?php

class editorConfiguration extends sfApplicationConfiguration
{
    protected $editorRouting = null;

    public function generateEditorUrl($name, $parameters = array(), $absolute = false)
    {
        return sfConfig::get('app_site_url').$this->getEditorRouting()->generate($name, $parameters, $absolute);
    }

    public function getEditorRouting()
    {
        if (!$this->editorRouting )
        {
            $this->editorRouting = new sfPatternRouting(new sfEventDispatcher());

            $config = new sfRoutingConfigHandler();
            $routes = $config->evaluate(array(sfConfig::get('sf_apps_dir').'/editor/config/routing.yml'));

            $this->editorRouting->setRoutes($routes);
        }

        return $this->editorRouting;
    }

    public function configure()
    {
    }
}
