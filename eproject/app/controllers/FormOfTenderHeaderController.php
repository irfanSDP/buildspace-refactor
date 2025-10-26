<?php

use PCK\FormOfTender\FormOfTenderRepository;
use PCK\Tenders\Tender;

class FormOfTenderHeaderController extends \BaseController {

    protected $repository;

    public function __construct(FormOfTenderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns the view to edit the header.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     */
    public function editHeader($project, $tenderId)
    {
        $tender = Tender::find($tenderId);
        $header = $tender->formOfTender->header;

        return View::make('form_of_tender.partials.single_section_edit', array(
            'model'       => $header,
            'updateRoute' => route('form_of_tender.header.update', array( $project->id, $tenderId )),
            'backRoute'   => route('form_of_tender.edit', array( $project->id, $tenderId )),
            'editable'    => $this->repository->isEditable($tenderId),
            'isTemplate'  => false,
            'project'     => $project,
            'tender'      => Tender::find($tenderId),
        ));
    }

    /**
     * Updates the Header resource.
     *
     * @param $project
     * @param $tenderId
     *
     * @return string
     */
    public function updateHeader($project, $tenderId)
    {
        $tender = Tender::find($tenderId);

        if( $this->repository->isEditable($tenderId, false) )
        {
            $this->repository->updateHeader($tender->formOfTender->header, Input::get('contentData'));
        }

        return route('form_of_tender.edit', array( $project->id, $tenderId ));
    }

}