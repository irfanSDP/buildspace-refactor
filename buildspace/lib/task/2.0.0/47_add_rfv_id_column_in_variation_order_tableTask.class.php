<?php

class add_rfv_id_column_in_variation_order_tableTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '2_0_0-47-add_rfv_id_column_in_variation_order_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-47-add_rfv_id_column_in_variation_order_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-47-add_rfv_id_column_in_variation_order_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = VariationOrderTable::getInstance()->getTableName();

        $queries = [];

        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."' and column_name ='eproject_rfv_id');");

        $stmt->execute();

        $columnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $columnExists )
        {
            $this->logSection('2_0_0-47-add_rfv_id_column_in_variation_order_table', "Column eproject_rfv_id already exists in {$tableName} table!");
        }
        else
        {
            $queries = [
                "ALTER TABLE {$tableName} ADD COLUMN eproject_rfv_id BIGINT;",
                "CREATE UNIQUE INDEX eproject_rfv_id_idx ON {$tableName} (eproject_rfv_id, deleted_at);",
            ];

        }

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('2_0_0-47-add_rfv_id_column_in_variation_order_table', "Successfully added eproject_rfv_id columns in {$tableName} table!");
    }
}
