<?php

class add_additional_columns_to_imported_variation_order_items_tableTask extends migrationTask
{
    protected $name = '3_4_0-3-add_additional_columns_to_imported_variation_order_items_table';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = ImportedVariationOrderItemTable::getInstance()->getTableName();

        $this->addTotalUnitColumn();
        $this->addUomSymbolColumn();
        $this->addRateColumn();
        $this->addQuantityColumn();
    }

    protected function addUomSymbolColumn()
    {
        if($this->columnExists($this->tableName, 'uom_symbol'))
        {
            return $this->logSection($this->name, "Column uom_symbol already exists!");
        }

        $stmt = $this->con->prepare("ALTER TABLE {$this->tableName} ADD COLUMN uom_symbol TEXT");

        $stmt->execute();

        $this->logSection($this->name, "Successfully added column uom_symbol in {$this->tableName} table!");
    }

    protected function addRateColumn()
    {
        if($this->columnExists($this->tableName, 'rate'))
        {
            return $this->logSection($this->name, "Column rate already exists!");
        }

        $stmt = $this->con->prepare("ALTER TABLE {$this->tableName} ADD COLUMN rate NUMERIC(18,5) DEFAULT 0");

        $stmt->execute();

        $this->logSection($this->name, "Successfully added column rate in {$this->tableName} table!");
    }

    protected function addTotalUnitColumn()
    {
        if($this->columnExists($this->tableName, 'total_unit'))
        {
            return $this->logSection($this->name, "Column total_unit already exists!");
        }

        $stmt = $this->con->prepare("ALTER TABLE {$this->tableName} ADD COLUMN total_unit INT DEFAULT 1 NOT NULL");

        $stmt->execute();

        $this->logSection($this->name, "Successfully added column total_unit in {$this->tableName} table!");
    }

    protected function addQuantityColumn()
    {
        if($this->columnExists($this->tableName, 'quantity'))
        {
            return $this->logSection($this->name, "Column quantity already exists!");
        }

        $stmt = $this->con->prepare("ALTER TABLE {$this->tableName} ADD COLUMN quantity NUMERIC(18,2) DEFAULT 0");

        $stmt->execute();

        $this->logSection($this->name, "Successfully added column quantity in {$this->tableName} table!");
    }
}
