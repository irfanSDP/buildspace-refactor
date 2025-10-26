<?php

class RecalculateBuildUpSummaryCost extends Task{

    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    function run(){
        $configuration = ProjectConfiguration::getApplicationConfiguration( 'backend', 'dev', false);

        $dbm = new sfDatabaseManager($configuration);
        $db = $dbm->getDatabase('main_conn');

        //strip dsn info from doctrine to be used in pg_connect
        $search = array('pgsql:', ';');
        $dsn = str_replace($search, " ", $db->getParameter('dsn'));

        $str = $dsn.' user='.$db->getParameter('username').' password='.$db->getParameter('password');

        $conn = pg_connect($str) or die ("TaskManager RecalculateBuildUpSummaryCost --> " . pg_last_error($conn));

        try
        {
            foreach($this->params as $param)
            {
                $infoArray = array(
                    'bill_item_id' => $param['item_id'],
                    'bill_element_id' => $param['element_id'],
                    'summary_markup' => $param['summary_markup'],
                    'summary_rounding_type' => $param['summary_rounding_type'],
                    'summary_conversion_factor_amount' => $param['summary_conversion_factor_amount'],
                    'summary_conversion_factor_operator' => $param['summary_conversion_factor_operator'],
                    'summary_apply_conversion_factor' => $param['summary_apply_conversion_factor']
                );

                $sql = "UPDATE ".BillBuildUpRateSummaryTable::getInstance()->getTableName()."
                SET total_cost = (SELECT COALESCE(SUM(b.line_total),0)
                FROM ".BillBuildUpRateItemTable::getInstance()->getTableName()." b
                WHERE b.bill_item_id = ".BillBuildUpRateSummaryTable::getInstance()->getTableName().".bill_item_id AND b.deleted_at IS NULL) WHERE
                bill_item_id = ".$infoArray['bill_item_id']." AND deleted_at IS NULL RETURNING total_cost";

                $res = pg_query($conn, $sql);

                $totalCostRow = pg_fetch_row($res);
                $totalCost = $totalCostRow[0];

                $conversionFactorAmount = $infoArray['summary_conversion_factor_amount'];
                $operator = $infoArray['summary_conversion_factor_operator'];

                if($infoArray['summary_apply_conversion_factor'])
                {
                    $exp = $totalCost.$operator.$conversionFactorAmount;

                    $evaluator = new EvalMath(true, true);
                    $evaluator->suppress_errors = true;
                    $evaluatedValue = $evaluator->evaluate($exp);

                    $totalCost = $evaluatedValue ? $evaluatedValue : 0;
                }

                $markupPrice = $totalCost * ($infoArray['summary_markup'] / 100);
                $finalCost = $totalCost + $markupPrice;

                switch($infoArray['summary_rounding_type'])
                {
                    case BillBuildUpRateSummary::ROUNDING_TYPE_UPWARD:
                        $finalCost  = ceil($finalCost);
                    case BillBuildUpRateSummary::ROUNDING_TYPE_DOWNWARD:
                        $finalCost  =  floor($finalCost);
                    case BillBuildUpRateSummary::ROUNDING_TYPE_NEAREST_WHOLE_NUMBER:
                        $finalCost  =  round($finalCost);
                    case BillBuildUpRateSummary::ROUNDING_TYPE_NEAREST_TENTH:
                        $finalCost  =  round($finalCost * 10) / 10;
                    default:
                        $finalCost  =  number_format($finalCost, 2, '.', '');
                }

                $sql = "UPDATE ".BillBuildUpRateSummaryTable::getInstance()->getTableName()." SET final_cost = ".$finalCost." WHERE
                bill_item_id = ".$infoArray['bill_item_id']." AND final_cost <> ".$finalCost." AND deleted_at IS NULL";

                $res = pg_query($conn, $sql);

                $sql = "UPDATE ".BillItemFormulatedColumnTable::getInstance()->getTableName()." SET
                value = '".$finalCost."', final_value = ".$finalCost."
                WHERE relation_id = ".$infoArray['bill_item_id']." AND
                column_name = '".BillItem::FORMULATED_COLUMN_RATE."' AND deleted_at IS NULL";

                $res = pg_query($conn, $sql);

                $sql = "UPDATE ".BillItemTable::getInstance()->getTableName()." SET
                grand_total_quantity = (SELECT COALESCE(SUM(r.total_quantity), 0) FROM ".BillItemTypeReferenceTable::getInstance()->getTableName()."
                AS r LEFT JOIN ".BillColumnSettingTable::getInstance()->getTableName()." AS c ON r.bill_column_setting_id = c.id
                WHERE r.bill_item_id = ".$infoArray['bill_item_id']." AND r.include IS TRUE AND r.deleted_at IS NULL AND c.deleted_at IS NULL)
                WHERE id = ".$infoArray['bill_item_id']."";

                $res = pg_query($conn, $sql);

                $sql = "UPDATE ".BillItemTable::getInstance()->getTableName()." SET
                grand_total = (SELECT COALESCE(SUM(ifc.final_value * i.grand_total_quantity), 0) FROM ".BillItemFormulatedColumnTable::getInstance()->getTableName()." AS ifc
                LEFT JOIN ".BillItemTable::getInstance()->getTableName()." AS i ON ifc.relation_id = i.id
                WHERE i.id = ".$infoArray['bill_item_id']." AND ifc.column_name = '".BillItem::FORMULATED_COLUMN_RATE."' AND ifc.final_value <> 0
                AND i.deleted_at IS NULL AND ifc.deleted_at IS NULL)
                WHERE id = ".$infoArray['bill_item_id']."";

                $res = pg_query($conn, $sql);

                $sql = "SELECT final_value FROM ".BillItemFormulatedColumnTable::getInstance()->getTableName()." WHERE
                relation_id = ".$infoArray['bill_item_id']." AND column_name = '".BillItem::FORMULATED_COLUMN_MARKUP_PERCENTAGE."' AND deleted_at IS NULL LIMIT 1";

                $result = pg_query($conn, $sql);

                while($row = pg_fetch_row($result))
                {
                    $markupAmount = ($row[0] / 100);

                    $sql = "UPDATE ".BillItemFormulatedColumnTable::getInstance()->getTableName()." SET
                    final_value = (SELECT COALESCE(ROUND(i.grand_total * ".$markupAmount.", 5), 0) FROM ".BillItemTable::getInstance()->getTableName()." AS i
                    WHERE i.id = ".$infoArray['bill_item_id']."
                    AND i.deleted_at IS NULL),
                    value = (SELECT COALESCE(ROUND(i.grand_total * ".$markupAmount.", 5), 0) FROM ".BillItemTable::getInstance()->getTableName()." AS i
                    WHERE i.id = ".$infoArray['bill_item_id']."
                    AND i.deleted_at IS NULL)
                    WHERE relation_id = ".$infoArray['bill_item_id']." AND column_name = '".BillItem::FORMULATED_COLUMN_MARKUP_AMOUNT."' AND deleted_at IS NULL";

                    $res = pg_query($conn, $sql);
                }
            }

            pg_close($conn);
        }
        catch (Exception $e)
        {
            ob_start();//prevent output to main process
            register_shutdown_function(create_function('$pars', 'ob_end_clean();posix_kill(getmypid(), SIGKILL);'), array());//to kill self before exit();, or else the resource shared with parent will be closed

            exit(1);
        }
    }
}
?>