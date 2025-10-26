<?php

class add_amount_column_to_bs_item_code_settings_tableTask extends migrationTask
{
    protected $name = '3_6_3-1-add_amount_column_to_bs_item_code_settings_tableTask';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = ItemCodeSettingTable::getInstance()->getTableName();

        $this->addColumn();
    }

    protected function addColumn()
    {
        if($this->columnExists($this->tableName, 'amount'))
        {
            return $this->logSection($this->name, "Column amount already exists!");
        }

        $stmt = $this->con->prepare("ALTER TABLE {$this->tableName} ADD COLUMN amount NUMERIC(18,2) DEFAULT 0 NOT NULL");

        $stmt->execute();

        $this->logSection($this->name, "Successfully added column amount in {$this->tableName} table!");
    }
}
