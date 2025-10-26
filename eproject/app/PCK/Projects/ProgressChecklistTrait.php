<?php namespace PCK\Projects;

use PCK\Buildspace\ProjectMainInformation;
use PCK\Buildspace\ProjectRevision;
use PCK\Buildspace\ProjectUserPermission as BsProjectUserPermission;
use PCK\Buildspace\Menu;
use PCK\ContractGroups\ContractGroup;
use PCK\ContractGroups\Types\Role;
use PCK\FormOfTender\Log as FormOfTenderLog;
use PCK\StructuredDocument\StructuredDocument;
use PCK\TenderDocumentFolders\TenderDocumentFolder;
use PCK\Tenders\Tender;
use PCK\Projects\ProjectProgressChecklist;
use PCK\CompanyProject\CompanyProject;
use PCK\ContractGroupProjectUsers\ContractGroupProjectUser;
use PCK\Companies\Company;

trait ProgressChecklistTrait {

    /**
     * Returns a checklist of the current project progress status
     * according to the tender.
     *
     * @param Tender $tender
     *
     * @return array
     */
    public function getProgressChecklist(Tender $tender)
    {
        $checkList = [];

        if($tender->project->getBsProjectMainInformation())
        {
            $targetBuildspaceModule = ( $tender->project->getBsProjectMainInformation()->status == ProjectMainInformation::STATUS_PRETENDER ) ? Menu::BS_APP_IDENTIFIER_PROJECT_BUILDER : Menu::BS_APP_IDENTIFIER_TENDERING;

            $buildspaceLink = getenv('BUILDSPACE_URL') . "?bsApp={$targetBuildspaceModule}&id={$tender->project->id}";

            $this->addItem($checkList, trans('projects.bqPreparedAndPublishedToTendering'), 'skip_bq_prepared_published_to_tendering', false, ( ! $this->inPreTenderStage() ), $this->filterAssignedRoles(Tender::rolesAllowedToUseModule($this)), getenv('progressCheckList.bqPreparationAndPlublishToTenderingTutorialURL'), $buildspaceLink);
        }
        
        $this->addItem($checkList, trans('projects.tenderDocumentsUploaded'), 'skip_tender_document_uploaded', true, $this->hasUploadedTenderDocumentFiles(), $this->filterAssignedRoles(array( $this->getCallingTenderRole() )), getenv('progressCheckList.tenderDocumentsTutorialURL'), route('projects.tenderDocument.index', array($this->id)));

        $this->addItem($checkList, trans('projects.formOfTenderEdited'), 'skip_form_of_tender_edited', true, $this->formOfTenderEdited($tender), $this->filterAssignedRoles(Tender::rolesAllowedToUseModule($this)), getenv('progressCheckList.formOfTenderTutorialURL'), route('form_of_tender.edit', array($this->id, $tender->id)));

        $this->addItem($checkList, trans('projects.rotFormSubmitted'), 'skip_rot_form_submitted', false, $this->submittedRecommendationOfTenderer($tender), $this->filterAssignedRoles(array( Role::PROJECT_OWNER )), getenv('progressCheckList.recommendationOfTendererFormTutorialURL'), route('projects.tender.show', array($this->id, $tender->id)) . '#' . \PCK\Forms\TenderRecommendationOfTendererInformationForm::TAB_ID);

        $this->addItem($checkList, trans('projects.lotFormSubmitted'), 'skip_lot_form_submitted', false, $this->submittedListOfTenderer($tender), $this->getListOfTendererRoles(), getenv('progressCheckList.listOfTendererFormTutorialURL'), route('projects.tender.show', array($this->id, $tender->id)) . '#' . \PCK\Forms\TenderListOfTendererInformationForm::TAB_ID);

        $this->addItem($checkList, trans('projects.callingTenderFormSubmitted'), 'skip_calling_tender_form_submitted', false, $this->submittedCallingTender($tender), $this->filterAssignedRoles(array( $this->getCallingTenderRole() )), getenv('progressCheckList.callingTenderFormTutorialURL'), route('projects.tender.show', array($this->id, $tender->id)) . '#' . \PCK\Forms\TenderCallingTenderInformationForm::TAB_ID);

        $checkList = $this->checkProjectProgressChecklist($checkList, $tender);

        $checkList = $this->checkContractGroupPermission($checkList, $tender);

        return $checkList;
    }

    public function checkProjectProgressChecklist($checkList, $tender)
    {
        $projectProgressChecklist = ProjectProgressChecklist::where('project_id', '=', $this->id)->first();

        if($projectProgressChecklist)
        {
            foreach($checkList as $key => $item)
            {
                if($projectProgressChecklist->{$item["id"]})
                {
                    $checkList[$key]["checked"] = $projectProgressChecklist->{$item["id"]};
                }
            }
        }

        return $checkList;
    }

    public function checkContractGroupPermission($checkList, $tender)
    {
        $user = \Confide::user();
        $contractGroupId = $tender->project->getCallingTenderRole();

        foreach($checkList as $key => $item)
        {
            $company = $user->getAssignedCompany($tender->project);
    
            $isProjectEditor = $company->isProjectEditor($tender->project, $user);
    
            if(!$isProjectEditor)
            {
                $checkList[$key]["skippable"] = false;
            }
        }

        return $checkList;
    }

    public function getCheckListItemById($checkList, $id)
    {
        foreach($checkList as $item)
        {
            if($item['id'] == $id) return $item;
        }

        return null;
    }

    /**
     * Returns the checklist for the tender addendum progress status.
     *
     * @param Tender $tender
     *
     * @return array
     */
    public function getAddendumChecklist(Tender $tender)
    {
        $checkList = array();

        $targetBuildspaceModule = Menu::BS_APP_IDENTIFIER_TENDERING;

        $buildspaceLink = getenv('BUILDSPACE_URL') . "?bsApp={$targetBuildspaceModule}&id={$tender->project->id}";

        $this->addItem($checkList, 'Project Addendum finalised', 'skip_project_addendum_finalised', false, $this->addendumFinalised($tender), $this->filterAssignedRoles($this->getTenderAddendumRoles()), 'http://buildsoft.com.my/How%20to%20activate%20project%20addendum.pdf', $buildspaceLink);

        $checkList = $this->checkProjectProgressChecklist($checkList, $tender);

        $checkList = $this->checkContractGroupPermission($checkList, $tender);

        return $checkList;
    }

    /**
     * Adds an item to the checklist.
     *
     * @param array $checkList
     * @param       $description
     * @param       $checked
     * @param array $groups
     * @param null  $reference
     */
    private function addItem(array &$checkList, $description, $id, $skippable, $checked, array $groups, $reference, $link)
    {
        $item = array();

        $item['description'] = $description;
        $item['id']          = $id;
        $item['skippable']   = $skippable;
        $item['checked']     = $checked;
        $item['reference']   = $reference;
        $item['link']        = $link;

        $parties = array();

        foreach(array_unique($groups) as $group)
        {
            $parties[$group] = $this->getRoleName($group);
        }

        $item['parties'] = $parties;

        $checkList[] = $item;
    }

    /**
     * Returns true if the project is in the tendering stage.
     *
     * @return bool
     */
    public function inTenderingStage()
    {
        return ( $this->getBsProjectMainInformation()->status == ProjectMainInformation::STATUS_TENDERING );
    }

    /**
     * Returns true if the project is in the pre-tender stage.
     *
     * @return bool
     */
    public function inPreTenderStage()
    {
        return ( $this->getBsProjectMainInformation()->status == ProjectMainInformation::STATUS_PRETENDER );
    }

    /**
     * Returns true if the project has at least one Tender Document file.
     *
     * @return bool
     */

     public function hasUploadedTenderDocumentFiles()
     {
         foreach($this->tenderDocumentFolders as $folder)
         {
             if( ( $structuredDocument = StructuredDocument::getDocument($folder) ) && ( $structuredDocument->isEdited() ) ) return true;
         }
 
         return ( count(TenderDocumentFolder::getProjectFiles($this, false)) > 0 );
    }

    public function hasUploadedTenderDocumentFilesSkippable(Tender $tender)
    {
        $checkListItem = $this->getCheckListItemById($this->getProgressChecklist($tender), 'skip_tender_document_uploaded');

        if( $checkListItem && $checkListItem['checked'] ) 
        {
            return true;
        }

        foreach($this->tenderDocumentFolders as $folder)
        {
            if( ( $structuredDocument = StructuredDocument::getDocument($folder) ) && ( $structuredDocument->isEdited() ) ) return true;
        }

        return ( count(TenderDocumentFolder::getProjectFiles($this, false)) > 0 );
    }

    /**
     * Returns true if the Form of Tender has been edited at least once.
     *
     * @param Tender $tender
     *
     * @return bool
     */
    public function formOfTenderEdited(Tender $tender)
    {
        return ( FormOfTenderLog::getByTender($tender)->count() > 0 );
    }

    public function formOfTenderEditedSkippable(Tender $tender)
    {
        $checkListItem = $this->getCheckListItemById($this->getProgressChecklist($tender), 'skip_form_of_tender_edited');

        if( $checkListItem && $checkListItem['checked'] ) 
        {
            return true;
        }

        return ( FormOfTenderLog::getByTender($tender)->count() > 0 );
    }

    /**
     * Returns true if the Recommendation of Tenderer has been submitted.
     *
     * @param Tender $tender
     *
     * @return bool
     */
    public function submittedRecommendationOfTenderer(Tender $tender)
    {
        $info = $tender->recommendationOfTendererInformation;

        if( $info ) return $info->isSubmitted();

        return false;
    }

    /**
     * Returns true if the List of Tenderer has been submitted.
     *
     * @param Tender $tender
     *
     * @return bool
     */
    public function submittedListOfTenderer(Tender $tender)
    {
        $info = $tender->listOfTendererInformation;

        if( $info ) return $info->isSubmitted();

        return false;
    }

    /**
     * Returns true if the Calling Tender has been submitted.
     *
     * @param Tender $tender
     *
     * @return bool
     */
    public function submittedCallingTender(Tender $tender)
    {
        $info = $tender->callingTenderInformation;

        if( $info ) return $info->isSubmitted();

        return false;
    }

    /**
     * Returns true if the addendum has been finalised.
     *
     * @param Tender $tender
     *
     * @return bool
     */
    public function addendumFinalised(Tender $tender)
    {
        $bsProjectRevision = ProjectRevision::where('project_structure_id', '=', $this->getBsProjectMainInformation()->project_structure_id)
            ->where('version', '=', $tender->count)
            ->first();

        return ( $bsProjectRevision && $bsProjectRevision->locked_status );
    }

    /**
     * Returns only the roles that have an assigned company.
     *
     * @param array $roles
     *
     * @return array
     */
    private function filterAssignedRoles(array $roles)
    {
        $filteredRoles = array();

        foreach($roles as $role)
        {
            if( $this->selectedCompanies()->where('contract_group_id', '=', ContractGroup::getIdByGroup($role))->first() ) $filteredRoles[] = $role;
        }

        return $filteredRoles;
    }

    /**
     * Returns the roles assigned to List of Tenderers.
     *
     * @return array
     */
    public function getListOfTendererRoles()
    {
        $listOfTendererRoles = $this->filterAssignedRoles(array( Role::GROUP_CONTRACT ));

        if( empty( $listOfTendererRoles ) ) $listOfTendererRoles = array( Role::PROJECT_OWNER );

        return $listOfTendererRoles;
    }

    /**
     * Returns the roles of the users in charge of the finalising the addendum.
     *
     * @return array
     */
    public function getTenderAddendumRoles()
    {
        $bsProjectUserPermissions = BsProjectUserPermission::where('project_structure_id', '=', $this->getBsProjectMainInformation()->project_structure_id)
            ->where('project_status', '=', BsProjectUserPermission::STATUS_TENDERING)
            ->get();

        $roles = array();

        foreach($bsProjectUserPermissions as $permission)
        {
            $user = $permission->User;

            if( ! $user ) continue;

            $user = $permission->User->Profile->getEprojectUser();

            if( ! $company = $user->getAssignedCompany($this) ) continue;

            if( ! $role = $company->getContractGroup($this) ) continue;

            // Use id as key to prevent duplicates by overwriting existing ones.
            $roles[ $role->id ] = $role->group;
        }

        return $roles;
    }

}