<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php
        $headerCount = 2;

        if(count($billColumnSettings))
        {
            $headerCount+= count($billColumnSettings) * 3;
        }

        if($withUnit)
        {
            foreach($billColumnSettings as $column)
            {
                $headerCount+= $column['quantity'];
            }
        }
        ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('printReport/postContractReportHeader', array('reportTitle' => $reportTitle, 'headerDescription' => $headerDescription, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" rowspan="3" style="min-width:80px;width:80px;">No.</td>
        <td class="bqHeadCell" rowspan="3" style="min-width:400px;width:400px;"><?php echo $descHeader; ?></td>
        <?php foreach($billColumnSettings as $column) :?>
            <?php
            if($withUnit){
                $colspan = 3 + $column['quantity'];
            }
            else
            {
                $colspan = 3;
            }
            ?>
            <td class="bqHeadCell" colspan="<?php echo $colspan; ?>" style="min-width:80px;width:80px;">
                <?php echo (strlen($column['name']) > 33) ? substr($column['name'],0,30).'...' : $column['name']; ?>
            </td>
        <?php endforeach;?>
    </tr>
    <tr>
        <?php foreach($billColumnSettings as $column) :?>
            <td class="bqHeadCell" rowspan="2" style="min-width:80px;width:80px;">
                <?php echo "Contract Amount"; ?>
            </td>
            <td class="bqHeadCell" colspan="2" style="min-width:80px;width:80px;">
                <?php echo "Work Done"; ?>
            </td>
            <?php if($withUnit): ?>
                <?php foreach($unitNames[$column['id']] as $unitName):?>
                    <td class="bqHeadCell" style="min-width:80px;width:80px;">
                        <?php echo (strlen($unitName) > 13) ? substr($unitName,0,10).'...' : $unitName;?>
                    </td>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach;?>
    </tr>
    <tr>
        <?php foreach($billColumnSettings as $column) :?>
            <td class="bqHeadCell" style="min-width:60px;width:60px;">
                <?php echo "%"; ?>
            </td>
            <td class="bqHeadCell" style="min-width:80px;width:80px;">
                <?php echo "Amount"; ?>
            </td>
            <?php if($withUnit): ?>
                <?php for($i = 1; $i <= $column['quantity']; $i++) :?>
                    <td class="bqHeadCell" style="min-width:60px;width:60px;">
                        <?php echo "%"; ?>
                    </td>
                <?php endfor; ?>
            <?php endif; ?>
        <?php endforeach;?>
    </tr>
    <?php

    $rowCount = 0;

    $totalAmount = 0;

    for($x=0; $x <= $maxRows; $x++):

        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        $rowCount++;

        $itemId = $itemRow ? $itemRow[0] : null;

        if ($itemRow and ($itemRow[4] == BillItem::TYPE_HEADER OR $itemRow[4] == BillItem::TYPE_HEADER_N))
        {
            $headerClass = 'bqHead'.$itemRow[3];
            $headerStyle = null;
        }
        elseif($itemRow and $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT)
        {
            $headerClass = 'elementHeader';
            $headerStyle = 'font-style: italic;';

            if ( $alignElementTitleToTheLeft )
            {
                $headerClass .= ' alignLeft';
            }
            else
            {
                $headerClass .= ' alignCenter';
            }
        }
        else
        {
            $headerClass = null;
            $headerStyle = null;
        }

        if ( $indentItem AND $itemRow AND ($itemRow[4] != sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $itemRow[4] != BillItem::TYPE_HEADER AND $itemRow[4] != BillItem::TYPE_HEADER_N) )
        {
            $itemPadding = 15;
        }
        else
        {
            $itemPadding = 6;
        }

        ?>
        <tr>
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
            <td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
            </td>

            <?php foreach($billColumnSettings as $column) :?>
                <td class="bqRateCell">
                    <?php echo $itemId > 0 && $elementTypeTotals && array_key_exists($column['id'], $elementTypeTotals[$itemId]) && $elementTypeTotals[$itemId][$column['id']]['grand_total'] != 0 ? number_format($elementTypeTotals[$itemId][$column['id']]['grand_total'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                </td>
                <td class="bqRateCell" style="text-align:center;">
                    <?php echo $itemId > 0 && $elementTypeTotals && array_key_exists($column['id'], $elementTypeTotals[$itemId]) && $elementTypeTotals[$itemId][$column['id']]['type_total_percentage'] != 0 ? number_format($elementTypeTotals[$itemId][$column['id']]['type_total_percentage'],2).'%' : null ?>
                </td>
                <td class="bqRateCell">
                    <?php echo $itemId > 0 && $elementTypeTotals && array_key_exists($column['id'], $elementTypeTotals[$itemId]) && $elementTypeTotals[$itemId][$column['id']]['type_total_up_to_date_amount'] != 0 ? number_format($elementTypeTotals[$itemId][$column['id']]['type_total_up_to_date_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                </td>
                <?php if($withUnit) : ?>
                    <?php if($itemId > 0 && array_key_exists($column['id'], $elementTypeTotals[$itemId]) && array_key_exists('unit_total', $elementTypeTotals[$itemId][$column['id']]) && count($elementTypeTotals[$itemId][$column['id']]['unit_total'])): ?>
                        <?php foreach( $elementTypeTotals[$itemId][$column['id']]['unit_total'] as $k => $value) :?>
                            <td class="bqRateCell" style="text-align:center;">
                                <?php echo !empty($value['unit_total_percentage']) ? number_format($value['unit_total_percentage'],2).'%' : '&nbsp;'; ?>
                            </td>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php for($i = 1; $i <= $column['quantity']; $i++) :?>
                            <td class="bqRateCell">
                                &nbsp;
                            </td>
                        <?php endfor; ?>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach;?>
        </tr>
        <?php unset($itemPage[$x], $amount);?>

    <?php endfor; ?>

    <?php if($printGrandTotal) : ?>
        <tr>
            <td class="footer" style="padding-right:5px;" colspan="<?php echo 2; ?>">
                <?php echo "Total "; ?> (<?php echo $currency->currency_code; ?>) :
            </td>
            <?php foreach($billColumnSettings as $column) :?>
                <td class="footerSumAmount">
                    <?php echo ($typeTotals && array_key_exists($column['id'], $typeTotals) && array_key_exists('total_per_unit', $typeTotals[$column['id']]) && $typeTotals[$column['id']]['total_per_unit'] != 0) ?  number_format($typeTotals[$column['id']]['total_per_unit'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                </td>
                <td class="footerSumAmount" style="text-align:center;">
                    <?php echo ($typeTotals && array_key_exists($column['id'], $typeTotals) && array_key_exists('up_to_date_percentage', $typeTotals[$column['id']]) && $typeTotals[$column['id']]['up_to_date_percentage'] != 0) ?  number_format($typeTotals[$column['id']]['up_to_date_percentage'],2).'%' : null ;?>
                </td>
                <td class="footerSumAmount">
                    <?php echo ($typeTotals && array_key_exists($column['id'], $typeTotals) && array_key_exists('up_to_date_amount', $typeTotals[$column['id']]) && $typeTotals[$column['id']]['up_to_date_amount'] != 0) ?  number_format($typeTotals[$column['id']]['up_to_date_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                </td>
                <?php if($withUnit) : ?>
                    <?php foreach( $typeTotals[$column['id']]['unit_totals'] as $k => $value) :?>
                        <td class="footerSumAmount">
                            <?php echo number_format($value['up_to_date_percentage'],2).'%'; ?>
                        </td>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach;?>
        </tr>
        <tr>
            <td style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
                Page <?php echo $pageCount; ?> of <?php echo $totalPage; ?>
            </td>
        </tr>
    <?php else: ?>
        <tr>
            <td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
                Page <?php echo $pageCount; ?> of <?php echo $totalPage; ?>
            </td>
        </tr>
    <?php endif;?>
</table>
</body>
</html>

