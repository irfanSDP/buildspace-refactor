<?php
class sfBuildspaceExportEditorProjectXML extends sfBuildspaceExportProjectXML
{
    public $company;
    
    function __construct(Company $company, $filename = null, $projectUniqueId = false, $exportType = null, $savePath = null, $extension = null, $deleteFile = false ) 
    {
        $this->company = $company;

        $this->projectUniqueId = ($projectUniqueId) ? $projectUniqueId : false;

        $this->exportType = ($exportType) ? $exportType : false;

        $savePath = ( $savePath ) ? $savePath : sfConfig::get( 'sf_web_dir' ).DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;

        parent::__construct($filename, $projectUniqueId, $exportType, $savePath, $extension, $deleteFile);
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

            $hasTenderAmount = false;
            $hasTenderAmountExceptPrimeCost = false;
            $hasTenderSupplyOfMaterialAmount = false;

            //Mainly to handle exported rates. The exported file will be imported into eproject with additional details
            if($projectStructure && is_array($projectStructure) && $project = ProjectStructureTable::getInstance()->find($projectStructure['id']))
            {
                if($project->node->isRoot())
                {
                    if(in_array('tender_amount', $projectStructure))
                    {
                        $hasTenderAmount = true;
                        $overallTotalByTenderAlternatives = TenderAlternativeTable::getEditorOverallTotalForTenderAlternatives($project, $this->company);
                    }

                    if(in_array('tender_amount_except_prime_cost_provisional', $projectStructure))
                    {
                        $hasTenderAmountExceptPrimeCost = true;
                        $overallTotalWithoutPrimeCostAndProvisionalBillByTenderAlternatives = TenderAlternativeTable::getEditorOverallTotalForTenderAlternativesWithoutPrimeCostAndProvisionalBill($project, $this->company);
                    }

                    if(in_array('tender_som_amount', $projectStructure))
                    {
                        $hasTenderSupplyOfMaterialAmount = true;
                    }
                }
            }

            $this->createTenderAlternativeTag();

            foreach($tenderAlternatives as $tenderAlternative)
            {
                $tenderAlternativeId = $tenderAlternative['id'];

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
                    $tenderAlternative['tender_som_amount'] = 0;
                }

                $this->addChildTag($this->tenderAlternatives, sfBuildspaceExportProjectXML::TAG_TENDER_ALTERNATIVE, $tenderAlternative);
            }
            
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
                $tenderAlternativeBills = parent::createTag(sfBuildspaceExportProjectXML::TAG_TENDER_ALTERNATIVES_BILLS);
            }

            foreach($tenderAlternativeBillXrefs as $xref)
            {
                $this->addChildTag($tenderAlternativeBills, sfBuildspaceExportProjectXML::TAG_TENDER_ALTERNATIVES_BILL, $xref);
            }
        }
    }
}
