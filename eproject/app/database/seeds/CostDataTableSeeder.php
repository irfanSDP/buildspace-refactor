<?php

class CostDataTableSeeder extends Seeder {

    public function run()
    {
        foreach(\PCK\Buildspace\CostData::withTrashed()->get() as $bsCostData)
        {
            if( \PCK\CostData\CostData::where('buildspace_origin_id', '=', $bsCostData->id)->first() ) continue;

            $costData = new \PCK\CostData\CostData;

            $costData->buildspace_origin_id = $bsCostData->id;
            $costData->save();
        }
    }

}