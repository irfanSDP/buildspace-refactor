<?php

class update_unique_constraint_for_bs_post_contract_claim_revisions_tableTask extends migrationTask
{
    protected $name = '3_6_3-2-update_unique_constraint_for_bs_post_contract_claim_revisions_tableTask';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = PostContractClaimRevisionTable::getInstance()->getTableName();

        $this->updateConstraint();
    }

    protected function updateConstraint()
    {
        $constraintName = 'post_contract_revision_version_idx';

        $queries = [
            "DROP INDEX IF EXISTS {$constraintName}",
            "CREATE UNIQUE INDEX {$constraintName} ON {$this->tableName}(post_contract_id, version, current_selected_revision) WHERE deleted_at IS NULL",
        ];

        $this->runQueries($queries);

        $this->logSection($this->name, "Successfully updated constraint for {$this->tableName} table!");
    }
}
