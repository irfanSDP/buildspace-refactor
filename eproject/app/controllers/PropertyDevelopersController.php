<?php

use PCK\PropertyDeveloper\PropertyDeveloper;
use PCK\Forms\PropertyDeveloperForm;
use PCK\Settings\SystemSettings;

class PropertyDevelopersController extends \BaseController {

    protected $propertyDeveloperForm;

    public function __construct(PropertyDeveloperForm $propertyDeveloperForm)
    {
        $this->propertyDeveloperForm = $propertyDeveloperForm;
    }

    public function index()
    {
        $data = [];

        $records = PropertyDeveloper::orderBy('name', 'asc')
            ->get();

        $recordIds = [];
        $hiddenIds = [];

        foreach($records as $record)
        {
            $data[] = [
                'id'           => $record->id,
                'name'         => $record->name,
                'hidden'       => $record->hidden,
            ];

            $recordIds[] = $record->id;
            if($record->hidden) $hiddenIds[] = $record->id;
        }

        $data[] = [
            'id'     => 'others',
            'name'   => trans('forms.othersPleaseSpecify'),
            'hidden' => ! SystemSettings::getValue('allow_other_property_developers'),
        ];

        $recordIds[] = 'others';
        if(! SystemSettings::getValue('allow_other_property_developers')) $hiddenIds[] = 'others';

        return View::make('property_developers.index', compact('data', 'recordIds', 'hiddenIds'));
    }

    public function create()
    {
        return View::make('property_developers.create');
    }

    public function store()
    {
        $this->propertyDeveloperForm->validate(Input::all());

        PropertyDeveloper::create(Input::all());

        \Flash::success(trans('forms.saved'));

        return Redirect::route('propertyDevelopers.index');
    }

    public function update()
    {
        $input = Input::get('id') ?? [];

        SystemSettings::setValue('allow_other_property_developers', !isset($input['others']));

        unset($input['others']);

        PropertyDeveloper::whereIn('id', $input)->update(array('hidden' => true));
        PropertyDeveloper::whereNotIn('id', $input)->update(array('hidden' => false));

        \Flash::success(trans('forms.saved'));

        return Redirect::route('propertyDevelopers.index');
    }
}