<?php

class migrate_static_cost_data_particulars_to_dynamicTask extends migrationTask
{
    protected $name = 'migrate_static_cost_data_particulars_to_dynamic';
    protected $namespace = 'costdata';
    protected $oldMasterCostDataParticularsIds = [];
    protected $newMasterCostDataParticulars = [];

    CONST GFA = 'gross_floor_area';
    CONST NFA = 'nett_floor_area';
    CONST TOTAL_UNITS = 'total_units';
    CONST TOTAL_ACRES = 'total_acres';

    CONST ASSIGNED_GROUP_GROUP_TYPE_TOTAL_UNITS      = 'total_units';
    CONST ASSIGNED_GROUP_GROUP_TYPE_NETT_FLOOR_AREA  = 'nett_floor_area';
    CONST ASSIGNED_GROUP_GROUP_TYPE_GROSS_FLOOR_AREA = 'gross_floor_area';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        try
        {
            $this->con->beginTransaction();

            $this->reorganizeProjectParticulars();

            $this->con->commit();
            print_r("Committed");
            print_r(PHP_EOL);
        }
        catch(Exception $e)
        {
            $this->con->rollback();

            print_r($e->getMessage());
            print_r(PHP_EOL);
            print_r($e->getTraceAsString());
            print_r(PHP_EOL);
        }
    }

    protected function reorganizeProjectParticulars()
    {
        $this->logSection($this->name, "Reorganizing project particulars");

        $this->defaultUserId = null;
        
        // MASTER
        // save list of current particulars (to delete later on)
        $statement = "
            SELECT mcdp.id
            FROM ". MasterCostDataParticularTable::getInstance()->getTableName() . " mcdp
            WHERE mcdp.deleted_at IS NULL
        ";

        $stmt = $this->con->prepare($statement);

        $stmt->execute();

        $this->oldMasterCostDataParticularsIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Prevent SQL error if empty
        if(empty($this->oldMasterCostDataParticularsIds)) $this->oldMasterCostDataParticularsIds = [0];

        $statement = "
            SELECT mcd.id
            FROM ". MasterCostDataTable::getInstance()->getTableName() . " mcd
            WHERE mcd.deleted_at IS NULL
        ";

        $stmt = $this->con->prepare($statement);

        $stmt->execute();

        $masterCostDataIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach($masterCostDataIds as $masterCostDataId)
        {
            $this->createNewMasterParticulars($masterCostDataId);

            $this->setMasterItemAndParticularPivotRecords($masterCostDataId);

            $this->setProjectParticularValues($masterCostDataId);
        }

        $this->deleteOldParticulars();
    }

    private function deleteOldParticulars()
    {
        $statement = "
            UPDATE ".MasterCostDataParticularTable::getInstance()->getTableName()."
            SET deleted_at = NOW()
            WHERE id in (". implode(',', $this->oldMasterCostDataParticularsIds) .")
        ";

        $stmt = $this->con->prepare($statement);

        $stmt->execute();

    }

    private function createNewMasterParticulars($masterCostDataId)
    {
        // create new master particulars
            // √     unit_and_quantity
            // √     gross_floor_area
            // √     nett_floor_area
            // √     total_acres
            // √      (Summary for all except total_acres)

        $particularUnits = $this->getParticularUnits($masterCostDataId);

        $statement = "INSERT INTO ".MasterCostDataParticularTable::getInstance()->getTableName()."
            (master_cost_data_id, description, priority, uom_id, created_at, updated_at, created_by, updated_by, is_summary_displayed, summary_description, is_used_for_cost_comparison, is_prime_cost_rate_summary_displayed, include_provisional_sum) VALUES
            (:master_cost_data_id, :description, :priority, :uom_id, NOW(), NOW(), :created_by, :updated_by, :is_summary_displayed, :summary_description, :is_used_for_cost_comparison, :is_prime_cost_rate_summary_displayed, :include_provisional_sum) RETURNING id";

        $stmt = $this->con->prepare($statement);

        $stmt->execute(array(
            'master_cost_data_id' => $masterCostDataId,
            'description' => "GFA",
            'priority' => 1,
            'uom_id' => $particularUnits[self::ASSIGNED_GROUP_GROUP_TYPE_GROSS_FLOOR_AREA] ?? null,
            'created_by' => $this->defaultUserId,
            'updated_by' => $this->defaultUserId,
            'is_summary_displayed' => 1,
            'summary_description' => "Total Cost/GFA",
            'is_used_for_cost_comparison' => 1,
            'is_prime_cost_rate_summary_displayed' => 0,
            'include_provisional_sum' => 1,
        ));

        $this->newMasterCostDataParticulars[$masterCostDataId][self::GFA] = $stmt->fetch(PDO::FETCH_COLUMN);

        $stmt->execute(array(
            'master_cost_data_id' => $masterCostDataId,
            'description' => "NFA",
            'priority' => 2,
            'uom_id' => $particularUnits[self::ASSIGNED_GROUP_GROUP_TYPE_NETT_FLOOR_AREA] ?? null,
            'created_by' => $this->defaultUserId,
            'updated_by' => $this->defaultUserId,
            'is_summary_displayed' => 1,
            'summary_description' => "Total Cost/NFA",
            'is_used_for_cost_comparison' => 0,
            'is_prime_cost_rate_summary_displayed' => 0,
            'include_provisional_sum' => 1,
        ));

        $this->newMasterCostDataParticulars[$masterCostDataId][self::NFA] = $stmt->fetch(PDO::FETCH_COLUMN);

        $stmt->execute(array(
            'master_cost_data_id' => $masterCostDataId,
            'description' => "Total Units",
            'priority' => 3,
            'uom_id' => $particularUnits[self::ASSIGNED_GROUP_GROUP_TYPE_TOTAL_UNITS] ?? null,
            'created_by' => $this->defaultUserId,
            'updated_by' => $this->defaultUserId,
            'is_summary_displayed' => 1,
            'summary_description' => "Avg Cost/Unit",
            'is_used_for_cost_comparison' => 0,
            'is_prime_cost_rate_summary_displayed' => 1,
            'include_provisional_sum' => 1,
        ));

        $this->newMasterCostDataParticulars[$masterCostDataId][self::TOTAL_UNITS] = $stmt->fetch(PDO::FETCH_COLUMN);

        $stmt->execute(array(
            'master_cost_data_id' => $masterCostDataId,
            'description' => "Total Acres",
            'priority' => 4,
            'uom_id' => null,
            'created_by' => $this->defaultUserId,
            'updated_by' => $this->defaultUserId,
            'is_summary_displayed' => 0,
            'summary_description' => "",
            'is_used_for_cost_comparison' => 0,
            'is_prime_cost_rate_summary_displayed' => 0,
            'include_provisional_sum' => 1,
        ));

        $this->newMasterCostDataParticulars[$masterCostDataId][self::TOTAL_ACRES] = $stmt->fetch(PDO::FETCH_COLUMN);
    }

    private function getParticularUnits($masterCostDataId)
    {
        $statement = "
            SELECT g.group_name, mp.uom_id
            FROM bs_master_cost_data_particular_assigned_groups g
            JOIN ". MasterCostDataParticularTable::getInstance()->getTableName() . " mp on mp.id = g.master_cost_data_particular_id
            WHERE mp.deleted_at IS NULL
            AND mp.master_cost_data_id = {$masterCostDataId}
            AND mp.id in (". implode(',', $this->oldMasterCostDataParticularsIds) .")
        ";

        $stmt = $this->con->prepare($statement);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function setProjectParticularValues($masterCostDataId)
    {
        $statement = "
            SELECT cd.id
            FROM ". CostDataTable::getInstance()->getTableName() . " cd
            WHERE cd.deleted_at IS NULL
        ";

        $stmt = $this->con->prepare($statement);

        $stmt->execute();

        $costDataIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach($costDataIds as $costDataId)
        {
            // get values of new default particulars
            $particularGroupTotals = $this->costData_getParticularGroupTotals($masterCostDataId, $costDataId);

            $statement = "INSERT INTO ".CostDataParticularTable::getInstance()->getTableName()."
                (cost_data_id, master_cost_data_particular_id, value, created_at, updated_at, created_by, updated_by) VALUES
                (:cost_data_id, :master_cost_data_particular_id, :value, NOW(), NOW(), :created_by, :updated_by);";

            $stmt = $this->con->prepare($statement);

            $stmt->execute(array(
                'master_cost_data_particular_id' => $this->newMasterCostDataParticulars[$masterCostDataId][self::GFA],
                'cost_data_id' => $costDataId,
                'value' => $particularGroupTotals['gross_floor_area'],
                'created_by' => $this->defaultUserId,
                'updated_by' => $this->defaultUserId,
            ));

            $stmt->execute(array(
                'master_cost_data_particular_id' => $this->newMasterCostDataParticulars[$masterCostDataId][self::NFA],
                'cost_data_id' => $costDataId,
                'value' => $particularGroupTotals['nett_floor_area'],
                'created_by' => $this->defaultUserId,
                'updated_by' => $this->defaultUserId,
            ));
            $stmt->execute(array(
                'master_cost_data_particular_id' => $this->newMasterCostDataParticulars[$masterCostDataId][self::TOTAL_UNITS],
                'cost_data_id' => $costDataId,
                'value' => $particularGroupTotals['total_units'],
                'created_by' => $this->defaultUserId,
                'updated_by' => $this->defaultUserId,
            ));
        }
    }

    private function setMasterItemAndParticularPivotRecords($masterCostDataId)
    {
        // set to display all particulars (default)
        $statement = "
            SELECT mi.id
            FROM ". MasterCostDataItemTable::getInstance()->getTableName() . " mi
            WHERE mi.deleted_at IS NULL
            AND mi.master_cost_data_id = {$masterCostDataId}
            AND mi.level = 1;
        ";

        $stmt = $this->con->prepare($statement);

        $stmt->execute();

        $overallProjectCostingItemIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach($overallProjectCostingItemIds as $overallProjectCostingItemId)
        {
            foreach($this->newMasterCostDataParticulars[$masterCostDataId] as $particularId)
            {
                // Column visibility for Work Category grid
                $statement = "
                    INSERT INTO ". MasterCostDataItemParticularTable::getInstance()->getTableName() . "
                    (master_cost_data_particular_id, master_cost_data_item_id, created_at, updated_at, created_by, updated_by) VALUES
                    (:master_cost_data_particular_id, :master_cost_data_item_id, NOW(), NOW(), :created_by, :updated_by);
                ";

                $stmt = $this->con->prepare($statement);

                $stmt->execute(array(
                    'master_cost_data_particular_id' => $particularId,
                    'master_cost_data_item_id' => $overallProjectCostingItemId,
                    'created_by' => $this->defaultUserId,
                    'updated_by' => $this->defaultUserId,
                ));

                // update new master particular components (provisional sum + all items for each particular)
                $statement = "
                    INSERT INTO ". MasterCostDataParticularMasterCostDataItemTable::getInstance()->getTableName() . "
                    (master_cost_data_particular_id, master_cost_data_item_id, created_at, updated_at, created_by, updated_by) VALUES
                    (:master_cost_data_particular_id, :master_cost_data_item_id, NOW(), NOW(), :created_by, :updated_by);
                ";

                $stmt = $this->con->prepare($statement);

                $stmt->execute(array(
                    'master_cost_data_particular_id' => $particularId,
                    'master_cost_data_item_id' => $overallProjectCostingItemId,
                    'created_by' => $this->defaultUserId,
                    'updated_by' => $this->defaultUserId,
                ));
            }
        }
    }

    private function masterCostDataParticularAssignedGroupTable_getParticularsByGroup($masterCostDataId)
    {
        $statement = "SELECT g.group_name, p.id FROM bs_master_cost_data_particular_assigned_groups g
        JOIN bs_master_cost_data_particulars p on p.id = g.master_cost_data_particular_id
        WHERE p.master_cost_data_id = {$masterCostDataId}";

        $stmt = $this->con->prepare($statement);

        $stmt->execute();

        $records =  $stmt->fetchAll(PDO::FETCH_ASSOC|PDO::FETCH_GROUP);

        $particulars = array();

        foreach($records as $groupName => $record)
        {
            $particulars[$groupName] = array_column($record, 'id');
        }

        $groupNames = array(
            self::ASSIGNED_GROUP_GROUP_TYPE_TOTAL_UNITS,
            self::ASSIGNED_GROUP_GROUP_TYPE_NETT_FLOOR_AREA,
            self::ASSIGNED_GROUP_GROUP_TYPE_GROSS_FLOOR_AREA,
        );

        foreach($groupNames as $groupName)
        {
            if(!array_key_exists($groupName, $particulars)) $particulars[$groupName] = array();
        }

        return $particulars;
    }

    private function costData_getParticularGroupTotals($masterCostDataId, $costDataId)
    {
        $assignedParticulars = $this->masterCostDataParticularAssignedGroupTable_getParticularsByGroup($masterCostDataId);

        $stmt = $this->con->prepare("SELECT mp.id, COALESCE(p.value, 0) as value
        FROM " . MasterCostDataParticularTable::getInstance()->getTableName() . " mp 
        JOIN " . MasterCostDataTable::getInstance()->getTableName() . " mcd on mcd.id = mp.master_cost_data_id
        JOIN " . CostDataTable::getInstance()->getTableName() . " cd on mcd.id = cd.master_cost_data_id
        LEFT JOIN " . CostDataParticularTable::getInstance()->getTableName() . " p ON p.master_cost_data_particular_id = mp.id AND p.cost_data_id = :costDataId
        WHERE cd.id = :costDataId
        and mp.id in (". implode(',', $this->oldMasterCostDataParticularsIds) .")
        AND mp.deleted_at IS NULL");

        $stmt->execute(array( 'costDataId' => $costDataId ));

        $values = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $totals = array();

        foreach($assignedParticulars as $group => $particularIds)
        {
            $totals[ $group ] = 0;

            foreach($particularIds as $particularId)
            {
                $totals[ $group ] += $values[ $particularId ] ?? 0;
            }
        }

        return $totals;
    }
}