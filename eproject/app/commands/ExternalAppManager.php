<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use PCK\ExternalApplication\Module\ContractorVariationOrder;

use PCK\Buildspace\VariationOrder;

class ExternalAppManager extends Command
{
    protected $name = 'external-app:manager';
    protected $description = 'External Application Manager - To run external application module';

    public function __construct()
    {
        parent::__construct();
    }

    protected function getArguments()
    {
        return [
            ['module_name', InputArgument::REQUIRED, 'External Application Module name'],
            ['id', InputArgument::REQUIRED, 'ID that is needed based on the module requirement'],
        ];
    }

    public function fire()
    {
        ini_set('memory_limit','2048M');

        $moduleName = $this->argument('module_name');
        $id = $this->argument('id');

        switch($moduleName)
        {
            case 'ContractorVariationOrder':
                $variationOrder = VariationOrder::find((int)$id);

                if(!$variationOrder)
                {
                    $this->error('Module: '.$moduleName.' - variation order with id '.$id.' does not exist!');
                    return;
                }

                \Queue::push('PCK\QueueJobs\ExternalOutboundAPI', [
                    'module' => 'ContractorVariationOrder',
                    'vo_id' => $variationOrder->id,
                ], 'ext_app_outbound');
                break;
            default:
                $this->error($moduleName.' is invalid module name!');
        }
    }
}
