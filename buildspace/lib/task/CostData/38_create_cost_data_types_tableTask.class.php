<?php

class create_cost_data_types_tableTask extends migrationTask
{
    protected $name = '38-create_cost_data_types_table';
    protected $namespace = 'costdata';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = CostDataTypeTable::getInstance()->getTableName();

        $queries = array(
            "CREATE TABLE {$this->tableName} (id BIGSERIAL, name VARCHAR(200) NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX cost_data_types_unique_idx ON {$this->tableName} (name);",
            "CREATE INDEX cost_data_types_id_idx ON {$this->tableName} (id);",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_cost_data_types_updated_by_BS_sf_guard_user_id FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$this->tableName} ADD CONSTRAINT BS_cost_data_types_created_by_BS_sf_guard_user_id FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        $this->createTable($this->tableName, $queries);

        $this->seed();
    }

    private function seed()
    {
        $stmt = $this->con->prepare("SELECT COUNT(*) FROM {$this->tableName};");
        $stmt->execute();

        $count = $stmt->fetch(PDO::FETCH_COLUMN);

        if($count > 0) return;

        $object = new CostDataType();
        $object->name = 'Standard';
        $object->save();

        $object = new CostDataType();
        $object->name = 'Historical';
        $object->save();
    }
}