<?php

use PCK\DailyLabourReports\ProjectLabourRate;

class ProjectLabourRatesTableSeeder extends Seeder {

    public function run()
    {
        foreach(\PCK\Projects\Project::all() as $project)
        {
            ProjectLabourRate::initialise($project);
        }
    }

}