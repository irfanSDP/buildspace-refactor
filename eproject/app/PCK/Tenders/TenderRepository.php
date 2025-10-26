<?php namespace PCK\Tenders;

use Carbon\Carbon;
use PCK\Base\Helpers;
use PCK\Buildspace\ProjectMainInformation;
use PCK\Companies\CompanyRepository;
use PCK\Exceptions\ValidationException;
use PCK\Helpers\Files;
use PCK\Helpers\NumberHelper;
use PCK\Notifications\EmailNotifier;
use PCK\Projects\Project;
use PCK\Companies\Company;
use PCK\TechnicalEvaluationVerifierLogs\TechnicalEvaluationVerifierLog;
use PCK\Tenders\Services\GetTenderAmountFromImportedZip;
use PCK\Users\User;
use PCK\Users\UserRepository;
use PCK\Filters\TenderFilters;
use Illuminate\Events\Dispatcher;
use PCK\Base\BaseModuleRepository;
use PCK\OpenTenderVerifierLogs\OpenTenderVerifierLog;
use PCK\TenderFormVerifierLogs\TenderFormVerifierLog;
use PCK\TenderCallingTenderInformation\TenderCallingTenderInformation;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformation;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;
use PCK\TenderRecommendationOfTendererInformation\TenderRecommendationOfTendererInformationRepository;
use PCK\TenderListOfTendererInformation\TenderListOfTendererInformationRepository;
use PCK\TenderCallingTenderInformation\TenderCallingTenderInformationRepository;
use PCK\TendererTechnicalEvaluationInformation\TendererTechnicalEvaluationInformationRepository;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PCK\TenderFormVerifierLogs\FormLevelStatus;
use Illuminate\Support\Facades\DB;
use PCK\LetterOfAward\LetterOfAwardRepository;
use PCK\RequestForInformation\RequestForInformationRepository;
use PCK\RiskRegister\RiskRegisterRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PCK\Helpers\SpreadsheetHelper;
use PCK\Tenders\CompanyTender;
use PCK\Tenders\CompanyTenderTenderAlternative;
use PCK\Tenders\OpenTenderPageInformation;
use PCK\EBiddings\EBidding;
use PCK\FormOfTender\FormOfTenderRepository;

class TenderRepository extends BaseModuleRepository {

    private $tender;

    protected $events;

    private $userRepo;
    private $emailNotifier;
    private $companyRepository;
    private $tenderROTInfoRepo;
    private $tenderLOTInfoRepo;
    private $tenderCallingTenderRepo;
    private $tenderTechEvalRepo;
    private $awardRecommendationRepo;
    private $letterOfAwardRepo;
    private $requestForInformationRepo;
    private $riskRegisterRisksRepo;

    protected $formOfTenderRepository;

    public function __construct(Tender $tender, Dispatcher $events, UserRepository $userRepo, CompanyRepository $companyRepository, 
        EmailNotifier $emailNotifier, 
        TenderRecommendationOfTendererInformationRepository $tenderROTInfoRepo,
        TenderListOfTendererInformationRepository $tenderLOTInfoRepo,
        TenderCallingTenderInformationRepository $tenderCallingTenderRepo,
        TendererTechnicalEvaluationInformationRepository $tenderTechEvalRepo,
        OpenTenderAwardRecommendationRepository $awardRecommendationRepo,
        LetterOfAwardRepository $letterOfAwardRepo,
        RequestForInformationRepository $requestForInformationRepo,
        RiskRegisterRepository $riskRegisterRisksRepo,
        FormOfTenderRepository $formOfTenderRepository
    )
    {
        $this->tender                    = $tender;
        $this->events                    = $events;
        $this->userRepo                  = $userRepo;
        $this->emailNotifier             = $emailNotifier;
        $this->companyRepository         = $companyRepository;
        $this->tenderROTInfoRepo         = $tenderROTInfoRepo;
        $this->tenderLOTInfoRepo         = $tenderLOTInfoRepo;
        $this->tenderCallingTenderRepo   = $tenderCallingTenderRepo;
        $this->tenderTechEvalRepo        = $tenderTechEvalRepo;
        $this->awardRecommendationRepo   = $awardRecommendationRepo;
        $this->letterOfAwardRepo         = $letterOfAwardRepo;
        $this->requestForInformationRepo = $requestForInformationRepo;
        $this->riskRegisterRisksRepo     = $riskRegisterRisksRepo;
        $this->formOfTenderRepository    = $formOfTenderRepository;
    }

    public function all(Project $project, array $with = array())
    {
        $query = $this->tender->where('project_id', '=', $project->id)
            ->orderBy('id', 'DESC');

        if( $with )
        {
            $query->with($with);
        }

        return $query->get();
    }

    public function find(Project $project, $tenderId)
    {
        return $this->tender->with('recommendationOfTendererInformation', 'selectedFinalContractors', 'reTenderVerifierLogs')
            ->where('project_id', '=', $project->id)
            ->orderBy('id', 'DESC')
            ->findOrFail($tenderId);
    }

    public function updateTenderStartAndClosingDate(Tender $tender, TenderCallingTenderInformation $callingTenderInformation, array $roles = array())
    {
        $tender->tender_starting_date          = $callingTenderInformation->date_of_calling_tender;
        $tender->tender_closing_date           = $callingTenderInformation->date_of_closing_tender;
        $tender->technical_tender_closing_date = $callingTenderInformation->technical_tender_closing_date;

        $tender->save();

        if( $roles )
        {
            // will get the final selected contractor IDS and then search for it's admin user,
            // so that system can blast email notification to them
            $companyAdminUsers = $this->userRepo->getAdminUserByCompanyIds($tender->selectedFinalContractors->lists('id'));

            $this->sendContractorAdminUserEmailNotification($tender, $companyAdminUsers->toArray(), 'calling_tender_date_extended', 'projects.submitTender');

            $this->emailNotifier->sendCallingTenderDateExtendedNotificationsToAssignedCompanies($tender);

            $users = $this->userRepo->getProjectSelectedUsersByProjectAndRoles($tender->project, $roles);

            $this->sendVerifierSystemNotification($tender, $users->toArray(), 'calling_tender_date_extended', 'projects.tender.show', '#s3');
        }

        return $tender;
    }

    public function cloneSelectedFinalContractors(Tender $tender, TenderCallingTenderInformation $callingTenderInformation)
    {
        $contractorIds = array();
        $contractors   = $callingTenderInformation->selectedContractors;

        foreach($contractors as $contractor)
        {
            if( $contractor->pivot->status == ContractorCommitmentStatus::TENDER_WITHDRAW )
            {
                continue;
            }

            $contractorIds[] = $contractor->id;
        }

        $tender->selectedFinalContractors()->sync($contractorIds);
    }

    public function updatedFormTypeStatus(Tender $tender, $status)
    {
        $tender->current_form_type = $status;

        $tender->save();

        return $tender;
    }

    public function updateTechnicalEvaluationStatus(Tender $tender)
    {
        $tender->technical_evaluation_status = $this->determineTechnicalTenderEvaluationStatus($tender);

        if($tender->isDirty('technical_evaluation_status'))
        {
            \Log::info("Updating Technical Evaluation Status [Tender id: {$tender->id}]", [
                'old value' => $tender->getOriginal('technical_evaluation_status'),
                'new value' => $tender->technical_evaluation_status,
            ]);

            $tender->save();

            if($tender->technical_evaluation_status == Tender::TECHNICAL_EVALUATION_STATUS_ENDED)
            {
                $this->emailNotifier->sendTechnicalEvaluationPeriodEndedNotifications($tender);
            }

            return $tender;
        }
    }

    public function determineTechnicalTenderEvaluationStatus($tender)
    {
        if(!$tender->configuredToHaveTechnicalEvaluation()) return null;

        $status = Tender::TECHNICAL_EVALUATION_STATUS_NOT_STARTED;

        if($tender->technical_evaluation_verification_status == Tender::SUBMISSION)
        {
            $status = Tender::TECHNICAL_EVALUATION_STATUS_OPENED;
        }
        elseif($tender->technicalEvaluationEnded())
        {
            $status = Tender::TECHNICAL_EVALUATION_STATUS_ENDED;
        }
        elseif($tender->technicalEvaluationStarted())
        {
            $status = Tender::TECHNICAL_EVALUATION_STATUS_STARTED;
        }

        return $status;
    }

    /**
     * Creates a new Tender Revision.
     *
     * @param Tender $latestTender
     *
     * @return bool|Tender
     */
    public function createNewReTender(Tender $tender)
    {
        $user        = \Confide::user();
        $project     = $tender->project;
        $contractors = array();

        // will need to check the last tender before allowing user to submit tender revision
        if( $tender->retender_status )
        {
            return false;
        }

        // will update the old tender's status to retender_status true;
        $tender->retender_status = true;
        $tender->updated_by      = $user->id;
        $tender->save();

        // create new tender
        $newTender                                = new Tender();
        $newTender->count                         = $tender->count + 1;
        $newTender->created_by                    = $user->id;
        $newTender->updated_by                    = $user->id;
        $newTender->current_form_type             = Project::STATUS_TYPE_LIST_OF_TENDERER;
        $newTender->tender_starting_date          = is_null($tender->tender_starting_date) ? $newTender->freshTimestamp() : $tender->tender_starting_date;
        $newTender->tender_closing_date           = $tender->tender_closing_date;
        $newTender->technical_tender_closing_date = $tender->technical_tender_closing_date;

        $newTender = $project->tenders()->save($newTender);

        // will create new List of Tenderer's information
        $lot                                = new TenderListOfTendererInformation();
        $lot->date_of_calling_tender        = $newTender->tender_starting_date;
        $lot->date_of_closing_tender        = $newTender->tender_closing_date;
        $lot->technical_tender_closing_date = $newTender->technical_tender_closing_date;

        $lot->completion_period                              = $tender->listOfTendererInformation->completion_period;
        $lot->project_incentive_percentage                   = $tender->listOfTendererInformation->project_incentive_percentage;
        $lot->allow_contractor_propose_own_completion_period = $tender->listOfTendererInformation->allow_contractor_propose_own_completion_period;
        $lot->contract_limit_id                              = $tender->listOfTendererInformation->contract_limit_id;
        $lot->procurement_method_id                          = $tender->listOfTendererInformation->procurement_method_id;

        $lot->created_by = $user->id;
        $lot->updated_by = $user->id;

        $lot = $newTender->listOfTendererInformation()->save($lot);

        foreach($tender->selectedFinalContractors()->lists('company_id') as $companyId)
        {
            // will disallowed previous Tender selected Final Contractors not to login anymore
            $tender->selectedFinalContractors()->updateExistingPivot($companyId, array(
                'can_login' => false
            ));

            $contractors[ $companyId ] = array( 'added_by_gcd' => false );
        }

        // copy final selected contractors list into LOT's Contractor list
        $lot->selectedContractors()->sync($contractors);

        \Event::fire('system.updateProjectStatus', array( $tender->project, $newTender->current_form_type ));

        $roles = array( TenderFilters::getListOfTendererFormRole($project), $project->getCallingTenderRole() );

        // will send e-mail notification to editor of List of Tenderer and Calling Tender
        $this->sendEmailNotification($project, $newTender, $roles, 'open_tender_retender_success', 'projects.tender.show');

        $users = $this->userRepo->getProjectSelectedUsersByProjectAndRoles($project, $roles);

        $this->sendVerifierSystemNotification($newTender, $users->toArray(), 'open_tender_retender_success', 'projects.tender.show');

        return $newTender;
    }

    public function updateToOpenTenderStatus(Project $project, Tender $tender)
    {
        $latestTenderId = $project->latestTender->id;

        if( $tender->id !== $latestTenderId )
        {
            return false;
        }

        $status = Tender::OPEN_TENDER_STATUS_NOT_YET_OPEN;

        if( $tender->open_tender_status === $status )
        {
            $status = Tender::OPEN_TENDER_STATUS_OPENED;
        }

        $tender->open_tender_status = $status;

        $success = $tender->save();

        $project->syncBuildSpaceContractorRates();

        $role = array( TenderFilters::getListOfTendererFormRole($project) );

        $this->emailNotifier->sendCommercialOpeningSubmittedNotifications($tender);

        $users = $this->userRepo->getProjectSelectedUsersByProjectAndRoles($project, array( $role ));

        $this->sendVerifierSystemNotification($tender, $users->toArray(), 'tender_open_tender_opened', 'projects.openTender.show');

        return $success;
    }

    public function cancelAccessToSelectedContractors(Tender $latestTender)
    {
        foreach($latestTender->selectedFinalContractors()->lists('company_id') as $companyId)
        {
            // will disallowed previous Tender selected Final Contractors not to login anymore
            $latestTender->selectedFinalContractors()->updateExistingPivot($companyId, array(
                'can_login' => false
            ));
        }
    }

    public function setSelectedContractor(Tender $latestTender, array $inputs)
    {
        $companyId = $inputs['contractorId'];

        $company = Company::find($companyId);

        if(!$company)
        {
            return false;
        }

        $selectedCompanyTender = $latestTender->selectedFinalContractors()->where('company_id', '=', $company->id)->first();

        if(!$selectedCompanyTender){
            $latestTender->selectedFinalContractors()->sync([$company->id], false);//dont delete old entries
        }

        $latestTender->selectedFinalContractors()->updateExistingPivot($company->id, array(
            'selected_contractor' => true
        ));
    }

    public function getSelectedTenderer(Project $project)
    {
        return $project->latestTender->selectedFinalContractorsWithProjects;
    }

    public function updateSubmitTenderRatesInformation(Company $company, Tender $tender, array $inputs)
    {
        $company->tenders()->updateExistingPivot($tender->id, array(
            'rates'                                                => $inputs['rates'],
            'tender_amount'                                        => $inputs['tender_amount'],
            'original_tender_amount'                               => $inputs['tender_amount'],
            'supply_of_material_amount'                            => $inputs['supply_of_material_amount'],
            'other_bill_type_amount_except_prime_cost_provisional' => $inputs['other_bill_type_amount_except_prime_cost_provisional'],
            'discounted_percentage'                                => 0,
            'discounted_amount'                                    => 0,
            'contractor_adjustment_percentage'                     => 0,
            'contractor_adjustment_amount'                         => 0,
            'completion_period'                                    => 0,
            'submitted'                                            => true,
            'submitted_at'                                         => Carbon::now(),
        ));

        $companyTender = $company->tenders()->find($tender->id);

        if($companyTender)
        {
            $companyTender = CompanyTender::find($companyTender->id);
            $companyTender->tenderAlternatives()->delete();

            $tenderAlternatives = [];
            if(array_key_exists('tender_alternatives', $inputs) && !empty($inputs['tender_alternatives']))
            {
                foreach($inputs['tender_alternatives'] as $tenderAlternative)
                {
                    $tenderAlternatives[] = new CompanyTenderTenderAlternative([
                        'tender_alternative_id'                                => $tenderAlternative['id'],
                        'tender_amount'                                        => $tenderAlternative['tender_amount'],
                        'other_bill_type_amount_except_prime_cost_provisional' => $tenderAlternative['tender_amount_except_prime_cost_provisional'],
                        'supply_of_material_amount'                            => $tenderAlternative['tender_som_amount'],
                        'original_tender_amount'                               => $tenderAlternative['tender_amount']
                    ]);
                }
                
                if(!empty($tenderAlternatives))
                {
                    $companyTender->tenderAlternatives()->saveMany($tenderAlternatives);
                }
            }
        }
    }

    public function updateTenderRateInformation(Company $company, Tender $tender, array $inputs)
    {
        $pivot = $company->tenders()->where('tenders.id', '=', $tender->id)->first()->pivot;

        $companyTender = CompanyTender::find($pivot->id);

        $contractorAdjustmentPercentage = 0;
        $completionPeriod               = 0;
        $contractorAdjustmentAmount     = 0;

        $companyTenderTenderAlternativeContractorAdjustmentValues = [];

        if( $tender->callingTenderInformation->allowContractorProposeOwnCompletionPeriod() )
        {
            if(array_key_exists('completion_period', $inputs))
            {
                if(is_array($inputs['completion_period']))
                {
                    foreach($inputs['completion_period'] as $tenderAlternativeId => $value)
                    {
                        $companyTenderTenderAlternativeContractorAdjustmentValues[$tenderAlternativeId]['completion_period'] = (!empty($value)) ? trim($value) : 0;
                    }
                }
                else
                {
                    $completionPeriod = empty( $inputs['completion_period'] ) ? 0 : trim($inputs['completion_period']);
                }
            }

            if(array_key_exists('contractor_adjustment_percentage', $inputs))
            {
                if(is_array($inputs['contractor_adjustment_percentage']))
                {
                    foreach($inputs['contractor_adjustment_percentage'] as $tenderAlternativeId => $value)
                    {
                        $companyTenderTenderAlternativeContractorAdjustmentValues[$tenderAlternativeId]['contractor_adjustment_percentage'] = (!empty($value)) ? trim($value) : 0;
                    }
                }
                else
                {
                    $contractorAdjustmentPercentage = empty( $inputs['contractor_adjustment_percentage'] ) ? 0 : trim($inputs['contractor_adjustment_percentage']);
                }
            }

            if(array_key_exists('contractor_adjustment_amount', $inputs))
            {
                if(is_array($inputs['contractor_adjustment_amount']))
                {
                    foreach($inputs['contractor_adjustment_amount'] as $tenderAlternativeId => $value)
                    {
                        $companyTenderTenderAlternativeContractorAdjustmentValues[$tenderAlternativeId]['contractor_adjustment_amount'] = (!empty($value)) ? trim($value) : 0;
                    }
                }
                else
                {
                    $contractorAdjustmentAmount = empty( $inputs['contractor_adjustment_amount'] ) ? 0 : trim($inputs['contractor_adjustment_amount']);
                }
            }
        }

        // calculate by given discount percentage
        $finalTenderAmountCalculated    = false;
        
        $discountAmount                 = 0;
        $discountedPercentage           = 0;

        $companyTenderTenderAlternativeValues = [];

        $companyTenderTenderAlternatives = [];
        foreach($companyTender->tenderAlternatives as $companyTenderTenderAlternative)
        {
            $companyTenderTenderAlternatives[$companyTenderTenderAlternative->tender_alternative_id] = $companyTenderTenderAlternative;
        }

        if( isset( $inputs['discounted_percentage'] ) and ! empty( $inputs['discounted_percentage'] ) )
        {
            if(is_array($inputs['discounted_percentage']))
            {
                foreach($inputs['discounted_percentage'] as $tenderAlternativeId => $value)
                {
                    $finalTenderAmountCalculated = false;
                    $tenderAmount         = 0;
                    $discountedPercentage = 0;
                    $discountedAmount     =0;
                    if(!empty($value))
                    {
                        $finalTenderAmountCalculated = true;
                        $tenderAmount = (array_key_exists($tenderAlternativeId, $companyTenderTenderAlternatives)) ? $companyTenderTenderAlternatives[$tenderAlternativeId]->original_tender_amount : 0;
                        $discountedPercentage = !empty(trim($value)) ? trim($value) : 0;
                        $percentage           = (float)$discountedPercentage / 100;
                        $discountedAmount     = $tenderAmount * $percentage;
                    }


                    $companyTenderTenderAlternativeValues[$tenderAlternativeId] = [
                        'final_tender_amount_calculated' => $finalTenderAmountCalculated,
                        'tender_amount'                  => $tenderAmount,
                        'discounted_percentage'          => $discountedPercentage,
                        'discounted_amount'              => $discountedAmount
                    ];
                }
            }
            else
            {
                $finalTenderAmountCalculated = true;
                $tenderAmount                = $companyTender->original_tender_amount;
                $discountedPercentage        = !empty(trim($inputs['discounted_percentage'])) ? trim($inputs['discounted_percentage']) : 0;
                $percentage                  = (float)$discountedPercentage / 100;
                $discountAmount              = $tenderAmount * $percentage;
            }
        }

        // calculate by give discount amount if the discount percentage amount has not been calculated yet
        if(( isset( $inputs['discounted_amount'] ) and ! empty( $inputs['discounted_amount'] ) ) )
        {
            if(is_array($inputs['discounted_amount']))
            {
                foreach($inputs['discounted_amount'] as $tenderAlternativeId => $value)
                {
                    if(!array_key_exists($tenderAlternativeId, $companyTenderTenderAlternativeValues) or (array_key_exists($tenderAlternativeId, $companyTenderTenderAlternativeValues) and !$companyTenderTenderAlternativeValues[$tenderAlternativeId]['final_tender_amount_calculated']))
                    {
                        $tenderAmount = (array_key_exists($tenderAlternativeId, $companyTenderTenderAlternatives)) ? $companyTenderTenderAlternatives[$tenderAlternativeId]->original_tender_amount : 0;
                        $discountedAmount = !empty(trim($value)) ? trim($value) : 0;
                        $discountedPercentage = ($tenderAmount) ? ( $discountedAmount / $tenderAmount ) * 100 : 0;

                        $companyTenderTenderAlternativeValues[$tenderAlternativeId] = [
                            'tender_amount'                  => $tenderAmount,
                            'discounted_percentage'          => $discountedPercentage,
                            'discounted_amount'              => $discountedAmount
                        ];
                    }
                }
            }
            else
            {
                if(! $finalTenderAmountCalculated)
                {
                    $tenderAmount         = $companyTender->original_tender_amount;
                    $discountAmount       = !empty(trim($inputs['discounted_amount'])) ? (float)trim($inputs['discounted_amount']) : 0;
                    $discountedPercentage = ($tenderAmount) ? ( $discountAmount / $tenderAmount ) * 100 : 0;
                }
            }
        }

        $bsProjectMainInformation = $tender->project->getBsProjectMainInformation();

        $hasTenderAlternative = ($bsProjectMainInformation && $bsProjectMainInformation->projectStructure->tenderAlternatives->count());

        if($hasTenderAlternative)
        {
            foreach($bsProjectMainInformation->projectStructure->tenderAlternatives as $tenderAlternative)
            {
                $tenderAmount       = array_key_exists($tenderAlternative->id, $companyTenderTenderAlternativeValues) ? $companyTenderTenderAlternativeValues[$tenderAlternative->id]['tender_amount'] : null;
                $discountPercentage = array_key_exists($tenderAlternative->id, $companyTenderTenderAlternativeValues) ? $companyTenderTenderAlternativeValues[$tenderAlternative->id]['discounted_percentage'] : null;
                $discountAmount     = array_key_exists($tenderAlternative->id, $companyTenderTenderAlternativeValues) ? $companyTenderTenderAlternativeValues[$tenderAlternative->id]['discounted_amount'] : null;
                
                $completionPeriod               = (array_key_exists($tenderAlternative->id, $companyTenderTenderAlternativeContractorAdjustmentValues) && array_key_exists('completion_period', $companyTenderTenderAlternativeContractorAdjustmentValues[$tenderAlternative->id])) ? $companyTenderTenderAlternativeContractorAdjustmentValues[$tenderAlternative->id]['completion_period'] : 0;
                $contractorAdjustmentPercentage = (array_key_exists($tenderAlternative->id, $companyTenderTenderAlternativeContractorAdjustmentValues) && array_key_exists('contractor_adjustment_percentage', $companyTenderTenderAlternativeContractorAdjustmentValues[$tenderAlternative->id])) ? $companyTenderTenderAlternativeContractorAdjustmentValues[$tenderAlternative->id]['contractor_adjustment_percentage'] : 0;
                $contractorAdjustmentAmount     = (array_key_exists($tenderAlternative->id, $companyTenderTenderAlternativeContractorAdjustmentValues) && array_key_exists('contractor_adjustment_amount', $companyTenderTenderAlternativeContractorAdjustmentValues[$tenderAlternative->id])) ? $companyTenderTenderAlternativeContractorAdjustmentValues[$tenderAlternative->id]['contractor_adjustment_amount'] : 0;

                $discountedTenderAmount = null;
                if(!is_null($tenderAmount) && !is_null($discountAmount))
                {
                    $discountedTenderAmount = $tenderAmount - $discountAmount;
                }

                if(array_key_exists($tenderAlternative->id, $companyTenderTenderAlternatives))
                {
                    $companyTenderTenderAlternative = $companyTenderTenderAlternatives[$tenderAlternative->id];
                }

                $companyTenderTenderAlternative->tender_amount         = (!is_null($discountedTenderAmount)) ? $discountedTenderAmount : $companyTenderTenderAlternative->tender_amount;
                $companyTenderTenderAlternative->discounted_percentage = (!is_null($discountPercentage)) ? $discountPercentage : $companyTenderTenderAlternative->discounted_percentage;
                $companyTenderTenderAlternative->discounted_amount     = (!is_null($discountAmount)) ? $discountAmount : $companyTenderTenderAlternative->discounted_amount;

                $companyTenderTenderAlternative->completion_period                = $completionPeriod;
                $companyTenderTenderAlternative->contractor_adjustment_percentage = $contractorAdjustmentPercentage;
                $companyTenderTenderAlternative->contractor_adjustment_amount     = $contractorAdjustmentAmount;

                $companyTenderTenderAlternative->save();
            }
        }
        else
        {
            $tenderAmount           = $companyTender->original_tender_amount;
            $discountedTenderAmount = $tenderAmount - $discountAmount;

            $company->tenders()->updateExistingPivot($tender->id, array(
                'tender_amount'                    => $discountedTenderAmount,
                'discounted_percentage'            => $discountedPercentage,
                'discounted_amount'                => $discountAmount,
                'contractor_adjustment_percentage' => $contractorAdjustmentPercentage,
                'contractor_adjustment_amount'     => $contractorAdjustmentAmount,
                'completion_period'                => $completionPeriod,
            ));
        }
    }

    public function updateSubmitTenderRateAttachments(Tender $tender, array $inputs)
    {
        // we will be saving attachment as well if available
        $this->saveAttachments($tender->pivot, $inputs);
    }

    public function updateReTenderVerificationStatus(Tender $tender, array $inputs)
    {
        $logStatus = false;
        $user      = \Confide::user();

        $sentToVerify        = isset( $inputs['send_to_verify'] );
        $verificationReject  = isset( $inputs['verification_reject'] );
        $verificationConfirm = isset( $inputs['verification_confirm'] );

        if( $tender->stillInProgress() OR $sentToVerify )
        {
            $this->syncSelectedReTenderVerifiers($tender, $inputs);
        }

        $hasVerifiers = ! $tender->reTenderVerifiers->isEmpty();

        if( $sentToVerify )
        {
            $tender->retender_verification_status = Tender::NEED_VALIDATION;
            $tender->updated_by                   = $user->id;
            $tender->request_retender_at          = Carbon::now();
            $tender->request_retender_by          = $user->id;
            $tender->request_retender_remarks     = $inputs['request_retender_remarks'];

            $logStatus = Tender::NEED_VALIDATION;
        }

        if( $verificationReject )
        {
            $this->setRejectedStatusToAllReTenderVerifiers($tender);

            $tender->retender_verification_status = Tender::IN_PROGRESS;
            $tender->request_retender_at          = null;
            $tender->request_retender_by          = null;
            $tender->request_retender_remarks     = null;

            $logStatus = Tender::USER_VERIFICATION_REJECTED;
        }

        if( $verificationConfirm )
        {
            $this->setVerificationConfirmToCurrentReTenderVerifier($tender);

            // reload the relation, in order not keep cache copy
            $tender->load('reTenderVerifiers');

            // if there is no in progress reTenderVerifiers available then straight update the status to submission
            if( $tender->reTenderVerifiers->isEmpty() )
            {
                $tender->retender_verification_status = Tender::SUBMISSION;
            }

            $logStatus = Tender::USER_VERIFICATION_CONFIRMED;
        }

        if( ! $hasVerifiers && $sentToVerify )
        {
            $tender->retender_verification_status = Tender::SUBMISSION;

            $logStatus = Tender::USER_VERIFICATION_CONFIRMED;
        }

        $tender->save();

        // send requesting verification email
        if( $sentToVerify )
        {
            $this->sendRequestVerification($tender);
        }

        // send verifier's decision email
        if( $verificationReject OR $verificationConfirm )
        {
            $tender->load('updatedBy');

            $viewName = 'tender_retender_confirm';

            if( $verificationReject )
            {
                $viewName = 'tender_retender_reject';
            }
            else
            {
                $this->sendRequestVerification($tender);
            }

            $this->sendTenderNotification(
                $tender,
                array( $tender->updatedBy ),
                null,
                $viewName,
                'projects.openTender.reTender'
            );
        }

        if( $logStatus )
        {
            $log          = new TenderFormVerifierLog();
            $log->user_id = $user->id;
            $log->type    = $logStatus;

            $tender->reTenderVerifierLogs()->save($log);
        }

        return $tender;
    }

    public function syncSelectedReTenderVerifiers(Tender $tender, array $inputs)
    {
        $data = array();

        if( ! isset( $inputs['verifiers'] ) ) return;

        foreach($inputs['verifiers'] as $verifier)
        {
            if( $verifier <= 0 ) continue;

            $data[] = $verifier;
        }

        $tender->reTenderVerifiers()->sync($data);

        $tender->load('reTenderVerifiers');
    }

    public function syncSelectedOpenTenderVerifiers(Tender $tender, $inputs)
    {
        $data = array();

        if( array_key_exists('selected_users', $inputs) )
        {
            $data = $inputs['selected_users'];
        }

        $tender->openTenderVerifiers()->sync($data);
    }

    public function updateTenderOpenTenderStatus(Tender $tender, array $inputs)
    {
        $logStatus = null;
        $user      = \Confide::user();

        if( $tender->openTenderStillInProgress() )
        {
            $tender->updated_by = $user->id;
        }

        if( isset( $inputs['send_to_verify'] ) )
        {
            $tender->open_tender_verification_status = Tender::NEED_VALIDATION;
            $tender->updated_by                      = $user->id;

            $logStatus = Tender::NEED_VALIDATION;
        }

        if( isset( $inputs['verification_reject'] ) )
        {
            $tender->rejectOpenTenderVerification();
            $logStatus = Tender::USER_VERIFICATION_REJECTED;
        }

        if( isset( $inputs['verification_confirm'] ) )
        {
            $this->setVerificationConfirmToCurrentOpenTenderVerifier($tender);

            // reload the relation, in order not keep cache copy
            $tender->load('openTenderVerifiers');

            // if there is no in progress verifiers available then straight update the status to submission
            if( $tender->openTenderVerifiers->isEmpty() )
            {
                $tender->open_tender_verification_status = Tender::SUBMISSION;
            }

            $logStatus = Tender::USER_VERIFICATION_CONFIRMED;
        }

        $tender->save();

        // send verifier's decision email
        if( isset( $inputs['verification_reject'] ) OR isset( $inputs['verification_confirm'] ) )
        {
            $tender->load('updatedBy');

            $viewName = 'tender_open_tender_confirm';

            if( isset( $inputs['verification_reject'] ) )
            {
                $viewName = 'tender_open_tender_reject';
            }

            $this->sendTenderNotification(
                $tender,
                array( $tender->updatedBy ),
                null,
                $viewName,
                'projects.openTender.viewOTVerifierLogs'
            );
        }

        if( $logStatus )
        {
            $log          = new OpenTenderVerifierLog();
            $log->user_id = $user->id;
            $log->type    = $logStatus;

            $tender->openTenderVerifierLogs()->save($log);
        }

        return $tender;
    }

    public function getOpenTenderVerifierDetail(Tender $tender, $receiverId)
    {
        return $tender->openTenderVerifiers()
            ->wherePivot('user_id', '=', $receiverId)
            ->first();
    }

    private function setRejectedStatusToAllReTenderVerifiers(Tender $tender)
    {
        $statuses = array(
            Tender::USER_VERIFICATION_IN_PROGRESS,
            Tender::USER_VERIFICATION_CONFIRMED
        );

        \DB::table('tender_user_verifier_retender')
            ->where('tender_id', '=', $tender->id)
            ->whereIn('status', $statuses)
            ->update(array( 'status' => Tender::USER_VERIFICATION_REJECTED ));
    }

    private function setVerificationConfirmToCurrentReTenderVerifier(Tender $object)
    {
        $user = \Confide::user();

        $object->reTenderVerifiers()->updateExistingPivot($user->id, array(
            'status' => Tender::USER_VERIFICATION_CONFIRMED
        ));
    }

    private function setRejectedStatusToAllOpenTenderVerifiers(Tender $tender)
    {
        $statuses = array(
            Tender::USER_VERIFICATION_IN_PROGRESS,
            Tender::USER_VERIFICATION_CONFIRMED
        );

        \DB::table('tender_user_verifier_open_tender')
            ->where('tender_id', '=', $tender->id)
            ->whereIn('status', $statuses)
            ->update(array( 'status' => Tender::USER_VERIFICATION_REJECTED ));
    }

    private function setVerificationConfirmToCurrentOpenTenderVerifier(Tender $tender)
    {
        $user = \Confide::user();

        $tender->openTenderVerifiers()->updateExistingPivot($user->id, array(
            'status' => Tender::USER_VERIFICATION_CONFIRMED
        ));
    }

    // will send out the first verifier first, then will follow up with remaining ones one by one
    private function sendRequestVerification(Tender $tender)
    {
        $tender->load('latestReTenderVerifiers');

        $this->sendTenderNotification(
            $tender,
            $tender->latestReTenderVerifiers->toArray(),
            $tender->updated_by,
            'tender_retender',
            'projects.openTender.reTender'
        );
    }

    /**
     * Update the Submit Tender Rate's (company_tender table) remarks.
     *
     * @param $tenderId
     * @param $companyId
     * @param $remarks
     *
     * @return mixed
     */
    public function updateSubmitTenderRateRemarks(Tender $tender, Company $company, int $tenderAlternativeId=null, $remarks)
    {
        if($tenderAlternativeId && $tenderAlternativeId > 0)
        {
            $pivot = $company->tenders()->where('tenders.id', '=', $tender->id)->first()->pivot;
            $companyTender = CompanyTender::find($pivot->id);

            $companyTenderTenderAlternative = CompanyTenderTenderAlternative::where('company_tender_id', $companyTender->id)
            ->where('tender_alternative_id', $tenderAlternativeId)->first();

            if($companyTenderTenderAlternative)
            {
                $companyTenderTenderAlternative->remarks = trim($remarks);
                $companyTenderTenderAlternative->save();

                return true;
            }
        }
        else
        {
            return $company->tenders()->updateExistingPivot($tender->id, array(
                'remarks' => trim($remarks)
            ));
        }
    }

    /**
     * Updates the Submit Tender Rate's (company_tender table) earnest_money inclusion.
     *
     * @param $tenderId
     * @param $companyId
     * @param $earnestMoneyIncluded
     *
     * @return mixed
     */
    public function updateSubmitTenderRateEarnestMoney(Tender $tender, Company $company, int $tenderAlternativeId=null, $earnestMoneyIncluded)
    {
        if($tenderAlternativeId && $tenderAlternativeId > 0)
        {
            $pivot = $company->tenders()->where('tenders.id', '=', $tender->id)->first()->pivot;
            $companyTender = CompanyTender::find($pivot->id);

            $companyTenderTenderAlternative = CompanyTenderTenderAlternative::where('company_tender_id', $companyTender->id)
            ->where('tender_alternative_id', $tenderAlternativeId)->first();

            if($companyTenderTenderAlternative)
            {
                $companyTenderTenderAlternative->earnest_money = $earnestMoneyIncluded;
                $companyTenderTenderAlternative->save();

                return true;
            }
        }
        else
        {
            return $company->tenders()->updateExistingPivot($tender->id, [
                'earnest_money' => $earnestMoneyIncluded
            ]);
        }
    }

    /**
     * Updates the tender's validity period.
     *
     * @param $tenderId
     * @param $numberOfDays
     *
     * @return bool
     */
    public function updateTenderValidityPeriod($tenderId, $numberOfDays)
    {
        $tender                          = Tender::find($tenderId);
        $tender->validity_period_in_days = $numberOfDays;

        return $tender->save();
    }

    /**
     * Returns an Open Tender Verifier Log.
     *
     * @param $tenderId
     * @param $userId
     *
     * @return mixed
     */
    public function getOpenTenderVerifierLogByTenderAndVerifierId($tenderId, $userId)
    {
        return OpenTenderVerifierLog::where('user_id', '=', $userId)
            ->where('tender_id', '=', $tenderId)
            ->first();
    }

    /**
     * Sorts the Submitted Tender Rate Contractors based on the amount of the nth included Tender Alternative.
     * TenderAlternativeData must be attached prior.
     *
     * @param     $submittedTenderRateContractors
     * @param int $nthIncludedTenderAlternative
     *
     * @return mixed
     */
    public function sortSubmittedTenderRateContractorsByTenderAlternativeAmount($submittedTenderRateContractors, $nthIncludedTenderAlternative = 1)
    {
        $tenderAlternativeIndex = $nthIncludedTenderAlternative - 1;
        $submittedTenderRateContractors->sortBy(function($contractor) use ($tenderAlternativeIndex)
        {
            if( ! array_key_exists($tenderAlternativeIndex, $contractor->tenderAlternativeData) )
            {
                $tenderAlternativeIndex = 0;
            }

            if( ! array_key_exists($tenderAlternativeIndex, $contractor->tenderAlternativeData) ) return 0;

            return $contractor->tenderAlternativeData[ $tenderAlternativeIndex ][0]['amount'];
        });

        return $submittedTenderRateContractors;
    }

    /**
     * Records users as verifiers.
     *
     * @param Tender $tender
     * @param        $inputs
     */
    public function syncSelectedTechnicalEvaluationVerifiers(Tender $tender, $inputs)
    {
        $data = array();

        if( array_key_exists('selected_users', $inputs) ) $data = $inputs['selected_users'];

        $tender->technicalEvaluationVerifiers()->sync($data);
    }

    /**
     * Updates the user verification status and the tender's technical evaluation status.
     *
     * @param Tender $tender
     * @param array  $inputs
     *
     * @return Tender
     */
    public function updateTenderTechnicalEvaluationVerificationStatus(Tender $tender, array $inputs)
    {
        $logStatus = null;
        $user      = \Confide::user();

        if( $tender->technicalEvaluationStillInProgress() )
        {
            $tender->updated_by = $user->id;
        }

        if( isset( $inputs['send_to_verify'] ) )
        {
            $tender->technical_evaluation_verification_status = Tender::NEED_VALIDATION;
            $tender->updated_by                               = $user->id;

            $logStatus = Tender::NEED_VALIDATION;
        }

        if( isset( $inputs['verification_reject'] ) )
        {
            $tender->rejectTechnicalEvaluationVerification();
            $logStatus = Tender::USER_VERIFICATION_REJECTED;
        }

        if( isset( $inputs['verification_confirm'] ) )
        {
            $this->setVerificationConfirmToCurrentTechnicalEvaluationVerifier($tender);

            $tender->load('technicalEvaluationVerifiers');

            // if there is no in progress verifiers available then straight update the status to submission
            if( $tender->technicalEvaluationVerifiers->isEmpty() )
            {
                $tender->technical_evaluation_verification_status = Tender::SUBMISSION;

                \Event::fire('system.updateTechnicalEvaluationStatus', array($tender));
            }

            $logStatus = Tender::USER_VERIFICATION_CONFIRMED;
        }

        $tender->save();

        // send verifier's decision email
        if( isset( $inputs['verification_reject'] ) OR isset( $inputs['verification_confirm'] ) )
        {
            $tender->load('updatedBy');

            $confirmationResponse = true;

            if( isset( $inputs['verification_reject'] ) ) $confirmationResponse = false;

            $this->emailNotifier->sendNotificationGeneric(
                $tender->project,
                $tender,
                array( $tender->updatedBy ),
                'notifications.email.tender.technical_evaluation_verifier_confirmation_response',
                route('technicalEvaluation.results.verifiers.logs', array( $tender->project->id, $tender->id )),
                array(
                    'senderName'           => \Confide::user()->name,
                    'confirmationResponse' => $confirmationResponse,
                )
            );
        }

        if( $logStatus )
        {
            $log          = new TechnicalEvaluationVerifierLog();
            $log->user_id = $user->id;
            $log->type    = $logStatus;

            $tender->technicalEvaluationVerifierLogs()->save($log);
        }

        return $tender;
    }

    /**
     * Returns all technical evaluation verifier's details for a tender.
     *
     * @param Tender $tender
     * @param        $receiverId
     *
     * @return mixed
     */
    public function getTechnicalEvaluationVerifierDetail(Tender $tender, $receiverId)
    {
        return $tender->technicalEvaluationVerifiers()
            ->wherePivot('user_id', '=', $receiverId)
            ->first();
    }

    /**
     * Set all technical evaluation verifiers' status to Rejected.
     *
     * @param Tender $tender
     */
    private function setRejectedStatusToAllTechnicalEvaluationVerifiers(Tender $tender)
    {
        $statuses = array(
            Tender::USER_VERIFICATION_IN_PROGRESS,
            Tender::USER_VERIFICATION_CONFIRMED
        );

        \DB::table('tender_user_technical_evaluation_verifier')
            ->where('tender_id', '=', $tender->id)
            ->whereIn('status', $statuses)
            ->update(array( 'status' => Tender::USER_VERIFICATION_REJECTED ));
    }

    /**
     * The verifier approves the verification.
     *
     * @param Tender $tender
     */
    private function setVerificationConfirmToCurrentTechnicalEvaluationVerifier(Tender $tender)
    {
        $user = \Confide::user();

        $tender->technicalEvaluationVerifiers()->updateExistingPivot($user->id, array(
            'status' => Tender::USER_VERIFICATION_CONFIRMED
        ));
    }

    /**
     * Reassigns the current Open Tender Verifiers
     * unless the verification is already over.
     *
     * @param Tender $tender
     */
    public function reassignOTVerifiers(Tender $tender)
    {
        // Do not reassign if status is already 'submission'.
        if( $tender->open_tender_verification_status == Tender::SUBMISSION ) return;

        $user = \Confide::user();
        $this->syncSelectedOpenTenderVerifiers($tender, array());
        $tender->open_tender_verification_status = Tender::IN_PROGRESS;
        $tender->updated_by                      = $user->id;

        $tender->save();

        $log          = new OpenTenderVerifierLog();
        $log->user_id = $user->id;
        $log->type    = Tender::REASSIGNED;

        $tender->openTenderVerifierLogs()->save($log);
    }

    /**
     * Reassigns the current Technical Evaluation Verifiers
     * unless the verification is already over.
     *
     * @param Tender $tender
     */
    public function reassignTechnicalEvaluationVerifiers(Tender $tender)
    {
        // Do not reassign if status is already 'submission'.
        if( $tender->technical_evaluation_verification_status == Tender::SUBMISSION ) return;

        $user = \Confide::user();
        $this->syncSelectedTechnicalEvaluationVerifiers($tender, array());
        $tender->technical_evaluation_verification_status = Tender::IN_PROGRESS;
        $tender->updated_by                               = $user->id;

        $tender->save();

        $log          = new TechnicalEvaluationVerifierLog();
        $log->user_id = $user->id;
        $log->type    = Tender::REASSIGNED;

        $tender->technicalEvaluationVerifierLogs()->save($log);
    }

    public function getFinalSelectedTenderer($project)
    {
        return $project->latestTender->selectedFinalContractors()->wherePivot("selected_contractor", '=', true)->first();
    }

    public function getTenderRateAmount($tenderAmount)
    {
        // 'tender_amount' is a number stored in a text field
        if (! NumberHelper::isNumber($tenderAmount, true)) { // Check if valid number
            return 0;
        }

        return NumberHelper::convertToFloat($tenderAmount);
    }

    // Selected Submitted Tender Rate (Single contractor - selected via radio button)
    public function getSelectedSubmittedTenderRate($project)
    {
        $result = ['success' => false, 'data' => null];

        $latestTender = $project->latestTender;
        if (! $latestTender) {
            $result['message'] = trans('errors.recordNotFound');
            return $result;
        }
        $selectedSubmittedTenderRateContractor = $latestTender->submittedTenderRateContractors()->where('company_id', $latestTender->currently_selected_tenderer_id)->first();
        if (! $selectedSubmittedTenderRateContractor) {
            $result['message'] = trans('eBidding.noSelectedTenderOption');
            return $result;
        }
        if (! $selectedSubmittedTenderRateContractor->pivot['submitted']) {
            $result['message'] = trans('tenders.notSubmitted');
            return $result;
        }
        $tenderAmount = $this->getTenderRateAmount($selectedSubmittedTenderRateContractor->pivot->tender_amount);
        if (empty($tenderAmount)) {
            $result['message'] = trans('eBidding.noTenderAmount');
            return $result;
        }

        $selectedTenderRate = [];

        $generator = new \PCK\TenderAlternatives\TenderAlternativeGenerator($latestTender, $selectedSubmittedTenderRateContractor->pivot);
        $includedTenderAlternatives = $this->formOfTenderRepository->getIncludedTenderAlternativesByFormOfTenderId($latestTender->formOfTender->id);

        $tenderAlternativeData = $generator->generateAllAfterContractorInput($includedTenderAlternatives);

        foreach ($tenderAlternativeData as $k1 => $tenderAlternatives) {
            foreach ($tenderAlternatives as $k2 => $tenderAlternative) {
                if ($tenderAlternative['tender_alternative_is_awarded']) {
                    $selectedTenderRate['companyId'] = $selectedSubmittedTenderRateContractor->id;
                    $selectedTenderRate['companyName'] = $selectedSubmittedTenderRateContractor->name;
                    $selectedTenderRate['tenderAlternativeId'] = $tenderAlternative['tender_alternative_id'];
                    $selectedTenderRate['tenderAlternativeTitle'] = $tenderAlternative['tender_alternative_title'];
                    $selectedTenderRate['tenderAmount'] = $tenderAmount;
                    $selectedTenderRate['isSelected'] = true;
                }
            }
        }

        if (empty($selectedTenderRate)) {
            $result['message'] = trans('eBidding.noSelectedTenderAlternative');
            return $result;
        }

        $result['data'] = $selectedTenderRate;
        $result['success'] = true;
        return $result;
    }

    // All selected Submitted Tender Rates (all contractors - matching the option selected via radio button)
    public function getSelectedSubmittedTenderRates($project)
    {
        $result = ['success' => false, 'data' => null];

        $selectedTenderRate = $this->getSelectedSubmittedTenderRate($project);
        if (! $selectedTenderRate['success']) {
            $result['message'] = $selectedTenderRate['message'];
            return $result;
        }

        $selectedTenderAlternativeId = $selectedTenderRate['data']['tenderAlternativeId'];

        $result['data'] = [];

        $latestTender = $project->latestTender;

        $selectedSubmittedTenderRateContractors = $latestTender->submittedTenderRateContractors;

        foreach ($selectedSubmittedTenderRateContractors as $tenderer) {
            $generator = new \PCK\TenderAlternatives\TenderAlternativeGenerator($latestTender, $tenderer->pivot);

            $includedTenderAlternatives = $this->formOfTenderRepository->getIncludedTenderAlternativesByFormOfTenderId($latestTender->formOfTender->id);
            $tenderAlternativeData = $generator->generateAllAfterContractorInput($includedTenderAlternatives);

            foreach($tenderAlternativeData as $k1 => $tenderAlternatives) {
                foreach ($tenderAlternatives as $k2 => $tenderAlternative) {
                    $tenderAmount = $this->getTenderRateAmount($tenderAlternative['amount']);

                    if ($tenderAlternative['tender_alternative_id'] === $selectedTenderAlternativeId && $tenderAmount > 0) {
                        $result['data'][] = [
                            'companyId' => $tenderer->id,
                            'companyName' => $tenderer->name,
                            'tenderAlternativeId' => $tenderAlternative['tender_alternative_id'],
                            'tenderAlternativeTitle' => $tenderAlternative['tender_alternative_title'],
                            'tenderAmount' => $tenderAmount,
                        ];
                    }
                }
            }
        }

        $result['success'] = true;
        return $result;
    }

    public function saveRates(Tender $tender, UploadedFile $ratesFile, User $user, Company $tenderer, $input = array())
    {
        $project = $tender->project;

        if( $tender->hasClosed() )
        {
            throw new ValidationException(trans('tenders.tenderHasClosed') . ' (' . $tender->tender_closing_date);
        }

        if( ! Files::hasExtension(Files::EXTENSION_RATES, $ratesFile) )
        {
            throw new ValidationException(trans('files.extensionMismatchRates'));
        }

        ProjectMainInformation::validateRatesFile($project, $ratesFile);

        $destinationPath = SubmitTenderRate::getContractorRatesUploadPath($project, $tender, $tenderer);
        $rateFileName    = SubmitTenderRate::getDefaultRateFileName() . '.' . $ratesFile->getClientOriginalExtension();

        $movedFile = $ratesFile->move($destinationPath, $rateFileName);

        if( empty( $movedFile ) )
        {
            throw new ValidationException(trans('files.uploadFailedRates'));
        }

        $archiveFilename = 'TR-p' . $project->id . '-t' . $tender->id . '-c' . $tenderer->id . '-u' . $user->id;

        Helpers::archivedFile($movedFile, $project, $archiveFilename);

        $service = new GetTenderAmountFromImportedZip($project, $tender, $user->company);

        $service->parseFile();

        $tenderAlternatives = $service->getTenderAlternativesDetails();

        $input['tender_amount']                                        = $service->getTenderAmount();
        $input['supply_of_material_amount']                            = $service->getSupplyOfMaterialAmount();
        $input['other_bill_type_amount_except_prime_cost_provisional'] = $service->getTenderAmountWithoutPrimeCostAndProvisional();
        $input['rates']                                                = $rateFileName;
        
        if(!empty($tenderAlternatives))
        {
            $input['tender_amount']                                        = 0;
            $input['supply_of_material_amount']                            = 0;
            $input['other_bill_type_amount_except_prime_cost_provisional'] = 0;
            $input['tender_alternatives']                                  = $tenderAlternatives;
        }

        $this->updateSubmitTenderRatesInformation($tenderer, $tender, $input);
    }

    public function getPendingTenderProcessesByUser(User $user, $includeFutureTasks, Project $project = null)
    {
        $allTenderProcessesToBeVerified = [
            'recommendationOfTenderers'     => $this->tenderROTInfoRepo->getPendingRecOfTenderersByUser($user, $includeFutureTasks, $project),
            'listOfTenderers'               => $this->tenderLOTInfoRepo->getPendingLotOfTenderersByUser($user, $includeFutureTasks, $project),
            'callingTenders'                => $this->tenderCallingTenderRepo->getPendingCallingTendersByUser($user, $includeFutureTasks, $project),
            'openTenders'                   => $this->tender->getPendingOpenTendersByUser($user, $project),
            'technicalEvaluation'           => $this->tenderTechEvalRepo->getPendingTechnicalEvaluationsByUser($user, $project),
            'technicalAssessment'           => $this->tenderTechEvalRepo->getPendingTechnicalAssessment($user, $includeFutureTasks, $project),
            'openTenderAwardRecommendation' => $this->awardRecommendationRepo->getPendingAwardRecommendation($user, $includeFutureTasks, $project),
            'letterOfAward'                 => $this->letterOfAwardRepo->getPendingApprovalLetterOfAward($user, $includeFutureTasks, $project),
            'tenderResubmission'            => $this->getPendingApprovalTenderResubmission($user, $includeFutureTasks, $project),
            'requestForInformation'         => $this->requestForInformationRepo->getPendingApprovalRequestForInformation($user, $includeFutureTasks, $project),
            'riskRegisters'                 => $this->riskRegisterRisksRepo->getPendingApprovalRiskRegisters($user, $includeFutureTasks, $project),
            'openTenderFormInformation'     => OpenTenderPageInformation::getPendingOpenTenderInfoPage($user, $includeFutureTasks, $project),
            'eBidding'                      => EBidding::getPendingEBidding($user, $includeFutureTasks, $project),
        ];

        // put all end elements into array
        $sortedTenderProcessesToBeVerified = [];

        foreach($allTenderProcessesToBeVerified as $type => $tenderProcesses)
        {
            foreach($tenderProcesses as $tenderProcess)
            {
                array_push($sortedTenderProcessesToBeVerified, $tenderProcess);
            }
        }

        // sort tenders by days pending descendingly
        uasort($sortedTenderProcessesToBeVerified, function($element1, $element2)
        {
            return $element2['days_pending'] <=> $element1['days_pending'];
        });

        return $sortedTenderProcessesToBeVerified;
    }

    public function generateListOfTendererExcelSpreadsheetInfo(Project $project, Tender $tender)
    {
        $listOfTendererInformation = $tender->listOfTendererInformation;
        $selectedContractors = $listOfTendererInformation->selectedContractors;
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $rowIndex = 1;

        // document title
        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(1, 16, $rowIndex));
        $sheet->setCellValueByColumnAndRow(1, $rowIndex, trans('tenders.listOfTenderersInformation'));
        $sheet->getStyle($sheet->getCellByColumnAndRow(1, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');
        $sheet->getStyle($sheet->getCellByColumnAndRow(1, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));
        $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(1, $rowIndex, 16, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_ALL, Border::BORDER_THIN));

        ++$rowIndex;

        // date of calling tender and commercial tender closing date
        ++$rowIndex;
        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(2, 5, $rowIndex));
        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(7, 10, $rowIndex));
        $sheet->setCellValueByColumnAndRow(2, $rowIndex, trans('tenders.dateOfCallingTender'));
        $sheet->setCellValueByColumnAndRow(7, $rowIndex, trans('tenders.commercialTenderClosingDate'));
        $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_LEFT, Fill::FILL_SOLID, Color::COLOR_BLACK));
        $sheet->getStyle($sheet->getCellByColumnAndRow(7, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_LEFT, Fill::FILL_SOLID, Color::COLOR_BLACK));

        ++$rowIndex;
        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(2, 5, $rowIndex));
        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(7, 10, $rowIndex));
        $sheet->setCellValueByColumnAndRow(2, $rowIndex, $project->getProjectTimeZoneTime($listOfTendererInformation->date_of_calling_tender));
        $sheet->setCellValueByColumnAndRow(7, $rowIndex, $project->getProjectTimeZoneTime($listOfTendererInformation->date_of_closing_tender));
        $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(2, $rowIndex, 5, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));
        $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(7, $rowIndex, 10, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));

        ++$rowIndex;
        ++$rowIndex;

        // technical closing date
        ++$rowIndex;
        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(7, 10, $rowIndex));
        $sheet->setCellValueByColumnAndRow(7, $rowIndex, trans('tenders.technicalClosingDate'));
        $sheet->getStyle($sheet->getCellByColumnAndRow(7, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_LEFT, Fill::FILL_SOLID, Color::COLOR_BLACK));

        ++$rowIndex;
        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(7, 10, $rowIndex));
        $sheet->setCellValueByColumnAndRow(7, $rowIndex, $project->getProjectTimeZoneTime($listOfTendererInformation->technical_tender_closing_date));
        $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(7, $rowIndex, 10, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));
        
        ++$rowIndex;
        ++$rowIndex;

        // completion period and procurement method
        ++$rowIndex;
        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(2, 5, $rowIndex));
        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(7, 10, $rowIndex));
        $sheet->setCellValueByColumnAndRow(2, $rowIndex, trans('tenders.completionPeriod') . ' (' . $tender->project->completion_period_metric . ')');
        $sheet->setCellValueByColumnAndRow(7, $rowIndex, trans('tenders.procurementMethod'));
        $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_LEFT, Fill::FILL_SOLID, Color::COLOR_BLACK));
        $sheet->getStyle($sheet->getCellByColumnAndRow(7, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_LEFT, Fill::FILL_SOLID, Color::COLOR_BLACK));

        ++$rowIndex;
        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(2, 5, $rowIndex));
        $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(7, 10, $rowIndex));
        $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('left');
        $sheet->getStyle($sheet->getCellByColumnAndRow(7, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('left');
        $sheet->setCellValueByColumnAndRow(2, $rowIndex, $listOfTendererInformation->completion_period);
        $sheet->setCellValueByColumnAndRow(7, $rowIndex, $listOfTendererInformation->procurementMethod ? $listOfTendererInformation->procurementMethod->name : '');
        $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(2, $rowIndex, 5, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));
        $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(7, $rowIndex, 10, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));

        // if allow contractor to propose own completion period
        if($listOfTendererInformation->allow_contractor_propose_own_completion_period)
        {
            // line breaks
            ++$rowIndex;
            ++$rowIndex;

            ++$rowIndex;

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(2, 7, $rowIndex));
            $sheet->setCellValueByColumnAndRow(2, $rowIndex, trans('tenders.allowContractorToSubmitOwnCompletionPeriod'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_LEFT, Fill::FILL_SOLID, Color::COLOR_BLACK));

            $sheet->setCellValueByColumnAndRow(8, $rowIndex, 'YES');
            $sheet->getStyle($sheet->getCellByColumnAndRow(8, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');

            $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(2, $rowIndex, 8, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));
        }
        
        // if tender rates submission is disabled
        if($listOfTendererInformation->disable_tender_rates_submission)
        {
            // line breaks
            ++$rowIndex;
            ++$rowIndex;

            ++$rowIndex;

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(2, 7, $rowIndex));
            $sheet->setCellValueByColumnAndRow(2, $rowIndex, trans('tenders.disableTenderRatesSubmission'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_LEFT, Fill::FILL_SOLID, Color::COLOR_BLACK));

            $sheet->setCellValueByColumnAndRow(8, $rowIndex, 'YES');
            $sheet->getStyle($sheet->getCellByColumnAndRow(8, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');

            $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(2, $rowIndex, 8, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));
        }

        // if technical evaluation is checked
        if($listOfTendererInformation->technical_evaluation_required)
        {
            // line breaks
            ++$rowIndex;
            ++$rowIndex;

            ++$rowIndex;

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(2, 7, $rowIndex));
            $sheet->setCellValueByColumnAndRow(2, $rowIndex, trans('technicalEvaluation.technicalEvaluation'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_LEFT, Fill::FILL_SOLID, Color::COLOR_BLACK));
            
            $sheet->setCellValueByColumnAndRow(8, $rowIndex, 'YES');
            $sheet->getStyle($sheet->getCellByColumnAndRow(8, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');

            $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(2, $rowIndex, 8, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));

            // contract limit
            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(10, 11, $rowIndex));
            $sheet->setCellValueByColumnAndRow(10, $rowIndex, trans('technicalEvaluation.contractLimit'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(10, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_LEFT, Fill::FILL_SOLID, Color::COLOR_BLACK));

            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(12, 15, $rowIndex));
            $sheet->setCellValueByColumnAndRow(12, $rowIndex, $listOfTendererInformation->contractLimit ? $listOfTendererInformation->contractLimit->limit : '');
            $sheet->getStyle($sheet->getCellByColumnAndRow(12, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');

            $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(12, $rowIndex, 15, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));
        }

        ++$rowIndex;
        ++$rowIndex;

        // remarks
        if(!is_null($listOfTendererInformation->remarks) || $listOfTendererInformation->remarks == '')
        {
            ++$rowIndex;

            $startCol = 2;
            $startRow = $rowIndex;

            $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_LEFT, Fill::FILL_SOLID, Color::COLOR_BLACK));
            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(2, 15, $rowIndex));
            $sheet->setCellValueByColumnAndRow(2, $rowIndex, trans('general.remarks'));
            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(2, 15, ++$rowIndex));
            $sheet->setCellValueByColumnAndRow(2, $rowIndex, $listOfTendererInformation->remarks);

            $endCol = 15;
            $endRow = $rowIndex;

            $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow($startCol, $startRow, $endCol, $endRow))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));
        }

        ++$rowIndex;
        ++$rowIndex;

        /* verifier logs */
        if($listOfTendererInformation->verifierLogs->count() > 0)
        {
            ++$rowIndex;

            $startCol = 2;
            $startRow = $rowIndex;

            // title
            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(2, 15, $rowIndex));
            $sheet->setCellValueByColumnAndRow(2, $rowIndex, trans('tenders.verificationLogs'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_LEFT, Fill::FILL_SOLID, Color::COLOR_BLACK));

            // verifier logs
            foreach($listOfTendererInformation->verifierLogs as $verifierLog)
            {
                $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(2, 15, ++$rowIndex));
                $sheet->setCellValueByColumnAndRow(2, $rowIndex, $verifierLog->present()->log_text_format(false));
                $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));
            }

            $endCol = 15;
            $endRow = $rowIndex;

            $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow($startCol, $startRow, $endCol, $endRow))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));
        }

        ++$rowIndex;
        ++$rowIndex;

        // selected contractors table
        if($listOfTendererInformation->selectedContractors->count() > 0)
        {
            ++$rowIndex;

            $startCol = 2;
            $startRow = $rowIndex;

            // table title
            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(2, 15, $rowIndex));
            $sheet->setCellValueByColumnAndRow(2, $rowIndex, trans('tenders.selectedContractors'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_WHITE, Alignment::HORIZONTAL_LEFT, Fill::FILL_SOLID, Color::COLOR_BLACK));

            /*column headers*/
            // number
            $sheet->setCellValueByColumnAndRow(2, ++$rowIndex, trans('general.no'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');
            $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            // contractor
            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(3, 9, $rowIndex));
            $sheet->setCellValueByColumnAndRow(3, $rowIndex, trans('tenders.contractor'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(3, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));
            
            // commitment status
            $sheet->setCellValueByColumnAndRow(10, $rowIndex, trans('general.status'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(10, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');
            $sheet->getStyle($sheet->getCellByColumnAndRow(10, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));
            
            // remarks
            $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(11, 15, $rowIndex));
            $sheet->setCellValueByColumnAndRow(11, $rowIndex, trans('general.remarks'));
            $sheet->getStyle($sheet->getCellByColumnAndRow(11, $rowIndex)->getCoordinate())->applyFromArray(SpreadsheetHelper::getCellStyleArrayFormatter(true, Color::COLOR_BLACK, Alignment::HORIZONTAL_CENTER, Fill::FILL_SOLID, 'DCDCDC'));

            $count = 0;

            foreach($listOfTendererInformation->selectedContractors as $contractor)
            {
                ++$rowIndex;

                // number
                $sheet->setCellValueByColumnAndRow(2, $rowIndex, ++$count);
                $sheet->getStyle($sheet->getCellByColumnAndRow(2, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');

                // contractor
                $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(3, 9, $rowIndex));
                $sheet->setCellValueByColumnAndRow(3, $rowIndex, $contractor->name);
                $sheet->getStyle($sheet->getCellByColumnAndRow(3, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);

                // font red, striked
                if($contractor->pivot->deleted_at)
                {
                    $sheet->getStyle($sheet->getCellByColumnAndRow(3, $rowIndex)->getCoordinate())->getFont()->getColor()->setARGB(Color::COLOR_RED);
                    $sheet->getStyle($sheet->getCellByColumnAndRow(3, $rowIndex)->getCoordinate())->getFont()->setStrikethrough(true);
                }

                // font blue
                if($contractor->pivot->added_by_gcd)
                {
                    $sheet->getStyle($sheet->getCellByColumnAndRow(3, $rowIndex)->getCoordinate())->getFont()->getColor()->setARGB(Color::COLOR_BLUE);
                }

                // commitment status
                $sheet->getStyle($sheet->getCellByColumnAndRow(10, $rowIndex)->getCoordinate())->getAlignment()->setHorizontal('center');
                $sheet->setCellValueByColumnAndRow(10, $rowIndex, ContractorCommitmentStatus::getText($contractor->pivot->status));
                
                // remarks
                $sheet->mergeCells(SpreadsheetHelper::cellsToMergeByColsRow(11, 15, $rowIndex));
                $sheet->setCellValueByColumnAndRow(11, $rowIndex, $contractor->pivot->remarks);
                $sheet->getStyle($sheet->getCellByColumnAndRow(11, $rowIndex)->getCoordinate())->getAlignment()->setWrapText(true);
            }

            $endCol = 15;
            $endRow = $rowIndex;
            
            $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow($startCol, $startRow, $endCol, $endRow))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THIN));
        }

        ++$rowIndex;

        $sheet->getStyle(SpreadsheetHelper::getRangeByColumnAndRow(1, 1, 16, $rowIndex))->applyFromArray(SpreadsheetHelper::getBorderStyleArrayFormatter(SpreadsheetHelper::BORDER_OUTLINE, Border::BORDER_THICK));

        return $spreadsheet;
    }

    public function getPendingApprovalTenderResubmission(User $user, $includeFutureTasks, Project $project = null)
    {
        $pendingTenderResumissions = [];
        $proceed = false;

        if($project)
        {
            $proceed = ($includeFutureTasks) ? in_array($user->id, $project->latestTender->reTenderVerifiers->lists('id')) : ($project->latestTender->latestReTenderVerifiers->first() && ($user->id === $project->latestTender->latestReTenderVerifiers->first()->id));

            if($project->latestTender->isBeingValidated() && $proceed)
            {
                array_push($pendingTenderResumissions, [
                    'project_reference'        => $project->reference,
                    'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                    'project_id'               => $project->id,
                    'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                    'company_id'               => $project->business_unit_id,
                    'project_title'            => $project->title,
                    'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                    'tender_id'                => $project->latestTender->id,
                    'module'                   => $project->latestTender->getTenderResubmissionModuleName(),
                    'days_pending'             => Tender::getPendingTenderResubmissionDaysPending($project->latestTender, $user),
                    'route'                    => route('projects.openTender.reTender', ['projectId' => $project->id, 'tenderId' => $project->latestTender->id]),
                ]);
            }
        }
        else
        {
            $allTenderResubmissions = Tender::where('retender_verification_status', FormLevelStatus::NEED_VALIDATION)->get();

            foreach($allTenderResubmissions as $tenderResubmission)
            {
                if(is_null($tenderResubmission->project)) continue;

                $proceed = ($includeFutureTasks) ? in_array($user->id, $tenderResubmission->reTenderVerifiers->lists('id')) : ($tenderResubmission->latestReTenderVerifiers->first() && ($user->id === $tenderResubmission->latestReTenderVerifiers->first()->id));

                if($proceed)
                {
                    $project = $tenderResubmission->project;

                    array_push($pendingTenderResumissions, [
                        'project_reference'        => $project->reference,
                        'parent_project_reference' => $project->isSubProject() ? $project->parentProject->reference : null,
                        'project_id'               => $project->id,
                        'parent_project_id'        => $project->isSubProject() ? $project->parentProject->id : null,
                        'company_id'               => $project->business_unit_id,
                        'project_title'            => $project->title,
                        'parent_project_title'     => $project->isSubProject() ? $project->parentProject->title : null,
                        'tender_id'                => $tenderResubmission->id,
                        'module'                   => $tenderResubmission->getTenderResubmissionModuleName(),
                        'days_pending'             => Tender::getPendingTenderResubmissionDaysPending($tenderResubmission, $user),
                        'route'                    => route('projects.openTender.reTender', ['projectId' => $tenderResubmission->project->id, 'tenderId' => $tenderResubmission->id]),
                    ]);
                }
            }
        }

        return $pendingTenderResumissions;
    }
}