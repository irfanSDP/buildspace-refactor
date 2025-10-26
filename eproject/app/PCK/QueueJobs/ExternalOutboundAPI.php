<?php namespace PCK\QueueJobs;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Queue\Jobs\Job;

use PCK\ExternalApplication\Module\AwardedConsultant;
use PCK\ExternalApplication\Module\AwardedContractor;
use PCK\ExternalApplication\Module\ContractorVariationOrder;
use PCK\ExternalApplication\Module\ProgressClaim;

class ExternalOutboundAPI
{
    public function fire(Job $job, array $data)
    {
        $logMsg = null;
        switch($data['module'])
        {
            case 'AwardedConsultant':
                $letterOfAward = \PCK\ConsultantManagement\LetterOfAward::findOrFail((int)$data['loa_id']);
                AwardedConsultant::outboundPost($letterOfAward);
                $job->delete();
                $logMsg = "Done running Awarded Consultant API for LOA with id:".$letterOfAward->id;
                break;
            case 'AwardedContractor':
                $accountCodeSetting = \PCK\AccountCodeSettings\AccountCodeSetting::findOrFail((int)$data['account_code_setting_id']);
                AwardedContractor::outboundPost($accountCodeSetting);
                $job->delete();
                $logMsg = "Done running Awarded Contractor API for Account Code Setting with id:".$accountCodeSetting->id;
                break;
            case 'ContractorVariationOrder':
                $variationOrder = \PCK\Buildspace\VariationOrder::findOrFail((int)$data['vo_id']);
                ContractorVariationOrder::outboundPost($variationOrder);
                $job->delete();
                $logMsg = "Done running Contractor Variation Order API for Variation Order with id:".$variationOrder->id;
                break;
            case 'ProgressClaim':
                $claimCertificate = \PCK\Buildspace\ClaimCertificate::findOrFail((int)$data['claim_cert_id']);
                ProgressClaim::outboundPost($claimCertificate);
                $job->delete();
                $logMsg = "Done running Progress Claim API for Claim Certificate with id:".$claimCertificate->id;
                break;
        }

        if($logMsg)
            \Log::info($logMsg);
    }
}