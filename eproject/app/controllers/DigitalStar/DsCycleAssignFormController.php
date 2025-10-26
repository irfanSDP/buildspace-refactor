<?php namespace DigitalStar;

use PCK\DigitalStar\Evaluation\DsCycle;
use PCK\DigitalStar\Evaluation\DsCycleTemplateForm;
use PCK\DigitalStar\Evaluation\DsCycleTemplateFormRepository;
use PCK\DigitalStar\TemplateForm\DsTemplateForm;

class DsCycleAssignFormController extends \Controller
{
    private $repository;

    public function __construct(DsCycleTemplateFormRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index($reportTypeId)
    {
        // ...
    }

    public function list($cycleId)
    {
        $cycle = DsCycle::find($cycleId);

        $data = $this->repository->list($cycle);

        return \Response::json($data);
    }

    public function assignableForms($cycleId)
    {
        $records = DsTemplateForm::where('status_id', [DsTemplateForm::STATUS_COMPLETED])
            ->orderBy('id', 'desc')
            ->get();

        $data = [];

        foreach ($records as $record) {
            $templateName = null;
            $weightedNode = $record->weightedNode;
            if ($weightedNode) {
                $templateName = $weightedNode->name;
            }
            if (! is_null($templateName)) {
                $data[] = [
                    'id' => $record->id,
                    'weighted_node_id' => $record->weighted_node_id,
                    'revision' => $record->revision,
                    'original_form_id' => $record->original_form_id,
                    'status_id' => $record->status_id,
                    'current_selected_revision' => $record->current_selected_revision,
                    'template_id' => $record->id,
                    'template_name' => $templateName,
                ];
            }
        }
        return $data;
    }

    public function assign($cycleId, $type)
    {
        $inputs  = \Input::all();
        $errors  = null;
        $success = false;

        try
        {
            $record = DsCycleTemplateForm::where('ds_cycle_id', $cycleId)->where('type', $type)->first();
            if ($record) {
                $record->ds_template_form_id = $inputs['templateId'];
                $record->save();

                $success = true;
            }
        }
        catch(\Exception $e)
        {
            $errors = $e->getMessage();
        }
        
        return \Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }
}