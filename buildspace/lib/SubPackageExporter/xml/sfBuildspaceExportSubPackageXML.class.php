<?php

class  sfBuildspaceExportSubPackageXML extends sfBuildspaceExportProjectXML
{
	function __construct( $filename = null, $projectUniqueId = false, $exportType = null, $subPackageId, $savePath = null, $extension = null, $deleteFile = false ) 
    {
        $this->subPackageId = $subPackageId;

        parent::__construct( $filename, $projectUniqueId, $exportType, $savePath, $extension, $deleteFile );
    }
	
	public function process( $structure = false, $information = false, $breakdowns = false, $revisions = false, $write = true ) 
    {
        parent::create( self::TAG_PROJECT , array('subPackageId'=>$this->subPackageId, 'buildspaceId'=>sfConfig::get('app_register_buildspace_id'), 'uniqueId' => $this->projectUniqueId, 'exportType' => $this->exportType));

        if ( is_array($structure) )
        {
            $this->createRootTag();

            $projectSummaryFooter = false;
            $projectSummaryGeneralSetting = false;

            if(array_key_exists('ProjectSummaryFooter', $structure))
            {
                if(count($structure['ProjectSummaryFooter']) > 0)
                {
                    $projectSummaryFooter = $structure['ProjectSummaryFooter'];
                }

                unset($structure['ProjectSummaryFooter']);
            }

            if(array_key_exists('ProjectSummaryGeneralSetting', $structure))
            {
                if(count($structure['ProjectSummaryGeneralSetting']) > 0)
                {
                    $projectSummaryGeneralSetting = $structure['ProjectSummaryGeneralSetting'];
                }

                unset($structure['ProjectSummaryGeneralSetting']);
            }

            $this->addRootChildren($structure);

            if(is_array($projectSummaryFooter))
            {
                $this->addChildTag($this->root, self::TAG_PROJECT_SUMMARY_FOOTER, $projectSummaryFooter);
            }

            if(is_array($projectSummaryGeneralSetting))
            {
                $this->addChildTag($this->root, self::TAG_PROJECT_SUMMARY_GENERAL_SETTING, $projectSummaryGeneralSetting);
            }
        }

        if ( is_array($breakdowns) && count($breakdowns) > 0)
        {
            $this->createBreakdownTag();

            foreach($breakdowns as $breakdown)
            {
                $projectSummaryStyle = false;

                if(array_key_exists('ProjectSummaryStyle', $breakdown))
                {
                    if(count($breakdown['ProjectSummaryStyle']) > 0)
                    {
                        $projectSummaryStyle = $breakdown['ProjectSummaryStyle'];
                    }

                    unset($breakdown['ProjectSummaryStyle']);
                }

                $structureTag = $this->addChildTag($this->breakdown, self::TAG_STRUCTURE, $breakdown);

                if(is_array($projectSummaryStyle))
                {
                    $this->addChildTag($structureTag, self::TAG_PROJECT_SUMMARY_STYLE, $projectSummaryStyle);
                }
            }
        }

        if ( is_array($information) )
        {
            $this->createInformationTag();

            if(array_key_exists('Regions', $information))
            {
                $this->addChildTag($this->information, self::TAG_REGION, $information['Regions']);

                unset($information['Regions']);
            }

            if(array_key_exists('Subregions', $information))
            {
                $this->addChildTag($this->information, self::TAG_SUBREGION, $information['Subregions']);

                unset($information['Subregions']);
            }

            if(array_key_exists('WorkCategory', $information))
            {
                $this->addChildTag($this->information, self::TAG_WORKCAT, $information['WorkCategory']);

                unset($information['WorkCategory']);
            }


            if(array_key_exists('Currency', $information))
            {
                $this->addChildTag($this->information, self::TAG_CURRENCY, $information['Currency']);

                unset($information['Currency']);
            }

            $this->addInformationChildren($information);
        }

        if (is_array($revisions) && count($revisions) > 0)
        {
            $this->createRevisionTag();

            foreach($revisions as $version)
            {
                $this->addChildTag($this->revision, self::TAG_VERSION, $version);
            }
        }

        if($write)
            parent::write();
    }
}

?>