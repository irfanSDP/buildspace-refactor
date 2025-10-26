<?php

class sfBuildspaceImportProjectBuilderXML extends sfBuildspaceImportProjectXML
{
    protected $con;
    protected $eprojectId   = null;
    protected $subPackageId = null;

    function __construct($userId, $filename = null, $uploadPath = null, Doctrine_Connection $con=null)
    {
        $this->userId = $userId;

        $this->con = $con ? $con : ProjectStructureTable::getInstance()->getConnection();

        $this->pdo = $this->con->getDbh();

        //we did this because we want to skip the sfBuildspaceImportProjectXML constructor but still calling sfBuildspaceXMLParser constructor
        $call = new ReflectionMethod(get_parent_class(get_parent_class($this)), '__construct');
        $call->invokeArgs( $this, [$filename, $uploadPath, "xml", false] );

        $this->extractData();
    }

    protected function extractData()
    {
        parent::read();

        $xmlData = parent::getProcessedData();

        $this->rootProject = ( $xmlData->{sfBuildspaceExportProjectXML::TAG_ROOT}->count() > 0 ) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_ROOT}->children() : false;

        $this->validateImportedFile((int) $xmlData->attributes()->exportType);

        $this->exportType = $xmlData->attributes()->exportType;

        $this->buildspaceId = ( (string) $xmlData->attributes()->buildspaceId ) ? (string) $xmlData->attributes()->buildspaceId : false;

        $this->subPackageId = ( (string) $xmlData->attributes()->subPackageId ) ? (string) $xmlData->attributes()->subPackageId : false;

        $this->projectUniqueId = $xmlData->attributes()->uniqueId;

        $this->information = ( $xmlData->{sfBuildspaceExportProjectXML::TAG_INFORMATION}->count() > 0 ) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_INFORMATION}->children() : false;

        $this->breakdown = ( $xmlData->{sfBuildspaceExportProjectXML::TAG_BREAKDOWN}->count() > 0 ) ? $xmlData->{sfBuildspaceExportProjectXML::TAG_BREAKDOWN}->children() : false;
    }

    protected function validateImportedFile($exportType)
    {
        if ($exportType != ExportedFile::EXPORT_TYPE_TENDER && $exportType != ExportedFile::EXPORT_TYPE_SUB_PACKAGE)
        {
            throw new Exception('Please Choose a correct Project File');
        }
    }

    public function setEprojectId($eprojectId)
    {
        $this->eprojectId = $eprojectId;
    }

    public function process()
    {
        $this->createProject();

        $this->endReader();
    }

    protected function createProject()
    {
        if (!$this->rootProject)
        {
            return false;
        }

        if (!$this->information)
        {
            return false;
        }

        if ($this->information->id)
        {
            unset( $this->information->id );
        }

        $regionId       = null;
        $subRegionId    = null;
        $workCategoryId = null;
        $currencyId     = null;

        if ($this->information->{sfBuildspaceExportProjectXML::TAG_REGION}->count() > 0)
        {
            $regionObject = $this->information->{sfBuildspaceExportProjectXML::TAG_REGION}->children();

            if (!$region = $this->getRegionByName((string) $regionObject->country))
            {
                $regionId = $this->processRegion($regionObject);
            }
            else
            {
                $regionId = $region['id'];
            }
        }


        if (( $this->information->{sfBuildspaceExportProjectXML::TAG_SUBREGION}->count() > 0 ) && $regionId != null)
        {
            $subRegionObject = $this->information->{sfBuildspaceExportProjectXML::TAG_SUBREGION}->children();

            if (!$subRegion = $this->getSubRegionByName((string) $subRegionObject->name))
            {
                $subRegionId = $this->processSubRegion($subRegionObject, $regionId);
            }
            else
            {
                $subRegionId = $subRegion['id'];
            }
        }

        if ($this->information->{sfBuildspaceExportProjectXML::TAG_WORKCAT}->count() > 0)
        {
            $workCategoryObject = $this->information->{sfBuildspaceExportProjectXML::TAG_WORKCAT}->children();

            if (!$workCategory = $this->getWorkCategoryByName((string) $workCategoryObject->name))
            {
                $workCategoryId = $this->processWorkCategory($workCategoryObject);
            }
            else
            {
                $workCategoryId = $workCategory['id'];
            }
        }

        if ($this->information->{sfBuildspaceExportProjectXML::TAG_CURRENCY}->count() > 0)
        {
            $currencyObject = $this->information->{sfBuildspaceExportProjectXML::TAG_CURRENCY}->children();

            if (!$currency = $this->getCurrencyByName((string) $currencyObject->currency_name))
            {
                $currencyId = $this->processCurrency($currencyObject);
            }
            else
            {
                $currencyId = $currency['id'];
            }
        }

        $this->information->region_id            = $regionId;
        $this->information->subregion_id         = $subRegionId;
        $this->information->work_category_id     = $workCategoryId;
        $this->information->currency_id          = $currencyId;
        $this->information->project_structure_id = $this->projectId;

        $project = new ProjectStructure();
        $project->created_by = $this->userId;

        $projectMainInfo = new ProjectMainInformation();
        $form = new ProjectMainInformationForm($projectMainInfo, array( 'projectStructure' => $project ));
        $formData = array(
            'title'            => $this->information->title,
            'description'      => $this->information->description,
            'client'           => $this->information->client,
            'work_category_id' => $this->information->work_category_id,
            'region_id'        => $this->information->region_id,
            'subregion_id'     => $this->information->subregion_id,
            'site_address'     => $this->information->site_address,
            'currency_id'      => $this->information->currency_id,
            'start_date'       => $this->information->start_date,
            '_csrf_token'      => $form->getCSRFToken(),
        );

        if( $this->eprojectId )
        {
            $formData['eproject_origin_id'] = $this->eprojectId;
            $form->setEprojectValidator();

            $formData['description'] = Doctrine_Core::getTable('EProjectProject')->find($this->eprojectId)->description;
        }

        $form->bind($formData);

        if($form->isValid())
        {
            $projectMainInfo = $form->save($this->con);

            $projectMainInfo->ProjectStructure->lft = $this->rootProject->lft;
            $projectMainInfo->ProjectStructure->rgt = $this->rootProject->rgt;
            $projectMainInfo->ProjectStructure->save($this->con);

            $this->projectId = $projectMainInfo->project_structure_id;

            $projectSummaryGeneralSettingXML = ( $this->rootProject->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_GENERAL_SETTING}->count() > 0 ) ? $this->rootProject->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_GENERAL_SETTING}->children() : null;

            if ($projectSummaryGeneralSettingXML instanceof SimpleXMLElement)
            {
                $projectSummaryGeneralSetting = $projectMainInfo->ProjectStructure->ProjectSummaryGeneralSetting;

                $projectSummaryGeneralSetting->project_title                     = $projectSummaryGeneralSettingXML->project_title;
                $projectSummaryGeneralSetting->summary_title                     = $projectSummaryGeneralSettingXML->summary_title;
                $projectSummaryGeneralSetting->include_printing_date             = !empty($projectSummaryGeneralSettingXML->include_printing_date);
                $projectSummaryGeneralSetting->carried_to_next_page_text         = $projectSummaryGeneralSettingXML->carried_to_next_page_text;
                $projectSummaryGeneralSetting->continued_from_previous_page_text = $projectSummaryGeneralSettingXML->continued_from_previous_page_text;
                $projectSummaryGeneralSetting->page_number_prefix                = $projectSummaryGeneralSettingXML->page_number_prefix;

                $projectSummaryGeneralSetting->save($this->con);

                $projectSummaryGeneralSetting->free(true);

                unset( $projectSummaryGeneralSettingXML );
            }

            $projectSummaryFooterXML = ( $this->rootProject->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_FOOTER}->count() > 0 ) ? $this->rootProject->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_FOOTER}->children() : null;

            if ($projectSummaryFooterXML instanceof SimpleXMLElement)
            {
                $projectSummaryFooter = $projectMainInfo->ProjectStructure->ProjectSummaryFooter;

                $projectSummaryFooter->first_row_text  = $projectSummaryFooterXML->first_row_text;
                $projectSummaryFooter->second_row_text = $projectSummaryFooterXML->second_row_text;
                $projectSummaryFooter->left_text       = $projectSummaryFooterXML->left_text;
                $projectSummaryFooter->right_text      = $projectSummaryFooterXML->right_text;

                $projectSummaryFooter->save($this->con);

                $projectSummaryFooter->free(true);

                unset( $projectSummaryFooterXML );
            }

            $this->processProjectBreakdown($projectMainInfo->ProjectStructure);

            $projectMainInfo->free(true);

            unset($projectMainInfo);

        }
        else
        {
            throw new Exception('There are errors in the import file. No of errors ('.count($form->getErrors()).')');
        }

        unset( $this->rootProject );

        return true;
    }

    protected function processProjectBreakdown(ProjectStructure $projectStructure)
    {
        if(!$this->breakdown)
            return;

        $breakdownXML = json_decode(json_encode((array)$this->breakdown), true);

        if(is_array($breakdownXML[sfBuildspaceExportProjectXML::TAG_STRUCTURE]) && !isset($breakdownXML[sfBuildspaceExportProjectXML::TAG_STRUCTURE][0]))
        {
            $projectStructures = array($breakdownXML[sfBuildspaceExportProjectXML::TAG_STRUCTURE]);
        }
        else
        {
            $projectStructures = $breakdownXML[sfBuildspaceExportProjectXML::TAG_STRUCTURE];
        }

        array_unshift($projectStructures, array(
            'id'    => $projectStructure->id,
            'level' => $projectStructure->level,
        ));

        $trees = array();
        // Node Stack. Used to help building the hierarchy
        $stack = array();

        foreach ( $projectStructures as $item )
        {
            $item['root_id']     = $projectStructure->id;
            $item['priority']    = $projectStructure->priority;
            $item['lft']         = 1;
            $item['rgt']         = 2;
            $item['__children'] = array();

            // Number of stack items
            $l = count($stack);

            // Check if we're dealing with different levels
            $item['level'];

            while ($l > 0 && $stack[$l - 1]['level'] >= $item['level'])
            {
                array_pop($stack);
                $l --;
            }

            // Stack is empty (we are inspecting the root)
            if ( $l == 0 )
            {
                // Assigning the root child
                $i         = count($trees);
                $trees[$i] = $item;
                $stack[]   = &$trees[$i];
            }
            else
            {
                $item['lft'] = $stack[$l - 1]['rgt'];
                $item['rgt'] = $item['lft'] + 1;

                // Add child to parent
                $i                               = count($stack[$l - 1]['__children']);
                $stack[$l - 1]['__children'][$i] = $item;
                $stack[]                         = &$stack[$l - 1]['__children'][$i];

                $x = $l;
                while($x-1 >= 0)
                {
                    $stack[$x - 1]['rgt'] = $stack[$x - 1]['rgt'] + 2;
                    $x--;
                }
            }
        }

        unset( $stack );

        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($trees));

        $idx = 0;
        $flattenStructure = array();

        foreach ($iterator as $key => $value)
        {
            if(strtolower($key) == 'id')
            {
                $idx = $value;
                $flattenStructure[$idx] = array();
            }

            $flattenStructure[$idx][$key] = $value;
        }

        $projectStructure->lft = $flattenStructure[$projectStructure->id]['lft'];
        $projectStructure->rgt = $flattenStructure[$projectStructure->id]['rgt'];

        $projectStructure->save($this->con);

        unset($flattenStructure[$projectStructure->id]);

        $stmt = new sfImportStatementGenerator();

        foreach ($this->breakdown as $structure)
        {
            $originalId     = (int)$structure->id;
            $originalRootId = (int)$this->rootProject->id;
            unset( $structure->id );

            if(array_key_exists($originalId, $flattenStructure))
            {
                $structure->tender_origin_id = ProjectStructureTable::generateSubPackageOriginId($this->buildspaceId, $originalId, $originalRootId, $this->subPackageId);
                $structure->lft              = $flattenStructure[ $originalId ]['lft'];
                $structure->rgt              = $flattenStructure[ $originalId ]['rgt'];
                $structure->root_id          = $flattenStructure[ $originalId ]['root_id'];
                $structure->created_by       = $this->userId;
                $structure->updated_by       = $this->userId;
                $structure->created_at       = 'NOW()';
                $structure->updated_at       = 'NOW()';

                $dataAndStructure = parent::generateArrayOfSingleData($structure, true);

                $stmt->createInsert(ProjectStructureTable::getInstance()->getTableName(), $dataAndStructure['structure']);

                $stmt->addRecord($dataAndStructure['data']);

                $stmt->save();

                $this->breakdownIds[$originalId] = $stmt->returningIds[0];

                if ($structure->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_STYLE}->count() > 0)
                {
                    $this->processProjectSummaryStyle($structure->{sfBuildspaceExportProjectXML::TAG_PROJECT_SUMMARY_STYLE}->children(), $this->breakdownIds[$originalId], $stmt);
                }

                if ($structure->type == ProjectStructure::TYPE_BILL)
                {
                    $billMarkupSetting                       = new BillMarkupSetting();
                    $billMarkupSetting->project_structure_id = $this->breakdownIds[$originalId];
                    $billMarkupSetting->rounding_type        = BillMarkupSetting::ROUNDING_TYPE_DISABLED;

                    $billMarkupSetting->save($this->con);

                    $billMarkupSetting->free(true);

                    unset( $billMarkupSetting );
                }
            }

            unset( $structure );
        }

        unset( $this->breakdown );
    }
}