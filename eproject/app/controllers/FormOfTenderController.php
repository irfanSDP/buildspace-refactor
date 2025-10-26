<?php

use PCK\FormOfTender\FormOfTender;
use PCK\FormOfTender\FormOfTenderRepository;
use PCK\Tenders\Tender;

class FormOfTenderController extends \BaseController {

    protected $repository;

    public function __construct(FormOfTenderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show the form for editing the specified resource.
     * GET /formoftenders/{id}/edit
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     * @internal param int $id
     */
    public function edit($project, $tenderId)
    {
        $tender = Tender::find($tenderId);

        return View::make('form_of_tender.edit', array(
            'isTemplate' => false,
            'tenderId'   => $tenderId,
            'projectId'  => $project->id,
            'log'        => $tender->formOfTender->logs,
            'editable'   => $this->repository->isEditable($tenderId),
            'project'    => $project,
            'tender'     => Tender::find($tenderId),
        ));
    }

    /**
     * Returns the view to edit the template for Form of Tender.
     *
     * @return \Illuminate\View\View
     */
    public function editTemplate($templateId)
    {
        $formOfTender = FormOfTender::find($templateId);

        return View::make('form_of_tender.edit', array(
            'tenderId'   => null,
            'isTemplate' => true,
            'log'        => $formOfTender->logs,
            'templateId' => $templateId,
            'name'       => $formOfTender->name,
        ));
    }

}