<?php

class set_default_value_for_units_column_in_cost_data_prime_cost_rates_tableTask extends sfBaseTask
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
        $this->name                = '22-set_default_value_for_units_column_in_cost_data_prime_cost_rates_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [22-set_default_value_for_units_column_in_cost_data_prime_cost_rates_table|INFO] task does things.
Call it with:

  [php symfony 22-set_default_value_for_units_column_in_cost_data_prime_cost_rates_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = CostDataPrimeCostRateTable::getInstance()->getTableName();

        $stmt = $con->prepare("ALTER TABLE {$tableName} ALTER COLUMN units SET DEFAULT 1;");

        $stmt->execute();

        return $this->logSection('22-set_default_value_for_units_column_in_cost_data_prime_cost_rates_table', "Successfully updated default value for units column in {$tableName} table!");
    }
}
