<?php $colSpan = 9; ?>

<table cellpadding="0" cellspacing="0" class="mainTable">
    <?php foreach($projectTitleRows as $projectTitleRow): ?>
        <tr>
            <td colspan="<?php echo $colSpan; ?>" style="text-align: left;">
                <?php echo '<pre>' . trim($projectTitleRow) . '</pre>'; ?>
            </td>
        </tr>
    <?php endforeach; ?>

    <tr>
        <td colspan="<?php echo $colSpan; ?>">&nbsp;</td>
    </tr>

    <tr>
        <td colspan="<?php echo $colSpan; ?>">
            <?php include_partial('singleTypeHeader', array(
                'printAmountOnly'          => false,
                'topLeftRow1'              => $topLeftRow1,
                'topRightRow1'             => $topRightRow1,
                'topLeftRow2'              => $topLeftRow2,
                'elementHeaderDescription' => $elementHeaderDescription,
                'printElementTitle'        => false,
                'printDollarAndCentColumn' => false,
            )); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:35px;width:35px;" rowspan="2">Item</td>
        <td class="bqHeadCell" style="min-width:320px;width:320px;" rowspan="2">Description</td>
        <td class="bqHeadCell" style="min-width:50px;width:50px;" rowspan="2">Unit</td>
        <td class="bqHeadCell" style="min-width:70px;width:70px;" rowspan="2">Estimated Qty (Incl. wastage)<br>(A)</td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;" rowspan="2">% of wastage allowed</td>
        <td class="bqHeadCell" colspan="3">Supply Rate (RM)</td>

        <td class="bqHeadCell"
            style="min-width:115px;width:115px;" rowspan="2">
            Amount (RM)<br>(C)=(A)X(B)
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">Contractor Rate<br>(X)</td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">Developer Rate<br>(Y)</td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">Difference<br>(B)=(Y)-(X)</td>
    </tr>
    <?php
    /*
     * 0 - id
     * 1 - row index
     * 2 - description
     * 3 - level
     * 4 - type
     * 5 - unit
     * 6 - estimated quantity
     * 7 - percentage wastage allowed
     * 8 - contractor rate
     * 9 - difference
     * 10 - amount
     */
    $rowCount = 0;
    $totalAmount = 0;

    for($x = 0; $x < $maxRows; $x++):
        $itemPadding = 6;
        $headerClass = null;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[ $x ] : false;

        if( $itemRow[4] == sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_TYPE_ELEMENT AND ! $printElementInGrid )
        {
            $x++;
            continue;
        }

        if( $printElementInGridOnce AND $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_TYPE ] == sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_TYPE_ELEMENT AND $pageCount > 1 )
        {
            $x++;
            continue;
        }

        $rowCount++;
        $rate = $itemRow ? $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_SUPPLY_RATE ] : null;
        $estimatedQuantity = $itemRow ? $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_ESTIMATED_QTY ] : null;
        $percentageWastage = $itemRow ? $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_PERCENTAGE_WASTAGE ] : null;
        $contractorRate = $itemRow ? $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_CONTRACTOR_SUPPLY_RATE ] : null;
        $difference = $itemRow ? $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_DIFFERENCE ] : null;
        $amount = $itemRow ? $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_AMOUNT ] : null;

        if( $itemRow and ( $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_TYPE ] == SupplyOfMaterialItem::TYPE_HEADER OR $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_TYPE ] == SupplyOfMaterialItem::TYPE_HEADER_N ) )
        {
            $headerClass = 'bqHead' . $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_LEVEL ];
        }
        elseif( $itemRow and $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_TYPE ] == sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_TYPE_ELEMENT )
        {
            $headerClass = 'elementHeader';

            if( $alignElementTitleToTheLeft )
            {
                $headerClass .= ' alignLeft';
            }
            else
            {
                $headerClass .= ' alignCenter';
            }
        }
        ?>
        <tr>
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_ROW_IDX ] : '&nbsp;'?></td>
            <td class="bqDescriptionCell <?php echo $headerClass?>"
                style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php if( $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_TYPE ] == sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_TYPE_PC_RATE ): ?>
                    <?php include_partial('printBQ/primeCostRateTable', array(
                        'printAmountOnly'  => false,
                        'currency'         => $currency,
                        'itemRow'          => $itemRow,
                        'priceFormatting'  => $priceFormatting,
                        'printNoPrice'     => $printNoPrice,
                        'printFullDecimal' => $printFullDecimal,
                        'rateCommaRemove'  => $rateCommaRemove
                    )) ?>
                <?php else: ?>
                    <?php $preClass = $headerClass ? $headerClass : 'description' ?>
                    <?php echo $itemRow ? '<pre class="' . $preClass . '">' . trim($itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_DESCRIPTION ]) . '</pre>' : null ?>
                <?php endif?>
            </td>

            <td class="bqUnitCell"><?php echo $itemRow ? $itemRow[ sfBuildspaceSupplyOfMaterialBillPageGenerator::ROW_BILL_ITEM_UNIT ] : '&nbsp;' ?></td>
            <td class="bqRateCell"><?php echo ( $estimatedQuantity AND $estimatedQuantity != 0 ) ? number_format($estimatedQuantity, $priceFormatting[2],
                    $priceFormatting[0],
                    ( $amtCommaRemove ) ? '' : $priceFormatting[1]) : null; ?></td>
            <td class="bqRateCell"><?php echo ( $percentageWastage AND $percentageWastage != 0 ) ? number_format($percentageWastage, $priceFormatting[2],
                    $priceFormatting[0],
                    ( $amtCommaRemove ) ? '' : $priceFormatting[1]) : null;?></td>
            <td class="bqRateCell"><?php echo ( $contractorRate AND $contractorRate != 0 ) ? number_format($contractorRate, $priceFormatting[2],
                    $priceFormatting[0],
                    ( $amtCommaRemove ) ? '' : $priceFormatting[1]) : null; ?></td>

            <td class="bqRateCell"><?php echo ( $rate AND $rate != 0 ) ? number_format($rate, $priceFormatting[2],
                    $priceFormatting[0],
                    ( $amtCommaRemove ) ? '' : $priceFormatting[1]) : null; ?></td>

            <td class="bqRateCell"><?php echo ( $difference AND $difference != 0 ) ? number_format($difference, $priceFormatting[2],
                    $priceFormatting[0],
                    ( $amtCommaRemove ) ? '' : $priceFormatting[1]) : null; ?></td>

            <?php
            if( $difference && $difference != 0 && $estimatedQuantity && $estimatedQuantity != 0 )
            {
                $amount = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($difference * $estimatedQuantity);
                $totalAmount += $amount;

                $amount = Utilities::displayScientific($amount, ( false ) ? 18 : 12, array(
                    'decimal_places'     => $priceFormatting[2],
                    'decimal_points'     => $priceFormatting[0],
                    'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
                ), $printFullDecimal);
            }
            ?>

            <td class="bqAmountCell">
                <?php echo ( ( ! $printNoPrice ) && ( isset( $amount ) ) && ( $amount != 0 ) ) ? $amount : null;
                ?>
            </td>
        </tr>
        <?php unset( $itemPage[ $x ], $amount );?>
    <?php endfor; ?>

    <tr>
        <td class="bqCounterCell">&nbsp;</td>
        <td class="bqDescriptionCell"></td>

        <td class="bqUnitCell"></td>
        <td class="bqRateCell"></td>
        <td class="bqRateCell"></td>
        <td class="bqRateCell"></td>
        <td class="bqRateCell"></td>
        <td class="bqRateCell"></td>

        <td class="bqAmountCell"></td>
    </tr>
    <tr>
        <td class="footer" style="padding-right:5px;" colspan="8"><?php echo $toCollection; ?>
            (<?php echo $currency->currency_code; ?>)
        </td>

        <?php
        if( $totalAmount != 0 )
        {
            $totalAmount = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($totalAmount);
            $printAmountOnly = isset( $printAmountOnly ) ? $printAmountOnly : false;
            $totalAmount = Utilities::displayScientific($totalAmount, ( $printAmountOnly ) ? 20 : 11, array(
                'decimal_places'     => $priceFormatting[2],
                'decimal_points'     => $priceFormatting[0],
                'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
            ), $printFullDecimal);
        }
        else
        {
            $totalAmount = null;
        }
        ?>

        <td class="footerSumAmount"><?php echo ! $printNoPrice ? $totalAmount : null; ?></td>
    </tr>
    <tr>
        <td colspan="<?php echo $colSpan; ?>">
            <table cellpadding="0" cellspacing="0" class="footer-table" style="width: 100%;">
                <tr>
                    <td style="width: 40%;" class="leftFooter">&nbsp;</td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">&nbsp;</td>
                    <td style="width: 40%;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width: 40%;" class="leftFooter">&nbsp;</td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">
                        <?php echo trim("{$pageNoPrefix}{$elementCount}/{$pageCount}"); ?>
                    </td>
                    <td style="width: 40%; text-align: right;">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>