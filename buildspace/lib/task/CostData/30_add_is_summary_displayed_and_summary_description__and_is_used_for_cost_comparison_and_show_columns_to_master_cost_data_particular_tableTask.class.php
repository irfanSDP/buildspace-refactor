<?php

class add_is_summary_displayed_and_summary_description__and_is_used_for_cost_comparison_and_show_columns_to_master_cost_data_particular_tableTask extends migrationTask
{
    protected $name = '30-add_is_summary_displayed_and_summary_description__and_is_used_for_cost_comparison_and_show_columns_to_master_cost_data_particular_table';
    protected $namespace = 'costdata';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = MasterCostDataParticularTable::getInstance()->getTableName();

        $this->addIsSummaryDisplayedColumn();
        $this->addIsSummaryDescriptionColumn();
        $this->addIsUsedForCostComparisonColumn();
        $this->addShowColumn();
    }

    protected function addIsSummaryDisplayedColumn()
    {
        $columnName = 'is_summary_displayed';

        if($this->columnExists($this->tableName, $columnName)) return $this->logSection($this->name, "Column {$columnName} already exists in {$this->tableName} table!");
        
        $stmt = $this->con->prepare("ALTER TABLE {$this->tableName} ADD COLUMN {$columnName} BOOLEAN DEFAULT 'false' NOT NULL");

        $stmt->execute();

        $this->logSection($this->name, "Successfully added column {$columnName} to {$this->tableName} table!");
    }

    protected function addIsSummaryDescriptionColumn()
    {
        $columnName = 'summary_description';

        if($this->columnExists($this->tableName, $columnName)) return $this->logSection($this->name, "Column {$columnName} already exists in {$this->tableName} table!");
        
        $stmt = $this->con->prepare("ALTER TABLE {$this->tableName} ADD COLUMN {$columnName} TEXT");

        $stmt->execute();

        $this->logSection($this->name, "Successfully added column {$columnName} to {$this->tableName} table!");
    }

    protected function addIsUsedForCostComparisonColumn()
    {
        $columnName = 'is_used_for_cost_comparison';

        if($this->columnExists($this->tableName, $columnName)) return $this->logSection($this->name, "Column {$columnName} already exists in {$this->tableName} table!");
        
        $stmt = $this->con->prepare("ALTER TABLE {$this->tableName} ADD COLUMN {$columnName} BOOLEAN DEFAULT 'false' NOT NULL");

        $stmt->execute();

        $this->logSection($this->name, "Successfully added column {$columnName} to {$this->tableName} table!");
    }

    protected function addShowColumn()
    {
        $columnName = 'show';

        if($this->columnExists($this->tableName, $columnName)) return $this->logSection($this->name, "Column {$columnName} already exists in {$this->tableName} table!");
        
        $stmt = $this->con->prepare("ALTER TABLE {$this->tableName} ADD COLUMN {$columnName} BOOLEAN DEFAULT 'true' NOT NULL");

        $stmt->execute();

        $this->logSection($this->name, "Successfully added column {$columnName} to {$this->tableName} table!");
    }
}