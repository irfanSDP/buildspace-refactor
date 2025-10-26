<?php

use PCK\FormOfTender\FormOfTenderRepository;
use PCK\Tenders\Tender;
use PCK\FormOfTender\FormOfTender;

class FormOfTenderAddressController extends \BaseController {

    protected $repository;

    public function __construct(FormOfTenderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns the view to edit the template for the address.
     *
     * @return \Illuminate\View\View
     */
    public function editAddressTemplate($templateId)
    {
        $formOfTender = FormOfTender::find($templateId);

        return View::make('form_of_tender.partials.single_section_edit', array(
            'model'        => $formOfTender->address,
            'templateName' => $formOfTender->name,
            'updateRoute'  => route('form_of_tender.address.template.update', array( $formOfTender->id, $formOfTender->address->id )),
            'backRoute'    => route('form_of_tender.template.edit', [$templateId]),
            'editable'     => true,
            'isTemplate'   => true,
            'templateId'   => $formOfTender->id,
        ));
    }

    /**
     * Returns the view to edit the address.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     */
    public function editAddress($project, $tenderId)
    {
        $tender = Tender::find($tenderId);
        $formOfTender = $tender->formOfTender;

        return View::make('form_of_tender.partials.single_section_edit', array(
            'model'       => $formOfTender->address,
            'updateRoute' => route('form_of_tender.address.update', array( $project->id, $tenderId )),
            'backRoute'   => route('form_of_tender.edit', array( $project->id, $tenderId )),
            'editable'    => $this->repository->isEditable($tenderId),
            'isTemplate'  => false,
            'project'     => $project,
            'tender'      => Tender::find($tenderId),
        ));
    }

    /**
     * Updates the Address resource.
     *
     * @param $project
     * @param $formOfTenderAddressId
     *
     * @return string
     */
    public function updateAddress($project, $tenderId)
    {
        $tender = Tender::find($tenderId);
        $formOfTender = $tender->formOfTender;

        if( $this->repository->isEditable($tenderId, false) )
        {
            $this->repository->updateAddress($formOfTender->address, Input::get('contentData'));
        }

        return route('form_of_tender.edit', array( $project->id, $tenderId ));
    }

    /**
     * Updates the template of the Address resource.
     *
     * @param $formOfTenderAddressId
     *
     * @return string
     */
    public function updateAddressTemplate($templateId)
    {
        $formOfTender = FormOfTender::find($templateId);

        if( $this->repository->isEditable(null, true) )
        {
            $this->repository->updateAddress($formOfTender->address, Input::get('contentData'));
        }

        return route('form_of_tender.template.edit', [$templateId]);
    }

}