<?php

class sfBuildspaceSupplyOfMaterialBillContractorPageGenerator extends sfBuildspaceSupplyOfMaterialBillPageGenerator {
    public $tenderCompany;

    public function __construct(ProjectStructure $bill, $element, $tenderCompany)
    {
        $this->tenderCompany = $tenderCompany;

        parent::__construct($bill, $element);
    }

    public function queryBillStructure()
    {
        $billStructure = array();

        $elementSqlPart = $this->billElement instanceof SupplyOfMaterialElement ? "AND e.id = " . $this->billElement->id : null;

        $stmt = $this->pdo->prepare("SELECT e.id, e.description FROM " . SupplyOfMaterialElementTable::getInstance()->getTableName() . " e
        WHERE e.project_structure_id = " . $this->bill->id . " " . $elementSqlPart . " AND e.deleted_at IS NULL ORDER BY e.priority");

        $stmt->execute();

        $elements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($elements as $element)
        {
            $result = array(
                'id'          => $element['id'],
                'description' => $element['description'],
                'items'       => array()
            );

            $stmt = $this->pdo->prepare("SELECT c.id, c.description, c.element_id, c.type, tenderer_rate.estimated_qty, tenderer_rate.percentage_of_wastage, tenderer_rate.contractor_supply_rate, tenderer_rate.difference, tenderer_rate.amount,
                COALESCE(c.supply_rate, 0) AS supply_rate, c.uom_id, c.lft, c.rgt, c.root_id, c.level, uom.symbol AS uom
                FROM " . SupplyOfMaterialItemTable::getInstance()->getTableName() . " c
                LEFT JOIN " . TenderSupplyOfMaterialRateTable::getInstance()->getTableName() . " tenderer_rate ON tenderer_rate.supply_of_material_item_id = c.id AND tenderer_rate.tender_company_id = " . $this->tenderCompany->id . "
                LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON c.uom_id = uom.id AND uom.deleted_at IS NULL
                WHERE c.element_id = " . $element['id'] . "
                AND c.deleted_at IS NULL ORDER BY c.priority, c.lft, c.level");

            $stmt->execute();

            $billItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $result['items'] = $billItems;

            array_push($billStructure, $result);

            unset( $element, $billItems );
        }

        return $billStructure;
    }

}