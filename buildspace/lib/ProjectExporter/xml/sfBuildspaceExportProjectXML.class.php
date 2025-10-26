<?php
class sfBuildspaceExportProjectXML extends sfBuildspaceXMLGenerator
{
    public $xml;
    public $root;
    public $information;
    public $breakdown;
    public $projectUniqueId;
    public $revision;
    public $tenderAlternatives;

    const TAG_PROJECT = "PROJECT";
    const TAG_ROOT = "ROOT";
    const TAG_BREAKDOWN = "BREAKDOWN";
    const TAG_STRUCTURE = "STRUCTURE";
    const TAG_PROJECT_SUMMARY_FOOTER = "PROJECT_SUMMARY_FOOTER";
    const TAG_PROJECT_SUMMARY_STYLE = "PROJECT_SUMMARY_STYLE";
    const TAG_PROJECT_SUMMARY_GENERAL_SETTING = "PROJECT_SUMMARY_GENERAL_SETTING";
    const TAG_INFORMATION = "INFORMATION";
    const TAG_REGION = "REGION";
    const TAG_SUBREGION = "SUBREGION";
    const TAG_CURRENCY = "CURRENCY";
    const TAG_WORKCAT = "WORKCAT";
    const TAG_REVISIONS = "REVISIONS";
    const TAG_VERSION = "VERSION";
    const TAG_TENDER_ALTERNATIVES = "TENDER_ALTERNATIVES";
    const TAG_TENDER_ALTERNATIVE = "TENDER_ALTERNATIVE";
    const TAG_TENDER_ALTERNATIVES_BILLS = "TENDER_ALTERNATIVES_BILLS";
    const TAG_TENDER_ALTERNATIVES_BILL = "TENDER_ALTERNATIVE_BILL";

    function __construct( $filename = null, $projectUniqueId = false, $exportType = null, $savePath = null, $extension = null, $deleteFile = false ) 
    {
        $this->projectUniqueId = ($projectUniqueId) ? $projectUniqueId : false;

        $this->exportType = ($exportType) ? $exportType : false;

        $savePath = ( $savePath ) ? $savePath : sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;

        parent::__construct( $filename, $savePath, $extension, $deleteFile );
    }

    public function process( $structure = false, $information = false, $breakdowns = false, $revisions = [], $tenderAlternatives = [], $write = true )
    {
        parent::create(self::TAG_PROJECT, array( 'buildspaceId' => sfConfig::get('app_register_buildspace_id'), 'uniqueId' => $this->projectUniqueId, 'exportType' => $this->exportType ));

        $this->processStructure($structure);

        $this->processBreakdowns($breakdowns);

        $this->processInformation($information);

        $this->processRevisions($revisions);

        $this->processTenderAlternatives($tenderAlternatives, $structure);

        if($write)
            parent::write();
    }

    public function createRootTag() 
    {
        $this->root = parent::createTag( self::TAG_ROOT );
    }

    public function addRootChildren( $fieldAndValues )
    {
        $this->addChildren( $this->root, $fieldAndValues );
    }

    public function createBreakdownTag() 
    {
        $this->breakdown = parent::createTag( self::TAG_BREAKDOWN );
    }

    public function createRevisionTag() 
    {
        $this->revision = parent::createTag( self::TAG_REVISIONS );
    }

    public function addStructureChildren( $fieldAndValues )
    {
        $this->addChildren( $this->structure, $fieldAndValues );
    }

    public function createInformationTag() 
    {
        $this->information = parent::createTag( self::TAG_INFORMATION );
    }

    public function createTenderAlternativeTag() 
    {
        $this->tenderAlternatives = parent::createTag( self::TAG_TENDER_ALTERNATIVES );
    }

    public function addInformationChildren( $fieldAndValues )
    {
        $this->addChildren( $this->information, $fieldAndValues );
    }

    protected function processStructure($structure)
    {
        if( ! is_array($structure) )
        {
            return;
        }

        $this->createRootTag();

        $projectSummaryFooter = false;
        $projectSummaryGeneralSetting = false;

        if( array_key_exists('ProjectSummaryFooter', $structure) && ( count($structure['ProjectSummaryFooter']) > 0 ) )
        {
            $projectSummaryFooter = $structure['ProjectSummaryFooter'];

            unset( $structure['ProjectSummaryFooter'] );
        }

        if( array_key_exists('ProjectSummaryGeneralSetting', $structure) && ( count($structure['ProjectSummaryGeneralSetting']) > 0 ) )
        {
            $projectSummaryGeneralSetting = $structure['ProjectSummaryGeneralSetting'];

            unset( $structure['ProjectSummaryGeneralSetting'] );
        }

        $this->addRootChildren($structure);

        if( is_array($projectSummaryFooter) )
        {
            $this->addChildTag($this->root, self::TAG_PROJECT_SUMMARY_FOOTER, $projectSummaryFooter);
        }

        if( is_array($projectSummaryGeneralSetting) )
        {
            $this->addChildTag($this->root, self::TAG_PROJECT_SUMMARY_GENERAL_SETTING, $projectSummaryGeneralSetting);
        }
    }

    protected function processBreakdowns($breakdowns)
    {
        if( ! ( is_array($breakdowns) && count($breakdowns) > 0 ) )
        {
            return;
        }

        $this->createBreakdownTag();

        foreach($breakdowns as $breakdown)
        {
            $projectSummaryStyle = false;

            if( array_key_exists('ProjectSummaryStyle', $breakdown) && ( count($breakdown['ProjectSummaryStyle']) > 0 ) )
            {
                $projectSummaryStyle = $breakdown['ProjectSummaryStyle'];

                unset( $breakdown['ProjectSummaryStyle'] );
            }

            $structureTag = $this->addChildTag($this->breakdown, self::TAG_STRUCTURE, $breakdown);

            if( is_array($projectSummaryStyle) )
            {
                $this->addChildTag($structureTag, self::TAG_PROJECT_SUMMARY_STYLE, $projectSummaryStyle);
            }
        }
    }

    protected function processInformation($information)
    {
        if( ! is_array($information) )
        {
            return;
        }

        $this->createInformationTag();

        if( array_key_exists('Regions', $information) )
        {
            $this->addChildTag($this->information, self::TAG_REGION, $information['Regions']);

            unset( $information['Regions'] );
        }

        if( array_key_exists('Subregions', $information) )
        {
            $this->addChildTag($this->information, self::TAG_SUBREGION, $information['Subregions']);

            unset( $information['Subregions'] );
        }

        if( array_key_exists('WorkCategory', $information) )
        {
            $this->addChildTag($this->information, self::TAG_WORKCAT, $information['WorkCategory']);

            unset( $information['WorkCategory'] );
        }


        if( array_key_exists('Currency', $information) )
        {
            $this->addChildTag($this->information, self::TAG_CURRENCY, $information['Currency']);

            unset( $information['Currency'] );
        }

        $this->addInformationChildren($information);
    }

    protected function processRevisions($revisions)
    {
        if( ! ( is_array($revisions) && count($revisions) > 0 ) )
        {
            return;
        }

        $this->createRevisionTag();

        foreach($revisions as $version)
        {
            $this->addChildTag($this->revision, self::TAG_VERSION, $version);
        }
    }

    protected function processTenderAlternatives($tenderAlternatives, $projectStructure)
    {
        if( !is_array($tenderAlternatives) && empty($tenderAlternatives) )
        {
            return;
        }

        $ids = array_column($tenderAlternatives, 'id');

        if(!empty($ids))
        {
            $overallTotalByTenderAlternatives = [];
            $overallTotalWithoutPrimeCostAndProvisionalBillByTenderAlternatives = [];
            $overallTotalSupplyOfMaterialByTenderAlternatives = [];

            $hasTenderAmount = false;
            $hasTenderAmountExceptPrimeCost = false;
            $hasTenderSupplyOfMaterialAmount = false;

            /*
             * this is how it would be for now. sfBuildspaceExportProjectXML will be used for exporting xml
             * for origin and participate project. If the exported project is a participate project, the 
             * $project id will be overwritten with the origin project id. The root_id where it supposed to be same as
             * project_id is not overwritten. This is not an issue because all the process when importing the participate
             * project include revolves around project_id only.
             * 
             * We will use this approache to check either tender alternative id needs to be overwrite with origin id or not.
             * If project_id != root_id it means that the export file will be for participate project (id will be overwritten)
             */
            $originProjectId = $projectStructure['id'];
            $projectId       = $projectStructure['id'];

            $useTenderAlternativeOriginId = false;

            if($projectStructure['id'] != $projectStructure['root_id'])
            {
                $originProjectId = $projectStructure['id'];
                $projectId       = $projectStructure['root_id'];

                $useTenderAlternativeOriginId = true;
            }

            //Mainly to handle exported rates. The exported file will be imported into eproject with additional details
            if($projectStructure && is_array($projectStructure) && $project = ProjectStructureTable::getInstance()->find($projectId))
            {
                if($project->node->isRoot())
                {
                    if(in_array('tender_amount', $projectStructure))
                    {
                        $hasTenderAmount = true;
                        $overallTotalByTenderAlternatives = TenderAlternativeTable::getOverallTotalForTenderAlternatives($project);
                    }

                    if(in_array('tender_amount_except_prime_cost_provisional', $projectStructure))
                    {
                        $hasTenderAmountExceptPrimeCost = true;
                        $overallTotalWithoutPrimeCostAndProvisionalBillByTenderAlternatives = TenderAlternativeTable::getOverallTotalForTenderAlternativesWithoutPrimeCostAndProvisionalBill($project);
                    }

                    if(in_array('tender_som_amount', $projectStructure))
                    {
                        $hasTenderSupplyOfMaterialAmount = true;
                        $overallTotalSupplyOfMaterialByTenderAlternatives = TenderAlternativeTable::getSupplyOfMaterialAmountForTenderAlternatives($project);
                    }
                }
            }

            $this->createTenderAlternativeTag();
            
            foreach($tenderAlternatives as $tenderAlternative)
            {
                $tenderAlternativeId = $tenderAlternative['id'];

                if($useTenderAlternativeOriginId && !empty($tenderAlternative['tender_origin_id']))
                {
                    $extractedIds = ProjectStructureTable::extractOriginId($tenderAlternative['tender_origin_id']);

                    $tenderAlternative['id'] = $extractedIds['origin_id'];
                    $tenderAlternative['project_structure_id'] = $originProjectId;
                }


                if($hasTenderAmount)
                {
                    $tenderAlternative['tender_amount'] = array_key_exists($tenderAlternativeId, $overallTotalByTenderAlternatives) ? $overallTotalByTenderAlternatives[$tenderAlternativeId] : 0;
                }

                if($hasTenderAmountExceptPrimeCost)
                {
                    $tenderAlternative['tender_amount_except_prime_cost_provisional'] = array_key_exists($tenderAlternativeId, $overallTotalWithoutPrimeCostAndProvisionalBillByTenderAlternatives) ? $overallTotalWithoutPrimeCostAndProvisionalBillByTenderAlternatives[$tenderAlternativeId] : 0;
                }

                if($hasTenderSupplyOfMaterialAmount)
                {
                    $tenderAlternative['tender_som_amount'] = array_key_exists($tenderAlternativeId, $overallTotalSupplyOfMaterialByTenderAlternatives) ? $overallTotalSupplyOfMaterialByTenderAlternatives[$tenderAlternativeId] : 0;
                }

                $this->addChildTag($this->tenderAlternatives, self::TAG_TENDER_ALTERNATIVE, $tenderAlternative);
            }

            /*
             * We don't swap any ids for TenderAlternativeBill since
             * we won't be updating anything when importing participate project
             * back into client's origin app. Normally only rates will be imported into
             * origin app. Unless in future there is a need to export origin id for
             * TenderAlternativeBill for participate project then this bit here need to be
             * refactored.
             */
            $pdo = TenderAlternativeTable::getInstance()->getConnection()->getDbh();

            $stmt = $pdo->prepare("SELECT x.tender_alternative_id, x.project_structure_id
            FROM ".TenderAlternativeBillTable::getInstance()->getTableName()." x
            JOIN ".TenderAlternativeTable::getInstance()->getTableName()." ta ON x.tender_alternative_id = ta.id
            WHERE ta.id IN (".implode(",", $ids).")
            AND ta.deleted_at IS NULL
            ORDER BY ta.id");

            $stmt->execute();

            $tenderAlternativeBillXrefs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(!empty($tenderAlternativeBillXrefs))
            {
                $tenderAlternativeBills = parent::createTag(self::TAG_TENDER_ALTERNATIVES_BILLS);
            }

            foreach($tenderAlternativeBillXrefs as $xref)
            {
                $this->addChildTag($tenderAlternativeBills, self::TAG_TENDER_ALTERNATIVES_BILL, $xref);
            }
        }
    }
}
