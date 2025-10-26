<?php

use PCK\Contracts\Contract;
use PCK\Menus\Menu;

class ContractsTableSeeder_IndonesiaCivilContract extends Seeder {

    public function run()
    {
        $contract = Contract::firstOrCreate(array(
            'name' => Contract::TYPE_INDONESIA_CIVIL_CONTRACT_NAME,
            'type' => Contract::TYPE_INDONESIA_CIVIL_CONTRACT,
        ));

        $this->createMenus($contract);
    }

    private function createMenus(Contract $contract)
    {
        Menu::firstOrCreate(array(
            'contract_id' => $contract->id,
            'name'        => 'architectInstruction',
            'icon_class'  => 'fa fa-lg fa-fw fa-building',
            'route_name'  => 'indonesiaCivilContract.architectInstructions',
            'priority'    => 1,
        ));

        Menu::firstOrCreate(array(
            'contract_id' => $contract->id,
            'name'        => 'extensionOfTime',
            'icon_class'  => 'fa fa-lg fa-fw fa-clock-o',
            'route_name'  => 'indonesiaCivilContract.extensionOfTime',
            'priority'    => 3,
        ));

        Menu::firstOrCreate(array(
            'contract_id' => $contract->id,
            'name'        => 'lossOrAndExpenses',
            'icon_class'  => 'fa fa-lg fa-fw fa-money',
            'route_name'  => 'indonesiaCivilContract.lossOrAndExpenses',
            'priority'    => 4,
        ));

        Menu::firstOrCreate(array(
            'contract_id' => $contract->id,
            'name'        => 'earlyWarning',
            'icon_class'  => 'fa fa-lg fa-fw fa-exclamation-triangle',
            'route_name'  => 'indonesiaCivilContract.earlyWarning',
            'priority'    => 7,
        ));
    }

}