<?php

class add_index_for_revision_id_to_post_contract_standard_claim_tableTask extends sfBaseTask
{
  protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace        = 'buildspace';
        $this->name             = '2_1_0-12-add_index_for_revision_id_to_post_contract_standard_claim_table';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
    The [2_1_0-12-add_index_for_revision_id_to_post_contract_standard_claim_table|INFO] task does things.
    Call it with:

    [php symfony 2_1_0-12-add_index_for_revision_id_to_post_contract_standard_claim_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for index existence, if not then proceed with insertion query.
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM pg_indexes 
            WHERE tablename = '".strtolower(PostContractStandardClaimTable::getInstance()->getTableName())."'
            AND indexname = 'post_contract_standard_claim_revision_id_idx');");

        $stmt->execute();

        $isIndexExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isIndexExists )
        {
            return $this->logSection('2_1_0-12-add_index_for_revision_id_to_post_contract_standard_claim_table', 'Index for revision_id already exists in '.PostContractTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("CREATE INDEX post_contract_standard_claim_revision_id_idx ON ".PostContractStandardClaimTable::getInstance()->getTableName()." (revision_id);");

        $stmt->execute();

        return $this->logSection('2_1_0-12-add_index_for_revision_id_to_post_contract_standard_claim_table', 'Successfully added index for revision_id in '.PostContractStandardClaimTable::getInstance()->getTableName().' table!');
    }
}
