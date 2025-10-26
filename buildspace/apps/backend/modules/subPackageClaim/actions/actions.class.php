<?php

class subPackageClaimActions extends sfActions
{
    public function executeGetSubPackages(sfWebRequest $request)
    {
       $this->forward404Unless(
            $request->isXmlHttpRequest() and
            $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid'))
        );

        $subProjects = ProjectStructureTable::getSubProjects($project);

        $form = new BaseForm();

        $data = [];

        $subProjectLatestApprovedClaimRevisions = SubProjectLatestApprovedClaimRevisionTable::getLatestApprovedSubProjectClaimRevision($project->PostContract->getCurrentSelectedClaimRevision());

        $profitAndAttendancePercentages = NominatedSubContractorInformationTable::getProfitAndAttendancePercentages($project);

        foreach($subProjects as $subProject)
        {
            if(!array_key_exists($subProject->id, $subProjectLatestApprovedClaimRevisions)) continue;

            $latestClaimRevision = $subProjectLatestApprovedClaimRevisions[$subProject->id];

            $claimCertificate = $latestClaimRevision->ClaimCertificate;

            if(!$claimCertificate->exists()) continue;

            $profitAndAttendancePercent = $profitAndAttendancePercentages[$subProject->id] ?? 0;

            $multiplier = Utilities::divide($profitAndAttendancePercent, 100);

            $data[] = [
                 'id'                                 => $subProject->id,
                 'description'                        => $subProject->title,
                 'claim_no'                           => $latestClaimRevision->version,
                 'contract_amount'                    => $subProject->PostContract->getContractSum(),
                 'vo_amount'                          => $subProject->NewPostContractFormInformation->getVoOverallTotal($latestClaimRevision),
                 'accumulative_work_done'             => $workDone = $latestClaimRevision->getWorkDone(),
                 'accumulative_vo_work_done'          => $voWorkDone = $subProject->NewPostContractFormInformation->getVOWorkDoneAmount($latestClaimRevision),
                 'amount_certified'                   => $claimCertificate->amount_certified,
                 'profit_and_attendance_percent'      => $profitAndAttendancePercent,
                 'profit_and_attendance_work_done'    => $profitAndAttendanceWorkDone = $workDone * $multiplier,
                 'profit_and_attendance_vo_work_done' => $profitAndAttendanceVariationOrderWorkDone = $voWorkDone * $multiplier,
                 'profit_and_attendance_total'        => $profitAndAttendanceWorkDone + $profitAndAttendanceVariationOrderWorkDone,
                 '_csrf_token'                        => $form->getCSRFToken(),
            ];
        }

        $data[] = [
            'id'                                 => -1,
            'description'                        => '',
            'claim_no'                           => 0,
            'contract_amount'                    => 0, 
            'vo_amount'                          => 0,
            'accumulative_work_done'             => 0,
            'accumulative_vo_work_done'          => 0,
            'amount_certified'                   => 0,
            'profit_and_attendance_percent'      => 0,
            'profit_and_attendance_work_done'    => 0,
            'profit_and_attendance_vo_work_done' => 0,
            'profit_and_attendance_total'        => 0,
            '_csrf_token'                        => $form->getCSRFToken(),
       ];

       return $this->renderJson(array(
           'identifier' => 'id',
           'items'      => $data
       ));
    }

    public function executeUpdateProfitAndAttendancePercentage(sfWebRequest $request)
    {
        $this->forward404Unless(
             $request->isXmlHttpRequest() and
             $project = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('pid')) and
             $subProject = Doctrine_Core::getTable('ProjectStructure')->find($request->getParameter('sid'))
         );

        $errorMsg = null;
        $rowData = [];

        try{
            $success = true;

            $record = Doctrine_Query::create()
                ->from('NominatedSubContractorInformation i')
                ->where('i.project_structure_id = ?',$project->id)
                ->andWhere('i.sub_project_id = ?',$subProject->id)
                ->fetchOne();

            if(!$record)
            {
                $record = new NominatedSubContractorInformation();
                $record->project_structure_id = $project->id;
                $record->sub_project_id = $subProject->id;
            }

            $fieldValue = $request->getParameter('val');

            $fieldValue = is_numeric($fieldValue) ? $fieldValue : 0;

            $record->profit_and_attendance_percentage = number_format($fieldValue, 2, '.', '');

            $record->save();

            $subProjectLatestApprovedClaimRevisions = SubProjectLatestApprovedClaimRevisionTable::getLatestApprovedSubProjectClaimRevision($project->PostContract->getCurrentSelectedClaimRevision());

            $workDone   = 0;
            $voWorkDone = 0;

            if(array_key_exists($subProject->id, $subProjectLatestApprovedClaimRevisions))
            {
                $latestClaimRevision = $subProjectLatestApprovedClaimRevisions[$subProject->id];

                $claimCertificate = $latestClaimRevision->ClaimCertificate;

                if($claimCertificate->exists())
                {
                    $workDone   = $latestClaimRevision->getWorkDone();
                    $voWorkDone = $subProject->NewPostContractFormInformation->getVOWorkDoneAmount($latestClaimRevision);
                }
            }

            $profitAndAttendancePercent = $record->profit_and_attendance_percentage;

            $multiplier = Utilities::divide($profitAndAttendancePercent, 100);

            $rowData = [
                'profit_and_attendance_percent'      => $profitAndAttendancePercent,
                'profit_and_attendance_work_done'    => $profitAndAttendanceWorkDone = $workDone * $multiplier,
                'profit_and_attendance_vo_work_done' => $profitAndAttendanceVariationOrderWorkDone = $voWorkDone * $multiplier,
                'profit_and_attendance_total'        => $profitAndAttendanceWorkDone + $profitAndAttendanceVariationOrderWorkDone,
            ];
        }
        catch(Exception $e)
        {
            $errorMsg = $e->getMessage();
            $success  = false;
        }

        return $this->renderJson(array( 'success' => $success, 'errorMsg' => $errorMsg, 'data' => $rowData ));
    }
}
