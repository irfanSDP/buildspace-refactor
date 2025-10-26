<?php

use PCK\ProjectRole\ProjectRole;

class ProjectRolesTableSeeder extends Seeder {

    public function run()
    {
        self::initialiseAllProjects();
    }

    public static function initialiseAllProjects($groups = array())
    {
        foreach(\PCK\Projects\Project::all() as $project)
        {
            ProjectRole::initialise($project, $groups);
        }
    }

}