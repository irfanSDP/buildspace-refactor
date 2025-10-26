<?php

use PCK\Menus\Menu;
use PCK\Contracts\Contract;

class ContractPAM2006Seeder extends Seeder {

    public function run()
    {
        $contract = Contract::firstOrCreate(array(
            'name' => Contract::TYPE_PAM2006_NAME,
            'type' => Contract::TYPE_PAM2006,
        ));

        $this->createMenus($contract);
    }

    private function createMenus(Contract $contract)
    {
        Menu::firstOrCreate(array(
            'contract_id' => $contract->id,
            'name'        => 'architectInstruction',
            'icon_class'  => 'fa fa-lg fa-fw fa-building',
            'route_name'  => 'ai',
            'priority'    => 1,
        ));

        Menu::firstOrCreate(array(
            'contract_id' => $contract->id,
            'name'        => 'engineerInstruction',
            'icon_class'  => 'fa fa-lg fa-fw fa-shield',
            'route_name'  => 'ei',
            'priority'    => 2,
        ));

        Menu::firstOrCreate(array(
            'contract_id' => $contract->id,
            'name'        => 'extensionOfTime',
            'icon_class'  => 'fa fa-lg fa-fw fa-clock-o',
            'route_name'  => 'eot',
            'priority'    => 3,
        ));

        Menu::firstOrCreate(array(
            'contract_id' => $contract->id,
            'name'        => 'lossOrAndExpenses',
            'icon_class'  => 'fa fa-lg fa-fw fa-money',
            'route_name'  => 'loe',
            'priority'    => 4,
        ));

        Menu::firstOrCreate(array(
            'contract_id' => $contract->id,
            'name'        => 'additionalExpenses',
            'icon_class'  => 'fa fa-lg fa-fw fa-dollar',
            'route_name'  => 'ae',
            'priority'    => 5,
        ));

        Menu::firstOrCreate(array(
            'contract_id' => $contract->id,
            'name'        => 'weatherRecord',
            'icon_class'  => 'fa fa-lg fa-fw fa-cloud',
            'route_name'  => 'wr',
            'priority'    => 6,
        ));

        Menu::firstOrCreate(array(
            'contract_id' => $contract->id,
            'name'        => 'interimClaim',
            'icon_class'  => 'fa fa-lg fa-fw fa-inbox',
            'route_name'  => 'ic',
            'priority'    => 7,
        ));
    }

}