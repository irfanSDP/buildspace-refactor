<?php

class add_reference_column_to_new_post_contract_form_information_tableTask extends sfBaseTask
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
        $this->name                = '3_0_0-1-add_reference_column_to_new_post_contract_form_information_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [3_0_0-1-add_reference_column_to_new_post_contract_form_information_table|INFO] task does things.
Call it with:

  [php symfony 3_0_0-1-add_reference_column_to_new_post_contract_form_information_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(NewPostContractFormInformationTable::getInstance()->getTableName())."' and column_name ='reference');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('3_0_0-1-add_reference_column_to_new_post_contract_form_information_table', 'Column reference already exists in '.NewPostContractFormInformationTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("ALTER TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." ADD COLUMN reference TEXT");

        $stmt->execute();

        $stmt = $con->prepare("CREATE UNIQUE INDEX new_post_contract_form_information_reference_unique_idx ON ".NewPostContractFormInformationTable::getInstance()->getTableName()." (reference);");

        $stmt->execute();

        $stmt = $con->prepare("SELECT i.id, i.project_structure_id, i.type, i.form_number
            FROM " . NewPostContractFormInformationTable::getInstance()->getTableName() . " i;");

        $stmt->execute();

        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $queries = array();

        foreach($records as $record)
        {
            try
            {
                $project   = Doctrine_Core::getTable('ProjectStructure')->find($record['project_structure_id']);

                if(!$project) throw new Exception("Project does not exist");

                if( $project->MainInformation->eproject_origin_id )
                {
                    // Uses the contract number of the Main Project.
                    if( $parentProject = ProjectStructureTable::getParentProject($project) ) $project = $parentProject;

                    if(!EProjectProjectTable::getByEProjectOriginId($project->MainInformation->eproject_origin_id)) throw new Exception("Project does not exist");
                }
                
                $reference = NewPostContractFormInformation::generateLetterOfAwardCode($project, $record['type'], $record['form_number']);

                $stmt = $con->prepare("UPDATE " . NewPostContractFormInformationTable::getInstance()->getTableName() . " SET reference = '{$reference}' WHERE id = {$record['id']};");

                $stmt->execute();       
            }
            catch(Exception $e)
            {
                $reference = "p{$record['project_structure_id']}-type{$record['type']}-formnumber{$record['form_number']}";

                $stmt = $con->prepare("UPDATE " . NewPostContractFormInformationTable::getInstance()->getTableName() . " SET reference = '{$reference}' WHERE id = {$record['id']};");

                $stmt->execute();
            }
        }

        $stmt = $con->prepare("ALTER TABLE ".NewPostContractFormInformationTable::getInstance()->getTableName()." ALTER COLUMN reference SET NOT NULL");

        $stmt->execute();

        return $this->logSection('3_0_0-1-add_reference_column_to_new_post_contract_form_information_table', 'Successfully added column reference in '.NewPostContractFormInformationTable::getInstance()->getTableName().' table!');
    }
}
