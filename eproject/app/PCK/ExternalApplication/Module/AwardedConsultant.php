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
use PCK\FormBuilder\Elements\SystemModuleElement;

use PCK\Companies\Company;
use PCK\Users\User;
use PCK\ConsultantManagement\LetterOfAward;
use PCK\SystemModules\SystemModuleConfiguration;

use PCK\ConsultantManagement\ConsultantManagementSubsidiary;
use PCK\ConsultantManagement\ConsultantManagementCallingRfp;
use PCK\ConsultantManagement\ConsultantManagementCallingRfpCompany;

use PCK\Subsidiaries\Subsidiary as EprojectSubsidiary;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;

class AwardedConsultant extends Base
{
    protected static $className = self::class;

    public static function outboundPost(LetterOfAward $letterOfAward)
    {
        if($letterOfAward->status != LetterOfAward::STATUS_APPROVED)
        {
            return [];
        }

        $rfp = $letterOfAward->consultantManagementVendorCategoryRfp;
        $contract = $rfp->consultantManagementContract;
        
        $latestRfpRevision = $rfp->getLatestRfpRevision();

        if(!$latestRfpRevision)
        {
            return [];
        }

        $awardedConsultant = $latestRfpRevision->getAwardedConsultant();

        if(!$awardedConsultant)
        {
            return [];
        }

        $vendorManagementModuleEnabled = SystemModuleConfiguration::isEnabled(SystemModuleConfiguration::MODULE_ID_VENDOR_MANAGEMENT);

        $isBumi = false;

        if($awardedConsultant->company_status && $awardedConsultant->company_status == Company::COMPANY_STATUS_BUMIPUTERA)
        {
            $isBumi = true;
        }
        else
        {
            $isBumi = $awardedConsultant->is_bumiputera;
        }

        $bankName = '';
        $bankAccountNo = '';

        if($vendorManagementModuleEnabled && $awardedConsultant->finalVendorRegistration)
        {
            $vendorRegistrationDetails = [];

            $formObjectMappping = FormObjectMapping::findRecord($awardedConsultant->finalVendorRegistration, DynamicForm::VENDOR_REGISTRATION_IDENTIFIER);

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

        $proposedFees = ConsultantManagementSubsidiary::select("subsidiaries.id AS subsidiary_id", "subsidiaries.name AS subsidiary_name", "consultant_management_consultant_rfp.company_id",
        "consultant_management_subsidiaries.launch_date",
        "consultant_management_consultant_rfp_proposed_fees.id AS proposed_fee_id", "consultant_management_consultant_rfp_common_information.updated_at AS submitted_at",
        "consultant_management_consultant_rfp_proposed_fees.proposed_fee_percentage", "consultant_management_consultant_rfp_proposed_fees.proposed_fee_amount", "consultant_management_subsidiaries.total_construction_cost", "consultant_management_subsidiaries.total_landscape_cost")
        ->join('consultant_management_consultant_rfp_proposed_fees', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_subsidiary_id', '=', 'consultant_management_subsidiaries.id')
        ->join('consultant_management_consultant_rfp', 'consultant_management_consultant_rfp_proposed_fees.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->join('consultant_management_rfp_revisions', 'consultant_management_rfp_revisions.id', '=', 'consultant_management_consultant_rfp.consultant_management_rfp_revision_id')
        ->join('consultant_management_vendor_categories_rfp', 'consultant_management_vendor_categories_rfp.id', '=', 'consultant_management_rfp_revisions.vendor_category_rfp_id')
        ->join('consultant_management_calling_rfp', 'consultant_management_calling_rfp.consultant_management_rfp_revision_id', '=', 'consultant_management_rfp_revisions.id')
        ->join('consultant_management_calling_rfp_companies', 'consultant_management_calling_rfp_companies.consultant_management_calling_rfp_id', '=', 'consultant_management_calling_rfp.id')
        ->join('consultant_management_consultant_rfp_common_information', 'consultant_management_consultant_rfp_common_information.consultant_management_consultant_rfp_id', '=', 'consultant_management_consultant_rfp.id')
        ->join('subsidiaries', 'subsidiaries.id', '=', 'consultant_management_subsidiaries.subsidiary_id')
        ->where('consultant_management_consultant_rfp.company_id', '=', $awardedConsultant->id)
        ->where('consultant_management_rfp_revisions.id', '=', $latestRfpRevision->id)
        ->where('consultant_management_vendor_categories_rfp.id', '=', $rfp->id)
        ->where('consultant_management_subsidiaries.consultant_management_contract_id', '=', $contract->id)
        ->where('consultant_management_calling_rfp.status', '=', ConsultantManagementCallingRfp::STATUS_APPROVED)
        ->where('consultant_management_calling_rfp_companies.status', '=', ConsultantManagementCallingRfpCompany::STATUS_YES)
        ->groupBy(\DB::raw("consultant_management_subsidiaries.id, consultant_management_consultant_rfp.id, consultant_management_consultant_rfp_common_information.id, consultant_management_consultant_rfp_proposed_fees.id, subsidiaries.id"))
        ->get();

        if(!$proposedFees)
        {
            return [];
        }

        $accountCodes = [];

        foreach($rfp->accountCodePivotRecords as $pivotRecord)
        {
            $accountCodes[] = [
                "tax_code" => $pivotRecord->bsAccountCode->tax_code,
                "wbs_code" => $pivotRecord->bsAccountCode->code,
                "amount"   => round(floatval($pivotRecord->amount), 2),
            ];
        }

        $approvalDocument = $rfp->approvalDocument;

        $contractData = [
            "vendor" => [
                "id" => $awardedConsultant->id,
                "name" => trim($awardedConsultant->name),
                "code" => $awardedConsultant->getVendorCode(),
                "group" => ($awardedConsultant->contractGroupCategory) ? trim($awardedConsultant->contractGroupCategory->name) : '',
                "roc" => trim($awardedConsultant->reference_no),
                "bank_name" => trim($bankName),
                "bank_account_no" => trim($bankAccountNo),
                "telephone_no" => trim($awardedConsultant->telephone_number),
                "email" => trim($awardedConsultant->email),
                "contact_person" => trim($awardedConsultant->main_contact),
                "tax_no" => $awardedConsultant->tax_registration_no ? trim($awardedConsultant->tax_registration_no) : $awardedConsultant->tax_registration_no,
                "bumi_status" => ($isBumi),
                "address" => trim($awardedConsultant->address),
                "state" => ($awardedConsultant->state_id) ? trim($awardedConsultant->state->name) : '',
                "country" => ($awardedConsultant->country_id) ? trim($awardedConsultant->country->country) : ''
            ],
            "contract" => [
                "id" => $rfp->id, //buildspace internal id
                "title" => trim($contract->title),
                "reference_no" => $contract->reference_no,
                "project_brief" => ($approvalDocument->sectionB) ? trim($approvalDocument->sectionB->project_brief) : '',
                "subsidiary" => '',
                "contract_sum" => '',
                "external_id" => '',
                'commencement_date' => date('Y-m-d', strtotime($letterOfAward->updated_at)),
                'completion_date' => '',
            ],
            "account_codes" => $accountCodes,
            "work_category" => [
                "code" => trim($rfp->vendorCategory->code),
                "name" => trim($rfp->vendorCategory->name)
            ],
            "loa_approved_date" => date('Y-m-d H:i:s', strtotime($letterOfAward->updated_at)),
            "user_email" => User::find($letterOfAward->updated_by)->email,
        ];

        $clientModules = ClientModule::where('module', '=', ClientModule::MODULE_AWARDED_CONSULTANT)
        ->where('outbound_status', '=', ClientModule::OUTBOUND_STATUS_ENABLED)
        ->whereNotNull('outbound_url_path')
        ->get();

        $identifierTbl = with(new Identifier)->getTable();
        $subsidiaryTbl = with(new EprojectSubsidiary)->getTable();

        foreach($clientModules as $clientModule)
        {
            $module = new AwardedConsultant($clientModule);

            foreach($proposedFees as $proposedFee)
            {
                $data = [];

                $subsidiaryExtIdentifier = \DB::table($subsidiaryTbl)->select($subsidiaryTbl.'.id', $subsidiaryTbl.'.name', $identifierTbl.'.internal_identifier', $identifierTbl.'.external_identifier')
                ->join($identifierTbl, $subsidiaryTbl.'.id', '=', $identifierTbl.'.internal_identifier')
                ->join('external_application_client_modules', $identifierTbl.'.client_module_id', '=', 'external_application_client_modules.id')
                ->where('external_application_client_modules.client_id', '=', $clientModule->client_id)
                ->where($identifierTbl.'.class_name', '=', EprojectSubsidiary::class)
                ->where($subsidiaryTbl.'.id', '=', $proposedFee->subsidiary_id)
                ->first();

                if($clientModule->outbound_only_same_source)
                {
                    if($subsidiaryExtIdentifier && $subsidiaryExtIdentifier->internal_identifier == $proposedFee->subsidiary_id)
                    {
                        $data = $contractData;
                        $data['contract']['external_id'] = intval($subsidiaryExtIdentifier->external_identifier);
                    }
                }
                else
                {
                    $data = $contractData;
                    $data['contract']['external_id'] = ($subsidiaryExtIdentifier) ? intval($subsidiaryExtIdentifier->external_identifier) : '';
                }

                if(!empty($data))
                {
                    $data['contract']['subsidiary'] = trim($proposedFee->subsidiary_name);
                    $data['contract']['contract_sum'] = floatval($proposedFee->proposed_fee_amount);
                    $data['contract']['completion_date'] = ($proposedFee->launch_date) ? date('Y-m-d', strtotime($proposedFee->launch_date)) : '';

                    $module->post($letterOfAward, $data);
                }
            }
        }
    }

    public function post(LetterOfAward $letterOfAward, $contractData)
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
}
