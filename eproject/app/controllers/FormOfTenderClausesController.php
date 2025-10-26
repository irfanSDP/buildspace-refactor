<?php

use PCK\FormOfTender\FormOfTenderRepository;
use PCK\Tenders\Tender;
use PCK\FormOfTender\FormOfTender;
use PCK\Forms\FormOfTenderClausesForm;

class FormOfTenderClausesController extends \BaseController {

    protected $repository;
    protected $clausesForm;

    public function __construct(FormOfTenderRepository $repository, FormOfTenderClausesForm $clausesForm)
    {
        $this->repository  = $repository;
        $this->clausesForm = $clausesForm;
    }

    /**
     * Returns the view to edit the Clauses.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     */
    public function editClauses($project, $tenderId)
    {
        $tender = Tender::find($tenderId);

        return View::make('form_of_tender.clauses.edit', array(
            'parentClauses' => $this->repository->getClausesAndTenderAlternativesMarkers($tender->formOfTender->id),
            'backRoute'     => route('form_of_tender.edit', array( $project->id, $tenderId )),
            'editable'      => $this->repository->isEditable($tenderId),
            'isTemplate'    => false,
            'project'       => $project,
            'tender'        => $tender,
        ));
    }

    /**
     * Returns the view to edit the template for Clauses.
     *
     * @return \Illuminate\View\View
     */
    public function editClausesTemplate($templateId)
    {
        $formOfTender = FormOfTender::find($templateId);

        return View::make('form_of_tender.clauses.edit', array(
            'parentClauses' => $this->repository->getClausesAndTenderAlternativesMarkers($formOfTender->id),
            'backRoute'     => route('form_of_tender.template.edit', [$templateId]),
            'isTemplate'    => true,
            'templateId'    => $templateId,
            'templateName'  => $formOfTender->name,
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * PUT /formoftenderclauses/{id}
     *
     * @param $project
     * @param $tenderId
     *
     * @return string
     */
    public function updateClauses($project, $tenderId)
    {
        $this->clausesForm->validate(Input::all());

        $tender = Tender::find($tenderId);

        if( $this->repository->isEditable($tenderId, false) )
        {
            $this->repository->updateClauses($tender->formOfTender->id, false, Input::all());
        }

        return route('form_of_tender.edit', array( $project->id, $tenderId ));
    }

    /**
     * Update the template of the specified clause resource(s) in storage.
     *
     * @return string
     */
    public function updateClausesTemplate($templateId)
    {
        $this->clausesForm->validate(Input::all());

        $this->repository->updateClauses($templateId, true, Input::all());

        return route('form_of_tender.template.edit', [$templateId]);
    }

}