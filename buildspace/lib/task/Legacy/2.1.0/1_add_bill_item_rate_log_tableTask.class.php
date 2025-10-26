<?php

class add_bill_item_rate_log_tableTask extends sfBaseTask {

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = 'legacy-2_1_0-1-add_bill_item_rate_log_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [legacy-2_1_0-1-add_bill_item_rate_log_table|INFO] task does things.
Call it with:

  [php symfony legacy-2_1_0-1-add_bill_item_rate_log_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower(BillItemRateLogTable::getInstance()->getTableName())."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('legacy-2_1_0-1-add_bill_item_rate_log_table', 'Table '.BillItemRateLogTable::getInstance()->getTableName().' already exists!');
        }

        $queries = array(
            "CREATE TABLE ".BillItemRateLogTable::getInstance()->getTableName()." (id BIGSERIAL, bill_item_id BIGINT NOT NULL, rate NUMERIC(18,5) DEFAULT 0, grand_total NUMERIC(18,5) DEFAULT 0, changes_count BIGINT DEFAULT 0 NOT NULL, project_revision_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, created_by BIGINT, updated_by BIGINT, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX bill_item_rate_logs_unique_idx ON ".BillItemRateLogTable::getInstance()->getTableName()." (bill_item_id, changes_count, project_revision_id);",
            "CREATE INDEX bill_item_rate_logs_id_idx ON ".BillItemRateLogTable::getInstance()->getTableName()." (id);",
            "CREATE INDEX bill_item_rate_logs_fk_idx ON ".BillItemRateLogTable::getInstance()->getTableName()." (bill_item_id, project_revision_id);",
            "ALTER TABLE ".BillItemRateLogTable::getInstance()->getTableName()." ADD CONSTRAINT bill_item_rate_log_revision FOREIGN KEY (project_revision_id) REFERENCES ".ProjectRevisionTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".BillItemRateLogTable::getInstance()->getTableName()." ADD CONSTRAINT bill_item_rate_log_updated_by_sf_guard_user FOREIGN KEY (updated_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".BillItemRateLogTable::getInstance()->getTableName()." ADD CONSTRAINT bill_item_rate_log_created_by_sf_guard_user FOREIGN KEY (created_by) REFERENCES ".sfGuardUserTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE ".BillItemRateLogTable::getInstance()->getTableName()." ADD CONSTRAINT bill_item_rate_log_bill_item_id FOREIGN KEY (bill_item_id) REFERENCES ".BillItemTable::getInstance()->getTableName()."(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            'CREATE OR REPLACE FUNCTION update_bill_item_rate_log() RETURNS TRIGGER AS $log_table$
                DECLARE
                    revisionID BIGINT;
                    numOfChange INTEGER;
                    itemRate NUMERIC(18,5) ;

                BEGIN
                    SELECT r.id INTO revisionID FROM bs_project_revisions r
                    JOIN bs_project_structures b ON b.root_id = r.project_structure_id
                    JOIN bs_bill_elements e ON e.project_structure_id = b.id
                    JOIN bs_bill_items i ON i.element_id = e.id
                    WHERE i.id = NEW.id  AND r.deleted_at IS NULL
                    AND e.deleted_at IS NULL AND b.deleted_at IS NULL
                    ORDER BY r.version DESC LIMIT 1;

                    SELECT COALESCE(MAX(changes_count), 0) + 1 INTO numOfChange FROM bs_bill_item_rate_logs
                    WHERE bill_item_id = NEW.id AND project_revision_id = revisionID
                    GROUP BY bill_item_id, project_revision_id;

                    SELECT COALESCE(final_value, 0) INTO itemRate FROM bs_bill_item_formulated_columns
                    WHERE relation_id = NEW.id AND column_name = \'rate\' AND deleted_at IS NULL
                    GROUP BY relation_id, column_name, final_value;

                    IF(numOfChange IS NULL) THEN
                        numOfChange = 1;
                    END IF;

                    IF(itemRate IS NULL) THEN
                        itemRate = 0.00;
                    END IF;

                    IF (TG_OP = \'UPDATE\') THEN
                        INSERT INTO bs_bill_item_rate_logs (bill_item_id, rate, grand_total, changes_count, project_revision_id, created_at, updated_at)
                        VALUES (NEW.id, itemRate, NEW.grand_total_after_markup, numOfChange, revisionID, NOW(), NOW());
                        RETURN NEW;
                    END IF;

                    RETURN NULL;
                END;
            $log_table$ LANGUAGE plpgsql;',
            "CREATE TRIGGER update_bill_item_rate_log_trigger
            AFTER UPDATE OF grand_total_after_markup ON bs_bill_items
            FOR EACH ROW
            WHEN (OLD.grand_total_after_markup IS DISTINCT FROM NEW.grand_total_after_markup AND NEW.deleted_at IS NULL)
            EXECUTE PROCEDURE update_bill_item_rate_log();"
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        return $this->logSection('legacy-2_1_0-1-add_bill_item_rate_log_table', 'Successfully added table '.BillItemRateLogTable::getInstance()->getTableName().'!');
    }
}