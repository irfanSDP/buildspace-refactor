<?php

class sfBuildspaceExportClaimsXML extends sfBuildspaceXMLGenerator
{
    protected $pdo;
    protected $project;
    protected $claimRevision;
    protected $standardClaims           = false;
    protected $preliminaryClaims        = false;
    protected $variationOrders          = false;
    protected $variationOrderItems      = false;
    protected $variationOrderClaimItems = false;
    protected $materialsOnSite          = false;
    protected $materialOnSiteItems      = false;

    const TAG_PROJECT                     = "PROJECT";
    const TAG_REVISION                    = "REVISION";
    const TAG_STANDARD_CLAIMS             = "STANDARDCLAIMS";
    const TAG_STANDARD_CLAIM              = "STANDARDCLAIM";
    const TAG_PRELIMINARY_CLAIMS          = "PRELIMINARYCLAIMS";
    const TAG_PRELIMINARY_CLAIM           = "PRELIMINARYCLAIM";
    const TAG_VARIATION_ORDERS            = "VARIATIONORDERS";
    const TAG_VARIATION_ORDER             = "VARIATIONORDER";
    const TAG_VARIATION_ORDER_ITEMS       = "VARIATIONORDERITEMS";
    const TAG_VARIATION_ORDER_ITEM        = "VARIATIONORDERITEM";
    const TAG_VARIATION_ORDER_CLAIM_ITEMS = "VARIATIONORDERCLAIMITEMS";
    const TAG_VARIATION_ORDER_CLAIM_ITEM  = "VARIATIONORDERCLAIMITEM";
    const TAG_MATERIALS_ON_SITE           = "MATERIALSONSITE";
    const TAG_MATERIAL_ON_SITE            = "MATERIALONSITE";
    const TAG_MATERIAL_ON_SITE_ITEMS      = "MATERIALONSITEITEMS";
    const TAG_MATERIAL_ON_SITE_ITEM       = "MATERIALONSITEITEM";
    const TAG_ATTACHMENTS                 = "ATTACHMENTS";
    const TAG_ATTACHMENT                  = "ATTACHMENT";
    const FOLDER_NAME_ATTACHMENTS         = "Attachments";

    public $attachmentFileInformation = array();
    public $itemAttachments           = array();

    function __construct($filename = null, $uploadPath = null, $extension = null, $deleteFile = null)
    {
        $this->pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        parent::__construct($filename, $uploadPath, $extension, $deleteFile);
    }

    protected function processPreliminaryClaims()
    {
        $this->createPreliminaryBillItemClaimsTag();

        $elements = DoctrineQuery::create()
            ->select('e.id, e.project_structure_id as bill_id')
            ->from('BillElement e')
            ->leftJoin('e.ProjectStructure bill')
            ->where('bill.root_id = ?', $this->project->id)
            ->addOrderBy('e.priority ASC')
            ->fetchArray();

        $bills = array();

        foreach($elements as $elementRecord)
        {
            if( ! array_key_exists($elementRecord['bill_id'], $bills) ) $bills[ $elementRecord['bill_id'] ] = Doctrine_Core::getTable('ProjectStructure')->find($elementRecord['bill_id']);

            $bill = $bills[ $elementRecord['bill_id'] ];

            $element = Doctrine_Core::getTable('BillElement')->find($elementRecord['id']);

            list(
                $billItems, $billItemTypeReferences, $billItemTypeRefFormulatedColumns, $initialCostings, $timeBasedCostings, $prevTimeBasedCostings, $workBasedCostings, $prevWorkBasedCostings, $finalCostings, $includeInitialCostings, $includeFinalCostings
                ) = PostContractBillItemRateTable::getDataStructureForPrelimBillItemList($this->project->PostContract, $element, $bill);

            foreach($billItems as $billItem)
            {
                $itemTotal              = $billItem['rate'] * $billItem['qty'];
                $billItem['item_total'] = Utilities::prelimRounding($itemTotal);

                PreliminariesClaimTable::calculateClaimRates($this->claimRevision, $billItem, $this->claimRevision, $initialCostings, $finalCostings, $timeBasedCostings, $workBasedCostings, $prevTimeBasedCostings, $prevWorkBasedCostings, $includeInitialCostings, $includeFinalCostings);

                $preliminaryBillItemClaimData = array(
                    'bill_item_id'        => $billItem['id'],
                    'bill_item_origin_id' => $billItem['tender_origin_id'],
                    'up_to_date_amount'   => $billItem['upToDateClaim-amount'],
                );

                if( $preliminaryBillItemClaimData['up_to_date_amount'] == 0 ) continue;

                parent::addChildTag($this->preliminaryClaims, self::TAG_PRELIMINARY_CLAIM, $preliminaryBillItemClaimData);
            }
        }
    }

    protected function processStandardClaims()
    {
        $this->createStandardClaimsTag();

        $stmt = $this->pdo->prepare("SELECT i.id as bill_item_id, i.tender_origin_id as bill_item_origin_id, ref.counter, cs.id as bill_column_setting_id, cs.tender_origin_id as bill_column_setting_origin_id, sc.current_percentage, sc.current_amount, sc.up_to_date_percentage, sc.up_to_date_amount, sc.up_to_date_qty
            FROM " . PostContractStandardClaimTable::getInstance()->getTableName() . " sc
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.id = sc.bill_item_id
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.id = i.element_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill ON bill.id = e.project_structure_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON p.id = bill.root_id
            JOIN " . PostContractStandardClaimTypeReferenceTable::getInstance()->getTableName() . " ref ON ref.id = sc.claim_type_ref_id
            JOIN " . BillColumnSettingTable::getInstance()->getTableName() . " cs ON cs.id = ref.bill_column_setting_id
            JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = sc.revision_id
            WHERE p.id = :projectId
            AND rev.version = :version
            AND NOT (sc.current_percentage = 0 AND sc.current_amount = 0 AND sc.up_to_date_percentage = 0 AND sc.up_to_date_amount = 0 AND sc.up_to_date_qty = 0)
            ORDER BY i.id ASC");

        $stmt->execute(array(
            'projectId' => $this->project->id,
            'version'   => $this->claimRevision->version,
        ));

        $standardClaimData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($standardClaimData as $standardClaim)
        {
            parent::addChildTag($this->standardClaims, self::TAG_STANDARD_CLAIM, $standardClaim);
        }
    }

    protected function processVariationOrders()
    {
        $this->createVariationOrdersTag();

        $stmt = $this->pdo->prepare("SELECT vo.id, vo.description, vo.priority
            FROM " . VariationOrderTable::getInstance()->getTableName() . " vo
            WHERE vo.project_structure_id = :projectId
            AND vo.is_approved = true
            AND vo.deleted_at IS NULL
            ORDER BY vo.priority ASC");

        $stmt->execute(array(
            'projectId' => $this->project->id,
        ));

        $variationOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $className = VariationOrderTable::getInstance()->getClassnameToReturn();

        $targetClassName = ImportedVariationOrderTable::getInstance()->getClassnameToReturn();

        foreach($variationOrders as $variationOrder)
        {
            parent::addChildTag($this->variationOrders, self::TAG_VARIATION_ORDER, $variationOrder);

            $this->processItemAttachments($variationOrder['id'], $className, $targetClassName);
        }
    }

    protected function processVariationOrderItems()
    {
        $this->createVariationOrderItemsTag();

        $stmt = $this->pdo->prepare("SELECT voi.id, voi.description, voi.variation_order_id, voi.type, voi.root_id, voi.lft, voi.rgt, voi.level, voi.priority, uom.symbol AS uom_symbol, voi.rate, voi.total_unit as total_unit, ROUND(voi.addition_quantity - voi.omission_quantity, 2) AS quantity
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " voi
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON vo.id = voi.variation_order_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON voi.uom_id = uom.id AND uom.deleted_at IS NULL
            WHERE vo.project_structure_id = :projectId
            AND vo.is_approved = true
            AND vo.deleted_at IS NULL AND voi.deleted_at IS NULL
            ORDER BY vo.priority ASC");

        $stmt->execute(array(
            'projectId' => $this->project->id,
        ));

        $variationOrderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $this->pdo->prepare("SELECT i.id,
        ROUND(COALESCE((i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate)), 2) AS nett_omission_addition
        FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
        JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON i.variation_order_id = vo.id
        WHERE vo.project_structure_id = :projectId AND i.type <> " . VariationOrderItem::TYPE_HEADER . " AND i.rate <> 0
        AND vo.deleted_at IS NULL AND i.deleted_at IS NULL");

        $stmt->execute(array(
            'projectId' => $this->project->id,
        ));

        $quantities = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $className = VariationOrderItemTable::getInstance()->getClassnameToReturn();

        $targetClassName = ImportedVariationOrderItemTable::getInstance()->getClassnameToReturn();

        foreach($variationOrderItems as $item)
        {
            $item['total_amount'] = $quantities[ $item['id'] ] ?? 0;

            parent::addChildTag($this->variationOrderItems, self::TAG_VARIATION_ORDER_ITEM, $item);

            $this->processItemAttachments($item['id'], $className, $targetClassName);
        }
    }

    protected function processVariationOrderClaimItems()
    {
        $this->createVariationOrderClaimItemsTag();

        $currentSelectedRevision = PostContractClaimRevisionTable::getCurrentProjectRevision($this->project->PostContract);

        $query = "SELECT claim.variation_order_item_id AS idx, claim.id, claim.variation_order_item_id,
            CASE WHEN ((voi.rate * voi.addition_quantity) - (voi.rate * voi.omission_quantity) < 0)
                THEN -1 * ABS(claim.up_to_date_amount)
                ELSE claim.up_to_date_amount
            END AS up_to_date_amount,
            CASE WHEN ((voi.rate * voi.addition_quantity) - (voi.rate * voi.omission_quantity) < 0)
                THEN -1 * ABS(claim.up_to_date_percentage)
                ELSE claim.up_to_date_percentage
            END AS up_to_date_percentage,
            CASE WHEN ((voi.rate * voi.addition_quantity) - (voi.rate * voi.omission_quantity) < 0)
                THEN -1 * ABS(claim.up_to_date_quantity)
                ELSE claim.up_to_date_quantity
            END AS up_to_date_quantity
            FROM " . VariationOrderClaimTable::getInstance()->getTableName() . " voc
            JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " claim ON claim.variation_order_claim_id = voc.id
            LEFT JOIN " . VariationOrderClaimClaimCertificateTable::getInstance()->getTableName() . " pivot ON pivot.variation_order_claim_id = claim.variation_order_claim_id
            LEFT JOIN " . ClaimCertificateTable::getInstance()->getTableName() . " cert ON cert.id = pivot.claim_certificate_id
            LEFT JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = cert.post_contract_claim_revision_id
            JOIN " . VariationOrderItemTable::getInstance()->getTableName() . " voi ON voi.id = claim.variation_order_item_id
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON vo.id = voi.variation_order_id
            JOIN (
                SELECT vo.id, MAX(rev.id) as max_rev_id
                FROM " . VariationOrderClaimTable::getInstance()->getTableName() . " claim
                LEFT JOIN " . VariationOrderClaimClaimCertificateTable::getInstance()->getTableName() . " pivot ON pivot.variation_order_claim_id = claim.id
                LEFT JOIN " . ClaimCertificateTable::getInstance()->getTableName() . " cert ON cert.id = pivot.claim_certificate_id
                LEFT JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.id = cert.post_contract_claim_revision_id
                JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON vo.id = claim.variation_order_id
                WHERE vo.project_structure_id = ".$this->project->id."
                AND (rev.version <= ".$this->claimRevision->version.")
                AND claim.deleted_at IS NULL AND vo.deleted_at IS NULL
                GROUP BY vo.id
            ) max_rev ON max_rev.id = vo.id
            WHERE (rev.id = max_rev.max_rev_id)
            AND claim.deleted_at IS NULL AND voi.deleted_at IS NULL AND vo.deleted_at IS NULL AND voc.deleted_at IS NULL
            ORDER BY voc.revision ASC";
        
        $stmt = $this->pdo->prepare($query);

        $stmt->execute();
        
        /*
         * The reason why fetch in group is to get the latest vo claim (by vo item id) for the current
         * post contract claim revision because vo claim can have multiple vo claim revisions under
         * the same post contract claim revision for the same vo item. This will result in an error while
         * importing the vo claim during e-claim submission because for each claim revisions we only store
         * unique (revision_id, vo_item_id). So for this situation we disregard the old vo claims and only
         * gets the latest vo claim.
         */

        $claimRecords = $stmt->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE|\PDO::FETCH_ASSOC);

        foreach($claimRecords as $claim)
        {
            parent::addChildTag($this->variationOrderClaimItems, self::TAG_VARIATION_ORDER_CLAIM_ITEM, $claim);
        }
    }

    protected function processMaterialsOnSite()
    {
        $this->createMaterialsOnSiteTag();

        $stmt = $this->pdo->prepare("SELECT mos.id, mos.description, mos.sequence
            FROM " . PostContractClaimTable::getInstance()->getTableName() . " mos
            JOIN " . ClaimCertificateTable::getInstance()->getTableName() . " cert ON cert.id = mos.claim_certificate_id
            WHERE mos.project_structure_id = {$this->project->id}
            AND mos.type = " . PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE . "
            AND mos.status = " . PostContractClaim::STATUS_APPROVED . "
            AND cert.post_contract_claim_revision_id = {$this->claimRevision->id}
            AND mos.deleted_at IS NULL
            ORDER BY mos.sequence ASC");

        $stmt->execute();

        $materialsOnSite = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $className = PostContractClaimTable::getInstance()->getClassnameToReturn();

        $targetClassName = ImportedMaterialOnSiteTable::getInstance()->getClassnameToReturn();

        foreach($materialsOnSite as $materialOnSite)
        {
            parent::addChildTag($this->materialsOnSite, self::TAG_MATERIAL_ON_SITE, $materialOnSite);

            $this->processItemAttachments($materialOnSite['id'], $className, $targetClassName);
        }
    }

    protected function processMaterialOnSiteItems()
    {
        $this->createMaterialOnSiteItemsTag();

        $stmt = $this->pdo->prepare("SELECT mosi.id, mosi.description, mos.id as material_on_site_id, COALESCE(uom.symbol, NULL) as uom_symbol, mosi.quantity, mosi.rate, pccmos.final_amount, pccmos.reduction_percentage, pccmos.reduction_amount, mosi.sequence, mosi.type, mosi.root_id, mosi.lft, mosi.rgt, mosi.level
            FROM " . PostContractClaimItemTable::getInstance()->getTableName() . " mosi
            JOIN " . PostContractClaimTable::getInstance()->getTableName() . " mos ON mos.id = mosi.post_contract_claim_id
            JOIN " . PostContractClaimMaterialOnSiteTable::getInstance()->getTableName() . " pccmos ON pccmos.post_contract_claim_item_id = mosi.id
            JOIN " . ClaimCertificateTable::getInstance()->getTableName() . " cert ON cert.id = mos.claim_certificate_id
            LEFT JOIN " . UnitOfMeasurementTable::getInstance()->getTableName() . " uom ON uom.id = mosi.uom_id
            WHERE mos.project_structure_id = {$this->project->id}
            AND mos.type = " . PostContractClaim::TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE . "
            AND mos.status = " . PostContractClaim::STATUS_APPROVED . "
            AND cert.post_contract_claim_revision_id = {$this->claimRevision->id}
            AND mos.deleted_at IS NULL
            AND mosi.deleted_at IS NULL
            ORDER BY mosi.sequence, mosi.lft, mosi.level");

        $stmt->execute();

        $materialOnSiteItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $className = PostContractClaimItemTable::getInstance()->getClassnameToReturn();

        $targetClassName = ImportedMaterialOnSiteItemTable::getInstance()->getClassnameToReturn();

        foreach($materialOnSiteItems as $materialOnSiteItem)
        {
            parent::addChildTag($this->materialOnSiteItems, self::TAG_MATERIAL_ON_SITE_ITEM, $materialOnSiteItem);

            $this->processItemAttachments($materialOnSiteItem['id'], $className, $targetClassName);
        }
    }

    public function process($claimRevision, $write = true)
    {
        $this->claimRevision = $claimRevision;
        $this->project       = $claimRevision->PostContract->ProjectStructure;

        parent::create(self::TAG_PROJECT, array( 'buildspaceId' => sfConfig::get('app_register_buildspace_id'), 'claimVersion' => $claimRevision->version ));

        $this->processStandardClaims();
        $this->processPreliminaryClaims();
        $this->processVariationOrders();
        $this->processVariationOrderItems();
        $this->processVariationOrderClaimItems();
        $this->processMaterialsOnSite();
        $this->processMaterialOnSiteItems();

        $this->processAttachments();

        if( $write )
            parent::write();
    }

    protected function createStandardClaimsTag()
    {
        $this->standardClaims = parent::createTag(self::TAG_STANDARD_CLAIMS);
    }

    protected function createPreliminaryBillItemClaimsTag()
    {
        $this->preliminaryClaims = parent::createTag(self::TAG_PRELIMINARY_CLAIMS);
    }

    protected function createVariationOrdersTag()
    {
        $this->variationOrders = parent::createTag(self::TAG_VARIATION_ORDERS);
    }

    protected function createVariationOrderItemsTag()
    {
        $this->variationOrderItems = parent::createTag(self::TAG_VARIATION_ORDER_ITEMS);
    }

    protected function createVariationOrderClaimItemsTag()
    {
        $this->variationOrderClaimItems = parent::createTag(self::TAG_VARIATION_ORDER_CLAIM_ITEMS);
    }

    protected function createMaterialsOnSiteTag()
    {
        $this->materialsOnSite = parent::createTag(self::TAG_MATERIALS_ON_SITE);
    }

    protected function createMaterialOnSiteItemsTag()
    {
        $this->materialOnSiteItems = parent::createTag(self::TAG_MATERIAL_ON_SITE_ITEMS);
    }

    protected function processItemAttachments($itemId, $itemClass, $targetItemClass)
    {
        $attachments = AttachmentsTable::getAttachments($itemId, $itemClass);

        if( count($attachments) < 1 ) return;

        $attachmentIndex = 0;

        foreach($attachments as $attachment)
        {
            $fileIdentifier = "{$itemClass}-{$itemId}-{$attachmentIndex}";

            $this->itemAttachments[] = array(
                'fileIdentifier' => $fileIdentifier,
                'filename'       => $attachment['filename'],
                'extension'      => $attachment['extension'],
                'filepath'       => $attachment['filepath'],
                'itemId'         => $itemId,
                'itemClass'      => $targetItemClass,
            );

            $attachmentIndex++;
        }
    }

    public function processAttachments()
    {
        $attachmentsTag = parent::createTag(self::TAG_ATTACHMENTS);

        $tempUploadPath = sfConfig::get('sf_sys_temp_dir');
        $webDir         = sfConfig::get('sf_web_dir');

        $this->attachmentFileInformation = array();

        foreach($this->itemAttachments as $attachmentInfo)
        {
            $filepath = $attachmentInfo['filepath'];
            $identifier = $attachmentInfo['fileIdentifier'];

            $ext = pathinfo($filepath, PATHINFO_EXTENSION);

            $filepath = $webDir . DIRECTORY_SEPARATOR . $filepath;

            $newFilePath = $tempUploadPath . $identifier . '.' . $ext;
            $newFileName = pathinfo($newFilePath, PATHINFO_FILENAME);
            $newDirName  = pathinfo($newFilePath, PATHINFO_DIRNAME);

            copy($filepath, $newFilePath);

            $this->attachmentFileInformation[] = array(
                'filename'  => $newFileName,
                'localname' => self::FOLDER_NAME_ATTACHMENTS . DIRECTORY_SEPARATOR . $newFileName,
                'extension' => $ext,
                'path'      => $newDirName . DIRECTORY_SEPARATOR,
            );

            unset($attachmentInfo['filepath']);

            parent::addChildTag($attachmentsTag, self::TAG_ATTACHMENT, $attachmentInfo);
        }

        return $this->attachmentFileInformation;
    }
}
