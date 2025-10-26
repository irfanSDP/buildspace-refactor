<?php

class sfBuildspaceSubPackageCompanyBQPageGenerator extends sfBuildspaceSubPackageBQPageGenerator
{
    protected $subPackage;

    function __construct(ProjectStructure $bill, $element, $subPackage, $subPackageCompany)
    {
        $this->subPackageCompany = $subPackageCompany;

        parent::__construct($bill, $element, $subPackage);
    }

    public function getRatesAfterMarkup()
    {
        $pdo = $this->pdo;

        $bill = $this->bill;

        $subPackageCompany = $this->subPackageCompany;

        $stmt = $pdo->prepare("SELECT r.bill_item_id, COALESCE(r.rate, 0) AS value 
            FROM ".SubPackageBillItemRateTable::getInstance()->getTableName()." r
            JOIN ".BillItemTable::getInstance()->getTableName()." i ON r.bill_item_id = i.id
            JOIN ".BillElementTable::getInstance()->getTableName()." e ON i.element_id = e.id
            JOIN ".ProjectStructureTable::getInstance()->getTableName()." b ON e.project_structure_id = b.id
            WHERE r.sub_package_company_id = ".$subPackageCompany->id." AND b.id = ".$bill->id." AND e.deleted_at IS NULL AND b.deleted_at IS NULL 
            AND i.project_revision_deleted_at IS NULL AND i.deleted_at IS NULL ORDER BY i.id");

        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        $result = array_map('reset', $result);

        return $result;
    }
    
}