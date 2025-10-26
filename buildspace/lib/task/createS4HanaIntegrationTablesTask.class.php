<?php

class createS4HanaIntegrationTablesTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', "app", sfCommandOption::PARAMETER_REQUIRED, 'The application name', "backend")
        ));

        $this->namespace        = 's4hana';
        $this->name             = 'createS4HanaIntegrationTables';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [s4hana:createS4HanaIntegrationTables|INFO] create integration tables for S4Hana.
Call it with:

  [php symfony s4hana:createS4HanaIntegrationTables|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $mainConn = $databaseManager->getDatabase('main_conn')->getConnection();

        $this->createContractHeaderTable($mainConn);

        $this->createContractItemTable($mainConn);

        $this->createClaimHeaderTable($mainConn);

        $this->createClaimItemTable($mainConn);

        $this->createIntegrationExportedClaimCertificateTable($mainConn);
    }

    protected function createContractHeaderTable($con)
    {
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = 'bs_contract_headers');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('s4HanaIntegrationTables', 'Table bs_contract_headers already exists!');
        }
        else
        {
            $queries = [
                "CREATE TABLE bs_contract_headers (batch_number BIGINT NOT NULL, project_id BIGINT NOT NULL, reference TEXT NOT NULL, date_of_award DATE, commencement_date DATE, completion_date DATE, published_date DATE,
                max_retention_sum NUMERIC(5,2) DEFAULT 0, retention NUMERIC(5,2) DEFAULT 0, letter_of_award_number TEXT, business_unit TEXT, work_category VARCHAR(255), contractor TEXT,
                currency VARCHAR(100), contract_sum NUMERIC(18,5) DEFAULT 0, created_at TIMESTAMP NOT NULL, PRIMARY KEY(batch_number, project_id))",
                "CREATE INDEX bs_contract_headers_primary_idx ON bs_contract_headers (batch_number, project_id)",
                "CREATE INDEX bs_contract_headers_batch_number_idx ON bs_contract_headers (batch_number)",
                "ALTER TABLE bs_contract_headers ADD CONSTRAINT bs_contract_headers_project_id_fk FOREIGN KEY (project_id) REFERENCES bs_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE"
            ];

            try
            {
                $con->beginTransaction();

                foreach ($queries as $query )
                {
                    $stmt = $con->prepare($query);
        
                    $stmt->execute();
                }

                $con->commit();
            }
            catch (Exception $e)
            {
                $con->rollBack();
                return $this->logSection('s4HanaIntegrationTables', 'Error creating bs_contract_headers table >> '.$e->getMessage());
            }

            return $this->logSection('s4HanaIntegrationTables', 'Successfully created bs_contract_headers table!');
        }
    }

    protected function createContractItemTable($con)
    {
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = 'bs_contract_items');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('s4HanaIntegrationTables', 'Table bs_contract_items already exists!');
        }
        else
        {
            $queries = [
                "CREATE TABLE bs_contract_items (batch_number BIGINT NOT NULL, project_id BIGINT NOT NULL, contract_number TEXT NOT NULL,
                item_id BIGINT NOT NULL, item_title TEXT NOT NULL, item_type VARCHAR(80) NOT NULL,
                reference_amount NUMERIC(18,5) DEFAULT 0, total NUMERIC(18,5) DEFAULT 0, nett_omission_addition NUMERIC(18,5) DEFAULT 0,
                previous_claim NUMERIC(18,5) DEFAULT 0, current_claim NUMERIC(18,5) DEFAULT 0, up_to_date_claim NUMERIC(18,5) DEFAULT 0,
                approved_date TIMESTAMP, created_at TIMESTAMP NOT NULL, PRIMARY KEY(batch_number, project_id, item_id, item_type))",
                "CREATE INDEX bs_contract_items_primary_idx ON bs_contract_items (batch_number, project_id, item_id, item_type)",
                "CREATE INDEX bs_contract_items_batch_number_idx ON bs_contract_items (batch_number)",
                "CREATE INDEX bs_contract_items_item_type_idx ON bs_contract_items (item_type)",
                "ALTER TABLE bs_contract_items ADD CONSTRAINT bs_contract_items_project_id_fk FOREIGN KEY (project_id) REFERENCES bs_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE"
            ];

            try
            {
                $con->beginTransaction();

                foreach ($queries as $query )
                {
                    $stmt = $con->prepare($query);
        
                    $stmt->execute();
                }

                $con->commit();
            }
            catch (Exception $e)
            {
                $con->rollBack();
                return $this->logSection('s4HanaIntegrationTables', 'Error creating bs_contract_items table >> '.$e->getMessage());
            }

            return $this->logSection('s4HanaIntegrationTables', 'Successfully created bs_contract_items table!');
        }
    }

    protected function createClaimHeaderTable($con)
    {
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = 'bs_claim_headers');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('s4HanaIntegrationTables', 'Table bs_claim_headers already exists!');
        }
        else
        {
            $queries = [
                "CREATE TABLE bs_claim_headers (batch_number BIGINT NOT NULL, project_id BIGINT NOT NULL, claim_certificate_id BIGINT NOT NULL,
                contract_number TEXT NOT NULL, claim_number INT NOT NULL, contractor_submitted_date DATE,
                site_verified_date DATE, certificate_received_date DATE, payment_due_date DATE, currency VARCHAR(100),
                contract_sum NUMERIC(18,5) DEFAULT 0, work_done NUMERIC(18,5) DEFAULT 0, amount_certified NUMERIC(18,5) DEFAULT 0, percentage_completion NUMERIC(5,2) DEFAULT 0,
                invoice_date DATE, invoice_reference TEXT, acc_retention_sum NUMERIC(18,5) DEFAULT 0, retention_sum NUMERIC(18,5) DEFAULT 0,
                release_retention_percentage NUMERIC(5,2) DEFAULT 0, acc_release_retention NUMERIC(18,5) DEFAULT 0, release_retention NUMERIC(18,5) DEFAULT 0,
                approved_date TIMESTAMP NOT NULL, creation_date TIMESTAMP NOT NULL, created_at TIMESTAMP NOT NULL, PRIMARY KEY(batch_number, project_id, claim_certificate_id))",
                "CREATE INDEX bs_claim_headers_primary_idx ON bs_claim_headers (batch_number, project_id, claim_certificate_id)",
                "CREATE INDEX bs_claim_headers_batch_number_idx ON bs_claim_headers (batch_number)",
                "ALTER TABLE bs_claim_headers ADD CONSTRAINT bs_claim_headers_project_id_fk FOREIGN KEY (project_id) REFERENCES bs_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE",
                "ALTER TABLE bs_claim_headers ADD CONSTRAINT bs_claim_headers_claim_cert_id_fk FOREIGN KEY (claim_certificate_id) REFERENCES bs_claim_certificates(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE"
            ];

            try
            {
                $con->beginTransaction();

                foreach ($queries as $query )
                {
                    $stmt = $con->prepare($query);
        
                    $stmt->execute();
                }

                $con->commit();
            }
            catch (Exception $e)
            {
                $con->rollBack();
                return $this->logSection('s4HanaIntegrationTables', 'Error creating bs_claim_headers table >> '.$e->getMessage());
            }

            return $this->logSection('s4HanaIntegrationTables', 'Successfully created bs_claim_headers table!');
        }
    }

    protected function createClaimItemTable($con)
    {
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = 'bs_claim_items');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('s4HanaIntegrationTables', 'Table bs_claim_items already exists!');
        }
        else
        {
            $queries = [
                "CREATE TABLE bs_claim_items (batch_number BIGINT NOT NULL, project_id BIGINT NOT NULL, claim_certificate_id BIGINT NOT NULL,
                contract_number TEXT NOT NULL, claim_number INT NOT NULL, item_id BIGINT NOT NULL, item_title TEXT NOT NULL, item_type VARCHAR(80) NOT NULL,
                total NUMERIC(18,5) DEFAULT 0, claim_amount NUMERIC(18,5) DEFAULT 0,
                approved_date TIMESTAMP NOT NULL, created_at TIMESTAMP NOT NULL, PRIMARY KEY(batch_number, project_id, claim_certificate_id, item_id, item_type))",
                "CREATE INDEX bs_claim_items_primary_idx ON bs_claim_items (batch_number, project_id, claim_certificate_id, item_id, item_type)",
                "CREATE INDEX bs_claim_items_batch_number_idx ON bs_claim_items (batch_number)",
                "ALTER TABLE bs_claim_items ADD CONSTRAINT bs_claim_items_project_id_fk FOREIGN KEY (project_id) REFERENCES bs_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE",
                "ALTER TABLE bs_claim_items ADD CONSTRAINT bs_claim_items_claim_cert_id_fk FOREIGN KEY (claim_certificate_id) REFERENCES bs_claim_certificates(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE"
            ];

            try
            {
                $con->beginTransaction();

                foreach ($queries as $query )
                {
                    $stmt = $con->prepare($query);
        
                    $stmt->execute();
                }

                $con->commit();
            }
            catch (Exception $e)
            {
                $con->rollBack();
                return $this->logSection('s4HanaIntegrationTables', 'Error creating bs_claim_items table >> '.$e->getMessage());
            }

            $this->logSection('s4HanaIntegrationTables', 'Successfully created bs_claim_items table!');
        }
    }

    protected function createIntegrationExportedClaimCertificateTable($con)
    {
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = 'bs_integration_exported_claim_certificates');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('s4HanaIntegrationTables', 'Table bs_integration_exported_claim_certificates already exists!');
        }
        else
        {
            $queries = [
                "CREATE TABLE bs_integration_exported_claim_certificates (batch_number BIGINT NOT NULL, claim_certificate_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, PRIMARY KEY(batch_number, claim_certificate_id))",
                "CREATE INDEX bs_integration_claim_cert_primary_idx ON bs_integration_exported_claim_certificates (batch_number, claim_certificate_id)",
                "CREATE INDEX bs_integration_claim_cert_batch_number_idx ON bs_integration_exported_claim_certificates (batch_number)",
                "ALTER TABLE bs_integration_exported_claim_certificates ADD CONSTRAINT bs_integration_claim_cert_claim_cert_id_fk FOREIGN KEY (claim_certificate_id) REFERENCES bs_claim_certificates(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE"
            ];

            try
            {
                $con->beginTransaction();

                foreach ($queries as $query )
                {
                    $stmt = $con->prepare($query);
        
                    $stmt->execute();
                }

                $con->commit();
            }
            catch (Exception $e)
            {
                $con->rollBack();
                return $this->logSection('s4HanaIntegrationTables', 'Error creating bs_integration_exported_claim_certificates table >> '.$e->getMessage());
            }

            return $this->logSection('s4HanaIntegrationTables', 'Successfully created bs_integration_exported_claim_certificates table!');
        }
    }
}
