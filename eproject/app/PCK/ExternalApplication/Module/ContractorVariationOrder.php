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
use PCK\AccountCodeSettings\AccountCodeSetting;
use PCK\SystemModules\SystemModuleConfiguration;

use PCK\Projects\Project;
use PCK\RequestForVariation\RequestForVariation;
use PCK\Subsidiaries\Subsidiary as EprojectSubsidiary;
use PCK\Verifier\Verifier;

use PCK\Buildspace\VariationOrder;
use PCK\Buildspace\PostContractClaim;
use PCK\Buildspace\ContractManagementClaimVerifier;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions as GuzzleRequestOptions;

class ContractorVariationOrder extends Base
{
    protected static $className = self::class;

    public static function outboundPost(VariationOrder $variationOrder)
    {
        if($variationOrder->status != PostContractClaim::STATUS_APPROVED)
        {
            return [];
        }

        $project = $variationOrder->projectStructure->mainInformation->getEProjectProject();

        if(!$project)
        {
            return [];
        }

        $awardedContractor = $project->getSelectedContractor();

        if(!$awardedContractor)
        {
            return [];
        }

        $letterOfAwardApprovedDate = Verifier::isApproved($project->letterOfAward) ? date('Y-m-d H:i:s', strtotime($project->getProjectTimeZoneTime(Verifier::verifiedAt($project->letterOfAward)))) : null;

        $accountCodeSetting = $project->accountCodeSetting;

        $contractData = [
            "vendor" => self::generateVendorData($awardedContractor),
            "contract" => self::generateProjectData($project),
            "vendor_category" => [
                "code" => ($accountCodeSetting && $accountCodeSetting->vendorCategory) ? trim($accountCodeSetting->vendorCategory->code) : null,
                "name" => ($accountCodeSetting && $accountCodeSetting->vendorCategory) ? trim($accountCodeSetting->vendorCategory->name) : null
            ],
            "loa_approved_date" => $letterOfAwardApprovedDate,
            "user_email" => $variationOrder->getEprojectUpdatedBy()->email,
        ];

        $clientModules = ClientModule::where('module', '=', ClientModule::MODULE_CONTRACTOR_VARIATION_ORDER)
        ->where('outbound_status', '=', ClientModule::OUTBOUND_STATUS_ENABLED)
        ->whereNotNull('outbound_url_path')
        ->get();

        $identifierTbl = with(new Identifier)->getTable();
        $subsidiaryTbl = with(new EprojectSubsidiary)->getTable();

        foreach($clientModules as $clientModule)
        {
            $module = new ContractorVariationOrder($clientModule);

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
                $variationOrderData = self::generateVariationOrderData($variationOrder);
                $data['variation_order'] = $variationOrderData;
                $module->post($data);
            }
        }
    }

    protected static function generateVariationOrderData(VariationOrder $variationOrder)
    {
        $requestForVariation = !empty(($variationOrder->eproject_rfv_id)) ? RequestForVariation::find($variationOrder->eproject_rfv_id) : null;
        $project = $variationOrder->projectStructure->mainInformation->getEProjectProject();

        $number = ($requestForVariation) ? trim($project->reference)."/VO".sprintf("%03d", $requestForVariation->rfv_number) : trim($project->reference);
        $omissionAddition = $variationOrder->getOmissionAddition();

        $claimVerifier = ContractManagementClaimVerifier::where('project_structure_id', '=', $variationOrder->project_structure_id)
        ->where('module_identifier', '=', PostContractClaim::TYPE_VARIATION_ORDER)
        ->where('object_id', '=', $variationOrder->id)
        ->orderBy('sequence_number', 'desc')
        ->first();

        return [
            'id' => $variationOrder->id,
            'number' => $number,
            'description' => trim($variationOrder->description),
            'amount' => ($omissionAddition) ? $omissionAddition->nett_omission_addition : 0,
            'rfv_category' => ($requestForVariation && $requestForVariation->requestForVariationCategory) ? trim($requestForVariation->requestForVariationCategory->name) : '',
            'rfv_reason' => ($requestForVariation) ? trim($requestForVariation->reasons_for_variation) : '',
            'approval_date' => ($claimVerifier) ?  date('Y-m-d', strtotime($claimVerifier->verified_at)) : '',
            'final_approver' => ($claimVerifier && $claimVerifier->verifier) ? $claimVerifier->verifier->name : '',
            'ai_number' => ($requestForVariation) ? trim($requestForVariation->ai_number) : '',
            'nett_addition' => ($omissionAddition) ? $omissionAddition->addition : 0,
            'nett_omission' => ($omissionAddition) ? $omissionAddition->omission : 0
        ];
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

    protected static function generateProjectData(Project $project)
    {
        $commencementDate = null;
        $completionDate   = null;
        $contractSum      = null;

        if($project->pam2006Detail)
        {
            $commencementDate = $project->getProjectTimeZoneTime($project->pam2006Detail->commencement_date);
            $completionDate   = $project->getProjectTimeZoneTime($project->pam2006Detail->completion_date);
            $contractSum      = floatval($project->pam2006Detail->contract_sum);
        }

        if($project->indonesiaCivilContractInformation)
        {
            $commencementDate = $project->getProjectTimeZoneTime($project->postContractInformation->commencement_date);
            $completionDate   = $project->getProjectTimeZoneTime($project->postContractInformation->completion_date);
            $contractSum      = floatval($project->postContractInformation->contract_sum);
        }

        $itemCodeAccountCodes = null;
        $itemCodeTaxCodes = null;

        if($project->accountCodeSetting && $project->accountCodeSetting->isApproved())
        {
            $accountCodeSettingRepository = \App::make('PCK\AccountCodeSettings\AccountCodeSettingRepository');

            $itemCodes = $accountCodeSettingRepository->getSavedItemCodes($project->id);

            $itemCodeAccountCodes = implode(',', array_filter(array_map('trim', array_column($itemCodes, 'accountCode'))));
            $itemCodeTaxCodes     = implode(',', array_filter(array_map('trim', array_column($itemCodes, 'taxCode'))));
        }

        return [
            "id" => $project->id, //buildspace internal id
            "title" => trim($project->title),
            "reference_no" => trim($project->reference),
            "project_brief" => trim($project->description),
            "subsidiary" => trim($project->subsidiary->name),
            "contract_sum" => $contractSum,
            "external_id" => '',
            'commencement_date' => date('Y-m-d', strtotime($commencementDate)),
            'completion_date' => date('Y-m-d', strtotime($completionDate)),
            'wbs_code' => $itemCodeAccountCodes,
            'tax_code' => $itemCodeTaxCodes
        ];
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
}
