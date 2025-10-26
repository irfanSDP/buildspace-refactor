<?php namespace PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationTenderAnalysis;

use Carbon\Carbon;
use Confide;
use DB;
use PCK\Projects\Project;
use PCK\Tenders\Tender;
use PCK\Tenders\CompanyTender;
use PCK\Companies\Company;
use PCK\Users\User;
use PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationTenderAnalysis\OpenTenderAwardRecommendationTenderSummary;
use PCK\Tenders\SubmitTenderRate;
use PCK\Tenders\Services\GetTenderAmountFromImportedZip;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationTenderAnalysis\OpenTenderAwardRecommendationBillDetail;
use PCK\OpenTenderAwardRecommendation\OpenTenderAwardRecommendationTenderAnalysisTableEditLog;

class OpenTenderAwardRecommendationTenderAnalysisRepository {

    public function getParticipantsDetails($project) {
        $rotParticipatingTendererIds = $this->getParticipatingTendererIdsInROT($project->tenders);
        $lotParticipatingTendererIds = $this->getParticipatingTendererIdsInLOT($project->tenders);
        $callingTenderSelectedTendererIds = $this->getParticipatingTendererIdsInCallingTender($project->tenders);
        // $declinedTendererIds = array_values(array_unique(array_merge($rotParticipatingTendererIds, $lotParticipatingTendererIds)));
        // $uniqueDeclinedTendererIds = array_values(array_diff($declinedTendererIds, $callingTenderSelectedTendererIds));

        $results = [];

        foreach($callingTenderSelectedTendererIds as $id) {
            array_push($results, [
                'participantName'   => Company::find($id)->name,
                'commitmentStatus'  => 'Participated'
            ]);
        }

        // hide the tenderer that is withdrawn instead of showing as declined

        // foreach($uniqueDeclinedTendererIds as $id) {
        //     array_push($results, [
        //         'participantName'   => Company::find($id)->name,
        //         'commitmentStatus'  => 'Declined'
        //     ]);
        // }

        return $results;
    }

    // get all tenderers in ROT except those with pending status, only available for original tender
    private function getParticipatingTendererIdsInROT($tenders) {
        $rotParticipatingTendererIds = [];

        foreach($tenders as $tender) {
            if(!$tender->isFirstTender()) continue;

            foreach($tender->recommendationOfTendererInformation->selectedContractors as $contractor) {
                if($contractor->pivot->status == ContractorCommitmentStatus::PENDING) continue;

                array_push($rotParticipatingTendererIds, $contractor->id);
            }
        }

        return $rotParticipatingTendererIds;
    }

    // get participating tenderers in LOT, duplicates removed
    private function getParticipatingTendererIdsInLOT($tenders) {
        $tendersInAscOrder = $tenders->reverse();
        $lotParticipatingTendererIds = [];

        foreach($tendersInAscOrder as $tender) {
            if($tender->isFirstTender()) {
                foreach($tender->listOfTendererInformation->selectedContractors as $contractor) {
                    if($contractor->pivot->status == ContractorCommitmentStatus::PENDING) continue;

                    array_push($lotParticipatingTendererIds, $contractor->id);
                }
            } else {
                $contractors = $tender->listOfTendererInformation->selectedContractors;
                for($i = 0; $i < $contractors->count(); $i++) {
                    if($contractors[$i]->pivot->status == ContractorCommitmentStatus::PENDING) continue;

                    $duplicateValueIndex = array_search($contractors[$i]->id, $lotParticipatingTendererIds);

                    if($duplicateValueIndex === false) {
                        array_push($lotParticipatingTendererIds, $contractors[$i]->id);
                    }
                }
            }
        }

        return $lotParticipatingTendererIds;
    }

    // get participating tenderers in Calling Tender, duplicates removed
    private function getParticipatingTendererIdsInCallingTender($tenders) {
        $callingTenderParticipatingTendererIds = [];

        foreach($tenders as $tender) {
            foreach($tender->callingTenderInformation->selectedContractors as $contractor) {
                if($contractor->pivot->status == ContractorCommitmentStatus::TENDER_WITHDRAW) continue;

                array_push($callingTenderParticipatingTendererIds, $contractor->id);
            }
        }

        return array_values(array_unique($callingTenderParticipatingTendererIds));
    }

    public function getOriginalTenderTendererDetails(Tender $tender)
    {
        $selectedTenderers      = $tender->selectedFinalContractors;
        $completionPeriodMetric = $tender->project->completion_period_metric;
        
        $records = [];

        $companyTenders = CompanyTender::select('id', 'company_id', 'tender_amount', 'completion_period', 'submitted')
            ->where('tender_id', $tender->id)
            ->get()
            ->keyBy('id');

        $bsProjectMainInformation = $tender->project->getBsProjectMainInformation();

        $hasTenderAlternative     = ($bsProjectMainInformation && $bsProjectMainInformation->projectStructure->tenderAlternatives->count());
        $awardedTenderAlternative = null;

        if($hasTenderAlternative)
        {
            $awardedTenderAlternative = $bsProjectMainInformation->projectStructure->getAwardedTenderAlternative();

            $companyTenderAlternatives = DB::table('company_tender_tender_alternatives')
            ->select('company_tender.tender_id', 'company_tender.company_id', 'company_tender_tender_alternatives.company_tender_id', 'company_tender_tender_alternatives.tender_alternative_id', 'company_tender_tender_alternatives.tender_amount', 'company_tender_tender_alternatives.completion_period')
            ->join('company_tender', 'company_tender_tender_alternatives.company_tender_id', '=', 'company_tender.id')
            ->where('company_tender.tender_id', $tender->id)
            ->where('company_tender_tender_alternatives.tender_alternative_id', ($awardedTenderAlternative) ? $awardedTenderAlternative->id : -1)
            ->orderBy('company_tender.tender_id', 'asc')
            ->orderBy('company_tender.company_id', 'asc')
            ->orderBy('company_tender_tender_alternatives.tender_alternative_id', 'asc')
            ->get();

            $companyTenderTenderAlternatives = [];
            foreach($companyTenderAlternatives as $companyTenderAlternative)
            {
                if(!array_key_exists($companyTenderAlternative->company_tender_id, $companyTenderTenderAlternatives))
                {
                    $companyTenderTenderAlternatives[$companyTenderAlternative->company_tender_id] = [];
                }

                $companyTenderTenderAlternatives[$companyTenderAlternative->company_tender_id] = $companyTenderAlternative;
            }
            
            unset($companyTenderAlternatives);

            foreach($companyTenders as $companyTenderId => $companyTender)
            {
                if(!array_key_exists($companyTender->company_id, $records))
                {
                    $records[$companyTender->company_id] = [];
                }

                if(array_key_exists($companyTenderId, $companyTenderTenderAlternatives))
                {
                    $completionPeriod = $tender->listOfTendererInformation->completion_period;

                    if( $tender->callingTenderInformation->allowContractorProposeOwnCompletionPeriod() )
                    {
                        $contractorCompletionPeriod = $companyTenderTenderAlternatives[$companyTenderId]->completion_period;
                        $completionPeriod = !empty($contractorCompletionPeriod) ? $contractorCompletionPeriod : $tender->listOfTendererInformation->completion_period;
                    }

                    $records[$companyTender->company_id] = [
                        'tenderer_name'            => $companyTender->company->name,
                        'tender_amount'            => $companyTenderTenderAlternatives[$companyTenderId]->tender_amount,
                        'completion_period'        => $completionPeriod,
                        'submitted'                => $companyTender->submitted,
                        'tender_alternative_id'    => $awardedTenderAlternative->id,
                        'tender_alternative_title' => $awardedTenderAlternative->title
                    ];
                }
            }

            unset($companyTenderTenderAlternatives);
        }
        else
        {
            foreach($companyTenders as $companyTenderId => $companyTender)
            {
                if(!array_key_exists($companyTender->company_id, $records))
                {
                    $records[$companyTender->company_id] = [];
                }

                $completionPeriod = $tender->listOfTendererInformation->completion_period;

                if( $tender->callingTenderInformation->allowContractorProposeOwnCompletionPeriod() )
                {
                    $completionPeriod = !empty($companyTender->completion_period) ? $companyTender->completion_period : $completionPeriod;
                }

                $records[$companyTender->company_id] = [
                    'tenderer_name'            => $companyTender->company->name,
                    'tender_amount'            => $companyTender->tender_amount,
                    'completion_period'        => $completionPeriod,
                    'submitted'                => $companyTender->submitted,
                    'tender_alternative_id'    => -1,
                    'tender_alternative_title' => ""
                ];
            }
        }

        $details = [];
        $allTenderSum = [];

        foreach($selectedTenderers as $tenderer)
        {
            $tenderAmount     = 0;
            $submitted        = false;
            $completionPeriod = null;

            if(array_key_exists($tenderer->id, $records) and !empty($records[$tenderer->id]))
            {
                $record = $records[$tenderer->id];

                if(($record['submitted']) && !empty($record['tender_amount']))
                {
                    array_push($allTenderSum, $record['tender_amount']);
                }

                $tenderAmount     = $record['tender_amount'];
                $submitted        = $record['submitted'];
                $completionPeriod = $record['completion_period'];
            }

            array_push($details, [
                'tendererName'     => $tenderer->name,
                'tenderSum'        => $tenderAmount,
                'submitted'        => $submitted,
                'completionPeriod' => $completionPeriod
            ]);
        }

        unset($records);

        usort($details, function($a, $b){
            return (!empty($a['tenderSum']) && !empty($b['tenderSum'])) ? $a['tenderSum'] <=> $b['tenderSum'] : 0;
        });

        return [
            'details'                => $details,
            'tenderAlternativeTitle' => ($hasTenderAlternative && $awardedTenderAlternative) ? $awardedTenderAlternative->title : "",
            'completionPeriod'       => $tender->listOfTendererInformation->completion_period,
            'completionPeriodMetric' => $completionPeriodMetric,
            'lowestTenderSum'        => empty($allTenderSum) ? 0 : min($allTenderSum)
        ];
    }

    public function getTenderResubmissionTendererDetails(Tender $tender, Tender $previousTender)
    {
        $currentTenderFinalTenderers = $tender->selectedFinalContractors;
        $completionPeriod = $tender->listOfTendererInformation->completion_period;
        $completionPeriodMetric = $tender->project->completion_period_metric;
        
        $records = [];

        $companyTenders = CompanyTender::select('id', 'company_id', 'tender_amount', 'completion_period', 'submitted')
            ->where('tender_id', $tender->id)
            ->get()
            ->keyBy('id');
        
        $previousCompanyTenders = CompanyTender::select('id', 'company_id', 'tender_amount', 'completion_period', 'submitted')
            ->where('tender_id', $previousTender->id)
            ->get()
            ->keyBy('company_id');

        $bsProjectMainInformation = $tender->project->getBsProjectMainInformation();

        $hasTenderAlternative     = ($bsProjectMainInformation && $bsProjectMainInformation->projectStructure->tenderAlternatives->count());
        $awardedTenderAlternative = null;

        if($hasTenderAlternative)
        {
            $awardedTenderAlternative = $bsProjectMainInformation->projectStructure->getAwardedTenderAlternative();

            $companyTenderAlternatives = DB::table('company_tender_tender_alternatives')
            ->select('company_tender.tender_id', 'company_tender.company_id', 'company_tender_tender_alternatives.company_tender_id', 'company_tender_tender_alternatives.tender_alternative_id', 'company_tender_tender_alternatives.tender_amount', 'company_tender_tender_alternatives.completion_period')
            ->join('company_tender', 'company_tender_tender_alternatives.company_tender_id', '=', 'company_tender.id')
            ->where('company_tender.tender_id', $tender->id)
            ->where('company_tender_tender_alternatives.tender_alternative_id', ($awardedTenderAlternative) ? $awardedTenderAlternative->id : -1)
            ->orderBy('company_tender.tender_id', 'asc')
            ->orderBy('company_tender.company_id', 'asc')
            ->orderBy('company_tender_tender_alternatives.tender_alternative_id', 'asc')
            ->get();

            $companyTenderTenderAlternatives = [];
            foreach($companyTenderAlternatives as $companyTenderAlternative)
            {
                if(!array_key_exists($companyTenderAlternative->company_id, $companyTenderTenderAlternatives))
                {
                    $companyTenderTenderAlternatives[$companyTenderAlternative->company_id] = [];
                }

                $companyTenderTenderAlternatives[$companyTenderAlternative->company_id] = $companyTenderAlternative;
            }
            
            unset($companyTenderAlternatives);

            $companyTenderPreviousAlternatives = DB::table('company_tender_tender_alternatives')
            ->select('company_tender.tender_id', 'company_tender.company_id', 'company_tender_tender_alternatives.company_tender_id', 'company_tender_tender_alternatives.tender_alternative_id', 'company_tender_tender_alternatives.tender_amount', 'company_tender_tender_alternatives.completion_period')
            ->join('company_tender', 'company_tender_tender_alternatives.company_tender_id', '=', 'company_tender.id')
            ->where('company_tender.tender_id', $previousTender->id)
            ->where('company_tender_tender_alternatives.tender_alternative_id', ($awardedTenderAlternative) ? $awardedTenderAlternative->id : -1)
            ->orderBy('company_tender.tender_id', 'asc')
            ->orderBy('company_tender.company_id', 'asc')
            ->orderBy('company_tender_tender_alternatives.tender_alternative_id', 'asc')
            ->get();

            $companyTenderPreviousTenderAlternatives = [];
            foreach($companyTenderPreviousAlternatives as $companyTenderAlternative)
            {
                if(!array_key_exists($companyTenderAlternative->company_id, $companyTenderPreviousTenderAlternatives))
                {
                    $companyTenderPreviousTenderAlternatives[$companyTenderAlternative->company_id] = [];
                }

                $companyTenderPreviousTenderAlternatives[$companyTenderAlternative->company_id] = $companyTenderAlternative;
            }
            
            unset($companyTenderPreviousAlternatives);

            foreach($companyTenders as $companyTenderId => $companyTender)
            {
                if(!array_key_exists($companyTender->company_id, $records))
                {
                    $records[$companyTender->company_id] = [];
                }

                if(array_key_exists($companyTender->company_id, $companyTenderTenderAlternatives))
                {
                    $completionPeriod = $tender->listOfTendererInformation->completion_period;

                    if( $tender->callingTenderInformation->allowContractorProposeOwnCompletionPeriod() )
                    {
                        $contractorCompletionPeriod = array_key_exists($companyTenderId, $companyTenderTenderAlternatives) ? $companyTenderTenderAlternatives[$companyTenderId]->completion_period : null;
                        $completionPeriod = !empty($contractorCompletionPeriod) ? $contractorCompletionPeriod : $tender->listOfTendererInformation->completion_period;
                    }

                    $records[$companyTender->company_id] = [
                        'tenderer_name'             => $companyTender->company->name,
                        'original_tender_amount'    => (array_key_exists($companyTender->company_id, $companyTenderPreviousTenderAlternatives)) ? $companyTenderPreviousTenderAlternatives[$companyTender->company_id]->tender_amount : 0,
                        'original_tender_submitted' => (array_key_exists($companyTender->company_id, $previousCompanyTenders->toArray())) ? $previousCompanyTenders[$companyTender->company_id]->submitted : false,
                        'revised_tender_amount'     => $companyTenderTenderAlternatives[$companyTender->company_id]->tender_amount,
                        'revised_tender_submitted'  => $companyTender->submitted,
                        'completion_period'         => $completionPeriod,
                        'tender_alternative_id'     => $awardedTenderAlternative->id,
                        'tender_alternative_title'  => $awardedTenderAlternative->title
                    ];
                }
            }

            unset($companyTenderTenderAlternatives, $companyTenderPreviousTenderAlternatives);
        }
        else
        {
            foreach($companyTenders as $companyTenderId => $companyTender)
            {
                if(!array_key_exists($companyTender->company_id, $records))
                {
                    $records[$companyTender->company_id] = [];
                }

                $completionPeriod = $tender->listOfTendererInformation->completion_period;

                if( $tender->callingTenderInformation->allowContractorProposeOwnCompletionPeriod() )
                {
                    $completionPeriod = !empty($companyTender->completion_period) ? $companyTender->completion_period : $completionPeriod;
                }

                $records[$companyTender->company_id] = [
                    'tenderer_name'             => $companyTender->company->name,
                    'original_tender_amount'    => (array_key_exists($companyTender->company_id, $previousCompanyTenders->toArray())) ? $previousCompanyTenders[$companyTender->company_id]->tender_amount : 0,
                    'original_tender_submitted' => (array_key_exists($companyTender->company_id, $previousCompanyTenders->toArray())) ? $previousCompanyTenders[$companyTender->company_id]->submitted : false,
                    'revised_tender_amount'     => $companyTender->tender_amount,
                    'revised_tender_submitted'  => $companyTender->submitted,
                    'completion_period'         => $completionPeriod,
                    'tender_alternative_id'     => -1,
                    'tender_alternative_title'  => ""
                ];
            }
        }

        $details = [];
        $allTenderSum = [];

        foreach($currentTenderFinalTenderers as $tenderer)
        {
            $tenderAmount     = 0;
            $submitted        = false;
            $completionPeriod = null;

            $originalTenderAmount    = 0;
            $originalTenderSubmitted = false;

            if(array_key_exists($tenderer->id, $records) and !empty($records[$tenderer->id]))
            {
                $record = $records[$tenderer->id];

                if(($record['revised_tender_submitted']) && !empty($record['revised_tender_amount']))
                {
                    array_push($allTenderSum, $record['revised_tender_amount']);
                }

                $tenderAmount     = $record['revised_tender_amount'];
                $submitted        = $record['revised_tender_submitted'];
                $completionPeriod = $record['completion_period'];

                $originalTenderAmount    = $record['original_tender_amount'];
                $originalTenderSubmitted = $record['original_tender_submitted'];
            }

            array_push($details, [
                'tendererName'               => $tenderer->name,
                'originalTenderSumSubmitted' => $originalTenderSubmitted,
                'originalTenderSum'          => $originalTenderAmount,
                'revisedTenderSubmitted'     => $submitted,
                'revisedTenderSum'           => $tenderAmount,
                'adjustment'                 => ($tenderAmount - $originalTenderAmount)
            ]);
        }

        unset($records);
        
        usort($details, function($a, $b){
            return (!empty($a['revisedTenderSum']) && !empty($b['revisedTenderSum'])) ? $a['revisedTenderSum'] <=> $b['revisedTenderSum'] : 0;
        });

        return [
            'details'                => $details,
            'tenderAlternativeTitle' => ($hasTenderAlternative && $awardedTenderAlternative) ? $awardedTenderAlternative->title : "",
            'completionPeriod'       => $tender->listOfTendererInformation->completion_period,
            'completionPeriodMetric' => $completionPeriodMetric,
            'lowestTenderSum'        => empty($allTenderSum) ? 0 : min($allTenderSum)
        ];
    }

    public function getTenderSummaryDetails(Tender $tender)
    {
        return OpenTenderAwardRecommendationTenderSummary::where('tender_id', $tender->id)->first();
    }

    public function updateConsultantEstimate(Tender $tender, $consultantEstimate)
    {
        $record = OpenTenderAwardRecommendationTenderSummary::where('tender_id', $tender->id)->first();

        if(is_null($record))
        {
            $record = new OpenTenderAwardRecommendationTenderSummary();
            $record->tender_id = $tender->id;
            $record->consultant_estimate = (float)$consultantEstimate;
            $record->updated_by = \Confide::user()->id;
            $record->save();

            $this->createLog($tender, $this->generateTableName($tender), 'Consultant\'s PTE');
        }

        if($record->consultant_estimate != (float)$consultantEstimate)
        {
            $record->consultant_estimate = (float)$consultantEstimate;
            $record->updated_by = \Confide::user()->id;
            $record->save();
    
            $this->createLog($tender, $this->generateTableName($tender), 'Consultant\'s PTE');
        }
    }

    public function updateBudget(Tender $tender, $budget)
    {
        $record = OpenTenderAwardRecommendationTenderSummary::where('tender_id', $tender->id)->first();

        if(is_null($record))
        {
            $record = new OpenTenderAwardRecommendationTenderSummary();
            $record->tender_id = $tender->id;
            $record->budget = (float)$budget;
            $record->updated_by = \Confide::user()->id;
            $record->save();

            $this->createLog($tender, $this->generateTableName($tender), 'Budget');
        }

        if($record->budget != (float)$budget)
        {
            $record->budget = (float)$budget;
            $record->updated_by = \Confide::user()->id;
            $record->save();

            $this->createLog($tender, $this->generateTableName($tender), 'Budget');
        }
    }

    private function generateTableName(Tender $tender) {
        $tableName = '';

        if($tender->isFirstTender()) {
            $tableName = 'Original Tender Summary';
        } else {
            $tableName = 'Tender Resubmission ' . $tender->count . ' Summary';
        }

        return $tableName;
    }

    private function getTotalAmountFromBillContents($contents) {
        $totalAmount = 0;

        foreach($contents->ELEMENTS->item as $item) {
            $totalAmount += (float)$item->total_amount;
        }

        return $totalAmount;
    }

    public function getBuildspaceBillDetails(Project $project, Tender $tender)
    {
        $currentlySelectedTenderer = Company::find($tender->currently_selected_tenderer_id);
        $fileName = SubmitTenderRate::ratesFileName;

        $path = null;

        if($currentlySelectedTenderer)
        {
            $path = SubmitTenderRate::getContractorRatesUploadPath($project, $tender, $currentlySelectedTenderer) . "/{$fileName}";
        }
        
        if(!file_exists($path))
        {
            return null;
        }

        $billData = [];
        $results = [];

        $currentlySelectedTenderer = Company::find($tender->currently_selected_tenderer_id);
        $service = new GetTenderAmountFromImportedZip($project, $tender, $currentlySelectedTenderer);

        $service->parseBillFiles();

        $bills = $service->getParsedBillFileContents();

        $consultantEstimateTotal = 0.0;
        $budgetTotal = 0.0;
        $billAmountTotal = 0.0;

        $bsProjectMainInformation = $tender->project->getBsProjectMainInformation();

        $hasTenderAlternative     = ($bsProjectMainInformation && $bsProjectMainInformation->projectStructure->tenderAlternatives->count());
        $tenderAlternativeBillIds = [];

        if($hasTenderAlternative)
        {
            $awardedTenderAlternative = $bsProjectMainInformation->projectStructure->getAwardedTenderAlternative();

            if($awardedTenderAlternative)
            {
                $results = \DB::connection('buildspace')->select(\DB::raw("SELECT tb.project_structure_id
                FROM bs_tender_alternatives_bills tb
                WHERE tb.tender_alternative_id = ".$awardedTenderAlternative->id));

                foreach($results as $result)
                {
                    $tenderAlternativeBillIds[] = (int)$result->project_structure_id;
                }
            }
        }

        foreach($bills as $bill)
        {
            if(empty($tenderAlternativeBillIds) or (!empty($tenderAlternativeBillIds) and in_array((int)$bill['contents']->attributes()->billId, $tenderAlternativeBillIds)))
            {
                $rec = OpenTenderAwardRecommendationBillDetail::where('tender_id', $tender->id)->where('buildspace_bill_id', $bill['contents']->attributes()->billId)->first();
                $rec->bill_amount = ((is_null($bill['contents']->ELEMENTS->item))) ? 0.00 : $this->getTotalAmountFromBillContents($bill['contents']);
                $rec->save();

                $res = \DB::connection('buildspace')
                        ->table('bs_project_structures')
                        ->where('id', $rec->buildspace_bill_id)
                        ->first();
                
                array_push($billData, [
                    'billId'            => $rec->buildspace_bill_id,
                    'description'       => $res->title,
                    'consultant_pte'    => $rec->consultant_pte,
                    'budget'            => $rec->budget,
                    'billAmount'        => $rec->bill_amount,
                ]);

                $consultantEstimateTotal += $rec->consultant_pte;
                $budgetTotal += $rec->budget;
                $billAmountTotal += $rec->bill_amount;
            }
        }

        $results['company_name'] = $currentlySelectedTenderer->name;
        $results['billData'] = $billData;
        $results['consultantEstimateTotal'] = $consultantEstimateTotal;
        $results['budgetTotal'] = $budgetTotal;
        $results['billAmountTotal'] = $billAmountTotal;

        return $results;
    }

    public function updatePteVsAwardSummary(Tender $tender, $inputs) {
        $valueChanged = false;

        foreach($inputs as $key => $value) {
            $record = OpenTenderAwardRecommendationBillDetail::where('tender_id', $tender->id)->where('buildspace_bill_id', $key)->first();
            
            if($record->consultant_pte != $value) {
                $record->consultant_pte = $value;
                $record->save();

                $valueChanged = true;
            }
        }

        if($valueChanged) $this->createLog($tender, 'PTE vs Award', 'Consultant\'s PTE');
    }

    public function updateBudgetVsAwardSummary(Tender $tender, $inputs) {
        $valueChanged = false;
        
        foreach($inputs as $key => $value) {
            $record = OpenTenderAwardRecommendationBillDetail::where('tender_id', $tender->id)->where('buildspace_bill_id', $key)->first();

            if($record->budget != $value) {
                $record->budget = $value;
                $record->save();

                $valueChanged = true;
            }
        }

        if($valueChanged) $this->createLog($tender, 'Budget vs Award', 'Budget');
    }

    private function createLog(Tender $tender, $tenderAnalysisTableName, $type) {
        $log = new OpenTenderAwardRecommendationTenderAnalysisTableEditLog();
        $log->tender_id = $tender->id;
        $log->table_name = $tenderAnalysisTableName;
        $log->type = $type;
        $log->user_id = \Confide::user()->id;
        $log->save();
    }

    public function getTenderAnalaysisEditLogs(Project $project) {
        $allTenders = $project->tenders;
        $allTenderIds = [];
        $formattedLogs = [];

        foreach($allTenders as $t) {
            array_push($allTenderIds, $t->id);
        }

        $tenderAnalysisEditLogs = OpenTenderAwardRecommendationTenderAnalysisTableEditLog::whereIn('tender_id', $allTenderIds)->orderBy('updated_at', 'asc')->get();

        foreach($tenderAnalysisEditLogs as $log) {
            array_push($formattedLogs, $this->formatLog($log));
        }

        return $formattedLogs;
    }

    private function formatLog($log) {
        $user = User::find($log->user_id);
        $tableName = $log->table_name;
        $type = $log->type;

        return [
            'user'      => $user->name,
            'tableName' => $tableName,
            'type'      => $type,
            'updatedAt' => $log->updated_at,
        ];
    }
}

