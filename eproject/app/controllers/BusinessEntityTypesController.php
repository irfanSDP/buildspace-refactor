<?php

use PCK\BusinessEntityType\BusinessEntityType;
use PCK\Forms\BusinessEntityTypeForm;
use PCK\Settings\SystemSettings;

class BusinessEntityTypesController extends \BaseController {

    protected $businessEntityTypeForm;

    public function __construct(BusinessEntityTypeForm $businessEntityTypeForm)
    {
        $this->businessEntityTypeForm = $businessEntityTypeForm;
    }

    public function index()
    {
        $data = [];

        $records = BusinessEntityType::orderBy('name', 'asc')
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
            'hidden' => ! SystemSettings::getValue('allow_other_business_entity_types'),
        ];

        $recordIds[] = 'others';
        if(! SystemSettings::getValue('allow_other_business_entity_types')) $hiddenIds[] = 'others';

        return View::make('business_entity_types.index', compact('data', 'recordIds', 'hiddenIds'));
    }

    public function create()
    {
        return View::make('business_entity_types.create');
    }

    public function store()
    {
        $this->businessEntityTypeForm->validate(Input::all());

        BusinessEntityType::create(Input::all());

        \Flash::success(trans('forms.saved'));

        return Redirect::route('businessEntityTypes.index');
    }

    public function update()
    {
        $input = Input::get('id') ?? [];

        SystemSettings::setValue('allow_other_business_entity_types', !isset($input['others']));

        unset($input['others']);

        BusinessEntityType::whereIn('id', $input)->update(array('hidden' => true));
        BusinessEntityType::whereNotIn('id', $input)->update(array('hidden' => false));

        \Flash::success(trans('forms.saved'));

        return Redirect::route('businessEntityTypes.index');
    }
}