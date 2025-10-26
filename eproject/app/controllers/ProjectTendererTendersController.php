<?php

use PCK\Base\Helpers;
use PCK\Buildspace\ProjectMainInformation;
use PCK\Exceptions\ValidationException;
use PCK\FormOfTender\FormOfTenderRepository;
use PCK\Forms\Contracts\TendererRatesInformationForm;
use PCK\Tenders\Tender;
use PCK\Forms\TendererRateForm;
use PCK\Helpers\Files;
use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReferenceRepository;
use PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationTendererOption;
use PCK\Tenders\SubmitTenderRate;
use PCK\Tenders\TenderRepository;
use PCK\Companies\CompanyRepository;
use PCK\Forms\TendererRateAttachmentsForm;
use PCK\Tenders\Services\GetTenderAmountFromImportedZip;
use PCK\Tenders\AcknowledgementLetter;
use PCK\Helpers\PdfHelper;

class ProjectTendererTendersController extends \BaseController {

    private $tendererRateForm;

    private $tendererRateAttachmentsForm;

    private $companyRepo;

    private $tenderRepo;
    private $formOfTenderRepository;
    private $setReferenceRepository;
    private $tendererRatesInformationForm;

    public function __construct(
        TendererRateForm $tendererRateForm,
        TendererRatesInformationForm $tendererRatesInformationForm,
        TendererRateAttachmentsForm $tendererRateAttachmentsForm,
        CompanyRepository $companyRepo,
        TenderRepository $tenderRepo,
        FormOfTenderRepository $formOfTenderRepository,
        TechnicalEvaluationSetReferenceRepository $setReferenceRepository
    )
    {
        $this->tendererRateForm             = $tendererRateForm;
        $this->tendererRatesInformationForm = $tendererRatesInformationForm;
        $this->tendererRateAttachmentsForm  = $tendererRateAttachmentsForm;
        $this->companyRepo                  = $companyRepo;
        $this->tenderRepo                   = $tenderRepo;
        $this->formOfTenderRepository       = $formOfTenderRepository;
        $this->setReferenceRepository       = $setReferenceRepository;
    }

    public function index($project)
    {
        $user = \Confide::user();

        $contractor = $this->companyRepo->getTendersByCompanyAndProject($user->company, $project);

        return View::make('projects.tenderingIndex', compact('project', 'contractor'));
    }

    public function showSubmitTender($project, $tenderId)
    {
        $user                      = \Confide::user();
        $contractor                = $this->companyRepo->getTendersByCompanyAndProject($user->company, $project, $tenderId);
        $tender                    = $contractor->tenders->first();
        $listOfTendererInformation = $tender->listOfTendererInformation;
        $acknowledgementLetter     = $tender->acknowledgementLetter ? AcknowledgementLetter::find($tender->acknowledgementLetter->id) : null;
        $tenderAlternativeData     = array();

        // get back previous uploaded file's info if submitted before
        if( $tender->pivot->submitted )
        {
            $uploadedFiles = $this->getAttachmentDetails($tender->pivot);

            $tenderAlternativeData = $this->formOfTenderRepository->getPrintTenderAlternativesAfterContractorInput($project->id, $tenderId, $contractor->id);
        }
        else
        {
            $uploadedFiles = $this->getAttachmentDetails();
        }

        $setReference      = null;
        $set               = null;
        $selectedOptionIds = array();
        $optionRemarks     = array();

        if( $listOfTendererInformation->technical_evaluation_required && ( $setReference = $this->setReferenceRepository->getSetReferenceByProject($project) ) )
        {
            $set               = $setReference->set;
            $selectedOptionIds = TechnicalEvaluationTendererOption::getTendererOptionIds($contractor, $set);
            $optionRemarks     = TechnicalEvaluationTendererOption::getAllOptionRemarks($contractor, $set);
        }
        
        return View::make('projects.tenderRates', array(
            'project'                   => $project,
            'tender'                    => $tender,
            'contractor'                => $contractor,
            'user'                      => $user,
            'uploadedFiles'             => $uploadedFiles,
            'tenderAlternativeData'     => $tenderAlternativeData,
            'currencySymbol'            => $project->modified_currency_code,
            'setReference'              => $setReference,
            'set'                       => $set,
            'selectedOptionIds'         => $selectedOptionIds,
            'optionRemarks'             => $optionRemarks,
            'acknowledgementLetter'     => $acknowledgementLetter
        ));
    }

    public function saveSubmitTender($project, $tenderId)
    {
        // trim all empty values
        Input::merge(array_map('trim', Input::all()));

        $input = Input::all();

        $this->tendererRateForm->validate($input);

        $rates = Input::file('rates');

        if ($rates && strtolower($rates->getClientOriginalExtension()) !== 'tr') {
            Flash::error(trans('files.extensionMismatchRates'));
            return Redirect::back()->withErrors(['rates' => trans('files.extensionMismatchRates')]);
        }

        $user       = \Confide::user();
        $contractor = $this->companyRepo->getTendersByCompanyAndProject($user->company, $project, $tenderId);
        $tender     = $contractor->tenders->first();

        try
        {
            $this->tenderRepo->saveRates($tender, $rates, $user, $contractor, $input);

            Flash::success(trans('files.uploadSuccessRates'));
        }
        catch(ValidationException $e)
        {
            Flash::error($e->getMessage());
        }

        return Redirect::route('projects.submitTender.rates', array( $project->id, $tender->id ));
    }

    public function saveTenderRateInformation($project, $tenderId)
    {
        $inputs = Input::all();

        $filteredInputs = [];

        foreach($inputs as $key => $input)
        {
            if(is_array($input))
            {
                foreach($input as $tenderAlternativeId => $val)
                {
                    if(!is_null($val) && $val !== '')
                    {
                        $filteredInputs[$key][$tenderAlternativeId] = $val;
                    }
                    
                }
            }
            elseif(!is_null($input) && $input !== '')
            {
                $filteredInputs[$key] = $input;
            }
        }

        unset($inputs);
        
        $this->tendererRatesInformationForm->reAdjustValidationRule($project->latestTender->callingTenderInformation);

        $this->tendererRatesInformationForm->validate($filteredInputs);

        $user       = \Confide::user();
        $contractor = $this->companyRepo->getTendersByCompanyAndProject($user->company, $project, $tenderId);
        $tender     = $contractor->tenders->first();

        try
        {
            $this->tenderRepo->updateTenderRateInformation($contractor, $tender, $filteredInputs);

            Flash::success(trans('tenders.updatedTenderRatesInformation'));
        }
        catch(ValidationException $e)
        {
            Flash::error($e->getMessage());
        }
        
        return Redirect::route('projects.submitTender.rates', array( $project->id, $tender->id ));
    }

    public function saveSubmitTenderAttachments($project, $tenderId)
    {
        $inputs = Input::all();

        $this->tendererRateAttachmentsForm->validate($inputs);

        $user       = \Confide::user();
        $contractor = $this->companyRepo->getTendersByCompanyAndProject($user->company, $project, $tenderId);

        $tender = $contractor->tenders->first();

        $this->tenderRepo->updateSubmitTenderRateAttachments($tender, $inputs);

        Flash::success('Tender Rates Attachment(s) Submitted!');

        return Redirect::route('projects.submitTender.rates', array( $project->id, $tender->id ));
    }

    public function downloadTenderRatesFile($project, $tenderId, $contractorId)
    {
        $company = $this->companyRepo->find($contractorId);
        $tender  = $this->tenderRepo->find($project, $tenderId);

        $file_name = PCK\Tenders\SubmitTenderRate::ratesFileName;

        $file = PCK\Tenders\SubmitTenderRate::getContractorRatesUploadPath($project, $tender,
                $company) . "/{$file_name}";

        return Response::download($file, null, array(
            'Content-Type: application/force-download'
        ));
    }

    public function printTenderAcknowledgementLetterDraft($project, $tenderId)
    {
        $tender                = Tender::find($tenderId);
        $acknowledgementLetter = $tender->acknowledgementLetter ? AcknowledgementLetter::find($tender->acknowledgementLetter->id) : null;

        $view = 'tenders.template.acknowledgement_letter_template';
        $data = array(
            'projectTitle' => $project->title,
            'companyName'  => \Confide::user()->Company->name,
            'dateTime'     => \Carbon\Carbon::now($project->timezone),
            'content'      => $acknowledgementLetter->letter_content ?? null,
        );

        $pdfHelper = new PdfHelper($view, $data);
        $pdfHelper->printPDF();
    }

    public function checkTenderSubmission($project, $tenderId)
    {
        $user                  = \Confide::user();
        $contractor            = $this->companyRepo->getTendersByCompanyAndProject($user->company, $project, $tenderId);
        $tender                = $contractor->tenders->first();
        $acknowledgementLetter = $tender->acknowledgementLetter ? AcknowledgementLetter::find($tender->acknowledgementLetter->id) : null;

        $success = false;

        if( isset( $acknowledgementLetter ) )
        {
            if( $tender->pivot->tenderSubmissionIsComplete() )
            {
                $success = $acknowledgementLetter->enable_letter;
            }
        }

        return Response::json(array(
            'success'               => $success,
            'acknowledgementLetter' => $acknowledgementLetter
        ));
    }

}