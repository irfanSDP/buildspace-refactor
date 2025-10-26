<?php

class create_master_cost_data_item_particulars_tableTask extends migrationTask
{
    protected $name = '35-create_master_cost_data_item_particulars_table';
    protected $namespace = 'costdata';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = MasterCostDataItemParticularTable::getInstance()->getTableName();

        $queries = array(
            "CREATE TABLE {$this->tableName} (id BIGSERIAL, master_cost_data_particular_id BIGINT NOT NULL, master_cost_data_item_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX master_cost_data_item_particulars_unique_idx ON {$this->tableName} (master_cost_data_particular_id, master_cost_data_item_id);",
            "CREATE INDEX master_cost_data_item_particulars_id_idx ON {$this->tableName} (id);",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_master_cost_data_item_particulars_updated_by FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_master_cost_data_item_particulars_item_id FOREIGN KEY (master_cost_data_item_id) REFERENCES BS_master_cost_data_items(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_master_cost_data_item_particulars_particular_id FOREIGN KEY (master_cost_data_particular_id) REFERENCES BS_master_cost_data_particulars(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_master_cost_data_item_particulars_created_by FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        $this->createTable($this->tableName, $queries);
    }
}