<?php

class remove_registration_no_unique_index_from_bs_companies_tableTask extends migrationTask
{
    protected $name = '3_5_0-1-remove_registration_no_unique_index_from_bs_companies_tableTask';

    protected function execute($arguments = array(), $options = array())
    {
        $this->con = $this->getConnection();

        $this->tableName = CompanyTable::getInstance()->getTableName();

        $this->removeUniqueIndex();
    }

    protected function removeUniqueIndex()
    {
        $constraintName = 'company_registration_no_unique_idx';

        $stmt = $this->con->prepare("DROP INDEX IF EXISTS {$constraintName}");

        $stmt->execute();

        $this->logSection($this->name, "Successfully removed index {$constraintName} from {$this->tableName} table!");
    }
}
