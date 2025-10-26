<?php

use PCK\FormOfTender\FormOfTender;
use PCK\FormOfTender\FormOfTenderRepository;
use PCK\Forms\TenderAlternativeForm;
use PCK\Tenders\Tender;

class FormOfTenderTenderAlternativesController extends \BaseController {

    protected $repository;
    private   $tenderAlternativeForm;

    public function __construct(FormOfTenderRepository $repository, TenderAlternativeForm $tenderAlternativeForm)
    {
        $this->repository            = $repository;
        $this->tenderAlternativeForm = $tenderAlternativeForm;
    }

    /**
     * Returns a view to edit the template Tender Alternatives.
     *
     * @return \Illuminate\View\View
     */
    public function editTenderAlternativesTemplate($templateId)
    {
        $formOfTender = FormOfTender::find($templateId);

        return View::make('form_of_tender.tender_alternatives.edit', array(
            'tenderAlternatives' => $this->repository->getTenderAlternativesDescriptionsByTemplateId($templateId),
            'backRoute'          => route('form_of_tender.template.edit', [$templateId]),
            'isTemplate'         => true,
            'tenderId'           => null,
            'templateId'         => $templateId,
            'templateName'       => $formOfTender->name,
            'tags'               => $this->repository->getTags()
        ));
    }

    /**
     * Returns a view to edit the Tender Alternatives.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     */
    public function editTenderAlternatives($project, $tenderId)
    {
        return View::make('form_of_tender.tender_alternatives.edit', array(
            'tenderAlternatives' => $this->repository->getTenderAlternativesDescriptionsByTenderId($tenderId),
            'backRoute'          => route('form_of_tender.edit', array( $project->id, $tenderId )),
            'tenderId'           => $tenderId,
            'isTemplate'         => false,
            'editable'           => $this->repository->isEditable($tenderId),
            'project'            => $project,
            'tender'             => Tender::find($tenderId),
            'tags'               => $this->repository->getTags($tenderId)
        ));
    }

    /**
     * Updates the Tender Alternatives.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTenderAlternatives($project, $tenderId)
    {
        $tender = Tender::find($tenderId);

        $this->tenderAlternativeForm->validate(Input::all());

        if( $this->repository->isEditable($tenderId, false) )
        {
            $this->repository->updateTenderAlternatives($tender->formOfTender->id, Input::get('tender_alternative'));

            if (Input::get('ta_ids')) {
                $ta_ids = explode(',', Input::get('ta_ids'));
                foreach ($ta_ids as $ta_id) {
                    $ta_description = trim(Input::get('tender_alternative_description_'.$ta_id));
                    $ta_description_text = ! empty($ta_description) ? strip_tags($ta_description) : $ta_description;
                    $this->repository->updateTenderAlternativesDescription($ta_id, ! empty($ta_description_text) ? $ta_description : null);
                }
            }
            \Flash::success(trans('forms.updateSuccessful'));
        }

        return Redirect::back();
        //return Redirect::to(route('form_of_tender.edit', array( $project->id, $tenderId )));
    }

    /**
     * Updates the template of the Tender Alternatives.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTenderAlternativesTemplate($templateId)
    {
        $this->tenderAlternativeForm->validate(Input::all());

        $this->repository->updateTenderAlternatives($templateId, Input::get('tender_alternative'));

        if (Input::get('ta_ids')) {
            $ta_ids = explode(',', Input::get('ta_ids'));
            foreach ($ta_ids as $ta_id) {
                $ta_description = trim(Input::get('tender_alternative_description_'.$ta_id));
                $ta_description_text = ! empty($ta_description) ? strip_tags($ta_description) : $ta_description;
                $this->repository->updateTenderAlternativesDescription($ta_id, ! empty($ta_description_text) ? $ta_description : null);
            }
        }
        \Flash::success(trans('forms.updateSuccessful'));

        return Redirect::back();
        //return Redirect::to(route('form_of_tender.template.edit', [$templateId]));
    }
}