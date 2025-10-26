<?php

class create_cost_data_nodeless_item_remarks_tableTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'costdata';
        $this->name                = '28-create_cost_data_nodeless_item_remarks_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [28-create_cost_data_nodeless_item_remarks_table|INFO] task does things.
Call it with:

  [php symfony 28-create_cost_data_nodeless_item_remarks_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(CostDataNodelessItemRemarkTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('28-create_cost_data_nodeless_item_remarks_table', 'Table '.CostDataNodelessItemRemarkTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE " . CostDataNodelessItemRemarkTable::getInstance()->getTableName() . " (id BIGSERIAL, cost_data_id BIGINT NOT NULL, type BIGINT NOT NULL, remarks TEXT, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, deleted_at TIMESTAMP, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX cost_data_nodeless_item_remarks_unique_idx ON " . CostDataNodelessItemRemarkTable::getInstance()->getTableName() . " (cost_data_id, type);",
            "CREATE INDEX cost_data_nodeless_item_remarks_id_idx ON " . CostDataNodelessItemRemarkTable::getInstance()->getTableName() . " (id);",
            "ALTER TABLE " . CostDataNodelessItemRemarkTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_cost_data_nodeless_item_remarks_updated_by_fk FOREIGN KEY (updated_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . CostDataNodelessItemRemarkTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_cost_data_nodeless_item_remarks_created_by_fk FOREIGN KEY (created_by) REFERENCES BS_sf_guard_user(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE " . CostDataNodelessItemRemarkTable::getInstance()->getTableName() . " ADD CONSTRAINT BS_cost_data_nodeless_item_remarks_cost_data_id_fk FOREIGN KEY (cost_data_id) REFERENCES BS_cost_data(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('28-create_cost_data_nodeless_item_remarks_table', 'Successfully created '.CostDataNodelessItemRemarkTable::getInstance()->getTableName().' table!');
    }
}
