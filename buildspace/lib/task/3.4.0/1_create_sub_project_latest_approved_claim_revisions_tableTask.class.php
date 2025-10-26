<?php

class create_sub_project_latest_approved_claim_revisions_tableTask extends sfBaseTask
{
    protected $matchedSubProjectClaimRevisions = [];

    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'BuildSpace'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'main_conn'),
            // add your own options here
        ));

        $this->namespace           = 'buildspace';
        $this->name                = '3_4_0-1-sub_project_latest_approved_claim_revisions_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [3_4_0-1-sub_project_latest_approved_claim_revisions_table|INFO] task does things.
Call it with:

  [php symfony 3_4_0-1-sub_project_latest_approved_claim_revisions_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        $tableName = SubProjectLatestApprovedClaimRevisionTable::getInstance()->getTableName();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = '".strtolower($tableName)."');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            return $this->logSection('3_4_0-1-sub_project_latest_approved_claim_revisions_table', "Table {$tableName} already exists!");
        }

        $queries = array(
            "CREATE TABLE {$tableName} (id BIGSERIAL, main_project_id BIGINT NOT NULL, main_project_claim_revision_id BIGINT NOT NULL, sub_project_id BIGINT NOT NULL, sub_project_claim_revision_id BIGINT NOT NULL, created_at TIMESTAMP NOT NULL, updated_at TIMESTAMP NOT NULL, PRIMARY KEY(id));",
            "CREATE UNIQUE INDEX sp_latest_approved_claim_rev_unique_idx ON {$tableName} (sub_project_claim_revision_id);",
            "CREATE INDEX sp_latest_approved_claim_rev_sub_project_claim_rev_id_fk_idx ON {$tableName} (sub_project_claim_revision_id);",
            "CREATE INDEX sp_latest_approved_claim_rev_main_project_claim_rev_id_fk_idx ON {$tableName} (main_project_claim_revision_id);",
            "CREATE INDEX sp_latest_approved_claim_rev_idx ON {$tableName} (id);",
            "ALTER TABLE {$tableName} ADD CONSTRAINT sp_latest_approved_claim_rev_sub_project_claim_revision_id_fk FOREIGN KEY (sub_project_claim_revision_id) REFERENCES BS_post_contract_claim_revisions(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT sp_latest_approved_claim_rev_sub_project_id_fk FOREIGN KEY (sub_project_id) REFERENCES BS_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT sp_latest_approved_claim_rev_main_project_claim_revision_id_fk FOREIGN KEY (main_project_claim_revision_id) REFERENCES BS_post_contract_claim_revisions(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
            "ALTER TABLE {$tableName} ADD CONSTRAINT sp_latest_approved_claim_rev_main_project_id_fk FOREIGN KEY (main_project_id) REFERENCES BS_project_structures(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;",
        );

        foreach ($queries as $query )
        {
            $stmt = $con->prepare($query);

            $stmt->execute();
        }

        $this->seed();

        return $this->logSection('3_4_0-1-sub_project_latest_approved_claim_revisions_table', "Successfully created {$tableName} table!");
    }

    protected function seedApprovedClaimCertificates($project)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT r.id, c.id as cert_id, l.created_at as approved_at
            FROM ".PostContractClaimRevisionTable::getInstance()->getTableName()." r
            JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c on c.post_contract_claim_revision_id = r.id
            JOIN ".ClaimCertificateApprovalLogTable::getInstance()->getTableName()." l on l.claim_certificate_id = c.id
            WHERE r.post_contract_id = " . $project->PostContract->id . "
            AND c.status = " . ClaimCertificate::STATUS_TYPE_APPROVED . "
            AND l.status = " . ClaimCertificate::STATUS_TYPE_APPROVED . "
            ORDER BY l.created_at ASC;"
        );

        $stmt->execute();

        $approvedMainProjectClaimRevisions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($approvedMainProjectClaimRevisions as $key => $claimRevision)
        {
            $approvedAt  = $claimRevision['approved_at'];
            $submittedAt = ContractManagementClaimVerifierTable::submittedAt($project, PostContractClaim::TYPE_CLAIM_CERTIFICATE, $claimRevision['cert_id']);

            $approvedMainProjectClaimRevisions[$key]['submitted_at'] = $submittedAt ? $submittedAt : $approvedAt;
        }

        usort($approvedMainProjectClaimRevisions, function ($item1, $item2) {
            return strtotime($item1['submitted_at']) <=> strtotime($item2['submitted_at']);
        });

        $insertStmt = new sfImportStatementGenerator();

        $insertStmt->createInsert(SubProjectLatestApprovedClaimRevisionTable::getInstance()->getTableName(), array(
            'main_project_id', 'main_project_claim_revision_id', 'sub_project_id', 'sub_project_claim_revision_id', 'created_at', 'updated_at'
        ));

        $subProjects = ProjectStructureTable::getSubProjects($project);

        foreach($subProjects as $subProject)
        {
            if(!$subProject->PostContract->exists()) continue;

            $stmt = $pdo->prepare("SELECT r.id, l.created_at as approved_at
                FROM ".PostContractClaimRevisionTable::getInstance()->getTableName()." r
                JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c on c.post_contract_claim_revision_id = r.id
                JOIN ".ClaimCertificateApprovalLogTable::getInstance()->getTableName()." l on l.claim_certificate_id = c.id
                WHERE r.post_contract_id = " . $subProject->PostContract->id . "
                AND c.status = " . ClaimCertificate::STATUS_TYPE_APPROVED . "
                AND l.status = " . ClaimCertificate::STATUS_TYPE_APPROVED . "
                ORDER BY l.created_at ASC;"
            );

            $stmt->execute();

            $approvedSubProjectClaimRevisions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($approvedMainProjectClaimRevisions as $mainProjectClaimRevision)
            {
                $matchingClaimRevision = null;

                foreach($approvedSubProjectClaimRevisions as $subProjectClaimRevision)
                {
                    if(strtotime($subProjectClaimRevision['approved_at']) > strtotime($mainProjectClaimRevision['submitted_at'])) break;

                    if(is_null($matchingClaimRevision))
                    {
                        $matchingClaimRevision = $subProjectClaimRevision;
                    }
                    {
                        $matchingClaimRevision = $subProjectClaimRevision;
                    }
                }

                if(!is_null($matchingClaimRevision) && !in_array($matchingClaimRevision['id'], $this->matchedSubProjectClaimRevisions))
                {
                    $insertStmt->addRecord(array(
                        $project->id, $mainProjectClaimRevision['id'], $subProject->id, $matchingClaimRevision['id'], 'NOW()', 'NOW()'
                    ));

                    // Record this sub project claim revision so that it won't be matched to another main project revision.
                    $this->matchedSubProjectClaimRevisions[] = $matchingClaimRevision['id'];
                }
            }
        }

        $insertStmt->save();
    }

    protected function seedPendingClaimCertificates($project)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT r.id, c.id as cert_id
            FROM ".PostContractClaimRevisionTable::getInstance()->getTableName()." r
            JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c on c.post_contract_claim_revision_id = r.id
            WHERE r.post_contract_id = " . $project->PostContract->id . "
            AND c.status = " . ClaimCertificate::STATUS_TYPE_PENDING_FOR_APPROVAL . ";"
        );

        $stmt->execute();

        $pendingMainProjectClaimRevisions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($pendingMainProjectClaimRevisions as $key => $mainProjectClaimRevision)
        {
            $pendingMainProjectClaimRevisions[$key]['submitted_at'] = ContractManagementClaimVerifierTable::submittedAt($project, PostContractClaim::TYPE_CLAIM_CERTIFICATE, $mainProjectClaimRevision['cert_id']);
        }

        usort($pendingMainProjectClaimRevisions, function ($item1, $item2) {
            return strtotime($item1['submitted_at']) <=> strtotime($item2['submitted_at']);
        });

        $insertStmt = new sfImportStatementGenerator();

        $insertStmt->createInsert(SubProjectLatestApprovedClaimRevisionTable::getInstance()->getTableName(), array(
            'main_project_id', 'main_project_claim_revision_id', 'sub_project_id', 'sub_project_claim_revision_id', 'created_at', 'updated_at'
        ));

        $subProjects = ProjectStructureTable::getSubProjects($project);

        foreach($subProjects as $subProject)
        {
            if(!$subProject->PostContract->exists()) continue;

            $stmt = $pdo->prepare("SELECT r.id, l.created_at as approved_at
                FROM ".PostContractClaimRevisionTable::getInstance()->getTableName()." r
                JOIN ".ClaimCertificateTable::getInstance()->getTableName()." c on c.post_contract_claim_revision_id = r.id
                JOIN ".ClaimCertificateApprovalLogTable::getInstance()->getTableName()." l on l.claim_certificate_id = c.id
                WHERE r.post_contract_id = " . $subProject->PostContract->id . "
                AND c.status = " . ClaimCertificate::STATUS_TYPE_APPROVED . "
                AND l.status = " . ClaimCertificate::STATUS_TYPE_APPROVED . "
                ORDER BY l.created_at DESC;"
            );

            $stmt->execute();

            $approvedSubProjectClaimRevisions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($pendingMainProjectClaimRevisions as $mainProjectClaimRevision)
            {
                foreach($approvedSubProjectClaimRevisions as $subProjectClaimRevision)
                {
                    if(strtotime($subProjectClaimRevision['approved_at']) > strtotime($mainProjectClaimRevision['submitted_at'])) continue;

                    if(!in_array($subProjectClaimRevision['id'], $this->matchedSubProjectClaimRevisions))
                    {
                        $insertStmt->addRecord(array(
                            $project->id, $mainProjectClaimRevision['id'], $subProject->id, $subProjectClaimRevision['id'], 'NOW()', 'NOW()'
                        ));

                        $this->matchedSubProjectClaimRevisions[] = $subProjectClaimRevision['id'];
                    }

                    break;
                }
            }
        }

        $insertStmt->save();
    }

    protected function seed()
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT p.id
            FROM ".ProjectStructureTable::getInstance()->getTableName()." p
            JOIN ".ProjectMainInformationTable::getInstance()->getTableName()." m ON m.project_structure_id = p.id
            WHERE m.status = ".ProjectMainInformation::STATUS_POSTCONTRACT."
            AND p.deleted_at IS NULL");

        $stmt->execute();

        $projectIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach($projectIds as $projectId)
        {
            $project = Doctrine_Core::getTable('ProjectStructure')->find($projectId);

            if(!$project->PostContract->exists()||!$project->MainInformation->getEProjectProject()) continue;

            $this->seedApprovedClaimCertificates($project);
            $this->seedPendingClaimCertificates($project);
        }

        $tableName = SubProjectLatestApprovedClaimRevisionTable::getInstance()->getTableName();

        $this->logSection('3_4_0-1-sub_project_latest_approved_claim_revisions_table', "Successfully seeded {$tableName} table!");
    }
}
