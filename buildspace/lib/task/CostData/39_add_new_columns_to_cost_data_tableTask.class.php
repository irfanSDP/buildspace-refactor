<?php

class add_new_columns_to_cost_data_tableTask extends migrationTask
{
    protected $name = '39-add_new_columns_to_cost_data_table';
    protected $namespace = 'costdata';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = CostDataTable::getInstance()->getTableName();

        if($this->columnExists($this->tableName, 'cost_data_type_id') ||
            $this->columnExists($this->tableName, 'region_id') ||
            $this->columnExists($this->tableName, 'subregion_id') ||
            $this->columnExists($this->tableName, 'currency_id') ||
            $this->columnExists($this->tableName, 'tender_date') ||
            $this->columnExists($this->tableName, 'award_date')
        )
        {
            return $this->logSection($this->name, "Columns already exist in {$this->tableName} table!");
        }

        $queries = array(
            "ALTER TABLE {$this->tableName} ADD COLUMN cost_data_type_id BIGINT",
            "ALTER TABLE {$this->tableName} ADD COLUMN region_id BIGINT",
            "ALTER TABLE {$this->tableName} ADD COLUMN subregion_id BIGINT",
            "ALTER TABLE {$this->tableName} ADD COLUMN currency_id BIGINT",
            "ALTER TABLE {$this->tableName} ADD COLUMN tender_date TIMESTAMP",
            "ALTER TABLE {$this->tableName} ADD COLUMN award_date TIMESTAMP",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_cost_data_sub_region_id_BS_subregions_id FOREIGN KEY (subregion_id) REFERENCES BS_subregions(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_cost_data_region_id_BS_regions_id FOREIGN KEY (region_id) REFERENCES BS_regions(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_cost_data_currency_id_BS_currency_id FOREIGN KEY (currency_id) REFERENCES BS_currency(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        $this->runQueries($queries);

        $this->seed();

        $queries = array(
            "ALTER TABLE {$this->tableName} ALTER COLUMN cost_data_type_id SET NOT NULL",
            "ALTER TABLE {$this->tableName} ALTER COLUMN region_id SET NOT NULL",
            "ALTER TABLE {$this->tableName} ALTER COLUMN subregion_id SET NOT NULL",
            "ALTER TABLE {$this->tableName} ALTER COLUMN currency_id SET NOT NULL",
            "ALTER TABLE {$this->tableName} DROP COLUMN IF EXISTS type"
        );

        $this->runQueries($queries);

        $this->logSection($this->name, "Successfully added columns added to {$this->tableName} table!");
    }

    private function seed()
    {
        $stmt = $this->con->prepare("SELECT id FROM " . CostDataTypeTable::getInstance()->getTableName() . " WHERE NAME = 'Standard'");
        $stmt->execute();
        $standardTypeId = $stmt->fetch(PDO::FETCH_COLUMN);

        $stmt = $this->con->prepare("SELECT id FROM " . CostDataTypeTable::getInstance()->getTableName() . " WHERE NAME = 'Historical'");
        $stmt->execute();
        $historicalTypeId = $stmt->fetch(PDO::FETCH_COLUMN);

        $stmt = $this->con->prepare("UPDATE {$this->tableName} SET cost_data_type_id = (CASE WHEN type = 1 THEN {$standardTypeId} ELSE {$historicalTypeId} END)");
        $stmt->execute();

        $stmt = $this->con->prepare("SELECT id FROM " . RegionsTable::getInstance()->getTableName() . " WHERE iso3 = 'MYS'");
        $stmt->execute();
        $defaultRegionId = $stmt->fetch(PDO::FETCH_COLUMN);

        if(!$defaultRegionId)
        {
            $stmt = $this->con->prepare("SELECT id FROM " . RegionsTable::getInstance()->getTableName());
            $stmt->execute();
            $defaultRegionId = $stmt->fetch(PDO::FETCH_COLUMN);
        }

        $stmt = $this->con->prepare("UPDATE {$this->tableName} SET region_id = {$defaultRegionId}");
        $stmt->execute();

        $stmt = $this->con->prepare("SELECT id FROM " . SubregionsTable::getInstance()->getTableName() . " WHERE region_id = {$defaultRegionId}");
        $stmt->execute();
        $defaultSubregionId = $stmt->fetch(PDO::FETCH_COLUMN);

        if(!$defaultSubregionId)
        {
            $stmt = $this->con->prepare("SELECT id FROM " . SubregionsTable::getInstance()->getTableName());
            $stmt->execute();
            $defaultSubregionId = $stmt->fetch(PDO::FETCH_COLUMN);
        }

        $stmt = $this->con->prepare("UPDATE {$this->tableName} SET subregion_id = {$defaultSubregionId}");
        $stmt->execute();

        $stmt = $this->con->prepare("SELECT id FROM " . CurrencyTable::getInstance()->getTableName() . " WHERE currency_code ilike 'myr' or currency_code ilike 'rm'");
        $stmt->execute();
        $defaultCurrencyId = $stmt->fetch(PDO::FETCH_COLUMN);

        if(!$defaultCurrencyId)
        {
            $stmt = $this->con->prepare("SELECT id FROM " . CurrencyTable::getInstance()->getTableName() . "");
            $stmt->execute();
            $defaultCurrencyId = $stmt->fetch(PDO::FETCH_COLUMN);
        }

        $stmt = $this->con->prepare("UPDATE {$this->tableName} SET currency_id = {$defaultCurrencyId}");
        $stmt->execute();
    }
}