<?php

use PCK\BuildingInformationModelling\BuildingInformationModellingLevel;
use PCK\Exceptions\ValidationException;
use PCK\Forms\BuildingInformationModellingLevelForm;

class BuildingInformationModellingLevelsController extends Controller
{
    private $bimLevelForm;

    public function __construct(BuildingInformationModellingLevelForm $bimLevelForm)
    {
        $this->bimLevelForm = $bimLevelForm;
    }

    public function index()
    {
        return View::make('building_information_modelling.level.index');
    }

    public function list()
    {
        $data = [];

        foreach(BuildingInformationModellingLevel::orderBy('id', 'ASC')->get() as $bimLevel)
        {
            array_push($data, [
                'id'           => $bimLevel->id,
                'name'         => $bimLevel->name,
                'canBeEdited'  => $bimLevel->canBeEdited(),
                'route_update' => $bimLevel->canBeEdited() ? route('buildingInformationModellingLevel.update', [$bimLevel->id]) : null,
                'route_delete' => $bimLevel->canBeEdited() ? route('buildingInformationModellingLevel.delete', [$bimLevel->id]) : null,
            ]);
        }

        return Response::json($data);
    }

    public function store()
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $this->bimLevelForm->validate($inputs);

            $bimLevel             = new BuildingInformationModellingLevel();
            $bimLevel->name       = $inputs['name'];
            $bimLevel->created_by = Confide::user()->id;
            $bimLevel->updated_by = Confide::user()->id;
            $bimLevel->save();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $errors = $e->getErrors();
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function update($bimLevelId)
    {
        $inputs  = Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $this->bimLevelForm->validate($inputs);

            $bimLevel             = BuildingInformationModellingLevel::find($bimLevelId);

            if($bimLevel->canBeEdited())
            {
                $bimLevel->name       = $inputs['name'];
                $bimLevel->updated_by = Confide::user()->id;
                $bimLevel->save();
            }

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $errors = $e->getErrors();
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function destroy($bimLevelId)
    {
        $errors  = null;
        $success = false;

        try
        {
            $bimLevel = BuildingInformationModellingLevel::find($bimLevelId);

            if($bimLevel && $bimLevel->canBeEdited())
            {
                $bimLevel->delete();
            }

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $errors = $e->getErrors();
        }

        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }
}
