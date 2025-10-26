<?php

abstract class migrationTask extends sfBaseTask
{
    protected $name = '';
    protected $namespace = 'buildspace';
    protected $con;

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [{$this->name}|INFO] task does things.
Call it with:

  [php symfony {$this->name}|INFO]
EOF;
    }

    protected function getConnection()
    {
        if(!$this->con)
        {
            $databaseManager = new sfDatabaseManager($this->configuration);

            $this->con = $databaseManager->getDatabase('main_conn')->getConnection();
        }

        return $this->con;
    }

    protected function columnExists($tableName, $columnName, $conn = null)
    {
        if(is_null($conn)) $conn = $this->getConnection();

        $stmt = $conn->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."' and column_name = '{$columnName}');");

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_COLUMN, 0);
    }

    protected function tableExists($tableName)
    {
        $stmt = $this->getConnection()->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_COLUMN, 0);
    }

    protected function createTable($tableName, array $queries)
    {
        if($this->tableExists($tableName)) return $this->logSection($this->name, "Table {$tableName} already exists!");

        $this->runQueries($queries);

        $this->logSection($this->name, "Successfully created {$this->tableName} table!");
    }

    protected function runQueries(array $queries)
    {
        foreach ($queries as $query )
        {
            $stmt = $this->getConnection()->prepare($query);

            $stmt->execute();
        }
    }
}