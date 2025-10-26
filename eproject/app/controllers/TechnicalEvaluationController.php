<?php

use Carbon\Carbon;
use PCK\Companies\Company;
use PCK\Companies\CompanyRepository;
use PCK\ContractLimits\ContractLimitRepository;
use PCK\Forms\TechnicalEvaluationItemForm;
use PCK\Forms\TechnicalEvaluationSetReferenceForm;
use PCK\Notifications\EmailNotifier;
use PCK\Notifications\SystemNotifier;
use PCK\TechnicalAssessments\TechnicalAssessmentRepository;
use PCK\TechnicalEvaluationAttachments\TechnicalEvaluationAttachmentRepository;
use PCK\TechnicalEvaluationItems\TechnicalEvaluationItem as Item;
use PCK\TechnicalEvaluationItems\TechnicalEvaluationItemRepository;
use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReferenceRepository;
use PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReference;
use PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationTendererOption;
use PCK\TechnicalEvaluationTendererOption\TechnicalEvaluationResponseLog;
use PCK\TendererTechnicalEvaluationInformation\TendererTechnicalEvaluationInformation;
use PCK\TendererTechnicalEvaluationInformation\TendererTechnicalEvaluationInformationRepository;
use PCK\TechnicalEvaluationAttachmentListItems\TechnicalEvaluationAttachmentListItem;
use PCK\TechnicalEvaluationAttachments\TechnicalEvaluationAttachment;
use PCK\Projects\Project;
use PCK\Tenders\Tender;
use PCK\Tenders\TenderRepository;
use PCK\Users\UserRepository;
use PCK\WorkCategories\WorkCategoryRepository;
use PCK\ContractGroups\Types\Role;
use PCK\Verifier\Verifier;
use PCK\Verifier\Verifiable;
use PCK\Reports\TechnicalAssessmentFormReportGenerator;

class TechnicalEvaluationController extends \BaseController {

    private $itemRepository;
    private $itemForm;
    private $setReferenceRepository;
    private $workCategoryRepository;
    private $contractLimitRepository;
    private $setReferenceForm;
    private $userRepository;
    private $tenderRepository;
    private $tendererTechnicalEvaluationInformationRepository;
    private $companyRepository;
    private $emailNotifier;
    private $systemNotifier;
    private $technicalEvaluationAttachmentRepository;
    private $technicalAssessmentRepository;

    public function __construct(
        TechnicalEvaluationSetReferenceRepository $setReferenceRepository,
        TechnicalEvaluationItemRepository $itemRepository,
        TechnicalEvaluationAttachmentRepository $technicalEvaluationAttachmentRepository,
        WorkCategoryRepository $workCategoryRepository,
        ContractLimitRepository $contractLimitRepository,
        TenderRepository $tenderRepository,
        TechnicalEvaluationItemForm $itemForm,
        TechnicalEvaluationSetReferenceForm $setReferenceForm,
        TendererTechnicalEvaluationInformationRepository $tendererTechnicalEvaluationInformationRepository,
        CompanyRepository $companyRepository,
        EmailNotifier $emailNotifier,
        SystemNotifier $systemNotifier,
        UserRepository $userRepository,
        TechnicalAssessmentRepository $technicalAssessmentRepository
    )
    {
        $this->itemRepository                                   = $itemRepository;
        $this->itemForm                                         = $itemForm;
        $this->setReferenceRepository                           = $setReferenceRepository;
        $this->workCategoryRepository                           = $workCategoryRepository;
        $this->contractLimitRepository                          = $contractLimitRepository;
        $this->setReferenceForm                                 = $setReferenceForm;
        $this->userRepository                                   = $userRepository;
        $this->tenderRepository                                 = $tenderRepository;
        $this->tendererTechnicalEvaluationInformationRepository = $tendererTechnicalEvaluationInformationRepository;
        $this->companyRepository                                = $companyRepository;
        $this->emailNotifier                                    = $emailNotifier;
        $this->systemNotifier                                   = $systemNotifier;
        $this->technicalEvaluationAttachmentRepository          = $technicalEvaluationAttachmentRepository;
        $this->technicalAssessmentRepository                    = $technicalAssessmentRepository;
    }

    /**
     * Shows the resource listing for Sets.
     *
     * @return \Illuminate\View\View
     */
    public function setsIndex()
    {
        $setReferences  = $this->setReferenceRepository->templateSetReferences();
        $workCategories = $this->workCategoryRepository->getAll();
        $contractLimits = $this->contractLimitRepository->getAll();

        return View::make('technical_evaluation.definition.index', array(
            'setReferences'  => $setReferences,
            'workCategories' => $workCategories,
            'contractLimits' => $contractLimits,
        ));
    }

    /**
     * Stores the Set resource.
     *
     * @return array
     */
    public function storeSet()
    {
        $input        = Input::get('data');
        $workCategory = $this->workCategoryRepository->find($input['workCategoryId']);
        $routeSetShow = null;

        try
        {
            $contractLimit = $this->contractLimitRepository->saveOrFindContractLimit($input);

            $this->setReferenceForm->setParameters($workCategory, $contractLimit);
            $this->setReferenceForm->validate($input);

            $templateSetReference = null;
            if( ! empty( $input['templateId'] ) )
            {
                $templateSetReference = TechnicalEvaluationSetReference::find($input['templateId']);
            }

            $success = $this->setReferenceRepository->storeTemplate($workCategory, $contractLimit, $templateSetReference);

            $routeSetShow = route('technicalEvaluation.item.show', array( $this->setReferenceRepository->findTemplate($workCategory, $contractLimit)->set->id ));

            $errors = null;
        }
        catch(\Laracasts\Validation\FormValidationException $e)
        {
            $success = false;
            $errors  = $e->getErrors();
        }

        return array(
            'success'       => $success,
            'errors'        => $errors,
            'route:setShow' => $routeSetShow,
        );
    }

    public function storeDestroy($setReferenceId)
    {
        try
        {
            $setReference = \PCK\TechnicalEvaluationSetReferences\TechnicalEvaluationSetReference::find($setReferenceId);

            $setReference->delete();
        }
        catch(Exception $e)
        {
            Flash::error(trans('technicalEvaluation.deleteFailed'));

            return Redirect::back();
        }

        Flash::success(trans('technicalEvaluation.deleteSuccess'));

        return Redirect::back();
    }

    /**
     * Returns the view for the Item resource.
     *
     * @param $itemId
     *
     * @return \Illuminate\View\View
     */
    public function show($itemId)
    {
        $item = Item::find($itemId);

        return View::make('technical_evaluation.definition.show', array(
            'item' => $item,
        ));
    }

    /**
     * Stores the Item resource.
     *
     * @return array
     */
    public function store()
    {
        $input = Input::get('data');

        try
        {
            $this->itemForm->validate($input);

            $success = $this->itemRepository->store($input);
            $errors  = null;
        }
        catch(Exception $e)
        {
            $success = false;
            $errors  = $e->getErrors();
        }

        return array(
            'success' => $success,
            'errors'  => $errors,
        );
    }

    /**
     * Updates the Item resource.
     *
     * @return array
     */
    public function update()
    {
        $input = Input::get('data');

        try
        {
            $this->itemForm->validate($input);

            $success = $this->itemRepository->update($input);
            $errors  = null;
        }
        catch(Exception $e)
        {
            $success = false;
            $errors  = $e->getErrors();
        }

        return array(
            'success' => $success,
            'errors'  => $errors,
        );
    }

    /**
     * Deletes the Item resource.
     *
     * @param $itemId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($itemId)
    {
        try
        {
            $this->itemRepository->delete($itemId);
        }

        catch(Exception $e)
        {
            Flash::error(trans('technicalEvaluation.deleteFailed'));

            return Redirect::back();
        }

        Flash::success(trans('technicalEvaluation.deleteSuccess'));

        return Redirect::back();
    }

    /**
     * Saves the respondent's response.
     *
     * @param $project
     * @param $companyId
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws Exception
     */
    public function formUpdateWithoutProject($setReferenceId, $companyId)
    {
        $input = Input::all();

        $company      = Company::find($companyId);
        $setReference = $this->setReferenceRepository->find($setReferenceId);

        TechnicalEvaluationTendererOption::removeTendererOptions($company, $setReference->set);

        foreach(( $input['options'] ?? array() ) as $itemId => $optionId)
        {
            if( ! $item = Item::find($itemId) ) continue;

            $remarks = ( $input['remarks'][ $optionId ] ) ? $input['remarks'][ $optionId ] : null;

            TechnicalEvaluationTendererOption::add($company, Item::find($itemId), Item::find($optionId), $remarks);
        }

        TechnicalEvaluationResponseLog::logThis($setReference, $company);

        return Redirect::back();
    }

    /**
     * Saves the respondent's response.
     *
     * @param $project
     * @param $companyId
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws Exception
     */
    public function formUpdate($project, $companyId)
    {
        $input = Input::all();

        $company      = Company::find($companyId);
        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);

        TechnicalEvaluationTendererOption::removeTendererOptions($company, $setReference->set);

        foreach(( $input['options'] ?? array() ) as $itemId => $optionId)
        {
            if( ! $item = Item::find($itemId) ) continue;

            $remarks = ( $input['remarks'][ $optionId ] ) ? $input['remarks'][ $optionId ] : null;

            TechnicalEvaluationTendererOption::add($company, Item::find($itemId), Item::find($optionId), $remarks);
        }

        TechnicalEvaluationResponseLog::logThis($setReference, $company);

        return Redirect::back();
    }

    /**
     * Returns the view for the index of the results.
     *
     * @param $project
     *
     * @return \Illuminate\View\View
     */
    public function resultsIndex($project)
    {
        $completedCompanies = array();

        foreach($project->tenders as $tender)
        {
            $completedCompaniesPerTender = 0;

            foreach($tender->selectedFinalContractors as $contractor)
            {
                if( $this->technicalEvaluationAttachmentRepository->compulsoryAttachmentsSubmitted($project, $contractor) ) $completedCompaniesPerTender++;
            }

            $completedCompanies[ $tender->id ] = $completedCompaniesPerTender;
        }

        return View::make('technical_evaluation.results.index', array(
            'project'            => $project,
            'tenders'            => $project->tenders,
            'completedCompanies' => $completedCompanies,
        ));
    }

    /**
     * Returns the view for the results.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     */
    public function resultsShow($project, $tenderId)
    {
        $tender       = $this->tenderRepository->find($project, $tenderId);
        $tenderers    = $tender->selectedFinalContractors;
        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);

        $tendererTechnicalEvaluationInformation = array();

        foreach($tenderers as $tenderer)
        {
            $tendererTechnicalEvaluationInformation[ $tenderer->id ] = $this->createEntryForTenderer($tenderer, $tender);
        }

        $submitter           = $tender->technicalEvaluation ? $this->userRepository->find($tender->technicalEvaluation->submitted_by) : null;
        $isProjectOwnerOrGCD = \Confide::user()->hasCompanyProjectRole($project, array( Role::PROJECT_OWNER, Role::GROUP_CONTRACT ));

        return View::make('technical_evaluation.results.show', array(
            'project'                 => $project,
            'tender'                  => $tender,
            'tenderers'               => $tenderers,
            'setReference'            => $setReference,
            'technicalEvaluationInfo' => $tendererTechnicalEvaluationInformation,
            'submitter'               => $submitter,
            'isProjectOwnerOrGCD'     => $isProjectOwnerOrGCD
        ));
    }

    public function getFormResponsesWithoutProject()
    {
        $setReference = $this->setReferenceRepository->find(Request::get('id'));

        return Response::json([
            'technical_evaluation_set' => $setReference->getCompleteSet(),
        ]);
    }

    public function getFormResponses($project, $tenderId)
    {
        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);

        $tender       = $this->tenderRepository->find($project, $tenderId);
        $tenderer     = Company::findOrFail(Request::get('id'));
        $formRoute    = route((Confide::user()->company_id == $tenderer->id) ? 'technicalEvaluation.form.update' : 'technicalEvaluation.form.update.foreign', [$project->id, $tenderer->id]);

        return Response::json([
            'company_name'             => $tenderer->name,
            'technical_evaluation_set' => $setReference->getCompleteSet(),
            'selected_options'         => TechnicalEvaluationTendererOption::getTendererOptionIds($tenderer, $setReference->set),
            'option_remarks'           => TechnicalEvaluationTendererOption::getAllOptionRemarks($tenderer, $setReference->set),
            'submission_date'          => $setReference->getAttachmentsSubmissionTime($tenderer),
            'form_route'               => $formRoute,
            'log_route'                => route('technicalEvaluation.results.show.tenderers.formResponses.log', [$project->id, $tender->id, $tenderer->id]),
        ]);
    }

    public function getFormResponseLog($project, $tenderId, $companyId)
    {
        $user = \Confide::user();

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);

        $model = TechnicalEvaluationResponseLog::select('technical_evaluation_response_log.id', 'users.name', 'technical_evaluation_response_log.created_at')
            ->join('users', 'users.id', '=', 'technical_evaluation_response_log.user_id')
            ->where('technical_evaluation_response_log.set_reference_id', '=', $setReference->id)
            ->where('technical_evaluation_response_log.company_id', '=', $companyId);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('users.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('technical_evaluation_response_log.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'        => $record->id,
                'counter'   => $counter,
                'name'      => $record->name,
                'timestamp' => Carbon::parse($record->created_at)->format(\Config::get('dates.created_at')),
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function resultsShowTenderers($project, $tenderId)
    {
        $tender = Tender::find($tenderId);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = Company::select("companies.id", "companies.name", "tenderer_technical_evaluation_information.shortlisted")
            ->leftJoin('tenderer_technical_evaluation_information', function($join) use ($tenderId){
                $join->on('tenderer_technical_evaluation_information.company_id', '=', 'companies.id');
                $join->on('tenderer_technical_evaluation_information.tender_id', '=', \DB::raw($tenderId));
            })
        ->whereIn('companies.id', $tender->selectedFinalContractors->lists('id'));

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'name':
                        if(strlen($val) > 0)
                        {
                            $model->where('companies.name', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('companies.name', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);

        $companyAttachmentCount = TechnicalEvaluationAttachment::select('technical_evaluation_attachments.company_id', \DB::raw("count(*)"))
            ->leftJoin('technical_evaluation_attachment_list_items', 'technical_evaluation_attachment_list_items.id', '=', 'technical_evaluation_attachments.item_id')
            ->where('technical_evaluation_attachment_list_items.set_reference_id', $setReference->id)
            ->groupBy('technical_evaluation_attachments.company_id')
            ->get();

        $companyAttachmentCount = $companyAttachmentCount->lists('count', 'company_id');

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'                 => $record->id,
                'counter'            => $counter,
                'name'               => $record->name,
                'submitted_at'       => $project->getProjectTimeZoneTime($setReference->getAttachmentsSubmissionTime($record)),
                'score'              => number_format(TechnicalEvaluationTendererOption::getTendererScore($record, $setReference->set), 2),
                'remarks'            => $this->createEntryForTenderer($record, $tender)->remarks,
                'shortlisted'        => $record->shortlisted,
                'route:attachments'  => route('technicalEvaluation.results.show.tenderers.attachments', [$project->id, $tenderId, $record->id]),
                'route:download_all' => route('technicalEvaluation.results.show.tenderers.attachments.download', [$project->id, $tenderId, $record->id]),
                'attachments_count'  => $companyAttachmentCount[$record->id] ?? 0,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function resultsShowTendererAttachments($project, $tenderId, $companyId)
    {
        $tender = Tender::find($tenderId);

        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);

        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $model = TechnicalEvaluationAttachmentListItem::select(
                "technical_evaluation_attachment_list_items.id",
                "technical_evaluation_attachments.id as attachment_id",
                "technical_evaluation_attachment_list_items.description",
                "technical_evaluation_attachment_list_items.compulsory",
                "technical_evaluation_attachments.filename")
            ->leftJoin('technical_evaluation_attachments', function($join) use ($companyId){
                $join->on('technical_evaluation_attachments.item_id', '=', 'technical_evaluation_attachment_list_items.id');
                $join->on('technical_evaluation_attachments.company_id', '=', \DB::raw($companyId));
            })
            ->where('technical_evaluation_attachment_list_items.set_reference_id', $setReference->id);

        if($request->has('filters'))
        {
            foreach($request->get('filters') as $filters)
            {
                if(!isset($filters['value']) || is_array($filters['value'])) continue;

                $val = trim($filters['value']);

                switch(trim(strtolower($filters['field'])))
                {
                    case 'description':
                        if(strlen($val) > 0)
                        {
                            $model->where('technical_evaluation_attachment_list_items.description', 'ILIKE', '%'.$val.'%');
                        }
                        break;
                }
            }
        }

        $model->orderBy('technical_evaluation_attachment_list_items.description', 'asc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $attachmentObjects = TechnicalEvaluationAttachment::whereIn('id', $records->lists('attachment_id'))
            ->where('company_id', '=', $companyId)
            ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'id'             => $record->id,
                'counter'        => $counter,
                'description'    => $record->description,
                'compulsory'     => $record->compulsory,
                'filename'       => $record->attachment_id ? $attachmentObjects->find($record->attachment_id)->upload->filename : '-',
                'route:download' => $record->attachment_id ? route('technicalEvaluation.results.fileDownload', [$project->id, $companyId, $record->attachment_id]) : null,
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return Response::json([
            'last_page' => $totalPages,
            'data'      => $data
        ]);
    }

    public function downloadTendererAttachments($project, $tenderId, $companyId)
    {
        $company = Company::find($companyId);

        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);

        $attachmentObjects = TechnicalEvaluationAttachment::whereHas('listItem', function($query) use ($setReference){
                $query->where('set_reference_id', '=', $setReference->id);
            })
            ->where('company_id', '=', $companyId)
            ->get();

        $filesToZip = [];

        foreach($attachmentObjects as $attachmentObject)
        {
            \PCK\Helpers\Files::copy($attachmentObject->upload->getFullFilePath(), $downloadPath = \PCK\Helpers\Files::getTmpFileUri());

            $filesToZip[$attachmentObject->upload->filename] = $downloadPath;
        }

        if( empty($filesToZip) )
        {
            Flash::error(trans('files.noFilesToDownload'));

            return Redirect::back();
        }

        $pathToZipFile = \PCK\Helpers\Zip::zip($filesToZip);

        foreach($filesToZip as $filepath)
        {
            \PCK\Helpers\Files::deleteFile($filepath);
        }

        $fileName = trans('technicalEvaluation.technicalEvaluation') . " ({$company->name})";

        $zipName = "{$fileName}.".\PCK\Helpers\Files::EXTENSION_ZIP;

        return \PCK\Helpers\Files::download(
            $pathToZipFile,
            $zipName,
            array(
                'Content-Type: application/zip',
            )
        );
    }

    /**
     * Returns the view of the overall Summary.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     */
    public function overallSummary($project, $tenderId)
    {
        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);
        $tender       = $this->tenderRepository->find($project, $tenderId);
        $tenderers    = $tender->selectedFinalContractors;

        return View::make('technical_evaluation.results.summary', array(
            'project'      => $project,
            'tender'       => $tender,
            'tenderers'    => $tenderers,
            'setReference' => $setReference,
        ));
    }

    public function overallSummaryExcelExport($project, $tenderId)
    {
        $tender       = $this->tenderRepository->find($project, $tenderId);
        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);

        $reportGenerator = new \PCK\Reports\TechnicalEvaluationOverallSummaryReportGenerator();

        return $reportGenerator->generate($tender, $setReference);
    }

    /**
     * Returns the view for the in-depth Summary.
     *
     * @param $project
     * @param $tenderId
     * @param $aspectId
     *
     * @return \Illuminate\View\View
     * @throws Exception
     */
    public function inDepthSummary($project, $tenderId, $aspectId)
    {
        $aspect = Item::find($aspectId);

        Item::validateType($aspect, Item::TYPE_ASPECT);

        $setReference = $this->setReferenceRepository->getSetReferenceByProject($project);
        $tender       = $this->tenderRepository->find($project, $tenderId);
        $tenderers    = $tender->selectedFinalContractors;

        $selectedOptionIds = array();

        foreach($tenderers as $tenderer)
        {
            $selectedOptionIds[ $tenderer->id ] = TechnicalEvaluationTendererOption::getAllOptionRemarks($tenderer, $setReference->set);
        }

        return View::make('technical_evaluation.results.in_depth', array(
            'project'           => $project,
            'tender'            => $tender,
            'tenderers'         => $tenderers,
            'setReference'      => $setReference,
            'aspect'            => $aspect,
            'selectedOptionIds' => $selectedOptionIds,
        ));
    }

    /**
     * Returns the view to select verifiers.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     */
    public function selectVerifierForm($project, $tenderId)
    {
        $user              = \Confide::user();
        $tender            = $this->tenderRepository->find($project, $tenderId);
        $selectedCompanies = $project->selectedCompanies;
        $selectedUsers     = $this->userRepository->getSelectedProjectUsersGroupByCompany($project);
        $selectedVerifiers = $tender->technicalEvaluationVerifiers->lists('id');
        $isEditor          = $user->isEditor($project);

        return View::make('technical_evaluation.results.select_verifiers_form', array(
            'project'           => $project,
            'tender'            => $tender,
            'selectedCompanies' => $selectedCompanies,
            'selectedUsers'     => $selectedUsers,
            'selectedVerifiers' => $selectedVerifiers,
            'isEditor'          => $isEditor,
            'user'              => $user,
        ));
    }

    /**
     * Assigns users as verifiers.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignVerifiers($project, $tenderId)
    {
        $inputs = Input::all();
        $tender = $this->tenderRepository->find($project, $tenderId);

        DB::transaction(function() use ($project, $tender, $inputs)
        {
            $this->tenderRepository->syncSelectedTechnicalEvaluationVerifiers($tender, $inputs);

            $this->tenderRepository->updateTenderTechnicalEvaluationVerificationStatus($tender, $inputs);

            Flash::success("Successfully updated Tender ({$tender->current_tender_name}) Technical Evaluation Verifier(s) Selection !");

            if( $tender->technicalEvaluationIsBeingValidated() )
            {
                Event::fire('system.notifyTechnicalEvaluationVerifiers', array( $tender ));
            }
        });

        return Redirect::back();
    }

    /**
     * Returns the view for the verification form.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     */
    public function showTechnicalEvaluationVerifierDecisionForm($project, $tenderId)
    {
        $user   = \Confide::user();
        $tender = $this->tenderRepository->find($project, $tenderId);

        if( $tender->technicalEvaluationIsSubmitted() )
        {
            return View::make('technical_evaluation.verification.submitted_message', compact('user', 'project', 'tender'));
        }

        return View::make('technical_evaluation.verification.verifier_decision_form', compact('user', 'project', 'tender'));
    }

    /**
     * Updates resources based on the verification form.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processTechnicalEvaluationVerifierDecisionForm($project, $tenderId)
    {
        $inputs = Input::all();
        $tender = $this->tenderRepository->find($project, $tenderId);

        DB::transaction(function() use ($project, $tender, $inputs)
        {
            $tender = $this->tenderRepository->updateTenderTechnicalEvaluationVerificationStatus($tender, $inputs);

            if( $tender->technicalEvaluationIsSubmitted() )
            {
                $this->emailNotifier->sendTechnicalOpeningSubmittedNotifications($tender);

                Flash::success("Successfully updated Tender ({$tender->current_tender_name}) Status !");
            }
            else
            {
                Flash::success('Thank you for verifying !');
            }
        });

        return Redirect::back();
    }

    /**
     * Resend email for technical evaluation verification.
     *
     * @param $project
     * @param $tenderId
     * @param $receiverId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resendTechnicalEvaluationVerifierEmail($project, $tenderId, $receiverId)
    {
        $tender = $this->tenderRepository->find($project, $tenderId);
        $user   = $this->tenderRepository->getTechnicalEvaluationVerifierDetail($tender, $receiverId);

        if( ! $user )
        {
            Flash::error('Sorry, we cannot process your request to send the Verification Email because the verifier is non-existent.');

            return Redirect::back();
        }

        Event::fire('system.notifyTechnicalEvaluationVerifiers', array( $tender, $user ));

        Flash::success("Successfully sent Technical Evaluation Verification Email to {$user->email}.");

        return Redirect::back();
    }

    /**
     * Returns the view for the technical evaluation verifier logs.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     */
    public function viewTechnicalEvaluationVerifierLogs($project, $tenderId)
    {
        $tender = $this->tenderRepository->find($project, $tenderId);

        return View::make('technical_evaluation.verification.verifier_log', compact('tender', 'project'));
    }

    /**
     * Updates the remark for the tenderer.
     *
     * @param $project
     * @param $tenderId
     *
     * @return array
     */
    public function updateRemark($project, $tenderId)
    {
        $input    = Input::all();
        $tenderer = $this->companyRepository->find($input['tenderer_id']);
        $tender   = $this->tenderRepository->find($project, $tenderId);

        $success = $this->tendererTechnicalEvaluationInformationRepository->update($tenderer, $tender, $input);

        return array(
            'success' => $success,
        );
    }

    /**
     * Stops the current Technical Evaluation Verification process and
     * reassigns the Technical Evaluation verifiers.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reassignVerifiers($project, $tenderId)
    {
        $this->tenderRepository->reassignTechnicalEvaluationVerifiers($this->tenderRepository->find($project, $tenderId));

        Flash::success('Reassigned verifiers.');

        return Redirect::back();
    }

    private function createEntryForTenderer($tenderer, $tender)
    {
        $company = $this->companyRepository->find($tenderer->id);
        $record  = $this->tendererTechnicalEvaluationInformationRepository->findOrNew($company, $tender);

        if( ! $record->exists ) $record->save();

        return $record;
    }

    public function syncTenderer($project, $tenderId)
    {
        $inputs     = Input::all();
        $tendererId = $inputs['tenderer'];
        $status     = ( $inputs['status'] === 'true' ) ? true : false;
        $tender     = Tender::find($tenderId);

        $company             = $this->companyRepository->find($tendererId);
        $record              = $this->tendererTechnicalEvaluationInformationRepository->findOrNew($company, $tender);
        $record->shortlisted = $status;

        return ( $record->save() ) ? 'success' : 'failed';
    }

    public function confirmAssessment($project, $tenderId)
    {
        $tender               = Tender::find($tenderId);
        $selectedTenderers    = $this->getTenderersByShortlistedStatus($tender->tendererTechnicalEvaluationInformation, true);
        $notSelectedTenderers = $this->getTenderersByShortlistedStatus($tender->tendererTechnicalEvaluationInformation, false);
        $roles                = Role::getRolesExcept(Role::CONTRACTOR); //exclude contractors

        $data = array(
            'tender'               => $tender,
            'project'              => $project,
            'selectedTenderers'    => $selectedTenderers,
            'notSelectedTenderers' => $notSelectedTenderers,
            'verifiers'            => $this->getVerifiers($project, $roles),
            'setReference'         => $this->setReferenceRepository->getSetReferenceByProject($project),
        );

        $tenderersRemarks = [];

        $records = TendererTechnicalEvaluationInformation::where('tender_id', $tenderId)->get();

        foreach($records as $record)
        {
            $tenderersRemarks[ $record->company_id ] = $record->remarks;
        }

        $data['tendererRemarks']   = $tenderersRemarks;
        $data['isCurrentVerifier'] = false;
        $data['remarks']           = null;

        if( $tender->technicalEvaluation )
        {
            $data['selectedVerifiers']        = Verifier::getAssignedVerifierRecords($tender->technicalEvaluation, false);
            $data['submitter']                = $this->userRepository->find($tender->technicalEvaluation->submitted_by);
            $data['isCurrentVerifier']        = Verifier::isCurrentVerifier(\Confide::user(), $tender->technicalEvaluation);
            $data['approvalProcessCompleted'] = Verifier::isApproved($tender->technicalEvaluation);
            $data['targetedDateOfAward']      = Carbon::parse($tender->technicalEvaluation->targeted_date_of_award)->format(Config::get('dates.submitted_at'));
            $data['verificationLogs']         = Verifier::getLog($tender->technicalEvaluation, true);
            $data['assignedVerifierRecords']  = Verifier::getAssignedVerifierRecords($tender->technicalEvaluation, true);
            $data['uploadedFiles']            = $this->getAttachmentDetails($tender->technicalEvaluation);
            $data['remarks']                  = $tender->technicalEvaluation->remarks;
        }

        return View::make('technical_evaluation.results.confirmation', $data);
    }

    private function getTenderersByShortlistedStatus($techEvalInfo, $flag)
    {
        $results = array();

        $records = $techEvalInfo->reject(function($obj) use ($flag)
        {
            return $obj->shortlisted != $flag;
        });

        foreach($records as $record)
        {
            array_push($results, $this->companyRepository->find($record->company_id));
        }

        return $results;
    }

    private function getVerifiers($project, $roles)
    {
        $verifiers = array();

        foreach($roles as $role)
        {
            if( ! $project->getCompanyByGroup($role) ) continue;

            $company = $project->getCompanyByGroup($role);

            foreach($company->getVerifierList($project) as $verifier)
            {
                array_push($verifiers, $verifier);
            }
        }

        return $verifiers;
    }

    public function submitTechnicalAssessmentForApproval($project, $tenderId)
    {
        $inputs = Input::all();
        $verifiers = [];

        if(array_key_exists('verifiers', $inputs))
        {
            $verifiers = array_filter($inputs['verifiers'], function($value)
            {
                return $value != "";
            });
        }

        $inputs['targeted_date_of_award'] = $project->getAppTimeZoneTime($inputs['targeted_date_of_award'] ?? null);

        $tender = Tender::find($tenderId);

        $technicalEvaluation = $this->technicalAssessmentRepository->createOrUpdateSubmittedTechnicalAssessmentData($tender, $inputs);

        if(empty($verifiers))
        {
            Verifier::setVerifierAsApproved(\Confide::user(), $technicalEvaluation);
        }
        else
        {
            Verifier::setVerifiers($verifiers, $technicalEvaluation);
            Verifier::sendPendingNotification($technicalEvaluation);
        }

        return Redirect::to(URL::previous());
    }

    public function updateTechnicalAssessmentApprovalStatus()
    {
        $inputs          = Input::all();
        $approve         = $inputs['verification_approve'] === 'approve';
        $remarks         = $inputs['verifier_remark'];
        $tender          = Tender::find($inputs['tenderId']);
        $techAssessment  = $tender->technicalEvaluation;
        $currentVerifier = Verifier::getCurrentVerifier($techAssessment);

        Verifier::updateVerifierRemarks($techAssessment, $remarks);
        Verifier::approve($techAssessment, $approve);

        if( $approve )
        {
            $this->notifyRequestorTechnicalAssessmentFormResponse($tender, $techAssessment, $currentVerifier);

            // not all verifiers responded
            if( Verifier::isBeingVerified($techAssessment) )
            {
                Verifier::sendPendingNotification($tender->technicalEvaluation);
            }
            else
            {
                Verifier::sendApprovedNotification($tender->technicalEvaluation);
            }
        }
        else
        {
            $this->resetTechnicalAssessmentApprovalProcess($tender);
            Verifier::sendRejectedNotification($techAssessment);

            return Redirect::route('technicalEvaluation.results.show', array( $tender->project->id, $tender->id ));
        }

        return Redirect::to(URL::previous());
    }

    private function notifyRequestorTechnicalAssessmentFormResponse(Tender $tender, Verifiable $object, $verifier)
    {
        $this->emailNotifier->sendVerificationResponseEmailTechnicalAssessment($tender, $object, $verifier);
        $this->systemNotifier->sendVerificationResponseNotificationTechnicalAssessment($tender, $object);
    }

    private function resetTechnicalAssessmentApprovalProcess($tender)
    {
        $tender->technicalEvaluation->resetSubmitter();
    }

    public function sendPendingVerificationEmailReminder($project)
    {
        Verifier::sendPendingNotification($project->latestTender->technicalEvaluation);
    }

    public function toggleHide($setReferenceId)
    {
        try
        {
            $templateSetReference = TechnicalEvaluationSetReference::find($setReferenceId);

            $templateSetReference->hidden = !$templateSetReference->hidden;

            $templateSetReference->save();

            $success  = true;
            $errorMsg = "";
        }
        catch(\Exception $e)
        {
            $success  = false;
            $errorMsg = $e->getMessage();
        }

        return Response::json([
            'success' => $success,
            'error'   => $errorMsg
        ]);
    }

    public function technicalAssessmentExport(Project $project, $tenderId)
    {
        $tender               = Tender::find($tenderId);
        $setReference         = $this->setReferenceRepository->getSetReferenceByProject($project);
        $selectedTenderers    = $this->getTenderersByShortlistedStatus($tender->tendererTechnicalEvaluationInformation, true);
        $notSelectedTenderers = $this->getTenderersByShortlistedStatus($tender->tendererTechnicalEvaluationInformation, false);

        $reportGenerator = new TechnicalAssessmentFormReportGenerator();

        return $reportGenerator->generate($tender, $setReference, $selectedTenderers, $notSelectedTenderers);
    }
}