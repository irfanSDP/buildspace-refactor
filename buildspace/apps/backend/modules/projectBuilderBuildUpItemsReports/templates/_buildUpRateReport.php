<table cellpadding="0" cellspacing="0" class="mainTable">
<tr>
    <?php $rowCount = 0; $headerCount = 10; ?>
    <td colspan="<?php echo $headerCount; ?>">
        <?php include_partial('bqReportHeader', array(
            'reportTitle'  => $printingPageTitle,
            'topLeftRow1'  => NULL,
            'topRightRow1' => $columnDescription,
            'topLeftRow2'  => $elementTitle,
            'topRightRow2' => $billDescription,
        ));
        ?>
    </td>
</tr>
<tr>
    <td class="bqHeadCell" style="min-width:80px;width:80px;">No</td>
    <td class="bqHeadCell" style="min-width:400px;width:auto;">Description</td>
    <td class="bqHeadCell" style="min-width:80px;width:80px;">Number</td>
    <td class="bqHeadCell" style="min-width:80px;width:80px;">Constant</td>
    <td class="bqHeadCell" style="min-width:80px;width:80px;">Qty</td>
    <td class="bqHeadCell" style="min-width:80px;width:80px;">Unit</td>
    <td class="bqHeadCell" style="min-width:80px;width:80px;">Rate</td>
    <td class="bqHeadCell" style="min-width:80px;width:80px;">Total</td>
    <td class="bqHeadCell" style="min-width:80px;width:80px;">Wastage</td>
    <td class="bqHeadCell" style="min-width:80px;width:80px;">Line Total</td>
</tr>

<?php foreach ( $billItemInfos as $billItemInfo ): ?>
    <tr>
        <td class="bqCounterCell" style="border-right: none;">&nbsp;</td>
        <td class="bqDescriptionCell" style="border-right: none;"><pre class="description"><?php echo $billItemInfo[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_DESCRIPTION]; ?></pre></td>

        <td class="bqRateCell" style="border-right: none;">&nbsp;</td>
        <td class="bqRateCell" style="border-right: none;">&nbsp;</td>
        <td class="bqRateCell" style="border-right: none;">&nbsp;</td>

        <?php if ( $billItemInfo[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_ID] > 0 ): ?>
            <td class="bqUnitCell" style="border-right: none;"><?php echo $billItemUOM; ?></td>
        <?php else: ?>
            <td class="bqUnitCell" style="border-right: none;">&nbsp;</td>
        <?php endif; ?>

        <td class="bqRateCell" style="border-right: none;">&nbsp;</td>
        <td class="bqRateCell" style="border-right: none;">&nbsp;</td>

        <?php if ( $billItemInfo[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_ID] > 0 ): ?>
            <td class="bqRateCell" style="border-right: none;">&nbsp;</td>
        <?php else: ?>
            <td class="bqRateCell" style="border-right: none;">&nbsp;</td>
        <?php endif; ?>

        <?php if ( $billItemInfo[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_ID] > 0 ): ?>
            <td class="bqRateCell">
                <strong>
                    <?php if ( isset($buildUpQuantitySummary['apply_conversion_factor']) AND $buildUpQuantitySummary['apply_conversion_factor'] ): ?>
                        <?php echo ! $printNoPrice && $buildUpQuantitySummary['final_cost'] && $buildUpQuantitySummary['final_cost'] != 0 ? number_format($buildUpQuantitySummary['final_cost'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                    <?php else: ?>
                        <?php echo ! $printNoPrice && $billItemRateValue && $billItemRateValue != 0 ? number_format($billItemRateValue, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                    <?php endif; ?>
                </strong>
            </td>
        <?php else: ?>
            <td class="bqRateCell">&nbsp;</td>
        <?php endif; ?>
    </tr>
    <?php $rowCount++; endforeach; ?>

<?php
$firstItem   = false;
$totalAmount = 0;

for($x=0; $x <= $maxRows; $x++):

    $itemPadding      = 6;
    $itemRow          = isset($itemPage[$x]) ? $itemPage[$x] : false;
    $headerClass      = null;
    $headerStyle      = null;
    $borderTopStyling = null;

    // column's formula color if available
    $numberColor   = 'color: black;';
    $constantColor = 'color: black;';
    $qtyColor      = 'color: black;';
    $rateColor     = 'color: black;';
    $wastageColor  = 'color: black;';

    $rowCount++;

    $number           = $itemRow ? $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_NUMBER]['final_value'] : NULL;
    $constant         = $itemRow ? $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_CONSTANT]['final_value'] : NULL;
    $qty              = $itemRow ? $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_QTY]['final_value'] : NULL;
    $rate             = $itemRow ? $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_RATE]['final_value'] : NULL;
    $wastage          = $itemRow ? $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_WASTAGE]['final_value'] : NULL;
    $unit             = $itemRow ? $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_UNIT] : NULL;

    $total            = $itemRow ? $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_TOTAL] : NULL;
    $lineTotal        = $itemRow ? $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_LINE_TOTAL] : NULL;
    $itemId           = $itemRow ? $itemRow[0] : null;
    $fontColorStyling = 'colorBlack';

    if ( ! $firstItem )
    {
        $borderTopStyling = 'border-top: 1px solid black;';
    }

    if ($itemRow and ($itemRow[4] == ResourceItem::TYPE_HEADER))
    {
        $headerClass = 'bqHead1';
        $headerStyle = 'text-decoration:underline;';
    }

    if ( $itemRow )
    {
        if ( $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_NUMBER]['has_formula'] )
        {
            $numberColor = 'color: #F78181;';
        }

        if ( $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_CONSTANT]['has_formula'] )
        {
            $constantColor = 'color: #F78181;';
        }

        if ( $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_QTY]['has_formula'] )
        {
            $qtyColor = 'color: #F78181;';
        }

        if ( $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_RATE]['has_formula'] )
        {
            $rateColor = 'color: #F78181;';
        }

        if ( $itemRow[sfBillItemBuildUpRateReportGenerator::ROW_BILL_ITEM_WASTAGE]['has_formula'] )
        {
            $wastageColor = 'color: #F78181;';
        }
    }
    ?>
    <tr>
        <td class="bqCounterCell" style="<?php echo $borderTopStyling; ?>"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
        <td class="bqDescriptionCell" style="padding-left: <?php echo $itemPadding; ?>px; <?php echo $borderTopStyling; ?>">
            <?php $preClass = $headerClass ? $headerClass : 'description'?>
            <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
        </td>

        <!-- Number -->
        <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $numberColor; ?>">
            <?php echo ! $printNoPrice && $number && $number != 0 ? number_format($number, 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
        </td>

        <!-- Constant -->
        <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $constantColor; ?>">
            <?php echo ! $printNoPrice && $constant && $constant != 0 ? number_format($constant, 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
        </td>

        <!-- Quantity -->
        <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $qtyColor; ?>">
            <?php echo ! $printNoPrice && $qty && $qty != 0 ? number_format($qty, 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
        </td>

        <!-- Unit -->
        <td class="bqUnitCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling; ?>">
            <?php echo $unit; ?>
        </td>

        <!-- Rate -->
        <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $rateColor; ?>">
            <?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
        </td>

        <!-- Total -->
        <td class="bqRateCell" style="<?php echo $borderTopStyling; ?>">
            <?php echo ! $printNoPrice && $total && $total != 0 ? number_format($total, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
        </td>

        <!-- Wastage -->
        <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $wastageColor; ?>">
            <?php echo ! $printNoPrice && $wastage && $wastage != 0 ? number_format($wastage, 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]).'%' : null ?>
        </td>

        <!-- Line Total -->
        <td class="bqRateCell" style="<?php echo $borderTopStyling; ?>">
            <?php echo ! $printNoPrice && $lineTotal && $lineTotal != 0 ? number_format($lineTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
        </td>
    </tr>
    <?php $firstItem = true; unset($itemRow, $itemPage[$x], $amount); endfor; ?>

<?php if ( $lastPage ): ?>
    <tr style="border-top: 1px solid black;">
        <td colspan="<?php echo $headerCount - 1; ?>" style="padding-right: 10px;">Total Cost</td>
        <td style="border: 1px solid black;"><?php echo ! $printNoPrice && $buildUpQuantitySummary['total_cost'] && $buildUpQuantitySummary['total_cost'] != 0 ? number_format($buildUpQuantitySummary['total_cost'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
    </tr>

    <?php if ( isset($buildUpQuantitySummary['apply_conversion_factor']) AND $buildUpQuantitySummary['apply_conversion_factor'] ) : ?>
        <tr>
            <td colspan="<?php echo $headerCount - 1; ?>" style="padding-right: 10px;">Conversion Factor (<?php echo $buildUpQuantitySummary['conversion_factor_operator']; ?>)</td>
            <td style="border: 1px solid black;"><?php echo ! $printNoPrice && $buildUpQuantitySummary['conversion_factor_amount'] && $buildUpQuantitySummary['conversion_factor_amount'] != 0 ? number_format($buildUpQuantitySummary['conversion_factor_amount'], 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
        </tr>
    <?php endif; ?>

    <tr>
        <td colspan="<?php echo $headerCount - 1; ?>" style="padding-right: 10px;">Mark Up %</td>
        <td style="border: 1px solid black;"><?php echo ! $printNoPrice && $buildUpQuantitySummary['markup'] && $buildUpQuantitySummary['markup'] != 0 ? number_format($buildUpQuantitySummary['markup'], 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
    </tr>
    <tr>
        <td style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
            Page <?php echo $pageCount; ?>
        </td>
    </tr>
<?php else: ?>
    <tr>
        <td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
            Page <?php echo $pageCount; ?>
        </td>
    </tr>
<?php endif; ?>
</table>
</body>
</html>