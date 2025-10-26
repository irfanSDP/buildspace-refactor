<?php namespace PCK\ModuleParameters\VendorManagement;

use Carbon\Carbon;

class VendorManagementGradeRepository
{
    public function getAllGrades()
    {
        $grades = [];

        foreach(VendorManagementGrade::where('is_template', true)->orderBy('id', 'ASC')->get() as $grade)
        {
            array_push($grades, [
                'id'           => $grade->id,
                'name'         => $grade->name,
                'created_at'   => Carbon::parse($grade->created_at)->format(\Config::get('dates.created_and_updated_at_formatting')),
                'route_show'   => route('vendor.management.grade.levels.show', [$grade->id]),
                'route_update' => route('vendor.management.grade.name.update', [$grade->id]),
                'route_delete' => route('vendor.management.grade.name.delete', [$grade->id]),
            ]);
        }

        return $grades;
    }

    public function update(VendorManagementGrade $grade, $inputs)
    {
        $grade->name       = $inputs['name'];
        $grade->updated_by = \Confide::user()->id;
        $grade->save();

        return VendorManagementGrade::find($grade->id);
    }

    public function getGradeLevels(VendorManagementGrade $grade)
    {
        $levels = [];

        foreach($grade->levels()->orderBy('score_upper_limit', 'ASC')->get() as $level)
        {
            array_push($levels, [
                'id'                => $level->id,
                'description'       => $level->description,
                'definition'        => $level->definition,
                'score_upper_limit' => $level->score_upper_limit,
                'route_update'      => route('vendor.management.grade.level.update', [$level->id]),
                'route_delete'      => route('vendor.management.grade.level.delete', [$level->id]),
            ]);
        }

        return $levels;
    }

    public function updateGradeLevel(VendorManagementGradeLevel $level, $inputs)
    {
        $level->description       = $inputs['description'];
        $level->score_upper_limit = $inputs['score_upper_limit'];
        $level->definition        = $inputs['definition'];
        $level->updated_by        = \Confide::user()->id;
        $level->save();

        return VendorManagementGradeLevel::find($level->id);
    }
}