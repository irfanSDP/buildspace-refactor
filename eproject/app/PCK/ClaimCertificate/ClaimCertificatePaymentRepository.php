<?php namespace PCK\ClaimCertificate;

use PCK\Subsidiaries\Subsidiary;
use PCK\Buildspace\Project as BsProjectStructure;
use PCK\Buildspace\ProjectCodeSetting;

Class ClaimCertificatePaymentRepository
{
    public function getProjectCodeSettingsPhaseSubsidiaryRecord($subsidiaryId)
    {
        $pcsQuery = \DB::connection('buildspace')->table('bs_project_code_settings AS pcs');
        $pcsQuery->join('bs_project_structures AS ps', 'ps.id', '=', 'pcs.project_structure_id');
        $pcsQuery->where('pcs.eproject_subsidiary_id', $subsidiaryId);
        $pcsQuery->where('pcs.type', ProjectCodeSetting::TYPE_PHASE_SUBSIDIARY);
        $pcsQuery->whereNull('pcs.deleted_at');
        $pcsQuery->whereNull('ps.deleted_at');
        $pcsQuery->orderBy('pcs.id', 'ASC');

        return $pcsQuery->get();
    }

    public function getListOfProjectIds($subsidiaryId)
    {
        $selectedSubsidiary = Subsidiary::find($subsidiaryId);
        $listOfProjectIds = [];

        $projectCodeSettingPhaseSubsidiary = $this->getProjectCodeSettingsPhaseSubsidiaryRecord($subsidiaryId);
        $hasRecordsInProjectCodeSettings = count($projectCodeSettingPhaseSubsidiary) > 0;

        if($hasRecordsInProjectCodeSettings)
        {
            $projectStructureIds = array_column($projectCodeSettingPhaseSubsidiary, 'project_structure_id');

            foreach($projectStructureIds as $projectStructureId)
            {
                array_push($listOfProjectIds, BsProjectStructure::find($projectStructureId)->mainInformation->eproject_origin_id);
            }

            foreach($selectedSubsidiary->getSubsidiaryChildrenIdRecursively() as $subId)
            {
                $projectCodeSettingsRecords = $this->getProjectCodeSettingsPhaseSubsidiaryRecord($subId);
                
                foreach($projectCodeSettingsRecords as $record)
                {
                    $projectId = BsProjectStructure::find($record->project_structure_id)->mainInformation->eproject_origin_id;

                    if(array_search($projectId, $listOfProjectIds) !== false) continue;

                    array_push($listOfProjectIds, $projectId);
                }
            }
        }
        else
        {
            $listOfSubsidiaryIds = $selectedSubsidiary->getSubsidiaryChildrenIdRecursively();

            foreach($listOfSubsidiaryIds as $subId)
            {
                $subsidiary = Subsidiary::find($subId);

                foreach($subsidiary->projects as $project)
                {
                    array_push($listOfProjectIds, $project->id);
                }
            }
        }

        return $listOfProjectIds;
    }
}

