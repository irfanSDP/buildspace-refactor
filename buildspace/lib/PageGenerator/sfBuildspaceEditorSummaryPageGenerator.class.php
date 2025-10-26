<?php

class sfBuildspaceEditorSummaryPageGenerator extends sfBuildspaceBQEditorPageGenerator
{
    public function __construct(EditorProjectInformation $editorProjectInfo, ProjectStructure $bill, $printLatestBQ=false)
    {
        $this->editorProjectInfo   = $editorProjectInfo;

        parent::__construct( $editorProjectInfo, null, $bill, $printLatestBQ);
    }

}
