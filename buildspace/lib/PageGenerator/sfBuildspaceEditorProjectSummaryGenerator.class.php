<?php

class sfBuildspaceEditorProjectSummaryGenerator extends sfBuildspaceProjectSummaryGenerator
{
    protected $editorProjectInfo;
    protected $tenderAlternative;

    public function __construct(EditorProjectInformation $editorProjectInfo, TenderAlternative $tenderAlternative=null, $withPrice, $includeCarriedForwardRow = true, $withNotListedItem=true)
    {
        $this->editorProjectInfo = $editorProjectInfo;

        $this->project = $editorProjectInfo->ProjectStructure;

        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $this->includeCarriedForwardRow = $includeCarriedForwardRow;

        $this->withNotListedItem = $withNotListedItem;

        $this->withPrice = $withPrice;

        $this->tenderAlternative = $tenderAlternative;

        $this->summaryItems = $this->querySummaryItems();

        $this->determineMaxRows();
    }

    protected function querySummaryItems()
    {
        $billTotals = [];

        $tenderAlternativeProjectStructureIds = [];
        if($this->tenderAlternative)
        {
            //we set default to -1 so it should return empty query if there is no assigned bill for this tender alternative;
            $tenderAlternativeProjectStructureIds = [-1];
            $tenderAlternativesBills = $this->tenderAlternative->getAssignedBills();

            if($tenderAlternativesBills)
            {
                $tenderAlternativeProjectStructureIds = array_column($tenderAlternativesBills, 'id');
            }
        }

        if($this->withPrice)
        {
            $whereClause = ($this->withNotListedItem) ? '' : ' AND i.type <> '.BillItem::TYPE_ITEM_NOT_LISTED.' ';

            $tenderAlternativeSql = "";
            $tenderAlternativeJoinSql = "";
            $tenderAlternativeSql2 = "";
            if(!empty($tenderAlternativeProjectStructureIds))
            {
                $tenderAlternativeSql = " AND b.id IN (".implode(',', $tenderAlternativeProjectStructureIds).") ";

                $tenderAlternativeJoinSql = "  JOIN " . TenderAlternativeBillTable::getInstance()->getTableName() . " x ON i.id = x.project_structure_id
                JOIN " . TenderAlternativeTable::getInstance()->getTableName() . " ta ON ta.id = x.tender_alternative_id ";

                $tenderAlternativeSql2 = " AND ta.id = " . $this->tenderAlternative->id . " AND ta.deleted_at IS NULL ";
            }

            $stmt = $this->pdo->prepare("SELECT b.id, ROUND(COALESCE(SUM(info.grand_total) ,0),2) AS total
                FROM ".EditorBillItemInfoTable::getInstance()->getTableName()." info
                JOIN ".BillItemTable::getInstance()->getTableName()." i ON info.bill_item_id = i.id
                JOIN ".BillElementTable::getInstance()->getTableName()." e ON e.id = i.element_id AND e.deleted_at IS NULL
                JOIN ".ProjectStructureTable::getInstance()->getTableName()." b ON b.id = e.project_structure_id
                JOIN ".ProjectStructureTable::getInstance()->getTableName()." p ON p.id = b.root_id
                JOIN " . ProjectRevisionTable::getInstance()->getTableName() . " r ON i.project_revision_id = r.id AND r.project_structure_id = p.id
                WHERE info.company_id = ".$this->editorProjectInfo->company_id."
                AND p.id = ".$this->project->id." ".$tenderAlternativeSql."
                AND r.project_structure_id = ".$this->project->id." AND r.locked_status IS TRUE
                AND i.deleted_at IS NULL AND i.project_revision_deleted_at IS NULL
                AND r.deleted_At IS NULL AND e.deleted_at IS NULL
                ".$whereClause."
                GROUP BY b.id");

            $stmt->execute();

            $billTotals = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        $stmt = $this->pdo->prepare("SELECT DISTINCT p.id, p.title, p.type, p.priority, p.lft, p.level, style.reference_char, style.is_bold, style.is_italic, style.is_underline
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " i
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p
            ON (i.lft BETWEEN p.lft AND p.rgt AND p.deleted_at IS NULL)
            ".$tenderAlternativeJoinSql."
            LEFT JOIN " . ProjectSummaryBillStyleTable::getInstance()->getTableName() . " style ON (p.id = style.project_structure_id)
            WHERE p.root_id = ".$this->project->id."
            ".$tenderAlternativeSql2."
            AND i.root_id = p.root_id AND i.type = ".ProjectStructure::TYPE_BILL."
            AND i.type <> " . ProjectStructure::TYPE_ROOT . " AND i.type <> " . ProjectStructure::TYPE_LEVEL . "
            AND p.type <> " . ProjectStructure::TYPE_ROOT . " AND p.type <> " . ProjectStructure::TYPE_LEVEL . "
            AND p.deleted_at IS NULL AND i.deleted_at IS NULL
            ORDER BY p.lft");
            
        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($records as $key => $record)
        {
            $records[$key]['page'] = null;
            $records[$key]['amount'] = ($record['type'] == ProjectStructure::TYPE_BILL and array_key_exists($record['id'], $billTotals)) ? $billTotals[$record['id']] : 0;

            if($record['type'] == ProjectStructure::TYPE_BILL)
            {
                $bill = ProjectStructureTable::getInstance()->find($record['id']);

                $bqPageGenerator = new sfBuildspaceBQPageGenerator($bill, null);
                $pages = $bqPageGenerator->generatePages();

                $records[$key]['page'] = $bqPageGenerator->getSummaryPageNumberingPrefix(count($pages['summary_pages']));//get last page;

                unset($bill, $bqPageGenerator);
            }

            unset($record);
        }

        unset($billTotals);

        return $records;

    }
}
