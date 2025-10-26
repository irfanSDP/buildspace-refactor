<?php namespace PCK\ProjectSectionalCompletionDate;

use Carbon\Carbon;
use PCK\Projects\Project;

class ProjectSectionalCompletionDateRepository
{
    public function getRecords(Project $project)
    {
        $data = [];

        foreach($project->sectionalCompletionDates()->orderBy('id', 'ASC')->get() as $record)
        {
            $data[] = [
                'id'           => $record->id,
                'date'         => $record->sectional_completion_date,
                'date_display' => Carbon::parse($record->sectional_completion_date)->format(\Config::get('dates.full_format_without_time')),
                'description'  => $record->description,
                'route:update' => route('project.sectionalCompletionDate.record.update', [$project->id, $record->id]),
                'route:delete' => route('project.sectionalCompletionDate.record.delete', [$project->id, $record->id]),
            ];
        }

        return $data;
    }

    public function add(Project $project, $inputs)
    {
        $record                            = new ProjectSectionalCompletionDate();
        $record->project_id                = $project->id;
        $record->sectional_completion_date = $inputs['date'];
        $record->description               = trim($inputs['description']);
        $record->save();

        return ProjectSectionalCompletionDate::find($record->id);
    }

    public function update(ProjectSectionalCompletionDate $record, $inputs)
    {
        $record->sectional_completion_date = $inputs['date'];
        $record->description               = trim($inputs['description']);
        $record->save();

        return ProjectSectionalCompletionDate::find($record->id);
    }
}