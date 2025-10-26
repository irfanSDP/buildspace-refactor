<?php
namespace PCK\ExternalApplication\Module;

use Carbon\Carbon;

use Illuminate\Http\Request;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

use PCK\ExternalApplication\Module\Base;
use PCK\ExternalApplication\Client;
use PCK\ExternalApplication\ClientModule;
use PCK\ExternalApplication\Identifier;
use PCK\ExternalApplication\OutboundAuthorization;
use PCK\ExternalApplication\OutboundLog;

use PCK\FormBuilder\FormObjectMapping;
use PCK\FormBuilder\DynamicForm;
use PCK\FormBuilder\Elements\Element;

use PCK\Companies\Company;
use PCK\Buildspace\ClaimCertificate;
use PCK\Buildspace\AccountCode;
use PCK\Buildspace\ProjectCodeSetting;
use PCK\Buildspace\ItemCodeSettingObjectBreakdown;
use PCK\SystemModules\SystemModuleConfiguration;

use PCK\Projects\Project;
use PCK\Subsidiaries\Subsidiary as EprojectSubsidiary;
use PCK\Verifier\Verifier;
use PCK\AccountCodeSettings\ExportAccountingReportLog;
use PCK\AccountCodeSettings\SubsidiaryApportionmentRecord;
use PCK\Base\Helpers;
use PCK\DocumentManagementFolders\DocumentManagementFolder;
use PCK\DocumentManagementFolders\FolderType;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;

class ProgressClaim extends Base
{
    protected static $className = self::class;

    public static function outboundPost(ClaimCertificate $claimCertificate)
    {
        if($claimCertificate->status != ClaimCertificate::STATUS_TYPE_APPROVED)
        {
            return [];
        }

        $claimRevision = $claimCertificate->postContractClaimRevision;

        $project = $claimRevision->postContract->projectStructure->mainInformation->getEProjectProject();

        $awardedContractor = $project->getSelectedContractor();

        if(!$awardedContractor)
        {
            return [];
        }

        $letterOfAwardApprovedDate = Verifier::isApproved($project->letterOfAward) ? date('Y-m-d H:i:s', strtotime($project->getProjectTimeZoneTime(Verifier::verifiedAt($project->letterOfAward)))) : null;

        $apportionmentAmount = 0;

        ItemCodeSettingObjectBreakdown::getRecordsBy($claimCertificate->id)->each(function($breakdown) use (&$apportionmentAmount) {
            $apportionmentAmount += floatval($breakdown['amount']);
        });

        $attachmentLink = null;

        if($folder = DocumentManagementFolder::where('project_id', '=', $project->id)->where('folder_type', '=', FolderType::TYPE_2D_DRAWING)->first())
        {
            $attachmentLink = route('projectDocument.index', [$project->id, $folder->id]);
        }

        $contractData = [
            "vendor" => self::generateVendorData($awardedContractor),
            "contract" => self::generateProjectData($project, $claimCertificate),
            "vendor_category" => [
                "code" => ($project->accountCodeSetting && $project->accountCodeSetting->vendorCategory) ? trim($project->accountCodeSetting->vendorCategory->code) : null,
                "name" => ($project->accountCodeSetting && $project->accountCodeSetting->vendorCategory) ? trim($project->accountCodeSetting->vendorCategory->name) : null,
            ],
            "loa_approved_date" => $letterOfAwardApprovedDate,
            "attachment_link" => $attachmentLink,
            "apportionment_amount" => round($apportionmentAmount, 2),
            "claim" => self::generateClaimData($claimCertificate),
            "subsidiary_proportion" => self::generateSubsidiaryProportionData($project, $claimCertificate),
            "user_email" => \Confide::user() ? \Confide::user()->email : null,
        ];

        $clientModules = ClientModule::where('module', '=', ClientModule::MODULE_PROGRESS_CLAIM)
        ->where('outbound_status', '=', ClientModule::OUTBOUND_STATUS_ENABLED)
        ->whereNotNull('outbound_url_path')
        ->get();

        $identifierTbl = with(new Identifier)->getTable();
        $subsidiaryTbl = with(new EprojectSubsidiary)->getTable();

        foreach($clientModules as $clientModule)
        {
            $module = new ProgressClaim($clientModule);

            $data = [];

            $subsidiaryExtIdentifier = \DB::table($subsidiaryTbl)->select($subsidiaryTbl.'.id', $subsidiaryTbl.'.name', $identifierTbl.'.internal_identifier', $identifierTbl.'.external_identifier')
            ->join($identifierTbl, $subsidiaryTbl.'.id', '=', $identifierTbl.'.internal_identifier')
            ->join('external_application_client_modules', $identifierTbl.'.client_module_id', '=', 'external_application_client_modules.id')
            ->where('external_application_client_modules.client_id', '=', $clientModule->client_id)
            ->where($identifierTbl.'.class_name', '=', EprojectSubsidiary::class)
            ->where($subsidiaryTbl.'.id', '=', $project->subsidiary_id)
            ->first();

            if($clientModule->outbound_only_same_source)
            {
                if($subsidiaryExtIdentifier && $subsidiaryExtIdentifier->internal_identifier == $project->subsidiary_id)
                {
                    $data = $contractData;

                    $data['contract']['external_id'] = intval($subsidiaryExtIdentifier->external_identifier);
                }
            }
            else
            {
                $data = $contractData;

                if($subsidiaryExtIdentifier)
                {
                    $data['contract']['external_id'] = intval($subsidiaryExtIdentifier->external_identifier);
                }
            }

            if(!empty($data))
            {
                self::updateSubsidiaryProportionExternalId($clientModule, $data);

                $module->post($data);
            }
        }
    }

    protected static function updateSubsidiaryProportionExternalId($clientModule, &$data)
    {
        $identifierTbl = with(new Identifier)->getTable();
        $subsidiaryTbl = with(new EprojectSubsidiary)->getTable();

        foreach($data['subsidiary_proportion'] as $subsidiaryProportionKey => $subsidiaryProportionInfo)
        {
            $subsidiaryExtIdentifier = \DB::table($subsidiaryTbl)->select($subsidiaryTbl.'.id', $subsidiaryTbl.'.name', $identifierTbl.'.internal_identifier', $identifierTbl.'.external_identifier')
            ->join($identifierTbl, $subsidiaryTbl.'.id', '=', $identifierTbl.'.internal_identifier')
            ->join('external_application_client_modules', $identifierTbl.'.client_module_id', '=', 'external_application_client_modules.id')
            ->where('external_application_client_modules.client_id', '=', $clientModule->client_id)
            ->where($identifierTbl.'.class_name', '=', EprojectSubsidiary::class)
            ->where($subsidiaryTbl.'.id', '=', $subsidiaryProportionInfo['subsidiary_id'])
            ->first();

            if($subsidiaryExtIdentifier)
            {
                $data['subsidiary_proportion'][$subsidiaryProportionKey]['external_id'] = intval($subsidiaryExtIdentifier->external_identifier);
            }

            unset($data['subsidiary_proportion'][$subsidiaryProportionKey]['subsidiary_id']);
        }
    }

    protected static function generateVendorData($awardedContractor)
    {
        $vendorManagementModuleEnabled = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT);

        $isBumi = false;

        if($awardedContractor->company_status && $awardedContractor->company_status == Company::COMPANY_STATUS_BUMIPUTERA)
        {
            $isBumi = true;
        }
        else
        {
            $isBumi = $awardedContractor->is_bumiputera;
        }

        $bankName = '';
        $bankAccountNo = '';

        if($vendorManagementModuleEnabled && $awardedContractor->finalVendorRegistration)
        {
            $vendorRegistrationDetails = [];

            $formObjectMappping = FormObjectMapping::findRecord($awardedContractor->finalVendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

            if($formObjectMappping)
            {
                $formElementIds = $formObjectMappping->dynamicForm->getAllFormElementIdsGroupedByType();

                $elementDisplayInfo = [];

                foreach($formElementIds[Element::ELEMENT_TYPE_ID] as $id)
                {
                    $element = Element::findById($id);
                    $values = $element->getSavedValuesDisplay();

                    if(mb_strtolower($values['label']) == 'bank name' && isset($values['values'][0]))
                    {
                        $bankName = $values['values'][0];
                    }

                    if(mb_strtolower($values['label']) == 'account no' && isset($values['values'][0]))
                    {
                        $bankAccountNo = $values['values'][0];
                    }
                }
            }
        }

        return [
            "id" => $awardedContractor->id,
            "name" => trim($awardedContractor->name),
            "code" => $awardedContractor->getVendorCode(),
            "group" => ($awardedContractor->contractGroupCategory) ? trim($awardedContractor->contractGroupCategory->name) : '',
            "roc" => trim($awardedContractor->reference_no),
            "bank_name" => trim($bankName),
            "bank_account_no" => trim($bankAccountNo),
            "telephone_no" => trim($awardedContractor->telephone_number),
            "email" => trim($awardedContractor->email),
            "contact_person" => trim($awardedContractor->main_contact),
            "tax_no" => $awardedContractor->tax_registration_no ? trim($awardedContractor->tax_registration_no) : null,
            "bumi_status" => ($isBumi),
            "address" => trim($awardedContractor->address),
            "state" => ($awardedContractor->state_id) ? trim($awardedContractor->state->name) : '',
            "country" => ($awardedContractor->country_id) ? trim($awardedContractor->country->country) : ''
        ];
    }

    protected static function generateProjectData(Project $project, ClaimCertificate $claimCertificate)
    {
        $commencementDate = null;
        $completionDate   = null;
        $contractSum      = null;

        if($project->pam2006Detail)
        {
            $commencementDate = $project->getProjectTimeZoneTime($project->pam2006Detail->commencement_date);
            $completionDate   = $project->getProjectTimeZoneTime($project->pam2006Detail->completion_date);
            $contractSum      = $project->pam2006Detail->contract_sum;
        }

        if($project->indonesiaCivilContractInformation)
        {
            $commencementDate = $project->getProjectTimeZoneTime($project->postContractInformation->commencement_date);
            $completionDate   = $project->getProjectTimeZoneTime($project->postContractInformation->completion_date);
            $contractSum      = $project->postContractInformation->contract_sum;
        }

        return [
            "id" => $project->id, //buildspace internal id
            "title" => trim($project->title),
            "reference_no" => trim($project->reference),
            "project_brief" => trim($project->description),
            "subsidiary" => trim($project->subsidiary->name),
            "contract_sum" => round(floatval($contractSum), 2),
            "external_id" => '',
            'commencement_date' => date('Y-m-d', strtotime($commencementDate)),
            'completion_date' => date('Y-m-d', strtotime($completionDate)),
        ];
    }

    protected static function generateClaimData(ClaimCertificate $claimCertificate)
    {
        $claimCertificateInfo = ClaimCertificate::getClaimCertInfo([$claimCertificate->id])[$claimCertificate->id];

        $creditNoteAmount = 0;
        $debitNoteAmount  = 0;

        foreach($claimCertificateInfo['debitCreditNoteBreakdownThisClaim'] as $creditDebitNoteBreakdown)
        {
            if($creditDebitNoteBreakdown['type'] == AccountCode::ACCOUNT_TYPE_PCN)
            {
                $creditNoteAmount += floatval($creditDebitNoteBreakdown['total']);
            }
            elseif($creditDebitNoteBreakdown['type'] == AccountCode::ACCOUNT_TYPE_PDN)
            {
                $debitNoteAmount += floatval($creditDebitNoteBreakdown['total']);
            }
        }

        return [
            "id" => $claimCertificate->id,
            "number" => is_null($claimCertificate->claimCertificateInvoiceInformation) ? null : trim($claimCertificate->claimCertificateInvoiceInformation->invoice_number),
            "approval_date" => $claimCertificate->approvalLog ? \Carbon\Carbon::parse($claimCertificate->approvalLog->created_at)->format(\Config::get('dates.reversed_date')) : null,
            "description" => trans('finance.claimNo:number', ['claimNumber' => $claimCertificate->postContractClaimRevision->version]),
            "amount_certified" => round(floatval($claimCertificateInfo['cumulativeAmountCertified'])),
            "certificate_number" => intval($claimCertificate->postContractClaimRevision->version),
            "certificate_received_date" => \Carbon\Carbon::parse($claimCertificate->qs_received_date)->format(\Config::get('dates.reversed_date')),
            "work_done_amount" => round(floatval($claimCertificateInfo['totalWorkDone']), 2),
            "retention_release" => round(floatval($claimCertificateInfo['currentReleaseRetentionAmount']) ,2),
            "cumulative_retention_sum" => round(floatval($claimCertificateInfo['cumulativeRetentionSum']) ,2),
            "mos_amount" => round(floatval($claimCertificateInfo['cumulativeMaterialOnSiteWorkDone']) ,2),
            "other_expenses_amount" => round(floatval($claimCertificateInfo['miscThisClaimSubTotal']) ,2) - round(floatval($claimCertificateInfo['materialOnSiteThisClaim']) ,2),
            "credit_note_amount" => round($creditNoteAmount, 2),
            "debit_note_amount" => round($debitNoteAmount, 2),
            "deduction_amount" => round(floatval($claimCertificateInfo['otherThisClaimSubTotal']), 2) - round(floatval($claimCertificateInfo['debitCreditNoteThisClaim']), 2) - round(floatval($claimCertificateInfo['penaltyThisClaim']), 2),
            "penalty_amount" => round(floatval($claimCertificateInfo['penaltyThisClaim']), 2),
            "qs_remarks" => $claimCertificate->qs_remarks ? trim($claimCertificate->qs_remarks) : null,
            "acc_remarks" => $claimCertificate->acc_remarks ? trim($claimCertificate->acc_remarks) : null,
        ];
    }

    protected static function generateSubsidiaryProportionData(Project $project, ClaimCertificate $claimCertificate)
    {
        if(!($project->accountCodeSetting && $project->accountCodeSetting->isApproved())) return [];

        $bsProject = $project->getBsProjectMainInformation()->projectStructure;

        $selectedSubsidiaryRecords = ProjectCodeSetting::getSelectedSubsidiaries($bsProject);

        $apportionmentType = $project->accountCodeSetting->apportionmentType;

        $subsidiaryApportionmentValues = SubsidiaryApportionmentRecord::where('apportionment_type_id', '=', $apportionmentType->id)
            ->whereIn('subsidiary_id', $selectedSubsidiaryRecords->lists('eproject_subsidiary_id'))
            ->lists('value', 'subsidiary_id');

        $amountByItemCodeSettingId = [];

        $accountCodeSettingRepository = \App::make('PCK\AccountCodeSettings\AccountCodeSettingRepository');

        $itemCodes = $accountCodeSettingRepository->getSavedItemCodes($project->id);

        $itemCodeSettingBreakdown = ItemCodeSettingObjectBreakdown::getRecordsBy($claimCertificate->id);

        foreach($itemCodes as $itemCode)
        {
            $apportionmentAmount = 0;

            $itemCodeSettingBreakdown->each(function($breakdown) use ($itemCode, &$apportionmentAmount) {
                if($breakdown['item_code_setting_id'] == $itemCode['id']) $apportionmentAmount += floatval($breakdown['amount']);
            });

            $amountByItemCodeSettingId[$itemCode['id']] = $apportionmentAmount;
        }

        $latestExportLog  = ExportAccountingReportLog::getLog($claimCertificate->id)->first();

        $subsidiaryIdsByProjectCodeSettingId = ProjectCodeSetting::whereIn('id', $latestExportLog->logDetails->lists('project_code_setting_id'))
            ->lists('eproject_subsidiary_id', 'id');

        $subsidiaryIdsByItemCodeSettingId = [];

        foreach($latestExportLog->logDetails as $logDetail)
        {
            foreach($logDetail->accountingReportExportLogItemCodes as $exportLogItemCode)
            {
                if(!array_key_exists($exportLogItemCode->item_code_setting_id, $subsidiaryIdsByItemCodeSettingId)) $subsidiaryIdsByItemCodeSettingId[$exportLogItemCode->item_code_setting_id] = [];

                array_push($subsidiaryIdsByItemCodeSettingId[$exportLogItemCode->item_code_setting_id], $subsidiaryIdsByProjectCodeSettingId[$logDetail->project_code_setting_id]);
            }
        }

        $itemCodeSettingIdsBySubsidiaryId = array_fill_keys($subsidiaryIdsByProjectCodeSettingId, []);

        foreach($latestExportLog->logDetails as $logDetail)
        {
            foreach($logDetail->accountingReportExportLogItemCodes as $exportLogItemCode)
            {
                array_push($itemCodeSettingIdsBySubsidiaryId[$subsidiaryIdsByProjectCodeSettingId[$logDetail->project_code_setting_id]], $exportLogItemCode->item_code_setting_id);
            }
        }

        $itemCodeSettingInformation = [];

        foreach($bsProject->itemCodeSettings as $itemCodeSetting)
        {
            $itemCodeSettingInformation[$itemCodeSetting->id] = [
                'wbs_code' => $itemCodeSetting->accountCode->code,
                'tax_code' => $itemCodeSetting->accountCode->tax_code,
            ];
        }

        $subsidiaryRatiosByItemCodeSettingId = [];

        foreach($subsidiaryIdsByItemCodeSettingId as $itemCodeSettingId => $subsidiaryIds)
        {
            $subsidiaryRatiosByItemCodeSettingId[$itemCodeSettingId] = [];

            $itemCodeSettingWeightageTotal = 0;

            foreach($subsidiaryIdsByItemCodeSettingId[$itemCodeSettingId] as $subsidiaryId)
            {
                $itemCodeSettingWeightageTotal += $subsidiaryApportionmentValues[$subsidiaryId];
            }

            foreach($subsidiaryIds as $subsidiaryId)
            {
                $subsidiaryRatiosByItemCodeSettingId[$itemCodeSettingId][$subsidiaryId] = Helpers::divide($subsidiaryApportionmentValues[$subsidiaryId], $itemCodeSettingWeightageTotal);
            }
        }

        $data = [];

        foreach($subsidiaryIdsByProjectCodeSettingId as $subsidiaryId)
        {
            $proportion = [];

            foreach($itemCodeSettingIdsBySubsidiaryId[$subsidiaryId] as $itemCodeSettingId)
            {
                $proportion[] = [
                    'tax_code' => trim($itemCodeSettingInformation[$itemCodeSettingId]['tax_code']),
                    'wbs_code' => trim($itemCodeSettingInformation[$itemCodeSettingId]['wbs_code']),
                    'apportionment_amount' => round(($amountByItemCodeSettingId[$itemCodeSettingId] * $subsidiaryRatiosByItemCodeSettingId[$itemCodeSettingId][$subsidiaryId]), 2),
                ];
            }

            $data[] = [
                "subsidiary_id" => $subsidiaryId,
                "external_id" => "",
                "proportion" => $proportion
            ];
        }

        return $data;
    }

    public function post($contractData)
    {
        $outboundAuth = $this->clientModule->client->outboundAuthorization;

        if($outboundAuth)
        {
            switch($outboundAuth->type)
            {
                case OutboundAuthorization::TYPE_OAUTH_TWO:
                    $this->oAuthTwoPost($contractData);
                    break;
                default:
                    break;
            }
        }
    }

    public function outboundLogs(Request $request)
    {
        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 100;
        $page  = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $outboundTbl = with(new OutboundLog)->getTable();

        $model = OutboundLog::select($outboundTbl.".id AS id", $outboundTbl.".data", $outboundTbl.".status_code", $outboundTbl.".response_contents",
        $outboundTbl.".created_at")
        ->where($outboundTbl.'.module', '=', get_class($this))
        ->where($outboundTbl.'.client_id', '=', $this->clientModule->client_id);

        $model->orderBy($outboundTbl.'.created_at', 'desc');

        $rowCount = $model->get()->count();

        $records = $model->skip($limit * ($page - 1))
        ->take($limit)
        ->get();

        $data = [];

        foreach($records->all() as $key => $record)
        {
            $counter = ($page-1) * $limit + $key + 1;

            $data[] = [
                'counter'           => $counter,
                'id'                => $record->id,
                'data'              => $record->data,
                'status_code'       => $record->status_code,
                'response_contents' => $record->response_contents,
                'created_at'        => Carbon::parse($record->created_at)->format('Y-m-d H:i:s')
            ];
        }

        $totalPages = ceil( $rowCount / $limit );

        return [$totalPages, $data];
    }

    public function update($id, array $data)
    {
        if($this->clientModule->downstream_permission == ClientModule::DOWNSTREAM_PERMISSION_DISABLED)
        {
            throw new UnauthorizedHttpException('Bearer', 'No authorization to update entity');
        }

        $success = false;

        if($claimCertificate = ClaimCertificate::find($id))
        {
            $claimCertificate->claimCertificateInformation->paid = true;
            $claimCertificate->claimCertificateInformation->save();

            $success = true;

            \Log::info("Updated Claim Certificate payment status to paid for Claim Certificate with id: {$claimCertificate->id}");
        }
        else
        {
            \Log::info("Failed to update Claim Certificate payment status to paid. Claim Certificate with id: {$claimCertificate->id} not found");
        }

        return ["success" => $success];
    }
}
