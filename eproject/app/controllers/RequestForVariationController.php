<?php

use Carbon\Carbon;
use PCK\Base\Upload;
use PCK\RequestForVariation\RequestForVariationRepository;
use PCK\RequestForVariation\RequestForVariationCategory;
use PCK\RequestForVariation\RequestForVariation;
use PCK\RequestForVariation\RequestForVariationFile;
use PCK\MyCompanyProfiles\MyCompanyProfileRepository;
use PCK\Projects\Project;
use PCK\Verifier\Verifier;
use GuzzleHttp\Client;
use PCK\RequestForVariation\RequestForVariationActionLog;
use PCK\Buildspace\VariationOrderItem;

class RequestForVariationController extends BaseController {
    private $rfvRepository;
    private $myCompanyProfileRepository;

    public function __construct(RequestForVariationRepository $rfvRepository, MyCompanyProfileRepository $myCompanyProfileRepository)
    {
        $this->rfvRepository              = $rfvRepository;
        $this->myCompanyProfileRepository = $myCompanyProfileRepository;
    }

    public function index(Project $project)
    {
        $user = \Confide::user();

        $userPermissionGroups = [];

        $userPermissionGroups[''] = trans('general.none');
        
        foreach($user->getRequestForVariationUserPermissionGroups($project) as $group)
        {
            $userPermissionGroups[$group->name] = $group->name;
        }

        $requestForVariationCategories = [];

        $requestForVariationCategories[''] = trans('general.none');

        foreach(RequestForVariationCategory::all() as $rfvCategory)
        {
            $requestForVariationCategories[$rfvCategory->name] = $rfvCategory->name;
        }

        return View::make('request_for_variation.rfv.index', [
            'project'                          => $project,
            'userPermissionGroups'             => json_encode($userPermissionGroups),
            'requestForVariationCategories'    => json_encode($requestForVariationCategories),
            'canCreateRfv'                     => $user->canCreateRequestForVariationForProject($project),
            'canViewContractAndContingencySum' => $user->canAccessRequestForVariationContractAndContingencySumForProject($project),
            'canViewVOReportForProject'        => $user->canAccessRequestForVariationVOReportForProject($project),
            'contractAndContingencySumFilled'  => ($project->requestForVariationContractAndContingencySum),
        ]);
    }

    public function listRequestForVariationByGroup()
    {
        $inputs  = Input::all();
        $user    = \Confide::user();
        $project = Project::find($inputs['projectId']);

        return Response::json($this->rfvRepository->listRequestForVariationByGroup($project, $user));
    }

    public function getRfvAmountInfo()
    {
        $inputs = Input::all();
        $project = Project::find($inputs['projectId']);
        $rfvIds = isset($inputs['rfvIds']) ? $inputs['rfvIds'] : null;

        $rfvOverallAmountByUser              = is_null($rfvIds) ? 0.0 : $project->getOverallRfvAmountByUser(\Confide::user(), $rfvIds);
        $rfvProposedCostEstimate             = is_null($rfvIds) ? 0.0 : $project->getProposedRfvAmountByUser(\Confide::user(), $rfvIds);
        $accumulativeApprovedRfvAmountByUser = is_null($rfvIds) ? 0.0 : $project->getAccumulativeApprovedRfvAmountByUser(\Confide::user(), $rfvIds);

        $data = [];

        array_push($data, [
            'rfvOverallTotalAmountByUser'         => $rfvOverallAmountByUser,
            'rfvProposedCostEstimate'             => $rfvProposedCostEstimate,
            'accumulativeApprovedRfvAmountByUser' => $accumulativeApprovedRfvAmountByUser,
        ]);

        return Response::json($data);
    }

    public function contractAndContingencySumFormShow(Project $project)
    {
        return View::make('request_for_variation.rfv.contractAndContingencyShow', [
            'project'                               => $project,
            'isContractAndContingencySumUpdated'    => !is_null($project->requestForVariationContractAndContingencySum),
            'postContractProjectOverallTotal'       => $this->getPostContractProjectOverallTotal($project),
            'contractSumIncludesContingencySum'     => !is_null($project->requestForVariationContractAndContingencySum) ? $project->requestForVariationContractAndContingencySum->contract_sum_includes_contingency_sum : false,
        ]);
    }

    public function contractAndContingencySumSave(Project $project)
    {
        $inputs = Input::all();

        $this->rfvRepository->contractAndContingencySumSave($project, $inputs);

        return Redirect::route('requestForVariation.index', [$project->id]);
    }

    public function create(Project $project)
    {
        if(!$this->rfvRepository->getIsContractAndContingencySumFilled($project))
        {
            Flash::error(trans('requestForVariation.contractAndContingencySumRequired'));

            return Redirect::back();
        }

        return View::make('request_for_variation.rfv.show', [
            'project'              => $project,
            'userPermissionGroups' => \Confide::user()->getRequestForVariationUserPermissionGroups($project),
            'rfvCategories'        => RequestForVariationCategory::orderBy('name', 'ASC')->get(),
            'submitForApprovalPermissionUsers' => [],
            'editableCostEstimate' => false,
            'canApprovePendingVerification' => false,
            'canAssignVerifiers'   => false,
            'canVerifyPendingApproval' => false,
            'canUserUploadDeleteFiles' => false,
            'requestForVariation'  => null,
            'rfvNumber'            => null,
            'verifiers'            => [],
            'uploadedFiles'        => [],
            'voItemTypes'          => [],
            'unitOfMeasurements'   => [],
            'showKpiLimitTable'    => false,
        ]);
    }

    public function submit(Project $project)
    {
        $inputs = Input::all();
        $user   = Confide::user();

        $requestForVariation = array_key_exists('requestForVariationId', $inputs) ? RequestForVariation::find($inputs['requestForVariationId']) : $this->rfvRepository->createNewRfv($project, $inputs);

        if(array_key_exists('requestForVariationId', $inputs) && ($requestForVariation->status == $inputs['requestForVariationStatusId']))
        {
            $this->rfvRepository->executeAction($requestForVariation, $inputs);

            if($requestForVariation->status == RequestForVariation::STATUS_PENDING_COST_ESTIMATE)
            {
                return Redirect::route('requestForVariation.form.show', [$project->id, $requestForVariation->id]);
            }
        }

        $isVerifierWithoutProjectAccess = is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier();

        if($isVerifierWithoutProjectAccess)
        {
            return Redirect::route('home.index');
        }
        
        return Redirect::route('requestForVariation.index', [$project->id]);
    }

    public function show(Project $project, $requestForVariationId)
    {
        if(!$this->rfvRepository->getIsContractAndContingencySumFilled($project))
        {
            Flash::error(trans('requestForVariation.contractAndContingencySumRequired'));

            return Redirect::back();
        }

        $user = \Confide::user();
        $requestForVariation = RequestForVariation::findOrFail($requestForVariationId);

        $editableCostEstimate = $requestForVariation->canUserEditCostEstimation($user);
        $canApprovePendingVerification = $requestForVariation->canUserApprovePendingVerification($user);
        $canAssignVerifiers = $requestForVariation->canUserAssignVerifiers($user);
        $canVerifyPendingApproval = $requestForVariation->canUserVerifyPendingApproval($user);
        $canUserUploadDeleteFiles = $requestForVariation->canUserUploadDeleteFiles($user);

        $voItemTypes = [];
        $unitOfMeasurements = [];

        if($requestForVariation->status == RequestForVariation::STATUS_PENDING_COST_ESTIMATE)
        {
            $uomRecords = \DB::connection('buildspace')
                ->table('bs_unit_of_measurements')
                ->select('id', 'symbol')
                ->where('display', true)
                ->whereNull('deleted_at')
                ->orderBy('symbol','ASC')
                ->get();

            $unitOfMeasurements[0] = "---";

            foreach($uomRecords as $uomRecord)
            {
                $unitOfMeasurements[$uomRecord->id] = $uomRecord->symbol;
            }

            $voItemTypes = [
                VariationOrderItem::TYPE_HEADER => VariationOrderItem::TYPE_HEADER_TEXT,
                VariationOrderItem::TYPE_WORK_ITEM => VariationOrderItem::TYPE_WORK_ITEM_TEXT
            ];
        }

        $financialStandingData = null;

        if($requestForVariation->status == RequestForVariation::STATUS_VERIFIED)
        {
            $financialStandingData = $this->rfvRepository->getFinancialStandingData($requestForVariation, false);
        }

        if(in_array($requestForVariation->status, [RequestForVariation::STATUS_PENDING_APPROVAL, RequestForVariation::STATUS_APPROVED, RequestForVariation::STATUS_REJECTED]))
        {
            $financialStandingData = $this->rfvRepository->getFinancialStandingData($requestForVariation);
        }

        $maxKpiLimit = null;
        $currentKpiLimit = null;

        if($requestForVariation->showKpiLimitTable())
        {
            $maxKpiLimit = $requestForVariation->requestForVariationCategory->kpi_limit;
            $cncTotal = $financialStandingData['cncTotal'];

            if(in_array($requestForVariation->status, [RequestForVariation::STATUS_VERIFIED, RequestForVariation::STATUS_PENDING_APPROVAL]))
            {
                $cumulativeRfvAmountForCategory = $requestForVariation->getCumulativeRfvAmountByStatusAndCategory([RequestForVariation::STATUS_APPROVED], $requestForVariation->request_for_variation_category_id) + $requestForVariation->nett_omission_addition;
            }
            else
            {
                $cumulativeRfvAmountForCategory = $requestForVariation->approved_category_amount;
            }

            $currentKpiLimit = ($cumulativeRfvAmountForCategory / $cncTotal) * 100.0;
        }

        $submitForApprovalPermissionUsers = $requestForVariation->getUsersCanBeAssignedAsVerifiers()->merge($project->getTopManagementVerifiersWithProjectAccess());

        $isVerifierWithoutProjectAccess = is_null($user->getAssignedCompany($project)) && $user->isTopManagementVerifier();

        $viewData = [
            'project'                           => $project,
            'userPermissionGroups'              => \Confide::user()->getRequestForVariationUserPermissionGroups($project),
            'requestForVariation'               => $requestForVariation,
            'rfvNumber'                         => $requestForVariation->rfv_number,
            'rfvCategories'                     => RequestForVariationCategory::orderBy('name', 'ASC')->get(),
            'uploadedFiles'                     => [],
            'submitForApprovalPermissionUsers'  => $submitForApprovalPermissionUsers,
            'editableCostEstimate'              => $editableCostEstimate,
            'canApprovePendingVerification'     => $canApprovePendingVerification,
            'canAssignVerifiers'                => $canAssignVerifiers,
            'canVerifyPendingApproval'          => $canVerifyPendingApproval,
            'canUserUploadDeleteFiles'          => $canUserUploadDeleteFiles,
            'verifiers'                         => Verifier::getAssignedVerifierRecords($requestForVariation),
            'voItemTypes'                       => $voItemTypes,
            'unitOfMeasurements'                => $unitOfMeasurements,
            'financialStandingData'             => $financialStandingData,
            'showKpiLimitTable'                 => $requestForVariation->showKpiLimitTable(),
            'maxKpiLimit'                       => $maxKpiLimit,
            'currentKpiLimit'                   => number_format($currentKpiLimit, 2, '.', ''),
            'isVerifierWithoutProjectAccess'    => $isVerifierWithoutProjectAccess,
        ];

        return View::make('request_for_variation.rfv.show', $viewData);
    }

    public function getUploadedFiles(Project $project, $requestForVariationId)
    {
        $fileInfo = $this->rfvRepository->getUploadedFiles($project, $requestForVariationId);

        return [
            'aaData'               => $fileInfo,
            'iTotalDisplayRecords' => count($fileInfo)
        ];
    }

    public function upload(Project $project, $requestForVariationId)
    {
        $file = Input::file('file');
        $upload = new Upload;

        try
        {
            $upload->process($file);
        }
        catch(Exception $exception)
        {
            // Something went wrong. Log it.
            Log::error($exception);
            $errors = array(
                'name'  => $file->getClientOriginalName(),
                'size'  => $file->getSize(),
                'error' => $exception->getMessage()
            );

            // Return error
            return Response::json($errors, 400);
        }

        if( $upload->id )
        {
            $fileParts = pathinfo($upload->filename);

            $requestForVariationFile = new RequestForVariationFile();
            $requestForVariationFile->request_for_variation_id = $requestForVariationId;
            $requestForVariationFile->filename = $fileParts['filename'];
            $requestForVariationFile->cabinet_file_id = $upload->id;
            $requestForVariationFile->save();

            $success               = new stdClass();
            $success->name         = $upload->filename;
            $success->size         = $upload->size;
            $success->url          = $upload->download_url;
            $success->thumbnailUrl = $upload->generateThumbnailURL();
            $success->deleteUrl    = route('requestForVariation.document.uploadDelete', [$project->id, $upload->id]);
            $success->deleteType   = 'POST';
            $success->fileID       = $upload->id;

            return Response::json(array( 'files' => array( $success ) ), 200);
        }
    }

    public function fileDownload(Project $project, $id)
    {
        $file = RequestForVariationFile::where('cabinet_file_id', $id)->first();
        $cabinetInfo = $file->fileProperties;

        if( ! $file )
        {
            App::abort(404);
        }

        return \PCK\Helpers\Files::download(
            $cabinetInfo->physicalPath() . $cabinetInfo->filename,
            $file->filename . '.' . $cabinetInfo->extension, array(
            'Content-Type: ' . $cabinetInfo->mimetype,
        ));
    }

    public function uploadDelete(Project $project, $id)
    {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

        $fileName = $this->rfvRepository->uploadDelete($project, $id);

        $success = new stdClass();
        $success->{$fileName} = true;

        return Response::json(array( 'files' => array( $success ) ), 200);
    }

    public function getActionLogs(Project $project, RequestForVariation $requestForVariation)
    {
        $actionLogs = $requestForVariation->actionLogs;

        $formattedLogs = [];

        foreach($actionLogs as $actionLog)
        {
            $formattedLogs[] = $actionLog->getFormattedActionLog();
        }

        return $formattedLogs;
    }

    public function saveRfvAiNumber(Project $project, RequestForVariation $requestForVariation)
    {
        $inputs = Input::all();

        $savedRecord = $this->rfvRepository->saveRfvAiNumber($requestForVariation, $inputs['ai_number']);

        if(!is_null($savedRecord->id)) {
            return Response::json([
                'success' => 'success'
            ]);
        }
    }

    public function downloadVariationOrderExcelReport(Project $project)
    {
        $url = Config::get('buildspace.BUILDSPACE_URL') . "exportExcelReport/exportVariationOrderReport/pid/{$project->getBsProjectMainInformation()->project_structure_id}";

        $inputs = Input::all();
        $rfvIds = $inputs['rfvIds'];
        $url = $url . "/rfvIds/{$rfvIds}";

        return Redirect::to($url);
    }

    public function getPostContractProjectOverallTotal(Project $project)
    {
        $projectStructureId = $project->getBsProjectMainInformation()->project_structure_id;
        $postContractProjectOverallTotal = null;

        $client = new Client(array(
            'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
            'base_uri' => getenv('BUILDSPACE_URL'),
        ));
        
        try
        {
            $response = $client->post('eproject_api/getPostContractProjectOverallTotal', array(
                'form_params' => array(
                    'projectStructureId' => $projectStructureId,
                )
            ));

            $response = json_decode($response->getBody());
            $postContractProjectOverallTotal = $response->postContractProjectOverallTotal;
        }
        catch(Exception $e)
        {
           \Log::info("Get post contract project overall total fails. [project_structure_id: { $projectStructureId }] => {$e->getMessage()}");
        }

        return $postContractProjectOverallTotal;
    }

    public function destroy(Project $project, $requestForVariationId)
    {
        $requestForVariation = RequestForVariation::find($requestForVariationId);

        try
        {
            $requestForVariation->deleted_at = Carbon::now();
            $requestForVariation->deleted_by = \Auth::id();
            $requestForVariation->save();

            Flash::success(trans('requestForVariation.deleteSuccess', ['no' => $requestForVariation->rfv_number]));
        }
        catch(\Exception $e)
        {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());
            Flash::error(trans('requestForVariation.deleteFailed', ['no' => $requestForVariation->rfv_number]));
        }

        return Redirect::route('requestForVariation.index', [$project->id]);
    }

    public function printRequestForVariation(Project $project, RequestForVariation $requestForVariation)
    {
        $companyLogoSrc                 = $this->myCompanyProfileRepository->find()->company_logo_path ? public_path() . $this->myCompanyProfileRepository->find()->company_logo_path : '';
        $uploadedFiles                  = $this->rfvRepository->getUploadedFiles($project, $requestForVariation->id);
        $financialStandingData          = $this->rfvRepository->getFinancialStandingData($requestForVariation);
        $cumulativeRfvAmountForCategory = $requestForVariation->getCumulativeRfvAmountByStatusAndCategory([RequestForVariation::STATUS_APPROVED], $requestForVariation->request_for_variation_category_id) + $requestForVariation->nett_omission_addition;
        $maxKpiLimit                    = $requestForVariation->requestForVariationCategory->kpi_limit;
        $currentKpiLimit                = ($cumulativeRfvAmountForCategory / $financialStandingData['cncTotal']) * 100.0;
        $variationOrderItems            = $this->rfvRepository->getVariationOrderItems($requestForVariation);
        $approvalRecords                = $requestForVariation->actionLogs()->whereIn('action_type', [RequestForVariationActionLog::ACTION_TYPE_RFV_APPROVED, RequestForVariationActionLog::ACTION_TYPE_RFV_REJECTED])->get();

        return PDF::html('request_for_variation.rfv.print.approved_request_for_varation', [
            'project'                   => $project,
            'companyLogoSrc'            => $companyLogoSrc,
            'requestForVariation'       => $requestForVariation,
            'uploadedFiles'             => $uploadedFiles,
            'financialStandingData'     => $financialStandingData,
            'maxKpiLimit'               => $maxKpiLimit,
            'currentKpiLimit'           => $currentKpiLimit,
            'verifiers'                 => Verifier::getAssignedVerifierRecords($requestForVariation),
            'variationOrderItems'       => $variationOrderItems,
            'approvalRecords'           => $approvalRecords,
        ]);
    }

    public function printListOfRequestForVariations(Project $project)
    {
        $user                 = \Confide::user();
        $companyLogoSrc       = $this->myCompanyProfileRepository->find()->company_logo_path ? public_path() . $this->myCompanyProfileRepository->find()->company_logo_path : '';
        $requestForVariations = $this->rfvRepository->listRequestForVariationByGroup($project, $user);

        $rfvIds                              = array_column($requestForVariations, 'id');
        $rfvOverallAmountByUser              = is_null($rfvIds) ? 0.0 : $project->getOverallRfvAmountByUser(\Confide::user(), $rfvIds);
        $rfvProposedCostEstimate             = is_null($rfvIds) ? 0.0 : $project->getProposedRfvAmountByUser(\Confide::user(), $rfvIds);
        $accumulativeApprovedRfvAmountByUser = is_null($rfvIds) ? 0.0 : $project->getAccumulativeApprovedRfvAmountByUser(\Confide::user(), $rfvIds);

        return PDF::html('request_for_variation.rfv.print.all_rfvs', [
            'project'                             => $project,
            'companyLogoSrc'                      => $companyLogoSrc,
            'requestForVariations'                => $requestForVariations,
            'rfvOverallAmountByUser'              => $rfvOverallAmountByUser,
            'rfvProposedCostEstimate'             => $rfvProposedCostEstimate,
            'accumulativeApprovedRfvAmountByUser' => $accumulativeApprovedRfvAmountByUser,
        ]);
    }
}