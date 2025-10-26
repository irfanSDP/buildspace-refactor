<?php namespace PCK\Forms;

use Illuminate\Support\MessageBag;
use PCK\Buildspace\CostData;
use PCK\Projects\Project;
use PCK\Subsidiaries\Subsidiary;

class CostDataForm extends CustomFormValidator {

    protected $id;

    protected $rules = [
        'name'                => 'required|min:3|max:150',
        'master_cost_data_id' => 'required|integer',
        'subsidiary_id'       => 'required|integer|exists:subsidiaries,id',
        'cost_data_type_id'   => 'required|integer',
        'region_id'           => 'required|integer',
        'subregion_id'        => 'required|integer',
        'currency_id'         => 'required|integer',
        'tender_date'         => 'integer|min:1900|max:2500',
        'award_date'          => 'integer|min:1900|max:2500',
    ];

    public function ignoreUnique($id)
    {
        $this->id = $id;
    }

    protected function postParentValidation($formData)
    {
        $errorMessages = new MessageBag();

        if( ! $this->nameIsUnique($formData['name']) )
        {
            $errorMessages->add('name', trans('validation.unique', array( 'attribute' => strtolower(trans('general.name')) )));
        }

        if( ! $this->projectsBelongToSubsidiary($formData) )
        {
            $errorMessages->add('project_id', trans('costData.projectsDoNotBelongToSubsidiary'));
        }

        return $errorMessages;
    }

    protected function nameIsUnique($name)
    {
        $record = CostData::where('name', '=', $name)->first();

        if( $record && ( $this->id == $record->id ) ) return true;

        return $record ? false : true;
    }

    protected function projectsBelongToSubsidiary($formData)
    {
        $subsidiaryDescendantIds = Subsidiary::getSelfAndDescendantIds([$formData['subsidiary_id']])[$formData['subsidiary_id']];

        return Project::whereIn('id', $formData['project_id'] ?? [])
            ->whereNotIn('subsidiary_id', $subsidiaryDescendantIds)
            ->count() === 0;
    }
}