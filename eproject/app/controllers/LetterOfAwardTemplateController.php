<?php

use Carbon\Carbon;
use PCK\Helpers\PdfHelper;
use PCK\LetterOfAward\LetterOfAward;
use PCK\LetterOfAward\LetterOfAwardRepository;
use PCK\Forms\LetterOfAwardTemplateForm;

class LetterOfAwardTemplateController extends BaseController {

    private $letterOfAwardRepo;
    private $form;

    public function __construct(LetterOfAwardRepository $letterOfAwardRepo, LetterOfAwardTemplateForm $form)
    {
        $this->letterOfAwardRepo = $letterOfAwardRepo;
        $this->form = $form;
    }

    public function index($templateId)
    {
        $letterOfAward = LetterOfAward::find($templateId);

        return View::make('letter_of_award.letterOfAward.index', [
            'letterOfAward'          => $letterOfAward,
            'isTemplate'             => $letterOfAward->is_template,
            'templateName'           => $letterOfAward->name,
            'printRoute'             => route('letterOfAward.template.process', [$letterOfAward->id]),
            'printSettingsEditRoute' => route('letterOfAward.template.print.settings.edit', [$letterOfAward->id]),
            'editLogRoute'           => route('letterOfAward.template.log.get', [$letterOfAward->id]),
        ]);
    }

    public function store()
    {
        $inputs = Input::all();
        $errors = null;
        $success = false;

        try
        {
            $this->form->validate($inputs);
            $this->letterOfAwardRepo->createNewTemplate($inputs);

            $success = true;
        }
        catch(\PCK\Exceptions\ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $errors = $e->getErrors();
        }
        
        return Response::json([
            'success' => $success,
            'errors'  => $errors,
        ]);
    }

    public function contractDetailsEdit($templateId)
    {
        $letterOfAward = LetterOfAward::find($templateId);

        return View::make('letter_of_award.letterOfAward.contract_details.edit', [
            'templateId'               => $letterOfAward->id,
            'contractDetails'          => $letterOfAward->contractDetail,
            'isTemplate'               => $letterOfAward->is_template,
            'templateName'             => $letterOfAward->name,
            'indexRoute'               => route('letterOfAward.template.index', [$templateId]),
            'populateContentsRoute'    => route('letterOfAward.template.contractDetails.get', [$templateId]),
            'saveContentsRoute'        => route('letterOfAward.template.contractDetails.save', [$templateId]),
            'canUserEditLetterOfAward' => true,
        ]);
    }

    public function getContractDetails($templateId)
    {
        $letterOfAward = LetterOfAward::find($templateId);

        return $this->letterOfAwardRepo->getContractDetail($letterOfAward);
    }

    public function saveContractDetails($templateId)
    {
        $inputs        = Input::all();
        $letterOfAward = LetterOfAward::find($templateId);
        $success       = $this->letterOfAwardRepo->saveContractDetails($letterOfAward, $inputs['contents']);

        return Response::json([
            'success' => $success
        ]);
    }

    public function signatoryEdit($templateId)
    {
        $letterOfAward = LetterOfAward::find($templateId);

        return View::make('letter_of_award.letterOfAward.signatory.edit', [
            'templateId'               => $letterOfAward->id,
            'signatory'                => $letterOfAward->signatory,
            'isTemplate'               => $letterOfAward->is_template,
            'templateName'             => $letterOfAward->name,
            'indexRoute'               => route('letterOfAward.template.index', [$templateId]),
            'populateContentsRoute'    => route('letterOfAward.template.signatory.get', [$templateId]),
            'saveContentsRoute'        => route('letterOfAward.template.signatory.save', [$templateId]),
            'canUserEditLetterOfAward' => true,
        ]);
    }

    public function getSignatory($templateId)
    {
        $letterOfAward = LetterOfAward::find($templateId);

        return $this->letterOfAwardRepo->getSignatory($letterOfAward);
    }

    public function saveSignatory($templateId)
    {
        $inputs        = Input::all();
        $letterOfAward = LetterOfAward::find($templateId);
        $success       = $this->letterOfAwardRepo->saveSignatory($letterOfAward, $inputs['contents']);

        return Response::json([
            'success' => $success
        ]);
    }

    public function clausesEdit($templateId)
    {
        $letterOfAward = LetterOfAward::find($templateId);
        $structuredClauses = $this->letterOfAwardRepo->getStructuredClauses($letterOfAward);

        return View::make('letter_of_award.letterOfAward.clauses.edit', [
            'templateId'               => $letterOfAward->id,
            'clauses'                  => json_encode($structuredClauses),
            'isTemplate'               => $letterOfAward->is_template,
            'templateName'             => $letterOfAward->name,
            'indexRoute'               => route('letterOfAward.template.index', [$templateId]),
            'populateContentsRoute'    => route('letterOfAward.template.clause.get', [$templateId]),
            'saveContentsRoute'        => route('letterOfAward.template.clause.save', [$templateId]),
            'canUserEditLetterOfAward' => true,
        ]);
    }

    public function saveClauses($templateId)
    {
        $inputs        = Input::all();
        $letterOfAward = LetterOfAward::find($templateId);
        $success       = $this->letterOfAwardRepo->saveClauses($letterOfAward, $inputs);

        return Response::json([
            'success' => $success,
            'url'     => route('letterOfAward.template.index', [$templateId]),
        ]);
    }

    public function print($templateId)
    {
        $letterOfAward        = LetterOfAward::find($templateId);
        $headerHeightInPixels = Input::get('h');

        $contractDetails = $this->letterOfAwardRepo->getContractDetail($letterOfAward);
        $printSettings   = $letterOfAward->printSetting;

        $data = [
            'contractDetails' => $contractDetails,
            'signatory'       => $this->letterOfAwardRepo->getSignatory($letterOfAward),
            'clauses'         => $this->letterOfAwardRepo->getStructuredClauses($letterOfAward),
            'printSettings'   => [
                'clause_font_size' => $printSettings->clause_font_size,
            ],
        ];

        $pdfHelper = new PdfHelper('letter_of_award.letterOfAward.print.layout', $data);

        $pdfHelper->setHeaderHtml($this->letterOfAwardRepo->getHeaderHtml($contractDetails, $printSettings));
        $pdfHelper->setOptions($this->letterOfAwardRepo->generatePdfOptions($printSettings, $headerHeightInPixels));

        return $pdfHelper->printPDF();
    }

    public function processLetterOfAward($templateId)
    {
        $letterOfAward = LetterOfAward::find($templateId);
        $printSettings = $letterOfAward->printSetting;

        $contractDetails = $letterOfAward->contractDetail;

        $content = $this->letterOfAwardRepo->getHeaderHtml($contractDetails, $printSettings);

        $content = PdfHelper::removeBreaksFromHtml($content);

        $routeGenerate = route('letterOfAward.template.print', [$templateId]);

        return View::make('letter_of_award.letterOfAward.print.getHeight', array(
            'content'       => $content,
            'routeGenerate' => $routeGenerate,
        ));
    }

    public function editPrintSettings($templateId)
    {
        $letterOfAward = LetterOfAward::find($templateId);

        return View::make('letter_of_award.letterOfAward.print.settings_edit', [
            'templateId'    => $letterOfAward->id,
            'isTemplate'    => $letterOfAward->is_template,
            'templateName'  => $letterOfAward->name,
            'printSettings' => $letterOfAward->printSetting,
            'indexRoute'    => route('letterOfAward.template.index', [$templateId]),
            'saveRoute'     => route('letterOfAward.template.print.settings.save', [$templateId]),
        ]);
    }

    public function savePrintSettings($templateId)
    {
        $inputs        = Input::all();
        $letterOfAward = LetterOfAward::find($templateId);

        $this->letterOfAwardRepo->savePrintSettings($letterOfAward, $inputs);

        return Redirect::route('letterOfAward.template.index', [$templateId]);
    }

    public function getLogs($templateId)
    {
        $letterOfAward = LetterOfAward::find($templateId);
        $logs          = $this->letterOfAwardRepo->getLogs($letterOfAward);

        foreach($logs as $key => $log)
        {
            $logs[$key]['date'] = Carbon::parse($log['date'])->format(\Config::get('dates.full_format'));
        }

        return Response::json([
            'logs' => $logs,
        ]);
    }
}

