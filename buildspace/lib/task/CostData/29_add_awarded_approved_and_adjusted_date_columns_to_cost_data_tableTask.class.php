<?php

class add_awarded_approved_and_adjusted_date_columns_to_cost_data_tableTask extends sfBaseTask
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
        $this->name                = '29-add_awarded_approved_and_adjusted_date_columns_to_cost_data_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [29-add_awarded_approved_and_adjusted_date_columns_to_cost_data_table|INFO] task does things.
Call it with:

  [php symfony 29-add_awarded_approved_and_adjusted_date_columns_to_cost_data_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $columns = array(
            'approved_date',
            'awarded_date',
            'adjusted_date'
        );

        foreach($columns as $column)
        {
            // check for table existence, if not then proceed with insertion query
            $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
            AND table_name = '".strtolower(CostDataTable::getInstance()->getTableName())."' and column_name = '{$column}');");

            $stmt->execute();

            $isColumnExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

            if ( $isColumnExists )
            {
                $this->logSection('29-add_awarded_approved_and_adjusted_date_columns_to_cost_data_table', "Column {$column} already exists in ".CostDataTable::getInstance()->getTableName().' table!');

                continue;
            }

            $stmt = $con->prepare("ALTER TABLE ".CostDataTable::getInstance()->getTableName()." ADD COLUMN {$column} TIMESTAMP");

            $stmt->execute();

            $this->logSection('29-add_awarded_approved_and_adjusted_date_columns_to_cost_data_table', "Successfully added column {$column} in ".CostDataTable::getInstance()->getTableName().' table!');
        }

        return $this->logSection('29-add_awarded_approved_and_adjusted_date_columns_to_cost_data_table', 'Successfully added awarded_date, approved_date, and adjusted_date columns to '.CostDataTable::getInstance()->getTableName().' table!');
    }
}
