<?php

class fixS4HanaExportedClaimsTask extends sfBaseTask
{
    protected function configure()
    {
        $this->addOptions(array(
            new sfCommandOption('application', "app", sfCommandOption::PARAMETER_REQUIRED, 'The application name', "backend")
        ));

        $this->namespace        = 's4hana';
        $this->name             = 'fixS4HanaExportedClaims';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [s4hana:fixS4HanaExportedClaims|INFO] fix incorrect exported bill claim current amount S4Hana.
Call it with:

  [php symfony s4hana:fixS4HanaExportedClaims|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $mainConn = $databaseManager->getDatabase('main_conn')->getConnection();

        $this->fixContractItemTable($mainConn);
        $this->fixClaimItemTable($mainConn);
    }

    protected function fixContractItemTable($con)
    {
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = 'bs_contract_items');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            $stmt = $con->prepare("SELECT DISTINCT cert.batch_number, cert.claim_certificate_id
            FROM bs_integration_exported_claim_certificates cert
            JOIN bs_claim_items ci ON ci.batch_number = cert.batch_number AND ci.claim_certificate_id = cert.claim_certificate_id
            ORDER BY cert.batch_number ASC");

            $stmt->execute();

            $exportedClaimCertificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $exportedClaimCertByBatches = [];
            foreach($exportedClaimCertificates as $exportedClaimCertificate)
            {
                if(!array_key_exists($exportedClaimCertificate['batch_number'], $exportedClaimCertByBatches))
                {
                    $exportedClaimCertByBatches[$exportedClaimCertificate['batch_number']] = [];
                }

                $exportedClaimCertByBatches[$exportedClaimCertificate['batch_number']][] = $exportedClaimCertificate['claim_certificate_id'];
            }

            unset($exportedClaimCertificates);

            $stmt = $con->prepare("SELECT DISTINCT ci.batch_number, ci.project_id, ci.item_id, ci.item_type, ci.previous_claim, ci.current_claim, cli.claim_certificate_id
            FROM bs_contract_items ci
            JOIN bs_claim_items cli ON cli.batch_number = ci.batch_number AND cli.project_id = ci.project_id AND cli.item_id = ci.item_id AND cli.item_type = ci.item_type
            WHERE ci.item_type = 'BILL'
            ORDER BY ci.batch_number ASC");

            $stmt->execute();

            $contractItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $contractItemByBatches = [];

            foreach($contractItems as $contractItem)
            {
                if(!array_key_exists($contractItem['batch_number'], $contractItemByBatches))
                {
                    $contractItemByBatches[$contractItem['batch_number']] = [];
                }

                if(!array_key_exists($contractItem['item_id'], $contractItemByBatches[$contractItem['batch_number']]))
                {
                    $contractItemByBatches[$contractItem['batch_number']][$contractItem['item_id']] = [];
                }

                $contractItemByBatches[$contractItem['batch_number']][$contractItem['item_id']] = $contractItem;
            }

            
            try
            {
                $con->beginTransaction();

                foreach($contractItemByBatches as $batchNumber => $contractItems)
                {
                    foreach($contractItems as $itemId => $contractItem)
                    {
                        $standardClaimBills = ClaimCertificateTable::getLatestStandardBillClaimsByClaimCertificateIds([$contractItem['claim_certificate_id']]);

                        if(array_key_exists($itemId, $standardClaimBills))
                        {
                            $this->logSection('Fix S4Hana Exported Contract Items', 'Fixing Batch Number >> '.$batchNumber);

                            $stmt = $con->prepare("UPDATE bs_contract_items SET previous_claim = ".$standardClaimBills[$itemId]['previous_amount'].", current_claim = ".$standardClaimBills[$itemId]['current_amount']."
                            WHERE batch_number = ".$batchNumber." AND project_id = ".$contractItem['project_id']." AND item_id = ".$contractItem['item_id']." AND item_type = 'BILL' ");

                            $stmt->execute();
                        }
                    }
                }
                
                $con->commit();
            }
            catch (Exception $e)
            {
                $con->rollBack();
                return $this->logSection('Fix S4Hana Exported Contract Items', 'Error fixing exported contract items >> '.$e->getMessage());
            }
        }
    }

    protected function fixClaimItemTable($con)
    {
        $stmt = $con->prepare("SELECT EXISTS(SELECT * FROM information_schema.tables WHERE table_schema = 'public'
        AND table_name = 'bs_claim_items');");

        $stmt->execute();

        $isTableExists = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $isTableExists )
        {
            $stmt = $con->prepare("SELECT DISTINCT ci.batch_number, ci.project_id, ci.claim_certificate_id, ci.item_id, ci.item_type, ci.total, ci.claim_amount
            FROM bs_claim_items ci
            WHERE ci.item_type = 'BILL'
            ORDER BY ci.batch_number ASC");

            $stmt->execute();

            $claimItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $claimItemByBatches = [];

            foreach($claimItems as $claimItem)
            {
                if(!array_key_exists($claimItem['batch_number'], $claimItemByBatches))
                {
                    $claimItemByBatches[$claimItem['batch_number']] = [];
                }

                $claimItemByBatches[$claimItem['batch_number']][] = $claimItem;
            }

            try
            {
                $con->beginTransaction();

                foreach($claimItemByBatches as $batchNumber => $claimItems)
                {
                    $claimCertIds = array_column($claimItems, 'claim_certificate_id');
                    $claimCertIds = array_values(array_unique($claimCertIds));

                    $standardClaimBills = ClaimCertificateTable::getStandardBillClaimsByClaimCertificateIds($claimCertIds);
                    
                    $this->logSection('Fix S4Hana Exported Claim Items', 'Fixing Batch Number >> '.$batchNumber);

                    foreach($claimItems as $claimItem)
                    {
                        if(array_key_exists($claimItem['claim_certificate_id'], $standardClaimBills) && array_key_exists($claimItem['item_id'], $standardClaimBills[$claimItem['claim_certificate_id']]))
                        {
                            $claim = $standardClaimBills[$claimItem['claim_certificate_id']][$claimItem['item_id']];
                            $stmt = $con->prepare("UPDATE bs_claim_items SET total = ".$claim['up_to_date_amount'].", claim_amount = ".$claim['current_amount']."
                            WHERE batch_number = ".$batchNumber." AND project_id = ".$claimItem['project_id']." AND claim_certificate_id = ".$claimItem['claim_certificate_id']." AND item_id = ".$claimItem['item_id']." AND item_type = 'BILL' ");

                            $stmt->execute();
                        }
                    }
                }
                
                $con->commit();
            }
            catch (Exception $e)
            {
                $con->rollBack();
                return $this->logSection('Fix S4Hana Exported Claim Items', 'Error fixing exported claim items >> '.$e->getMessage());
            }
        }
    }
}
