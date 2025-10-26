<?php

class BudgetReport {

    public static function getProjectItemRates(ProjectStructure $project)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $currentClaimRevision = PostContractClaimRevisionTable::getCurrentProjectRevision($project->PostContract);

        $stmt = $pdo->prepare("SELECT i.id, SUM(COALESCE(i_type.total_quantity, 0)) as total_quantity, COALESCE(i_rates.rate, 0) as rate, std_claim.up_to_date_qty, COALESCE(fc.final_value, 0) as budget_rate
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " bill
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.project_structure_id = bill.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON p.id = bill.root_id
            JOIN " . PostContractTable::getInstance()->getTableName() . " pc ON pc.project_structure_id = p.id
            LEFT JOIN " . PostContractBillItemTypeTable::getInstance()->getTableName() . " i_type ON i_type.bill_item_id = i.id AND i_type.post_contract_id = pc.id
            LEFT JOIN " . PostContractBillItemRateTable::getInstance()->getTableName() . " i_rates ON i_rates.bill_item_id = i.id AND i_rates.post_contract_id = pc.id
            LEFT JOIN (
                SELECT i.id, SUM(COALESCE(std_claim.up_to_date_qty, 0)) as up_to_date_qty
                FROM " . BillItemTable::getInstance()->getTableName() . " i
                JOIN " . BillElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
                JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill on e.project_structure_id = bill.id
                JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON p.id = bill.root_id
                JOIN " . PostContractTable::getInstance()->getTableName() . " pc ON pc.project_structure_id = p.id
                LEFT JOIN " . PostContractStandardClaimTable::getInstance()->getTableName() . " std_claim ON std_claim.bill_item_id = i.id AND std_claim.revision_id = {$currentClaimRevision['id']}
                GROUP BY i.id
            ) AS std_claim ON std_claim.id = i.id
            LEFT JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " fc ON fc.relation_id = i.id AND fc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
            WHERE p.id = {$project->id}
            AND p.deleted_at IS NULL
            AND bill.deleted_at IS NULL
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            GROUP BY i.id, i_rates.rate, std_claim.up_to_date_qty, fc.final_value;");

        $stmt->execute();

        $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $output = array();

        foreach($rates as $rate)
        {
            $output[ $rate['id'] ] = array(
                'total_quantity'   => $rate['total_quantity'],
                'rate'             => $rate['rate'],
                'budget_rate'      => $rate['budget_rate'],
                'up_to_date_qty'   => $rate['up_to_date_qty'],
                'revenue'          => $rate['total_quantity'] * $rate['rate'],
                'budget'           => $rate['total_quantity'] * $rate['budget_rate'],
                'progress_revenue' => $rate['up_to_date_qty'] * $rate['rate'],
                'sub_con_quantity' => 0,
                'sub_con_rate'     => 0,
                'sub_con_budget'   => 0,
                'sub_con_cost'     => 0,
                'progress_cost'    => 0,
            );
        }

        return $output;
    }

    public static function getSubProjectItemRates(ProjectStructure $project)
    {
        $subProjects = ProjectStructureTable::getSubProjects($project);

        $subProjectIds = array_column($subProjects, 'id');

        if( empty( $subProjectIds ) ) return array();

        $implodedSubProjectIds = implode(',', array_values($subProjectIds));

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT i.id, p.id as project_id, i.tender_origin_id, SUM(COALESCE(i_type.total_quantity, 0)) as total_quantity, COALESCE(i_rates.rate, 0) as rate, std_claim.up_to_date_qty, COALESCE(fc.final_value, 0) as budget_rate
            FROM " . ProjectStructureTable::getInstance()->getTableName() . " bill
            JOIN " . BillElementTable::getInstance()->getTableName() . " e ON e.project_structure_id = bill.id
            JOIN " . BillItemTable::getInstance()->getTableName() . " i ON i.element_id = e.id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON p.id = bill.root_id
            JOIN " . PostContractTable::getInstance()->getTableName() . " pc ON pc.project_structure_id = p.id
            JOIN (
                SELECT i.id, SUM(COALESCE(std_claim.up_to_date_qty, 0)) as up_to_date_qty
                FROM " . BillItemTable::getInstance()->getTableName() . " i
                JOIN " . BillElementTable::getInstance()->getTableName() . " e on e.id = i.element_id
                JOIN " . ProjectStructureTable::getInstance()->getTableName() . " bill on e.project_structure_id = bill.id
                JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON p.id = bill.root_id
                JOIN " . PostContractTable::getInstance()->getTableName() . " pc ON pc.project_structure_id = p.id
                JOIN (
                    SELECT p.id, MAX(rev.id) as max_rev_id, pc.id as pc_id
                    FROM " . ProjectStructureTable::getInstance()->getTableName() . " p
                    JOIN " . PostContractTable::getInstance()->getTableName() . " pc ON pc.project_structure_id = p.id
                    JOIN " . PostContractClaimRevisionTable::getInstance()->getTableName() . " rev ON rev.post_contract_id = pc.id
                    WHERE p.id IN ({$implodedSubProjectIds})
                    GROUP BY p.id, pc.id
                ) AS max_rev ON max_rev.pc_id = pc.id AND max_rev.id = p.id
                LEFT JOIN " . PostContractStandardClaimTable::getInstance()->getTableName() . " std_claim ON std_claim.bill_item_id = i.id AND std_claim.revision_id = max_rev.max_rev_id
                GROUP BY i.id
            ) AS std_claim ON std_claim.id = i.id
            LEFT JOIN " . PostContractBillItemTypeTable::getInstance()->getTableName() . " i_type ON i_type.bill_item_id = i.id AND i_type.post_contract_id = pc.id
            LEFT JOIN " . PostContractBillItemRateTable::getInstance()->getTableName() . " i_rates ON i_rates.bill_item_id = i.id AND i_rates.post_contract_id = pc.id
            LEFT JOIN " . BillItemFormulatedColumnTable::getInstance()->getTableName() . " fc ON fc.relation_id = i.id AND fc.column_name = '" . BillItem::FORMULATED_COLUMN_RATE . "'
            WHERE p.id IN ({$implodedSubProjectIds})
            AND i.tender_origin_id IS NOT NULL
            AND p.deleted_at IS NULL
            AND bill.deleted_at IS NULL
            AND e.deleted_at IS NULL
            AND i.deleted_at IS NULL
            GROUP BY i.id, p.id, i_rates.rate, std_claim.up_to_date_qty, fc.final_value;");

        $stmt->execute();

        $itemRates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subPackageItemRates = array();

        foreach($itemRates as $itemRate)
        {
            $originInfo = ProjectStructureTable::extractOriginId($itemRate['tender_origin_id']);

            $originalItemId = $originInfo['origin_id'];

            if( ! array_key_exists($originalItemId, $subPackageItemRates) ) $subPackageItemRates[ $originalItemId ] = array();

            $subPackageItemRates[ $originalItemId ][ $itemRate['project_id'] ] = array(
                'id'               => $itemRate['id'],
                'total_quantity'   => 0,
                'rate'             => 0,
                'budget_rate'      => $itemRate['budget_rate'],
                'up_to_date_qty'   => $itemRate['up_to_date_qty'],
                'revenue'          => 0,
                'budget'           => 0,
                'progress_revenue' => 0,
                'sub_con_quantity' => $itemRate['total_quantity'],
                'sub_con_rate'     => $itemRate['rate'],
                'sub_con_budget'   => $itemRate['total_quantity'] * $itemRate['budget_rate'],
                'sub_con_cost'     => $itemRate['total_quantity'] * $itemRate['rate'],
                'progress_cost'    => $itemRate['up_to_date_qty'] * $itemRate['rate'],
            );
        }

        return $subPackageItemRates;
    }

    public static function getProjectVariationOrderItemRates(ProjectStructure $project)
    {
        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $currentClaimRevision = PostContractClaimRevisionTable::getCurrentProjectRevision($project->PostContract);

        $stmt = $pdo->prepare("SELECT i.id, p.id AS project_id, i.tender_origin_id, claim_items.up_to_date_quantity, i.rate,
            ROUND(COALESCE(SUM((i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate))), 2) AS nett_omission_addition
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON vo.id = i.variation_order_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON p.id = vo.project_structure_id
            LEFT JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " claim_items ON claim_items.variation_order_item_id = i.id
            LEFT JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " vo_claims ON vo_claims.id = claim_items.variation_order_claim_id
            LEFT JOIN (
                SELECT i.id,max(vo_claims.revision) as max_revision
                FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
                JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON vo.id = i.variation_order_id
                JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON p.id = vo.project_structure_id
                LEFT JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " vo_claims ON vo_claims.variation_order_id = vo.id
                WHERE vo.project_structure_id = {$project->id}
                AND vo_claims.status = " . VariationOrderClaim::STATUS_CLOSED . "
                GROUP BY i.id, i.description
            ) max_rev ON max_rev.id = i.id AND max_rev.max_revision = vo_claims.revision
            WHERE vo.project_structure_id = {$project->id}
            AND vo.is_approved = TRUE
            AND p.deleted_at IS NULL
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL
            GROUP BY i.id, p.id,claim_items.up_to_date_quantity;");

        $stmt->execute();

        $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $output = array();

        foreach($rates as $rate)
        {
            $output[ $rate['id'] ] = array(
                'revenue'          => $rate['nett_omission_addition'],
                'progress_revenue' => $rate['up_to_date_quantity'] * $rate['rate'],
                'sub_con_cost'     => 0,
                'progress_cost'    => 0,
            );
        }

        return $output;
    }

    public static function getSubProjectVariationOrderItemRates(ProjectStructure $project)
    {
        $subProjects = ProjectStructureTable::getSubProjects($project);

        $subProjectIds = array_column($subProjects, 'id');

        if( empty( $subProjectIds ) ) return array();

        $implodedSubProjectIds = implode(',', array_values($subProjectIds));

        $pdo = ProjectStructureTable::getInstance()->getConnection()->getDbh();

        $stmt = $pdo->prepare("SELECT i.id, p.id AS project_id, i.tender_origin_id, claim.up_to_date_qty, i.rate,
            ROUND(COALESCE((i.total_unit * i.addition_quantity * i.rate) - (i.total_unit * i.omission_quantity * i.rate)), 2) AS nett_omission_addition
            FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
            JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON vo.id = i.variation_order_id
            JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON p.id = vo.project_structure_id
            LEFT JOIN (
                SELECT i.id, (COALESCE(claim_item.up_to_date_quantity, 0)) AS up_to_date_qty
                FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
                JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON vo.id = i.variation_order_id
                JOIN (
                    SELECT i.id,max(vo_claims.revision) AS max_revision
                    FROM " . VariationOrderItemTable::getInstance()->getTableName() . " i
                    JOIN " . VariationOrderTable::getInstance()->getTableName() . " vo ON vo.id = i.variation_order_id
                    JOIN " . ProjectStructureTable::getInstance()->getTableName() . " p ON p.id = vo.project_structure_id
                    LEFT JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " vo_claims ON vo_claims.variation_order_id = vo.id
                    WHERE p.id IN ({$implodedSubProjectIds})
                    AND vo_claims.status = " . VariationOrderClaim::STATUS_CLOSED . "
                    GROUP BY i.id
                )
                AS max_rev ON max_rev.id = i.id
                LEFT JOIN " . VariationOrderClaimTable::getInstance()->getTableName() . " vo_claim ON vo_claim.variation_order_id = vo.id and vo_claim.revision = max_rev.max_revision
                LEFT JOIN " . VariationOrderClaimItemTable::getInstance()->getTableName() . " claim_item ON claim_item.variation_order_item_id = i.id AND claim_item.variation_order_claim_id = vo_claim.id
            ) AS claim ON claim.id = i.id
            WHERE p.id IN ({$implodedSubProjectIds})
            AND vo.is_approved = TRUE
            AND i.tender_origin_id IS NOT NULL
            AND p.deleted_at IS NULL
            AND vo.deleted_at IS NULL
            AND i.deleted_at IS NULL");

        $stmt->execute();

        $itemRates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subPackageItemRates = array();

        foreach($itemRates as $itemRate)
        {
            $originInfo = ProjectStructureTable::extractOriginId($itemRate['tender_origin_id']);

            $originalItemId = $originInfo['origin_id'];

            if( ! array_key_exists($originalItemId, $subPackageItemRates) ) $subPackageItemRates[ $originalItemId ] = array();

            $subPackageItemRates[ $originalItemId ][ $itemRate['project_id'] ] = array(
                'id'               => $itemRate['id'],
                'revenue'          => 0,
                'progress_revenue' => 0,
                'sub_con_cost'     => $itemRate['nett_omission_addition'],
                'progress_cost'    => $itemRate['up_to_date_qty'] * $itemRate['rate'],
            );
        }

        return $subPackageItemRates;
    }
}