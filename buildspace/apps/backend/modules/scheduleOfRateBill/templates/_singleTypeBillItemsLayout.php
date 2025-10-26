<?php $colSpan = 4; ?>

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
        <td class="bqHeadCell" style="min-width:35px;width:35px;">Item</td>
        <td class="bqHeadCell" style="min-width:320px;width:320px;">Description</td>
        <td class="bqHeadCell" style="min-width:50px;width:50px;">Unit</td>
        <td class="bqHeadCell" style="min-width:115px;width:115px;">Rate
            (<?php echo $currency->currency_code; ?>)
        </td>

    </tr>
    <?php
    /*
     * 0 - id
     * 1 - row index
     * 2 - description
     * 3 - level
     * 4 - type
     * 5 - unit
     * 6 - rate
     */
    $rowCount = 0;

    for($x = 0; $x < $maxRows; $x++):
        $itemPadding = 6;
        $headerClass = null;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[ $x ] : false;

        if( $itemRow[4] == sfBuildspaceScheduleOfRateBillPageGenerator::ROW_TYPE_ELEMENT AND ! $printElementInGrid )
        {
            $x++;
            continue;
        }

        if( $printElementInGridOnce AND $itemRow[ sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_TYPE ] == sfBuildspaceScheduleOfRateBillPageGenerator::ROW_TYPE_ELEMENT AND $pageCount > 1 )
        {
            $x++;
            continue;
        }

        $rowCount++;
        $rate = $itemRow ? $itemRow[ sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_RATE ] : null;

        if( $itemRow and ( $itemRow[ sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_TYPE ] == SupplyOfMaterialItem::TYPE_HEADER OR $itemRow[ sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_TYPE ] == SupplyOfMaterialItem::TYPE_HEADER_N ) )
        {
            $headerClass = 'bqHead' . $itemRow[ sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_LEVEL ];
        }
        elseif( $itemRow and $itemRow[ sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_TYPE ] == sfBuildspaceScheduleOfRateBillPageGenerator::ROW_TYPE_ELEMENT )
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
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[ sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_ROW_IDX ] : '&nbsp;'?></td>
            <td class="bqDescriptionCell <?php echo $headerClass?>"
                style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php $preClass = $headerClass ? $headerClass : 'description' ?>
                <?php echo $itemRow ? '<pre class="' . $preClass . '">' . trim($itemRow[ sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_DESCRIPTION ]) . '</pre>' : null ?>
            </td>

            <td class="bqUnitCell"><?php echo $itemRow ? $itemRow[ sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_UNIT ] : '&nbsp;' ?></td>

            <?php
            if( $rate && ( $rate != 0 ) )
            {
                $amount = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($rate);

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
        <td class="bqAmountCell"></td>
    </tr>
    <tr>
        <td class="footer" colspan="4"></td>
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