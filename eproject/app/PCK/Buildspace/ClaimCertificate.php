<?php namespace PCK\Buildspace;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use PCK\ClaimCertificate\ClaimCertificatePayment;
use PCK\Users\User;
use PCK\Buildspace\Project;

class ClaimCertificate extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_claim_certificates';

    const STATUS_TYPE_IN_PROGRESS          = 1;
    const STATUS_TYPE_PENDING_FOR_APPROVAL = 2;
    const STATUS_TYPE_APPROVED             = 4;
    const STATUS_TYPE_REJECTED             = 128;

    public function updatedBy()
    {
        return $this->belongsTo('PCK\Buildspace\User', 'updated_by');
    }

    public function getEprojectUpdatedBy()
    {
        return $this->updatedBy->Profile->getEProjectUser();
    }

    public function postContractClaimRevision()
    {
        return $this->belongsTo('PCK\Buildspace\PostContractClaimRevision', 'post_contract_claim_revision_id');
    }

    public function getDisplayDescriptionAttribute()
    {
        return $this->postContractClaimRevision->version;
    }

    public function claimCertificateInformation()
    {
        return $this->hasOne('PCK\Buildspace\ClaimCertificateInformation', 'claim_certificate_id', 'id');
    }

    public function approvalLog()
    {
        return $this->hasOne('PCK\Buildspace\ClaimCertificateApprovalLog', 'claim_certificate_id', 'id');
    }

    public function claimCertificateInvoiceInformation()
    {
        return $this->hasOne('PCK\Buildspace\ClaimCertificateInvoice', 'claim_certificate_id', 'id');
    }

    public function claimCertificatePayments()
    {
        return $this->hasMany('PCK\ClaimCertificate\ClaimCertificatePayment', 'claim_certificate_id');
    }

    public function onReview($project, $moduleId)
    {
        if( ContractManagementClaimVerifier::isApproved($project, $moduleId, $this->id) )
        {
            $this->status = self::STATUS_TYPE_APPROVED;
            
            $approvalLog                       = new ClaimCertificateApprovalLog();
            $approvalLog->status               = $this->status;
            $approvalLog->claim_certificate_id = $this->id;
            $approvalLog->save();

            // Update due date to approval date
            $this->due_date = max($this->due_date, $approvalLog->created_at);
            $this->save();

            $postContractClaimRevision = $this->postContractClaimRevision;

            $postContractClaimRevision->locked_status = true;
            $postContractClaimRevision->save();
        }
        elseif( ContractManagementClaimVerifier::isRejected($project, $moduleId, $this->id) )
        {
            if( $this->postContractClaimRevision->id == $this->postContractClaimRevision->postContract->currentProjectRevision->id )
            {
                $this->status = self::STATUS_TYPE_IN_PROGRESS;
                $this->save();

                $postContractClaimRevision = $this->postContractClaimRevision;

                $postContractClaimRevision->locked_status = false;
                $postContractClaimRevision->save();

                SubProjectLatestApprovedClaimRevision::deleteRecords($postContractClaimRevision);
            }
            else
            {
                $this->status = self::STATUS_TYPE_REJECTED;
                $this->save();

                $approvalLog                       = new ClaimCertificateApprovalLog();
                $approvalLog->status               = $this->status;
                $approvalLog->claim_certificate_id = $this->id;
                $approvalLog->save();

                $postContractClaimRevision = $this->postContractClaimRevision;

                $postContractClaimRevision->locked_status = true;
                $postContractClaimRevision->save();
            }
        }

        return $this->save();
    }

    public static function getClaimCertInfo(array $certIds)
    {
        if( empty( $certIds ) ) return array();

        $client = new Client(array(
            'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
            'base_uri' => \Config::get('buildspace.BUILDSPACE_URL')
        ));

        try
        {
            $response = $client->post('eproject_api/getClaimCertInfo', array(
                'form_params' => array(
                    'claim_certificate_ids' => $certIds
                )
            ));

            $response = json_decode($response->getBody(), true);

            if( ! $response['success'] ) throw new \Exception();
        }
        catch(\Exception $e)
        {
            $ids = implode(',', $certIds);
            throw new \Exception("Failed to obtain claim certificate information (ids: {$ids})");
        }

        return $response['claimCertificates'];
    }

    public static function checkClaimCertificateAccountingExportValidity(Project $projectStructure, ClaimCertificate $claimCertificate)
    {
        $client = new Client(array(
            'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
            'base_uri' => \Config::get('buildspace.BUILDSPACE_URL')
        ));

        try
        {
            $response = $client->post('eproject_api/checkClaimCertificateAccountingExportValidity', array(
                'form_params' => array(
                    'projectStructureId' => $projectStructure->id,
                    'claimCertificateId' => $claimCertificate->id,
                )
            ));

            $response = json_decode($response->getBody(), true);
        }
        catch(\Exception $e)
        {
            throw new \Exception("Failed to check claim certificate accounting export validity (id: {$claimCertificate->id})");
        }

        return $response['isValid'];
    }

    public function paidAmount()
    {
        return ClaimCertificatePayment::where('claim_certificate_id', '=', $this->id)->sum('amount');
    }

    public function getPrintInfoUrl()
    {
        return \Config::get('buildspace.BUILDSPACE_URL') . "ClaimCertificatePDF/{$this->postContractClaimRevision->postContract->projectStructure->id}/{$this->id}";
    }

    public function getExportAccountingReportUrl()
    {
        return \Config::get('buildspace.BUILDSPACE_URL') . 'exportExcelReport/exportPostContractAccountingReport/pid/' . $this->postContractClaimRevision->postContract->projectStructure->id. '/cid/' . $this->id;
    }

    public static function getClaimCertificateQuery($paid = null)
    {
        $query = \DB::connection('buildspace')->table("bs_claim_certificates");

        $query->select('bs_claim_certificates.id', 'bs_claim_certificates.post_contract_claim_revision_id', 'bs_claim_certificates.contractor_submitted_date', 'bs_claim_certificates.site_verified_date', 'bs_claim_certificates.qs_received_date', 'bs_claim_certificates.release_retention_amount', 'bs_claim_certificates.person_in_charge', 'bs_claim_certificates.valuation_date', 'bs_claim_certificates.due_date', 'bs_claim_certificates.budget_amount', 'bs_claim_certificates.budget_due_date', 'bs_claim_certificates.tax_percentage', 'bs_claim_certificates.status', 'bs_claim_certificates.retention_tax_percentage', 'bs_claim_certificates.release_retention_percentage', 'bs_claim_certificates.amount_certified',
        'bs_claim_certificate_information.claim_certificate_id',
        'bs_post_contract_claim_revisions.version',
        'bs_post_contracts.project_structure_id', 'bs_post_contracts.selected_type_rate', 'bs_post_contracts.published_type',
        'bs_project_main_information.eproject_origin_id',
        'bs_claim_certificate_approval_logs.status AS claim_certificate_approval_log_status', 'bs_claim_certificate_approval_logs.remarks AS claim_certificate_approval_log_remarks',
        'bs_tender_settings.awarded_company_id', 'bs_tender_settings.original_tender_value',
        'bs_companies.id AS bs_company_id', 'bs_companies.name', 'bs_companies.reference_id',
        'bs_claim_certificates.created_at');

        $query->join('bs_claim_certificate_information', 'bs_claim_certificates.id', '=', 'bs_claim_certificate_information.claim_certificate_id');
        $query->join('bs_post_contract_claim_revisions', 'bs_post_contract_claim_revisions.id', '=', 'bs_claim_certificates.post_contract_claim_revision_id');
        $query->join('bs_post_contracts', 'bs_post_contracts.id', '=', 'bs_post_contract_claim_revisions.post_contract_id');
        $query->join('bs_project_structures', 'bs_project_structures.id', '=', 'bs_post_contracts.project_structure_id');
        $query->join('bs_project_main_information', 'bs_project_main_information.project_structure_id', '=', 'bs_project_structures.id');
        $query->leftJoin('bs_new_post_contract_form_information', 'bs_new_post_contract_form_information.project_structure_id', '=', 'bs_project_main_information.project_structure_id');
        $query->join('bs_claim_certificate_approval_logs', 'bs_claim_certificate_approval_logs.claim_certificate_id', '=', 'bs_claim_certificates.id');
        $query->join('bs_tender_settings', 'bs_tender_settings.project_structure_id', '=', 'bs_project_structures.id');
        $query->join('bs_companies', 'bs_companies.id', '=', 'bs_tender_settings.awarded_company_id');
        
        $query->where('bs_claim_certificates.status', '=', self::STATUS_TYPE_APPROVED);
        $query->whereNull('bs_project_structures.deleted_at');
        $query->whereNotNull('bs_project_main_information.eproject_origin_id');

        if( ! is_null($paid) ) $query->where('bs_claim_certificate_information.paid', '=', $paid);

        return $query;
    }

    public static function getContractorClaimCertificateQuery(User $user, $status, $withFilter = false, $listOfProjects = [])
    {

        $projectRepository = \App::make('PCK\Projects\ProjectRepository');

        $queryResults = array_filter($projectRepository->createProjectFilteringQuery($user)->get(), function($item) use ($status) {
            return $item->status_id === $status;
        });

        $projectIds = array_column($queryResults, 'project_id');

        if($withFilter)
        {
            $projectIds = array_intersect($projectIds, $listOfProjects);
        }

        $query = self::getClaimCertificateQuery();
        return $query->whereIn('bs_project_main_information.eproject_origin_id', $projectIds);
    }

    public function updateClaimCertificate(User $user)
    {
        $client = new Client(array(
            'verify'   => getenv('GUZZLE_SSL_VERIFICATION') ? true : false,
            'base_uri' => \Config::get('buildspace.BUILDSPACE_URL')
        ));

        try
        {
            $response = $client->post('eproject_api/updateClaimCertificate', array(
                'form_params' => array(
                    'ccid' => $this->id,
                    'uid'  => $user->id
                )
            ));

            $response = json_decode($response->getBody());

            if($response->success)
            {
                \Log::info("Claim certificate successfully updated. [id : {$this->id}]");
            }
        }
        catch(\Exception $e)
        {
           \Log::info("Update claim certificate fails. [id: {$this->id}] => {$e->getMessage()}");
        }
    }
}