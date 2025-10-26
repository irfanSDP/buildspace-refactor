<?php

use Carbon\Carbon;
use PCK\Forms\ClaimCertificatePaymentForm;
use PCK\Forms\ClaimCertificateInvoiceInformationForm;
use PCK\Subsidiaries\SubsidiaryRepository;
use PCK\Projects\Project;
use PCK\Buildspace\ClaimCertificate;
use PCK\Buildspace\ClaimCertificateInvoice;
use PCK\ClaimCertificate\ClaimCertificatePaymentRepository;
use PCK\AccountCodeSettings\AccountCodeSetting;
use PCK\AccountCodeSettings\SubsidiaryApportionmentRecord;
use PCK\Buildspace\ProjectCodeSetting;
use PCK\Buildspace\ItemCodeSetting;
use PCK\Buildspace\ItemCodeSettingObjectBreakdown;
use PCK\Subsidiaries\Subsidiary;
use PCK\Users\User;
use PCK\ModulePermission\ModulePermission;
use PCK\ClaimCertificate\ClaimCertificatePrintLog;
use PCK\ClaimCertificate\ClaimCertificateEmailLog;
use PCK\ClaimCertificate\ClaimCertificatePaymentNotificationLog;
use PCK\ClaimCertificate\ClaimCertificatePayment;
use PCK\AccountCodeSettings\ExportAccountingReportLog;
use PCK\Exceptions\ValidationException;

class ClaimCertificatePaymentController extends BaseController {

    private $form;
    private $invoiceForm;
    private $subsidiaryRepository;
    private $claimCertificatePaymentRepository;

    public function __construct(ClaimCertificatePaymentForm $form, ClaimCertificateInvoiceInformationForm $invoiceForm, SubsidiaryRepository $subsidiaryRepository, ClaimCertificatePaymentRepository $claimCertificatePaymentRepository)
    {
        $this->form                 = $form;
        $this->invoiceForm          = $invoiceForm;
        $this->subsidiaryRepository = $subsidiaryRepository;
        $this->claimCertificatePaymentRepository = $claimCertificatePaymentRepository;
    }

    public function list()
    {
        $user = Confide::user();

        $subsidiaries = $this->subsidiaryRepository->getHierarchicalCollection();

        if(!$user->isSuperAdmin())
        {
            $visibleSubsidiaryIds = $user->modulePermission(ModulePermission::MODULE_ID_FINANCE)->first()->subsidiaries->lists('id');

            $subsidiaries = $subsidiaries->filter(function($subsidiary) use ($visibleSubsidiaryIds) {
                return in_array($subsidiary->id, $visibleSubsidiaryIds);
            });
        }

        $subsidiaries = $subsidiaries->lists('fullName', 'id');

        return View::make('finance/claim-certificate/index', compact('subsidiaries'));
    }

    public function getClaimCertificateList()
    {
        $user = Confide::user();
        $params = Input::all();
        $subsidiaryId = isset($params['subsidiaryId']) ? $params['subsidiaryId'] : null;
        $query = ClaimCertificate::getClaimCertificateQuery();

        $filteredParentProjects = [];
        $filteredSubConProjects = [];
        $subConProjectTitleFilterValue = null;
        $contractorFilterValue = null;
        $claimVersionFilterValue = null;
        $paymentStatusFilterValue = null;

        /*
         * Prepare to filter if there is filters param
         */
        if(array_key_exists('filters', $params) && is_array($params['filters']))
        {
            $parentProjectTitleValue = null;
            $parentProjectReferenceValue = null;

            foreach($params['filters'] as $filter)
            {
                switch($filter['field'])
                {
                    case 'projectTitle':
                        $parentProjectTitleValue = trim($filter['value']);
                        break;
                    case 'reference':
                        $parentProjectReferenceValue = trim($filter['value']);
                        break;
                    case 'contractor':
                        $contractorFilterValue = trim($filter['value']);
                        break;
                    case 'subContractWork':
                        $subConProjectTitleFilterValue = trim($filter['value']);
                        break;
                    case 'version':
                        $claimVersionFilterValue = (int)trim($filter['value']);
                        break;
                    case 'paymentStatus':
                        $val = (is_array($filter['value'])) ? $filter['value'][0] : $filter['value'];//tabulator select filter sometimes returns array instead of values
                        $paymentStatusFilterValue = trim($val);
                        if(strlen($paymentStatusFilterValue) > 0)
                        {
                            $query = ClaimCertificate::getClaimCertificateQuery((strtolower($paymentStatusFilterValue) == 'paid'));
                        }
                        break;
                    default:
                        break;
                }
            }

            if(($parentProjectTitleValue && strlen($parentProjectTitleValue) > 0) || ($parentProjectReferenceValue && strlen($parentProjectReferenceValue) > 0))
            {
                $parentProjectQuery = \DB::table('projects');

                if($parentProjectTitleValue)
                {
                    $parentProjectQuery->where('title', 'ILIKE', '%'.$parentProjectTitleValue.'%');
                }

                if($parentProjectReferenceValue)
                {
                    $parentProjectQuery->where('reference', 'ILIKE', '%'.$parentProjectReferenceValue.'%');
                }

                $filteredParentProjects = $parentProjectQuery->whereNull('deleted_at')
                    ->lists('id');
                
                $filteredParentProjects = empty($filteredParentProjects) ? [ -1 ] : $filteredParentProjects;
            }
        }

        $visibleProjectIds = [];

        if(!$user->isSuperAdmin())
        {
            $visibleSubsidiaryIds = $user->modulePermission(ModulePermission::MODULE_ID_FINANCE)->first()->subsidiaries->lists('id');

            foreach($visibleSubsidiaryIds as $id)
            {
                $visibleProjectIds = array_unique(array_merge($visibleProjectIds, $this->claimCertificatePaymentRepository->getListOfProjectIds($id)));
            }
        }
        
        if(!empty($filteredParentProjects))
        {
            /* -1 means there is filters param but return empty result - meaning filtered values does not exists
             * $visibleProjectIds will always need to be set if there is filters param so -1 indicates there is no return result from the filtered parent project
             */
            if($filteredParentProjects[0] != -1)
            {
                $filteredProjectIds = \DB::table('projects')
                ->where(function($queryContainer) use ($filteredParentProjects){
                    $queryContainer->whereIn('id', array_values($filteredParentProjects))
                        ->whereNull('parent_project_id');
                })
                ->orWhereIn('parent_project_id', array_values($filteredParentProjects))
                ->whereNull('deleted_at')
                ->lists('id');

                if(!empty($visibleProjectIds))//for non super admin user - remove non related project ids from $visibleProjectIds
                {
                    $visibleProjectIds = array_intersect($visibleProjectIds, $filteredProjectIds);

                    $visibleProjectIds = empty($visibleProjectIds) ? [-1] : $visibleProjectIds;
                }
                else
                {
                    //for super admin user - $visibleProjectIds should comes straight from $filteredProjectIds
                    $visibleProjectIds = $filteredProjectIds;
                }
            }
            else
            {
                $visibleProjectIds = [-1];
            }

            unset($filteredParentProjects);
        }

        if($subConProjectTitleFilterValue && strlen($subConProjectTitleFilterValue) > 0)
        {
            $subConProjectQuery = \DB::table('projects')
                ->where('title', 'ILIKE', '%'.$subConProjectTitleFilterValue.'%')
                ->whereNotNull('parent_project_id');
            
            if(!empty($visibleProjectIds))
            {
                $subConProjectQuery->whereIn('id', array_values($visibleProjectIds));
            }

            $filteredSubConProjects = $subConProjectQuery->whereNull('deleted_at')
                ->lists('id');
            
            $filteredSubConProjects = empty($filteredSubConProjects) ? [ -1 => ""] : $filteredSubConProjects;

            if(!empty($visibleProjectIds))//for non super admin user - remove non related sub project ids from $visibleProjectIds
            {
                $visibleProjectIds = array_intersect($visibleProjectIds, $filteredSubConProjects);

                $visibleProjectIds = empty($visibleProjectIds) ? [-1] : $visibleProjectIds;
            }
            else
            {
                //for super admin user - $visibleProjectIds should comes straight from $filteredSubConProjects
                $visibleProjectIds = $filteredSubConProjects;
            }

            unset($filteredSubConProjects);
        }

        if(!empty($visibleProjectIds))
        {
            $query->whereIn('bs_project_main_information.eproject_origin_id', array_unique(array_values($visibleProjectIds)));
        }

        if($subsidiaryId)
        {
            $listOfProjectIds = $this->claimCertificatePaymentRepository->getListOfProjectIds($subsidiaryId);
            $query->whereIn('bs_project_main_information.eproject_origin_id', $listOfProjectIds);
        }

        if($contractorFilterValue && strlen($contractorFilterValue) > 0)
        {
            $query->where('bs_companies.name', 'ILIKE', '%'.trim($contractorFilterValue).'%');
        }

        if($claimVersionFilterValue && (int)$claimVersionFilterValue > 0)
        {
            $query->where('bs_post_contract_claim_revisions.version', '=', (int)$claimVersionFilterValue);
        }

        $totalCount = $query->count(\DB::raw('DISTINCT bs_claim_certificates.id'));

        $resultPerPage = array_key_exists('size', $params) ? (int)$params['size'] : 100;
        $pageNum = array_key_exists('page', $params) ? (int)$params['page'] : 1;
        $offset = ($pageNum - 1) * $resultPerPage;
        $totalRows = $resultPerPage;
        $totalPages = ceil( $totalCount / $resultPerPage );

        $query->orderByRaw('bs_claim_certificates.updated_at DESC LIMIT '.$totalRows.' OFFSET '.$offset);
        $queryResults = $query->get();

        $data = [];
        $count = 0;

        foreach($queryResults as $result)
        {
            $project = Project::find($result->eproject_origin_id);
            $claimCertificate = ClaimCertificate::find($result->claim_certificate_id);
            $paidAmount = $claimCertificate->paidAmount();
            $canExportAccounting = false;

            if($project->accountCodeSetting && ($project->accountCodeSetting->status == AccountCodeSetting::STATUS_APPROVED))
            {
                $hasItemCodeSettings = (ItemCodeSetting::where('project_structure_id', $result->project_structure_id)->count() > 0);
                $hasItemCodeSettingObjectBreakdowns = (ItemCodeSettingObjectBreakdown::getRecordsBy($claimCertificate->id)->count() > 0);

                if($hasItemCodeSettings && $hasItemCodeSettingObjectBreakdowns)
                {
                    $canExportAccounting = true;
                }
            }

            $invoiceUpload = PCK\ModuleUploadedFiles\ModuleUploadedFile::where('uploadable_id', '=', $claimCertificate->postContractClaimRevision->id)
                ->where('uploadable_type', '=', get_class($claimCertificate->postContractClaimRevision))
                ->where('type', '=', PCK\ModuleUploadedFiles\ModuleUploadedFile::TYPE_CLAIM_CERTIFICATE_INVOICE)
                ->first();
            
            
            array_push($data, [
                'indexNo'                            => ($pageNum-1) * $resultPerPage + $count + 1,
                'id'                                 => $claimCertificate->id,
                'projectId'                          => $project->id,
                'reference'                          => $project->isSubProject() ? $project->parentProject->reference : $project->reference,
                'projectTitle'                       => $project->isSubProject() ? $project->parentProject->title : $project->title,
                'subsidiary'                         => $project->subsidiary->fullName,
                'contractor'                         => $result->name,
                'letterOfAwardCode'                  => $claimCertificate->postContractClaimRevision->postContract->projectStructure->letterOfAward->code,
                'subContractWork'                    => $project->isSubProject() ? $project->title : '-',
                'version'                            => $claimCertificate->postContractClaimRevision->version,
                'currency'                           => $project->modified_currency_code,
                'approvedDate'                       => $claimCertificate->approvalLog ? \Carbon\Carbon::parse($claimCertificate->approvalLog->created_at)->format(\Config::get('dates.submitted_at')) : '-',
                'paidAmount'                         => number_format($paidAmount, 2, '.', ','),
                'route_print'                        => route('finance.claim-certificate.print', $claimCertificate->id),
                'route_printLog'                     => route('finance.claim-certificate.print.log', $claimCertificate->id),
                'printLogCount'                      => ClaimCertificatePrintLog::getLog($claimCertificate->id)->count(),
                'route_claimCertPaymentStore'        => route('finance.claim-certificate.payment.store', $claimCertificate->id),
                'route_claimCertPaymentAmounts'      => route('finance.claim-certificate.payment.amounts.get', $claimCertificate->id),
                'route_getClaimCertificatePayments'  => route('finance.claim-certificate.payments.get', $claimCertificate->id),
                'route_sendClaimCert'                => route('finance.claim-certificate.email.send', $claimCertificate->id),
                'route_sendClaimCertLog'             => route('finance.claim-certificate.email.send.log', $claimCertificate->id),
                'sendClaimCertLogCount'              => ClaimCertificateEmailLog::getLog($claimCertificate->id)->count(),
                'route_sendPaymentNotification'      => route('finance.claim-certificate.payment.notification', $claimCertificate->id),
                'route_sendPaymentNotificationLog'   => route('finance.claim-certificate.payment.notification.log', $claimCertificate->id),
                'hasPendingPaymentNotifications'     => ClaimCertificatePayment::where('claim_certificate_id', '=', $claimCertificate->id)->where('notification_sent', '=', false)->count() > 0,
                'paymentNotificationLogCount'        => ClaimCertificatePaymentNotificationLog::getLog($claimCertificate->id)->count(),
                'approvedAmount'                     => number_format($claimCertificate->claimCertificateInformation->approved_amount, 2, '.', ','),
                'balance'                            => number_format(( $claimCertificate->claimCertificateInformation->approved_amount - $paidAmount ), 2, '.', ','),
                'paid'                               => $claimCertificate->claimCertificateInformation->paid,
                'paymentStatus'                      => $claimCertificate->claimCertificateInformation->paid ? trans('finance.paid') : trans('finance.pending'),
                'apportionmentTypeName'              => is_null($project->accountCodeSetting) ? trans('accountCodes.weightage') : $project->accountCodeSetting->apportionmentType->name,
                'route_getApprovedPhaseSubsidiaries' => route('project.code.approved.phase.subsidiaries.get', $project->id),
                'route_exportAccounting'             => route('finance.claim-certificate.account.report.export', $claimCertificate->id),
                'route_getExportAccountingLog'       => route('finance.claim-certificate.account.report.export.logs.get', $claimCertificate->id),
                'exportAccountingLogCount'           => ExportAccountingReportLog::getLog($claimCertificate->id)->count(),
                'canExportAccounting'                => $canExportAccounting,
                'route_getClaimCertInfo'             => route('finance.claim-certificate.invoice.information.get', $claimCertificate->id),
                'route_claimCertInvoiceInfoStore'    => route('finance.claim-certificate.invoce.information.store', $claimCertificate->id),
                'hasInvoiceInformation'              => is_null($claimCertificate->claimCertificateInvoiceInformation) ? false : true,
                'route:invoiceDownload'              => $invoiceUpload ? route('finance.contractor.claims.invoices.list', array( $project->id, $claimCertificate->postContractClaimRevision->id )) : null,
                'route_validateExportAccounting'     => route('finance.claim-certificate.account.report.validate', [$claimCertificate->id]),
                'currencyCode'                       => $project->modified_currency_code,
            ]);

            ++$count;
        }

        return Response::json([
            'last_page' => $totalPages,
            'data' => $data
        ]);
    }

    public function getClaimCertificatePayments($claimCertificateId)
    {
        $payments = ClaimCertificatePayment::where('claim_certificate_id', '=', $claimCertificateId)->orderBy('created_at', 'desc')->get();
        $count    = $payments->count();
        $data     = [];

        foreach($payments as $payment)
        {
            array_push($data, [
                'count'     => $count --,
                'id'        => $payment->id,
                'bank'      => $payment->bank,
                'reference' => $payment->reference,
                'amount'    => number_format($payment->amount, 2),
                'date'      => Carbon::parse($payment->date)->format(\Config::get('dates.submitted_at')),
                'createdBy' => $payment->createdBy->name,
            ]);
        }

        return Response::json($data);
    }

    public function getClaimCertificatePaymentAmounts($claimCertificateId)
    {
        $claimCertificate     = ClaimCertificate::find($claimCertificateId);
        $project              = $claimCertificate->postContractClaimRevision->postContract->projectStructure->mainInformation->getEProjectProject();
        $claimCertificateInfo = ClaimCertificate::getClaimCertInfo(array( $claimCertificateId ))[ $claimCertificateId ];
        $approvedAmount       = $claimCertificateInfo['netPayableAmountOverallTotal'];
        
        $claimCertificate = ClaimCertificate::find($claimCertificateId);
        $paidAmount       = $claimCertificate->paidAmount();
        $balance          = $approvedAmount - $paidAmount;

        return Response::json([
            'balance' => number_format($balance, 2),
            'paidAmount'     => number_format($paidAmount, 2),
        ]);
    }

    public function store($claimCertificateId)
    {
        $errors = null;
        $success = false;
        $user = Confide::user();

        Input::merge(array( 'reference' => preg_replace("/ /", "", Input::get('reference')) ));

        try
        {
            $claimCertificate            = ClaimCertificate::find($claimCertificateId);
            $claimCertificateInformation = $claimCertificate->claimCertificateInformation;
            $approvedAmount              = $claimCertificateInformation->approved_amount;
            $paidAmount                  = $claimCertificate->paidAmount();

            $this->form->setMaxPayableAmount($approvedAmount - $paidAmount);

            $this->form->validate(Input::all());

            $payment = new ClaimCertificatePayment(Input::all());

            $payment->claim_certificate_id = $claimCertificateId;

            $payment->created_by = $user->id;
            $payment->updated_by = $user->id;

            $payment->save();

            $claimCertificateInformation->paid_amount = $claimCertificate->paidAmount();
            if( $claimCertificateInformation->paid_amount >= $approvedAmount ) $claimCertificateInformation->paid = true;
            $claimCertificateInformation->save();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $errors = $e->getErrors();
        }

        return Response::json([
            'errors'   => $errors,
            'success' => $success,
        ]);
    }

    public function sendCertificate($claimCertificateId)
    {
        $certificate = ClaimCertificate::find($claimCertificateId);

        $project = $certificate->postContractClaimRevision->postContract->projectStructure->mainInformation->getEProjectProject();

        $client = new GuzzleHttp\Client(array(
            'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
            'base_uri' => \Config::get('buildspace.BUILDSPACE_URL')
        ));

        try
        {
            $response = $client->post('postContract/savePrintedClaimCertificate', array(
                'form_params' => array(
                    'claimCertificateId' => $claimCertificateId,
                    'pid'                => $certificate->postContractClaimRevision->postContract->projectStructure->id,
                )
            ));

            $response = json_decode($response->getBody(), true);

            if( ! $response['success'] ) throw new \Exception($response['errorMsg']);

            $pathToFile = $response['pathToFile'];
        }
        catch(\Exception $e)
        {
            Log::error("Failed to save claim certificate printout (id: {$claimCertificateId}) -> {$e->getMessage()}");

            return Response::json(array( 'success' => false ));
        }

        $mainProject = $project->isMainProject() ? $project : $project->parentProject;

        $claimCertInfo = ClaimCertificate::getClaimCertInfo(array( $claimCertificateId ))[ $claimCertificateId ];

        $emailSubject = "[{$mainProject->reference}] Claim No. {$certificate->postContractClaimRevision->version} Dated: {$claimCertInfo['dueDate']}";

        $mailer = new \PCK\Helpers\Mailer($emailSubject, 'notifications.email.finance.claim_certificate', array(
            'claimNo'         => $claimCertInfo['claimNo'],
            'certDueDate'     => $claimCertInfo['dueDate'],
            'currency'        => $project->modified_currency_code,
            'amountBeforeTax' => number_format($claimCertInfo['netPayableAmount'], 2),
            'taxPercentage'   => number_format($claimCertInfo['taxPercentage'], 2),
            'taxAmount'       => number_format($claimCertInfo['cumulativeAmountGSTAmount'], 2),
            'totalAmount'     => number_format($claimCertInfo['netPayableAmountOverallTotal'], 2),
        ));

        $mailer->setRecipients($project->getSelectedContractor()->companyAdmins);

        $mailer->setCCList(array_column($this->getFinanceEditors($project->subsidiary), 'email'));

        $mailer->addAttachment($pathToFile, trans('finance.claimCertificate') . '.' . \PCK\Helpers\Files::EXTENSION_PDF);

        $mailer->send();

        ClaimCertificateEmailLog::addEntry($claimCertificateId, Confide::user());

        return Response::json(array( 'success' => true ));
    }

    public function sendPaymentCollectionNotification($claimCertificateId)
    {
        $certificate = ClaimCertificate::find($claimCertificateId);

        $project = $certificate->postContractClaimRevision->postContract->projectStructure->mainInformation->getEProjectProject();

        $mainProject = $project->isMainProject() ? $project : $project->parentProject;

        $emailSubject = "[{$mainProject->reference}] Claim No. {$certificate->postContractClaimRevision->version} Payment Ready for Collection";

        $mailer = new \PCK\Helpers\Mailer($emailSubject, 'notifications.email.finance.payment_collection', array(
            'projectReference' => $mainProject->reference,
            'claimNo'          => $certificate->postContractClaimRevision->version,
        ));

        $mailer->setRecipients($project->getSelectedContractor()->companyAdmins);

        $mailer->setCCList(array_column($this->getFinanceEditors($project->subsidiary), 'email'));

        $mailer->send();

        ClaimCertificatePaymentNotificationLog::addEntry($claimCertificateId, Confide::user());

        ClaimCertificatePayment::where('claim_certificate_id', '=', $claimCertificateId)->update(array( 'notification_sent' => true ));

        return Response::json(array( 'success' => true ));
    }

    private function getFinanceEditors(Subsidiary $subsidiary)
    {
        $financeEditors = [];

        foreach(ModulePermission::getEditorList(ModulePermission::MODULE_ID_FINANCE) as $financeModuleEditor)
        {
            $financeUserAssignedSubsidiaryIds = $financeModuleEditor->modulePermission(ModulePermission::MODULE_ID_FINANCE)->first()->subsidiaries->lists('id');

            if($financeUserAssignedSubsidiaryIds->count() == 0) continue;

            if(in_array($subsidiary->id, $financeUserAssignedSubsidiaryIds))
            {
                array_push($financeEditors, $financeModuleEditor);
            }
        }

        return $financeEditors;
    }

    public function certificatePrintLog($claimCertificateId)
    {
        $claimCertificate = ClaimCertificate::find($claimCertificateId);
        $project          = $claimCertificate->postContractClaimRevision->postContract->projectStructure->mainInformation->getEProjectProject();
        $logs             = [];

        foreach(ClaimCertificatePrintLog::getLog($claimCertificateId) as $log)
        {
            array_push($logs, [
                'id'        => $log->id,
                'username'  => $log->user->name,
                'timestamp' => Carbon::parse($project->getProjectTimeZoneTime($log->created_at))->format(\Config::get('dates.full_format')),
                
            ]);
        }

        return Response::json($logs);
    }

    public function getExportAccountingReportLogs($claimCertificateId)
    {
        $claimCertificate      = ClaimCertificate::find($claimCertificateId);
        $project               = $claimCertificate->postContractClaimRevision->postContract->projectStructure->mainInformation->getEProjectProject();
        $exportLogs            = ExportAccountingReportLog::getLog($claimCertificateId);
        $apportionmentTypeName = $project->accountCodeSetting ? $project->accountCodeSetting->apportionmentType->name : null;
        $data                  = [];
        $count                 = 0;

        foreach($exportLogs as $log)
        {
            array_push($data, [
                'count'                   => ++ $count,
                'id'                      => $log->id,
                'username'                => User::find($log->user_id)->name,
                'exportDate'              => Carbon::parse($project->getProjectTimeZoneTime($log->created_at))->format(\Config::get('dates.full_format')),
                'route_viewExportDetails' => route('finance.claim-certificate.account.report.export.log.details.get', [$claimCertificate->id, $log->id]),
                'apportionmentTypeName'   => $apportionmentTypeName,
            ]);
        }

        return Response::json($data);
    }

    public function getExportAccountingReportLastSelectedOptions($claimCertificateId)
    {
        $claimCertificate = ClaimCertificate::find($claimCertificateId);
        $latestExportLog  = ExportAccountingReportLog::getLog($claimCertificateId)->first();
        
        if(is_null($latestExportLog)) return Response::json([]);
        
        $data = array_fill_keys($latestExportLog->logDetails->lists('project_code_setting_id'), []);

        foreach($latestExportLog->logDetails as $logDetail)
        {
            foreach($logDetail->accountingReportExportLogItemCodes as $exportLogItemCode)
            {
                array_push($data[$logDetail->project_code_setting_id], $exportLogItemCode->item_code_setting_id);
            }
        }

        return Response::json($data);
    }

    public function getExportAccountingReportLogDetails($claimCertificateId, $logId)
    {
        $claimCertificate       = ClaimCertificate::find($claimCertificateId);
        $project                = $claimCertificate->postContractClaimRevision->postContract->projectStructure->mainInformation->getEProjectProject();
        $apportionmentType      = $project->accountCodeSetting->apportionmentType;
        $exportLog              = ExportAccountingReportLog::find($logId);
        $exportLogDetails       = $exportLog->logDetails;
        $projectCodeSettingsIds = $exportLogDetails->lists('project_code_setting_id');
        $projectCodeSettings    = [];
        $data                   = [];

        foreach($projectCodeSettingsIds as $projectCodeSettingsId)
        {
            array_push($projectCodeSettings, ProjectCodeSetting::find($projectCodeSettingsId));
        }

        $proportionsGroupedByIds = ProjectCodeSetting::getProportionsGroupedByIds($claimCertificate->postContractClaimRevision->postContract->projectStructure, $projectCodeSettings);

        foreach($exportLogDetails as $logDetail)
        {
            $subsidiary                         = Subsidiary::find($logDetail->projectCodeSetting->eproject_subsidiary_id);
            $apportionment                      = SubsidiaryApportionmentRecord::getSubsidiaryApportionment($subsidiary, $apportionmentType->id);
            $accountingReportExportLogItemCodes = $logDetail->accountingReportExportLogItemCodes;

            // before v3.2, users will export by selecting all item codes by default
            // in v3.2, users will have the option to specify manually the item codes to export
            if($logDetail->accountingReportExportLogItemCodes->isEmpty())
            {
                // applicable for logs before v3.2, get all item code settings
                $accountingReportExportLogItemCodes = ItemCodeSetting::getItemCodeSettings($claimCertificate->postContractClaimRevision->postContract->projectStructure);
            }

            foreach($accountingReportExportLogItemCodes as $object)
            {
                array_push($data, [
                    'subsidiaryName'      => $subsidiary->name,
                    'subsidiaryCode'      => $logDetail->projectCodeSetting->subsidiary_code,
                    'weightage'           => $apportionment->value,
                    'proportion'          => $proportionsGroupedByIds[$logDetail->projectCodeSetting->id],
                    'itemCodeId'          => $logDetail->accountingReportExportLogItemCodes->isEmpty() ? $object->id : $object->itemCodeSetting->id,
                    'itemCodeDescription' => $logDetail->accountingReportExportLogItemCodes->isEmpty() ? $object->accountCode->description : $object->itemCodeSetting->accountCode->description,
                ]);
            }
        }

        return Response::json($data);
    }

    public function printClaimCertificate($claimCertificateId)
    {
        $claimCertificate = ClaimCertificate::find($claimCertificateId);

        ClaimCertificatePrintLog::addEntry($claimCertificateId, Confide::user());

        return Redirect::to($claimCertificate->getPrintInfoUrl());
    }

    public function validateAccountingReport($claimCertificateId)
    {
        $inputs           = Input::all();
        $project          = Project::find($inputs['projectId']);
        $projectStructure = $project->getBsProjectMainInformation()->projectStructure;
        $claimCertificate = ClaimCertificate::find($claimCertificateId);
        $isValid          = ClaimCertificate::checkClaimCertificateAccountingExportValidity($projectStructure, $claimCertificate);

        return Response::json(['isValid' => $isValid]);
    }

    public function exportAccountingReport($claimCertificateId)
    {
        $inputs = Input::all();
        $claimCertificate = ClaimCertificate::find($claimCertificateId);
        $exportAccountingURL = $claimCertificate->getExportAccountingReportUrl() . '?projectCodeSettingIds=' . $inputs['projectCodeSettingIds'];

        ExportAccountingReportLog::addEntry($claimCertificateId, Confide::user(), explode(',', $inputs['projectCodeSettingIds']));

        \Queue::push('PCK\QueueJobs\ExternalOutboundAPI', [
            'module' => 'ProgressClaim',
            'claim_cert_id' => $claimCertificate->id,
        ], 'ext_app_outbound');

        return Redirect::to($exportAccountingURL);
    }

    public function sendCertificateLog($claimCertificateId)
    {
        $claimCertificate = ClaimCertificate::find($claimCertificateId);
        $project          = $claimCertificate->postContractClaimRevision->postContract->projectStructure->mainInformation->getEProjectProject();
        $logs             = [];

        foreach(ClaimCertificateEmailLog::getLog($claimCertificateId) as $log)
        {
            array_push($logs, [
                'id'        => $log->id,
                'username'  => $log->user->name,
                'timestamp' => Carbon::parse($project->getProjectTimeZoneTime($log->created_at))->format(\Config::get('dates.full_format')),
            ]);
        }

        return $logs;
    }

    public function paymentCollectionNotificationLog($claimCertificateId)
    {
        $claimCertificate = ClaimCertificate::find($claimCertificateId);
        $project          = $claimCertificate->postContractClaimRevision->postContract->projectStructure->mainInformation->getEProjectProject();
        $logs             = [];

        foreach(ClaimCertificatePaymentNotificationLog::getLog($claimCertificateId) as $log)
        {
            array_push($logs, [
                'id'        => $log->id,
                'username'  => $log->user->name,
                'timestamp' => Carbon::parse($project->getProjectTimeZoneTime($log->created_at))->format(\Config::get('dates.full_format')),
            ]);
        }

        return $logs;
    }

    public function getUpdatedSentClaimCertificateLogCount($claimCertificateId)
    {
        $claimCertificate = ClaimCertificate::find($claimCertificateId);
        return ClaimCertificateEmailLog::getLog($claimCertificate->id)->count();
    }

    public function getUpdatedPrintLogCount($claimCertificateId)
    {
        $claimCertificate = ClaimCertificate::find($claimCertificateId);
        return ClaimCertificatePrintLog::getLog($claimCertificate->id)->count();
    }

    public function getUpdatedAccountingExportLogCount($claimCertificateId)
    {
        $claimCertificate = ClaimCertificate::find($claimCertificateId);
        return ExportAccountingReportLog::getLog($claimCertificate->id)->count();
    }

    public function getUpdatePaymentNotificationLogCount($claimCertificateId)
    {
        $claimCertificate = ClaimCertificate::find($claimCertificateId);
        return ClaimCertificatePaymentNotificationLog::getLog($claimCertificateId)->count();
    }

    public function listProjects()
    {
        $user = Confide::user();

        $subsidiaries = $this->subsidiaryRepository->getHierarchicalCollection();

        if(!$user->isSuperAdmin())
        {
            $visibleSubsidiaryIds = $user->modulePermission(ModulePermission::MODULE_ID_FINANCE)->first()->subsidiaries->lists('id');
            $subsidiaries = $subsidiaries->filter(function($subsidiary) use ($visibleSubsidiaryIds) {
                return in_array($subsidiary->id, $visibleSubsidiaryIds);
            });
        }

        $subsidiaries = $subsidiaries->lists('fullName', 'id');

        return View::make('finance/claim-certificate/projects', compact('subsidiaries'));
    }

    public function getProjectsList()
    {
        $params = Input::all();
        $subsidiaryId = isset($params['subsidiaryId']) ? $params['subsidiaryId'] : null;
        $filters =  isset($params['filters']) ? $params['filters'] : [];
        $hasData = true;
        $projects = [];
        $user = Confide::user();

        $query = "select i.eproject_origin_id, count(c.id)
                    from bs_project_main_information i
                    join bs_project_structures p on p.id = i.project_structure_id
                    join bs_post_contracts pc on pc.project_structure_id = p.id
                    join bs_post_contract_claim_revisions rev on rev.post_contract_id = pc.id
                    join bs_claim_certificates c on c.post_contract_claim_revision_id = rev.id
                    where c.status = " . ClaimCertificate::STATUS_TYPE_APPROVED . "
                    and eproject_origin_id is not null ";

        if(!$user->isSuperAdmin())
        {
            $visibleProjectIds = array();

            $visibleSubsidiaryIds = $user->modulePermission(ModulePermission::MODULE_ID_FINANCE)->first()->subsidiaries->lists('id');

            foreach($visibleSubsidiaryIds as $id)
            {
                $visibleProjectIds = array_merge($visibleProjectIds, $this->claimCertificatePaymentRepository->getListOfProjectIds($id));
            }

            if(empty($visibleProjectIds)) $visibleProjectIds = [0];

            $query .= "and eproject_origin_id in (" . implode(',',$visibleProjectIds) . ") ";
        }

        if(!empty($subsidiaryId))
        {
            $projIds = $this->claimCertificatePaymentRepository->getListOfProjectIds($subsidiaryId);

            if(count($projIds) > 0)
            {
                $query .= "and eproject_origin_id in (" . implode(',',$projIds) . ") ";
            }
            else
            {
                $hasData = false;
            }
        }

        if($hasData)
        {
            $query .= "group by i.eproject_origin_id";

            $claimCertCount = \DB::connection('buildspace')->select(\DB::raw($query));
    
            $projectIds     = array_column($claimCertCount, 'eproject_origin_id');
            $claimCertCount = new Illuminate\Database\Eloquent\Collection($claimCertCount);
    
            $claimCertCount = $claimCertCount->lists('count', 'eproject_origin_id');
    
            $projectsQuery = \DB::table('projects')
                ->select(array( 'id', 'title', 'reference', 'subsidiary_id' ))
                ->whereIn('id', $projectIds)
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc');

            if(!empty($filters))
            {
                foreach($filters as $filter)
                {
                    $field = $filter['field'];
                    $value = $filter['value'];
         
                    $projectsQuery->where($field, 'iLike', '%' . $value . '%');
                }
            }

            $projects = $projectsQuery->get();

            foreach($projects as $key => $project)
            {
                $project->no             = $key + 1;
                $project->claimCertCount = $claimCertCount[ $project->id ];
                $project->subsidiaries   = array( $project->subsidiary_id );
            }
        }

        return Response::json($projects);
    }

    public function exportReport()
    {
        $reportGenerator = new \PCK\Reports\ProjectClaimCertificatesReportGenerator();

        $projectIds = Input::get('projectIds');

        if(!is_array($projectIds)) $projectIds = array();

        return $reportGenerator->generate($projectIds);
    }
     
    public function exportReportWithCreditDebitNotes()
    {
        $reportGenerator = new \PCK\Reports\ProjectsClaimCertificateDebitCreditNotesReport();

        $projectIds = Input::get('projectIds');

        if(!is_array($projectIds)) $projectIds = [];

        return $reportGenerator->generate($projectIds);
    }

    public function getClaimCertificateInvoiceInformation($claimCertificateId)
    {
        $claimCertificate   = ClaimCertificate::find($claimCertificateId);

        return Response::json([
            'claimCertInvoiceNumber'    => is_null($claimCertificate->claimCertificateInvoiceInformation) ? null : $claimCertificate->claimCertificateInvoiceInformation->invoice_number,
            'claimCertInvoiceDate'      => is_null($claimCertificate->claimCertificateInvoiceInformation) ? Carbon::now()->format('d-M-Y') : Carbon::parse($claimCertificate->claimCertificateInvoiceInformation->invoice_date)->format(\Config::get('dates.submission_date_formatting')),
            'claimCertInvoicePostMonth' => is_null($claimCertificate->claimCertificateInvoiceInformation) ? date('Ym') : $claimCertificate->claimCertificateInvoiceInformation->post_month,
        ]);
    }

    public function claimCertificateInvoiceInformationStore($claimCertificateId)
    {
        $errors = null;
        $success = false;

        try
        {
            $inputs = Input::all();
            $this->invoiceForm->validate($inputs);

            $invoiceInformation = ClaimCertificateInvoice::where('claim_certificate_id', $claimCertificateId)->first();
            
            if(is_null($invoiceInformation))
            {
                $invoiceInformation = new ClaimCertificateInvoice();
                $invoiceInformation->claim_certificate_id = $claimCertificateId;
                $invoiceInformation->created_by = \Confide::user()->getBsUser()->id;
            }

            $invoiceInformation->invoice_date = $inputs['invoiceDate'];
            $invoiceInformation->invoice_number = $inputs['invoiceNumber'];
            $invoiceInformation->post_month = $inputs['postMonth'];
            $invoiceInformation->updated_by = \Confide::user()->getBsUser()->id;
            $invoiceInformation->save();

            $success = true;
        }
        catch(ValidationException $e)
        {
            $errors = $e->getMessageBag();
        }
        catch(Exception $e)
        {
            $errors = $e->getErrors();
        }

        return Response::json([
            'errors'   => $errors,
            'success' => $success,
        ]);
    }
}