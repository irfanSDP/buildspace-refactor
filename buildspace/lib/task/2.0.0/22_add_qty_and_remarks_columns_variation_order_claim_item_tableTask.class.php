<?php

class add_qty_and_remarks_column_variation_order_claim_item_tableTask extends sfBaseTask
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
        $this->name                = '2_0_0-22-add_qty_and_remarks_column_variation_order_claim_item_table';
        $this->briefDescription    = '';
        $this->detailedDescription = <<<EOF
The [2_0_0-22-add_qty_and_remarks_column_variation_order_claim_item_table|INFO] task does things.
Call it with:

  [php symfony 2_0_0-22-add_qty_and_remarks_column_variation_order_claim_item_table|INFO]
EOF;
    }

    protected function execute($arguments = array(), $options = array())
    {
        // initialize the database connection
        $databaseManager = new sfDatabaseManager($this->configuration);
        $con = $databaseManager->getDatabase($options['connection'])->getConnection();

        // check for table existence, if not then proceed with insertion query
        $stmt = $con->prepare("SELECT COUNT(*) FROM information_schema.columns
        WHERE table_schema = 'public' AND table_name = '".strtolower(VariationOrderClaimItemTable::getInstance()->getTableName())."' AND
        EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(VariationOrderClaimItemTable::getInstance()->getTableName())."' and column_name = 'current_quantity')
        AND EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(VariationOrderClaimItemTable::getInstance()->getTableName())."' and column_name = 'up_to_date_quantity')
        AND EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = 'public'
        AND table_name = '".strtolower(VariationOrderClaimItemTable::getInstance()->getTableName())."' and column_name = 'remarks');");

        $stmt->execute();

        $count = $stmt->fetch(PDO::FETCH_COLUMN, 0);

        if ( $count > 0 )
        {
            return $this->logSection('2_0_0-22-add_qty_and_remarks_column_variation_order_claim_item_table', 'Columns qty and remarks already exists in '.VariationOrderClaimItemTable::getInstance()->getTableName().' table!');
        }

        $stmt = $con->prepare("ALTER TABLE ".VariationOrderClaimItemTable::getInstance()->getTableName()." ADD COLUMN current_quantity NUMERIC(18,2) DEFAULT 0, ADD COLUMN up_to_date_quantity NUMERIC(18,2) DEFAULT 0, ADD COLUMN remarks text");

        $stmt->execute();

        //migrating existing variation order claim items data to set the qty values
        $stmt = $con->prepare("SELECT DISTINCT i.id, i.rate
        FROM ".VariationOrderItemTable::getInstance()->getTableName()." i
        JOIN ".VariationOrderClaimItemTable::getInstance()->getTableName()." c ON c.variation_order_item_id = i.id
        WHERE i.type = ".VariationOrderItem::TYPE_WORK_ITEM."
        AND i.rate <> 0 AND i.deleted_at IS NULL");

        $stmt->execute();

        $variationOrderItems = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmt = $con->prepare("SELECT c.variation_order_item_id, c.id, c.current_amount, c.up_to_date_amount
        FROM ".VariationOrderClaimItemTable::getInstance()->getTableName()." c
        WHERE c.deleted_at IS NULL");

        $stmt->execute();

        $variationOrderClaimItems = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);

        $currentQtySQLStr = null;
        $upToDateQtySQLStr = null;
        $claimItemsIds = array();

        foreach($variationOrderClaimItems as $variationOrderItemId => $claims)
        {
            if(array_key_exists($variationOrderItemId, $variationOrderItems))
            {
                foreach($claims as $claim)
                {
                    $currentQty = (abs($claim['current_amount']) > 0) ? round(abs($claim['current_amount']) / abs($variationOrderItems[$variationOrderItemId]), 2) : 0;
                    $upToDateQty = (abs($claim['up_to_date_amount']) > 0) ? round(abs($claim['up_to_date_amount']) / abs($variationOrderItems[$variationOrderItemId]), 2) : 0;

                    $currentQtySQLStr .= "WHEN ".$claim['id']." THEN ".$currentQty." ";
                    $upToDateQtySQLStr .= "WHEN ".$claim['id']." THEN ".$upToDateQty." ";

                    $claimItemsIds[] = $claim['id'];
                }
            }
        }

        if(!empty($currentQtySQLStr) && !empty($claimItemsIds))
        {
            $stmt = $con->prepare("UPDATE ".VariationOrderClaimItemTable::getInstance()->getTableName()."
            SET current_quantity = CASE id ".$currentQtySQLStr."
            ELSE current_quantity
            END
            WHERE id IN(". implode(",", $claimItemsIds) .") AND deleted_at IS NULL");

            $stmt->execute();
        }

        if(!empty($upToDateQtySQLStr) && !empty($claimItemsIds))
        {
            $stmt = $con->prepare("UPDATE ".VariationOrderClaimItemTable::getInstance()->getTableName()."
            SET up_to_date_quantity = CASE id ".$upToDateQtySQLStr."
            ELSE up_to_date_quantity
            END
            WHERE id IN(". implode(",", $claimItemsIds) .") AND deleted_at IS NULL");

            $stmt->execute();
        }

        return $this->logSection('2_0_0-22-add_qty_and_remarks_column_variation_order_claim_item_table', 'Successfully added columns qty and remarks in '.VariationOrderClaimItemTable::getInstance()->getTableName().' table!');
    }
}
