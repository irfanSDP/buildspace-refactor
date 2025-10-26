<?php namespace PCK\Projects;

use PCK\ContractGroups\ContractGroup;
use PCK\DocumentManagementFolders\DocumentManagementFolder;
use PCK\DocumentManagementFolders\FolderType;
use PCK\ProjectContractGroupTenderDocumentPermissions\ProjectContractGroupTenderDocumentPermission;
use PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFolder;
use PCK\TenderDocumentFolders\TenderDocumentFolder;

trait DocumentsTrait {

    /**
     * Generates the master root folder for the project.
     */
    protected function generateMasterRootFolders()
    {
        $this->grantDefaultTenderDocumentAccess();

        $this->generateTenderDocumentBQMasterRootFolder();

        $this->copyTemplateFolders();

        $this->generateDocumentManagementRootFolder();
    }

    protected function grantDefaultTenderDocumentAccess()
    {
        $model = $this->contractGroupTenderDocumentPermission ?: new ProjectContractGroupTenderDocumentPermission();

        $model->contract_group_id = ContractGroup::getIdByGroup($this->getCallingTenderRole());

        $this->contractGroupTenderDocumentPermission()->save($model);
    }

    protected function copyTemplateFolders()
    {
        if( ! $templateFolder = $this->workCategory->getTemplateTenderDocumentFolder() ) return;

        $templateRoot = TemplateTenderDocumentFolder::getRootFolder($templateFolder->id);

        if(!$templateRoot)
        {
            return;
        }
        
        foreach($templateRoot->children as $templateFolder)
        {
            TenderDocumentFolder::copyTemplateFolderAndDescendants($this, $templateFolder);
        }
    }

    protected function generateTenderDocumentBQMasterRootFolder()
    {
        $tenderDocumentFolder                          = new TenderDocumentFolder();
        $tenderDocumentFolder->name                    = TenderDocumentFolder::DEFAULT_BQ_FILES_FOLDER_NAME;
        $tenderDocumentFolder->project_id              = $this->id;
        $tenderDocumentFolder->priority                = 0;
        $tenderDocumentFolder->root_id                 = null;
        $tenderDocumentFolder->parent_id               = null;
        $tenderDocumentFolder->system_generated_folder = true;

        $tenderDocumentFolder->save();
    }

    protected function generateDocumentManagementRootFolder()
    {
        $folders = array(
            FolderType::TYPE_2D_DRAWING        => FolderType::TYPE_2D_DRAWING_TEXT,
            FolderType::TYPE_BIM_FILE          => FolderType::TYPE_BIM_FILE_TEXT,
            FolderType::TYPE_OTHER_DOCUMENT    => FolderType::TYPE_OTHER_DOCUMENT_TEXT,
            FolderType::TYPE_MINUTE_OF_MEETING => FolderType::TYPE_MINUTE_OF_MEETING_TEXT,
            FolderType::TYPE_PROJECT_PHOTO     => FolderType::TYPE_PROJECT_PHOTO_TEXT
        );

        $priority = 1;

        foreach($folders as $type => $name)
        {
            $folder = new DocumentManagementFolder();

            $folder->name        = $name;
            $folder->priority    = $priority;
            $folder->project_id  = $this->id;
            $folder->folder_type = $type;
            $folder->depth       = 0;

            $folder->save();

            $priority++;
        }
    }
}