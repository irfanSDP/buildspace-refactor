<?php

    if ( isset($buildUpQuantitySummary['apply_conversion_factor']) AND $buildUpQuantitySummary['apply_conversion_factor'] )
    {
        $totalQtyPerColumnSetting = $buildUpQuantitySummary['final_quantity'];
    }

?>

<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php $rowCount = 0; $headerCount = 5 + count($dimensions); ?>
        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('printReport/buildUpQtyHeaderReport', array(
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
        <td class="bqHeadCell" style="min-width:400px;width:auto;"><?php echo 'Description'; ?></td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;"><?php echo 'Factor'; ?></td>

        <!-- this column of header will be dynamically generated -->
        <?php foreach ( $dimensions as $dimension ): ?>
        <td class="bqHeadCell" style="min-width:80px;width:80px;"><?php echo $dimension['name']; ?></td>
        <?php endforeach; ?>

        <td class="bqHeadCell" style="min-width:80px;width:80px;"><?php echo 'Total'; ?></td>
        <td class="bqHeadCell" style="min-width:40px;width:40px;"><?php echo 'Sign'; ?></td>
    </tr>

    <?php foreach ( $billItemInfos as $billItemInfo ): ?>
    <tr>
        <td class="bqCounterCell" style="border-right: none;">&nbsp;</td>
        <td class="bqDescriptionCell" style="border-right: none;"><pre class="description"><?php echo $billItemInfo[sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_BILL_ITEM_DESCRIPTION]; ?></pre></td>

        <?php if ( $billItemInfo[sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_BILL_ITEM_ID] > 0 ): ?>
        <td class="bqUnitCell" style="border-right: none;"><?php echo $billItemUOM; ?></td>
        <?php else: ?>
        <td class="bqUnitCell" style="border-right: none;">&nbsp;</td>
        <?php endif; ?>

        <?php if ( count($dimensions) > 0 ): ?>
        <td class="bqRateCell" style="border-right: none;" colspan="<?php echo count($dimensions); ?>">&nbsp;</td>
        <?php endif; ?>

        <?php if ( $billItemInfo[sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_BILL_ITEM_ID] > 0 ): ?>
        <td class="bqRateCell" style="border-right: none;"><?php echo ! $printNoPrice && $totalQtyPerColumnSetting && $totalQtyPerColumnSetting != 0 ? number_format($totalQtyPerColumnSetting, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
        <?php else: ?>
        <td class="bqRateCell" style="border-right: none;">&nbsp;</td>
        <?php endif; ?>

        <td class="bqRateCell">&nbsp;</td>
    </tr>
    <?php $rowCount++; endforeach; ?>

    <?php
        $firstItem   = false;
        $totalAmount = 0;

        for($x=0; $x <= $maxRows; $x++):

            $itemPadding      = 6;
            $itemRow          = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;
            $headerClass      = null;
            $headerStyle      = null;
            $borderTopStyling = null;
            $factorFontColor  = null;

            $rowCount++;

            $factor           = $itemRow ? $itemRow[sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_BILL_ITEM_FACTOR] : null;
            $total            = $itemRow ? $itemRow[sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_BILL_ITEM_TOTAL] : null;
            $sign             = $itemRow ? $itemRow[sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_BILL_ITEM_SIGN] : null;
            $itemId           = $itemRow ? $itemRow[0] : null;
            $fontColorStyling = ($sign == '-') ? 'colorRed' : 'colorBlack';

            if ( ! $firstItem )
            {
                $borderTopStyling = 'border-top: 1px solid black;';
            }

            if ($itemRow and ($itemRow[4] == sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_SOQ_HEADER_TYPE))
            {
                $headerStyle = 'font-weight:bold;text-decoration:underline;';
            }

            if ($itemRow and ($itemRow[4] == sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_SOQ_ITEM_TYPE))
            {
                $headerStyle = 'font-weight:bold;';
            }

            if ($itemRow and ($itemRow[4] == sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_SOQ_MEASUREMENT_TYPE))
            {
                $headerStyle = 'padding-left: 6px;';
            }

            if ($itemRow and ($itemRow[4] == ResourceItem::TYPE_HEADER))
            {
                $headerClass = 'bqHead'.$itemRow[3];
                $headerStyle = null;
            }

            if ( isset($factor['has_formula']) AND $factor['has_formula'] )
            {
                $factorFontColor = 'color: pink;';
            }
        ?>
        <tr>
            <td class="bqCounterCell" style="<?php echo $borderTopStyling; ?>"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
            <td class="bqDescriptionCell <?php echo $fontColorStyling; ?>" style="padding-left: <?php echo $itemPadding; ?>px; <?php echo $borderTopStyling; ?>">
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
            </td>
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $factorFontColor; ?>">
                <?php echo ! $printNoPrice && $factor['final_value'] && $factor['final_value'] != 0 ? number_format($factor['final_value'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>

            <!-- this column of header will be dynamically generated -->
            <?php foreach ( $dimensions as $dimension ): ?>
                <?php

                $formulatedColValue = 0;
                $fontColor          = NULL;

                if ( isset($itemRow[sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_FORMULATED_COLUMNS][$dimension['id'].'-dimension_column']) ) {
                    $formulatedColValue = $itemRow[sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_FORMULATED_COLUMNS][$dimension['id'].'-dimension_column']['final_value'];

                    if ( $itemRow[sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_FORMULATED_COLUMNS][$dimension['id'].'-dimension_column']['has_formula'] )
                    {
                        $fontColor = 'color: pink;';
                    }
                }
                ?>

                <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $fontColor; ?>">
                    <?php echo ! $printNoPrice && $formulatedColValue && $formulatedColValue != 0 ? number_format($formulatedColValue, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                </td>
            <?php endforeach; ?>

            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling; ?>">
                <?php echo ! $printNoPrice && $total && $total != 0 ? number_format($total, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
            <td class="bqUnitCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling; ?>">
                <?php echo ($itemRow[sfBuildSpaceVariationOrderBuildUpItemGenerator::ROW_BILL_ITEM_ID]) ? $sign : "&nbsp;"; ?>
            </td>
        </tr>
    <?php $firstItem = true; unset($itemPage[$x], $amount); endfor; ?>

    <?php if ( $lastPage AND isset($buildUpQuantitySummary['apply_conversion_factor']) AND $buildUpQuantitySummary['apply_conversion_factor'] ): ?>
    <tr style="border-top: 1px solid black;">
        <td colspan="<?php echo $headerCount - 2; ?>" style="padding-right: 10px;">Conversion Factor (<?php echo $buildUpQuantitySummary['conversion_factor_operator']; ?>)</td>
        <td style="border: 1px solid black;"><?php echo ! $printNoPrice && $buildUpQuantitySummary['conversion_factor_amount'] && $buildUpQuantitySummary['conversion_factor_amount'] != 0 ? number_format($buildUpQuantitySummary['conversion_factor_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="<?php echo $headerCount - 2; ?>" style="padding-right: 10px;">Final Quantity</td>
        <td style="border: 1px solid black;"><?php echo ! $printNoPrice && $buildUpQuantitySummary['final_quantity'] && $buildUpQuantitySummary['final_quantity'] != 0 ? number_format($buildUpQuantitySummary['final_quantity'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
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