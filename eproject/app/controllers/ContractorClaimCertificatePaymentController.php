<?php

use PCK\Projects\ProjectRepository;
use PCK\Users\User;
use PCK\Projects\Project;
use PCK\Buildspace\ClaimCertificate;
use PCK\Subsidiaries\SubsidiaryRepository;
use PCK\ClaimCertificate\ClaimCertificatePaymentRepository;

class ContractorClaimCertificatePaymentController extends \BaseController {

    private $projectRepo;
    private $subsidiaryRepository;
    private $claimCertificatePaymentRepository;

    public function __construct(ProjectRepository $projectRepo, SubsidiaryRepository $subsidiaryRepository, ClaimCertificatePaymentRepository $claimCertificatePaymentRepository)
    {
        $this->projectRepo = $projectRepo;
        $this->subsidiaryRepository = $subsidiaryRepository;
        $this->claimCertificatePaymentRepository = $claimCertificatePaymentRepository;
    }

    public function list($userId)
    {
        $user = User::find($userId);
        $subsidiaries = $this->subsidiaryRepository->getRelevantSubsidiaries($user)->lists('fullName', 'id');
        $limitToThisContractor = true;

        return View::make('finance/claim-certificate/index', compact('user', 'subsidiaries', 'limitToThisContractor'));
    }

    public function getClaimCertificateList()
    {
        $user = \Confide::user();
        $params = Input::all();
        $subsidiaryId = isset($params['subsidiaryId']) ? $params['subsidiaryId'] : null;
        $withFilter = $subsidiaryId ? true : false;
        $listOfProjectIds = $subsidiaryId ? $this->claimCertificatePaymentRepository->getListOfProjectIds($subsidiaryId) : [];

        $query = ClaimCertificate::getContractorClaimCertificateQuery($user, Project::STATUS_TYPE_POST_CONTRACT, $withFilter, $listOfProjectIds);

        if($subsidiaryId)
        {
            $query->whereIn('bs_project_main_information.eproject_origin_id', $listOfProjectIds);
        }

        $query->orderBy('bs_claim_certificates.updated_at', 'DESC');
        $queryResults = $query->get();

        $data = [];
        $count = 0;

        foreach($queryResults as $result)
        {
            $project = Project::find($result->eproject_origin_id);
            $claimCertificate = ClaimCertificate::find($result->claim_certificate_id);
            $paidAmount = $claimCertificate->paidAmount();
            
            array_push($data, [
                'indexNo'                          => ++$count,
                'id'                               => $claimCertificate->id,
                'reference'                        => $project->isMainProject() ? $project->reference : $project->parentProject->reference,
                'projectTitle'                     => $project->title,
                'subsidiary'                       => $project->subsidiary->fullName,
                'contractor'                       => $result->name,
                'letterOfAwardCode'                => $claimCertificate->postContractClaimRevision->postContract->projectStructure->letterOfAward->code,
                'subContractWork'                  => $project->isSubProject() ? $project->title : '-',
                'version'                          => $claimCertificate->postContractClaimRevision->version,
                'approvedAmount'                   => number_format($claimCertificate->claimCertificateInformation->approved_amount, 2),
                'currency'                         => $project->modified_currency_code,
                'approvedDate'                     => $claimCertificate->approvalLog ? \Carbon\Carbon::parse($claimCertificate->approvalLog->created_at)->format(\Config::get('dates.submitted_at')) : '-',
                'paidAmount'                       => number_format($paidAmount, 2),
                'route_print'                      => route('contractor.finance.claim-certificate.print', $claimCertificate->id),
                'balance'                          => number_format(( $claimCertificate->claimCertificateInformation->approved_amount - $paidAmount ), 2),
                'paid'                             => $claimCertificate->claimCertificateInformation->paid,
                'paymentStatus'                    => $claimCertificate->claimCertificateInformation->paid ? trans('finance.paid') : trans('finance.pending'),
            ]);
        }

        return Response::json($data);
    }
}

