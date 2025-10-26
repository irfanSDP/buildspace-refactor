<?php

class recalculateCostDataItemValuesTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', "app", sfCommandOption::PARAMETER_REQUIRED, 'The application name', "backend")
        ));

        $this->namespace        = 'costdata';
        $this->name             = 'recalculateCostDataItemValuesTask';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [costdata:recalculateCostDataItemValuesTask|INFO] create integration tables for costdata.
Call it with:

  [php symfony costdata:recalculateCostDataItemValuesTask|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);

        $this->conn = $databaseManager->getDatabase('main_conn')->getConnection();

        $this->updateItemValues();
    }

    protected function updateItemValues()
    {
        try
        {
            $this->updateStandardItemValues();
            $this->updatePrimeCostSumItemValues();
            $this->updatePrimeCostSumColumnValues();
        }
        catch (Exception $e)
        {
            return $this->logSection('recalculateCostDataItemValuesTask', 'Error updating values >> '.$e->getMessage());
        }

        return $this->logSection('recalculateCostDataItemValuesTask', 'Cost Data values updated!');
    }

    protected function updateStandardItemValues()
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT(p.id)
            FROM bs_bill_item_cost_data_item pivot
            JOIN bs_bill_items i ON i.id = pivot.bill_item_id
            JOIN bs_bill_elements e ON e.id = i.element_id
            JOIN bs_project_structures b ON b.id = e.project_structure_id
            JOIN bs_project_structures p ON p.id = b.root_id
            WHERE i.deleted_at IS NULL
            AND e.deleted_at IS NULL
            AND b.deleted_at IS NULL
            AND p.deleted_at IS NULL;");

        $stmt->execute();

        $projectIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        CostDataItemTable::updateProjectCostDataItemValues($projectIds);
    }

    protected function updatePrimeCostSumItemValues()
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT(p.id)
            FROM bs_bill_item_cost_data_prime_cost_sum_item pivot
            JOIN bs_bill_items i ON i.id = pivot.bill_item_id
            JOIN bs_bill_elements e ON e.id = i.element_id
            JOIN bs_project_structures b ON b.id = e.project_structure_id
            JOIN bs_project_structures p ON p.id = b.root_id
            WHERE i.deleted_at IS NULL
            AND e.deleted_at IS NULL
            AND b.deleted_at IS NULL
            AND p.deleted_at IS NULL;");

        $stmt->execute();

        $projectIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        CostDataPrimeCostSumItemTable::updateProjectCostDataItemValues($projectIds);
    }

    protected function updatePrimeCostSumColumnValues()
    {
        $stmt = $this->conn->prepare("SELECT DISTINCT(p.id)
            FROM bs_bill_item_cost_data_prime_cost_sum_column pivot
            JOIN bs_bill_items i ON i.id = pivot.bill_item_id
            JOIN bs_bill_elements e ON e.id = i.element_id
            JOIN bs_project_structures b ON b.id = e.project_structure_id
            JOIN bs_project_structures p ON p.id = b.root_id
            WHERE i.deleted_at IS NULL
            AND e.deleted_at IS NULL
            AND b.deleted_at IS NULL
            AND p.deleted_at IS NULL;");

        $stmt->execute();

        $projectIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        CostDataPrimeCostSumColumnTable::updateProjectCostDataItemValues($projectIds);
    }
}
