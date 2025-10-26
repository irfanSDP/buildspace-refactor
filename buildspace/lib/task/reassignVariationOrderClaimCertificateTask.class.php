<?php

class reasignVariationOrderClaimCertificateTask extends sfBaseTask
{
    protected $startTime;

    protected function configure()
    {
        $this->addArguments(array(
            new sfCommandArgument('variation-order-id', sfCommandArgument::REQUIRED, 'Variation Order ID'),
            new sfCommandArgument('buildspace-project-id', sfCommandArgument::REQUIRED, 'Buildspace Project ID')
        ));

        /*$this->addOptions(array(
            new sfCommandOption('application', "app", sfCommandOption::PARAMETER_REQUIRED, 'The application name', "backend")
        ));*/

        $this->namespace        = 'buildspace';
        $this->name             = 'reassignVariationOrdeClaimCert';
        $this->briefDescription = '';
        $this->detailedDescription = <<<EOF
The [buildspace:reassignVariationOrdeClaimCert|INFO] to reassigned VO claim revision to the latest claim cert.
Call it with:

  [php symfony buildspace:reassignVariationOrdeClaimCert|INFO]
EOF;
    }
    
    protected function execute($arguments = array(), $options = array())
    {
        ini_set('memory_limit','2048M');

        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $pdo = $databaseManager->getDatabase('main_conn')->getConnection();

        $this->startTime = microtime(true); 

        $project = ProjectStructureTable::getInstance()->find((int)$arguments['buildspace-project-id']);

        if(!$project or !$project->node->isRoot() or !$project->MainInformation->EProjectProject or !$project->PostContract)
        {
            $this->logSection('error', 'Invalid Project with project_id :'.$arguments['buildspace-project-id']);

            return $this->logSection('progress', 'END');
        }

        $this->logSection('info', 'Project Ref:'.$project->MainInformation->EProjectProject->reference);
        $this->logSection('info', 'Project Title:'.$project->MainInformation->title);

        $postContract = $project->PostContract;

        $stmt = $pdo->prepare("SELECT cert.id, rev.version, rev.id as claim_revision_id
        FROM ".ClaimCertificateTable::getInstance()->getTableName()." cert
        JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = cert.post_contract_claim_revision_id
        WHERE rev.post_contract_id = :postContractId
        AND rev.deleted_at IS NULL
        ORDER BY rev.version DESC");

        $stmt->execute(['postContractId' => $postContract->id]);

        $latestClaimRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        if(empty($latestClaimRecord))
        {
            return $this->logSection('error', 'No claim record found!');
        }

        $latestClaimCertificate = ClaimCertificateTable::getInstance()->find($latestClaimRecord['id']);

        $variationOrder = VariationOrderTable::getInstance()->find((int)$arguments['variation-order-id']);

        if(!$variationOrder or $variationOrder->project_structure_id != $project->id)
        {
            $this->logSection('error', 'Invalid Variation Order with id :'.$arguments['variation-order-id']);

            return $this->logSection('progress', 'END');
        }

        $stmt = $pdo->prepare("SELECT vo.id, voi.id AS variation_order_item_id, cert.id AS claim_certificate_id, rev.version,
        voc.id AS variation_order_claim_id,
        CASE WHEN ((voi.rate * voi.addition_quantity) - (voi.rate * voi.omission_quantity) < 0)
            THEN -1 * ABS(voci.current_amount)
            ELSE voci.current_amount
        END AS current_amount
        FROM ".VariationOrderItemTable::getInstance()->getTableName()." voi
        JOIN ".VariationOrderTable::getInstance()->getTableName()." vo ON voi.variation_order_id = vo.id
        JOIN ".VariationOrderClaimItemTable::getInstance()->getTableName()." voci ON voci.variation_order_item_id = voi.id
        JOIN ".VariationOrderClaimTable::getInstance()->getTableName()." voc ON voc.variation_order_id = vo.id AND voci.variation_order_claim_id = voc.id
        JOIN ".VariationOrderClaimClaimCertificateTable::getInstance()->getTableName()." vocx on vocx.variation_order_claim_id = voc.id
        JOIN ".ClaimCertificateTable::getInstance()->getTableName()." cert ON cert.id = vocx.claim_certificate_id
        JOIN ".PostContractClaimRevisionTable::getInstance()->getTableName()." rev ON rev.id = cert.post_contract_claim_revision_id
        WHERE vo.id = :variationOrderId
        AND vo.project_structure_id = :projectId
        AND rev.version = :version
        AND voci.current_amount <> 0
        AND vo.deleted_at IS NULL AND voi.deleted_at IS NULL
        AND voc.deleted_at IS NULL AND voci.deleted_at IS NULL
        AND rev.deleted_at IS NULL");

        $stmt->execute([
            'variationOrderId' => $variationOrder->id,
            'projectId'        => $project->id,
            'version'          => $latestClaimRecord['version'] - 1
        ]);

        $claimRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalClaim = 0;
        $claimCertificateId = 0;
        $variationOrderClaimIds = [];

        foreach($claimRecords as $claimRecord)
        {
            $claimCertificateId = $claimRecord['claim_certificate_id'];
            $variationOrderClaimIds[] = $claimRecord['variation_order_claim_id'];

            $totalClaim += $claimRecord['current_amount'];
        }

        $prevClaimCertificate = ClaimCertificateTable::getInstance()->find($claimCertificateId);
        if(!$prevClaimCertificate)
        {
            $this->logSection('error', 'No previous claim certificate');

            return $this->logSection('progress', 'END');
        }

        //reset vo claim to latest claim cert
        $stmt = $pdo->prepare("UPDATE bs_variation_order_claims_claim_certificates
        SET claim_certificate_id = CASE
            WHEN claim_certificate_id = ".$prevClaimCertificate->id." THEN ".$latestClaimCertificate->id."
            ELSE claim_certificate_id
        END
        WHERE variation_order_claim_id IN (".implode(',', $variationOrderClaimIds).")");

        $stmt->execute();

        //reset prev claim cert certified amount to deduct vo total claim
        $prevClaimCertificate->save(); 

        //update latest claim cert certified amount with vo total claim
        $latestClaimCertificate->save();

        $endTime = microtime(true); 

        $executionTime = ($endTime - $this->startTime); 

        $this->logSection('progress', $executionTime.' seconds');

        $this->logSection('progress', 'END');
    }
}
