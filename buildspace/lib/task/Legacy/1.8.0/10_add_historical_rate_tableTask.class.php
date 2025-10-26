<?php

class add_historical_rate_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = '';
        $this->name                = '1_8_0_10_add_historical_rate_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [1_8_0_10_add_historical_rate_table|INFO] task does things.
Call it with:

  [php symfony 1_8_0_10_add_historical_rate_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".HistoricalRateTable::getInstance()->getTableName()."');");

        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ( $result['exists'] )
        {
            return $this->logSection('1_8_0_10_add_historical_rate_table', 'Table '.HistoricalRateTable::getInstance()->getTableName().' already exists!');
        }
        
        $queries = array(
        'CREATE TABLE '.HistoricalRateTable::getInstance()->getTableName().' (id BIGSERIAL, bill_item_id BIGINT NOT NULL, rate NUMERIC(18,5) DEFAULT 0, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));',
        'CREATE UNIQUE INDEX historical_rates_unique_idx ON '.HistoricalRateTable::getInstance()->getTableName().' (bill_item_id);',
        'CREATE INDEX historical_rates_id_idx ON '.HistoricalRateTable::getInstance()->getTableName().' (id, bill_item_id);'
        );
        
        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('1_8_0_10_add_historical_rate_table', 'Successfully added table '.HistoricalRateTable::getInstance()->getTableName().'!');
    }
}