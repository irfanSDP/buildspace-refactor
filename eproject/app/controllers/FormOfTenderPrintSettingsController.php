<?php

use PCK\FormOfTender\FormOfTenderRepository;
use PCK\Forms\FormOfTenderPrintSettingsForm;
use PCK\Tenders\Tender;
use PCK\FormOfTender\FormOfTender;

class FormOfTenderPrintSettingsController extends \BaseController {

    protected $repository;
    private   $printSettingsForm;

    public function __construct(
        FormOfTenderRepository $repository,
        FormOfTenderPrintSettingsForm $printSettingsForm
    )
    {
        $this->repository        = $repository;
        $this->printSettingsForm = $printSettingsForm;
    }

    /**
     * Return view to edit template of Print Settings.
     *
     * @return \Illuminate\View\View
     */
    public function editPrintSettingsTemplate($templateId)
    {
        $formOfTender = FormOfTender::find($templateId);

        return View::make('form_of_tender.print_settings_edit', array(
            'settings'   => $formOfTender->printSettings,
            'backRoute'  => route('form_of_tender.template.edit', [$templateId]),
            'isTemplate' => true,
            'templateId' => $templateId,
            'templateName' => $formOfTender->name,
        ));
    }

    /**
     * Return view to edit Print Settings.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     */
    public function editPrintSettings($project, $tenderId)
    {
        $tender = Tender::find($tenderId);

        return View::make('form_of_tender.print_settings_edit', array(
            'settings'     => $tender->formOfTender->printSettings,
            'backRoute'    => route('form_of_tender.edit', [$project->id, $tenderId]),
            'isTemplate'   => false,
            'project'      => $project,
            'tender'       => $tender,
        ));
    }

    /**
     * Updates Print Settings.
     *
     * @param $project
     * @param $printSettingsId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePrintSettings($project, $tenderId)
    {
        $input = Input::all();

        try
        {
            $this->printSettingsForm->validate($input);
        }
        catch(Laracasts\Validation\FormValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withInput();
        }

        $tender = Tender::find($tenderId);
        $settings = $tender->formOfTender->printSettings;

        $this->repository->savePrintSettings($settings, $input);

        $this->repository->addLogEntry($tender->formOfTender->id);

        return Redirect::to(route('form_of_tender.edit', array( $project->id, $tenderId )));
    }

    /**
     * Updates Print Settings.
     *
     * @param $templateId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePrintSettingsTemplate($templateId)
    {
        $input = Input::all();

        try
        {
            $this->printSettingsForm->validate($input);
        }
        catch(Laracasts\Validation\FormValidationException $e)
        {
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withInput();
        }

        $formOfTender = FormOfTender::find($templateId);

        $this->repository->savePrintSettings($formOfTender->printSettings, $input);

        $this->repository->addLogEntry($templateId);

        return Redirect::to(route('form_of_tender.template.edit', [$templateId]));
    }
}