<?php namespace PCK\InterimClaimInformation;

use PCK\Users\User;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\InterimClaims\InterimClaim;
use PCK\ContractGroups\Types\Role;
use PCK\ICInfoNettAddOmiAttachments\ICInfoNettAddOmiAttachment;
use PCK\ICInfoGrossValuesAttachments\ICInfoGrossValuesAttachment;

class InterimClaimInformationRepository extends BaseModuleRepository {

    private $interimClaimInformation;

    protected $events;

    public function __construct(InterimClaimInformation $interimClaimInformation, Dispatcher $events)
    {
        $this->interimClaimInformation = $interimClaimInformation;
        $this->events                  = $events;
    }

    public function find($iccId)
    {
        return $this->interimClaimInformation
            ->with(
                'interimClaim.project', 'createdBy.company'
            )->findOrFail($iccId);
    }

    public function add(InterimClaim $ic, User $user, array $inputs)
    {
        $icInfo     = $this->interimClaimInformation;
        $sendToRole = [ Role::CLAIM_VERIFIER, Role::CONTRACTOR ];

        $icInfo->interim_claim_id                = $ic->id;
        $icInfo->created_by                      = $user->id;
        $icInfo->reference                       = $inputs['reference'];
        $icInfo->date                            = $inputs['date'];
        $icInfo->nett_addition_omission          = $inputs['nett_addition_omission'];
        $icInfo->net_amount_of_payment_certified = $this->calculateNetAmountPaymentCertified($ic, $inputs);
        $icInfo->gross_values_of_works           = $inputs['gross_values_of_works'];
        $icInfo->amount_in_word                  = $inputs['amount_in_word'];

        if( $user->hasCompanyProjectRole($ic->project, array( Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER )) )
        {
            $icInfo->date_of_certificate = $inputs['date_of_certificate'];
        }

        $icInfo->type = $user->getAssignedCompany($ic->project)->getContractGroup($ic->project)->group;

        $icInfo = $this->save($icInfo);

        // save attached attachment for Nett Addition/Omission
        $this->saveNettAdditionOmissionAttachments($icInfo, $inputs);

        // save attached attachment for Gross Values
        $this->saveGrossValuesAttachments($icInfo, $inputs);

        if( $icInfo->type == Role::CONTRACTOR )
        {
            $sendToRole = [ Role::INSTRUCTION_ISSUER, Role::CLAIM_VERIFIER ];
        }
        elseif( $icInfo->type == Role::CLAIM_VERIFIER )
        {
            $sendToRole = [ Role::INSTRUCTION_ISSUER, Role::CONTRACTOR ];
        }

        $this->sendEmailNotification($ic->project, $ic, $sendToRole, 'interim_claim', 'ic.show');
        $this->sendSystemNotification($ic->project, $ic, $sendToRole, 'interim_claim', 'ic.show');

        return $icInfo;
    }

    public function save(InterimClaimInformation $instance)
    {
        $instance->save();

        return $instance;
    }

    private function calculateNetAmountPaymentCertified(InterimClaim $ic, $inputs)
    {
        $retentionFund = $ic->getCertifiedRetentionFund($inputs['gross_values_of_works']);

        if( $retentionFund > $ic->max_retention_fund )
        {
            $retentionFund = $ic->max_retention_fund;
        }

        return $inputs['gross_values_of_works'] - $retentionFund - $inputs['previous_claim_amount'];
    }

    private function saveNettAdditionOmissionAttachments(InterimClaimInformation $object, array $inputs)
    {
        if( ! isset( $inputs['nett_addition_omission_uploaded_files'] ) )
        {
            return false;
        }

        foreach($inputs['nett_addition_omission_uploaded_files'] as $file)
        {
            $newObject                               = new ICInfoNettAddOmiAttachment();
            $newObject->interim_claim_information_id = $object->id;
            $newObject->upload_id                    = $file;
            $newObject->save();

            unset( $newObject );
        }

        return true;
    }

    private function saveGrossValuesAttachments(InterimClaimInformation $object, array $inputs)
    {
        if( ! isset( $inputs['gross_values_uploaded_files'] ) )
        {
            return false;
        }

        foreach($inputs['gross_values_uploaded_files'] as $file)
        {
            $newObject                               = new ICInfoGrossValuesAttachment();
            $newObject->interim_claim_information_id = $object->id;
            $newObject->upload_id                    = $file;
            $newObject->save();

            unset( $newObject );
        }

        return true;
    }

}