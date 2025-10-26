<?php

use Carbon\Carbon;
use PCK\Exceptions\ValidationException;
use PCK\Helpers\DBTransaction;
use PCK\Projects\Project;
use PCK\ProjectSectionalCompletionDate\ProjectSectionalCompletionDate;
use PCK\ProjectSectionalCompletionDate\ProjectSectionalCompletionDateRepository;
use PCK\Forms\ProjectSectionalCompletionDateForm;

class ProjectSectionalCompletionDatesController extends Controller
{
    private $repository;
    private $form;

    public function __construct(ProjectSectionalCompletionDateRepository $repository, ProjectSectionalCompletionDateForm $form)
    {
        $this->repository = $repository;
        $this->form       = $form;
    }

    public function getRecords(Project $project)
    {
        $records = $this->repository->getRecords($project);

        return Response::json($records);
    }

    public function add(Project $project)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $this->form->validate($inputs);
            $this->repository->add($project, $inputs);

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(\Exception $e)
        {
            $errors = $e->getErrors();
            \Log::error("Unable to add sectional completion date for project [{$project->id}] -> {$e->getMessage()}");
            \Log::error($e->getTraceAsString());
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function update(Project $project, $recordId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $record = ProjectSectionalCompletionDate::find($recordId);

            $this->form->validate($inputs);
            $this->repository->update($record, $inputs);

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(\Exception $e)
        {
            $errors = $e->getErrors();
            \Log::error("Unable to update sectional completion date for project [{$project->id}] -> {$e->getMessage()}");
            \Log::error($e->getTraceAsString());
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function delete(Project $project, $recordId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $record = ProjectSectionalCompletionDate::find($recordId);
            $record->delete();

            $success = true;
        }
        catch(Exception $e)
        {
            $errors = $e->getMessage();
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }
}