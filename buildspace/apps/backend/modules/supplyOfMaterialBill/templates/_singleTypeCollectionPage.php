<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <td colspan="6">
            <?php include_partial('singleTypeHeader', array( 'topLeftRow1' => $topLeftRow1, 'topRightRow1' => $topRightRow1, 'topLeftRow2' => $topLeftRow2, 'printAmountOnly' => false, 'printElementTitle' => $printElementTitle )); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:35px;width:35px;"></td>
        <td class="bqHeadCell"
            style="border-right:none!important;min-width:395px;width:395px;"><?php echo $descHeader; ?></td>
        <td class="bqHeadCell"
            style="border-left:none!important;border-right:none!important;min-width:10px;width:10px;"></td>
        <td class="bqHeadCell"
            style="border-left:none!important;border-right:1px solid #000;min-width:10px;width:10px;"></td>
        <td class="bqHeadCell"
            style="border-left:none!important;border-right:none!important;min-width:15px;width:15px;"></td>

        <td class="bqHeadCell"
            style="border-left:none!important;min-width:205px;width:205px;"><?php echo $amtHeader . " (" . $currency->currency_code . ")"; ?></td>
    </tr>

    <?php
    $rowCount = 0;

    for($x = 0; $x < $maxRows; $x++):
        $itemRow = array_key_exists($x, $collectionPage) ? $collectionPage[ $x ] : false;
        $rowCount++;

        if( ! $printElementInGrid AND $itemRow AND $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT )
        {
            $x++;
            continue;
        }

        if( $itemRow and $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT )
        {
            $descriptionClass = 'elementHeader';

            if( $alignElementTitleToTheLeft )
            {
                $descriptionClass .= ' alignLeft';
            }
            else
            {
                $descriptionClass .= ' alignCenter';
            }
        }
        elseif( $itemRow and $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_LAST_COLLECTION )
        {
            $descriptionClass = 'bqDescriptionCell collectionLastDescription';
        }
        elseif( $itemRow and $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_COLLECTION_TITLE )
        {
            $descriptionClass = 'bqDescriptionCell collectionTitle';
        }
        else
        {
            $descriptionClass = 'bqDescriptionCell';
        }
        ?>
        <tr>
            <td class="bqCounterCell"></td>
            <td class="<?php echo $descriptionClass?>" style="padding-left: 6px; border:none!important;">
                <pre><?php echo $itemRow ? trim($itemRow[0]) : '&nbsp;'?></pre>
            </td>
            <td colspan="2"></td>

            <?php

            if( $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_COLLECTION_TITLE )
            {
                if( $printGrandTotalQty )
                {
                    $rowAmount = isset( $itemRow[2] ) ? $itemRow[2] : null;
                }
                else
                {
                    $rowAmount = null;
                }

                //displays even if zero
                $rowAmount = isset( $rowAmount ) ? number_format($rowAmount, $priceFormatting[2], $priceFormatting[0], ( $amtCommaRemove ) ? '' : $priceFormatting[1]) : null;
            }
            else
            {
                if( $itemRow[2] instanceof SplFixedArray )
                {
                    $rowAmount = $itemRow[2][0][1];
                    $rowAmount = number_format($rowAmount, $priceFormatting[2], $priceFormatting[0], ( $amtCommaRemove ) ? '' : $priceFormatting[1]);
                }
                else
                {
                    $rowAmount = ( $printGrandTotalQty && ! is_null($itemRow[2]) ) ? number_format($itemRow[2], $priceFormatting[2], $priceFormatting[0], ( $amtCommaRemove ) ? '' : $priceFormatting[1]) : null;
                }
            }
            ?>

            <td class="bqAmountCell" style="border-left:1px solid #000;" colspan="2">
                <?php echo ! $printNoPrice ? $rowAmount : null; ?>
            </td>
        </tr>
        <?php unset( $rowAmount ); endfor; ?>
    <tr>
        <td class="bqCounterCell"></td>
        <td class="bqDescriptionCell" style="border:none!important;"></td>
        <td colspan="2"></td>

        <td class="bqAmountCell" style="border-left:1px solid #000;" colspan="2"></td>
    </tr>

    <?php if( $isLastPage ): ?>
        <tr>
            <?php
            if( $printGrandTotalQty )
            {
                $totalAmount = $collectionPage['total_amount'];
            }
            else
            {
                $totalAmount = null;
            }

            $totalAmount = number_format($totalAmount, $priceFormatting[2], $priceFormatting[0], ( $amtCommaRemove ) ? '' : $priceFormatting[1]);
            ?>

            <td class="footer" style="padding-right:5px;" colspan="4">Total (<?php echo $currency->currency_code; ?>)
            </td>

            <td class="footerSumAmount" colspan="2">
                <?php echo ! $printNoPrice ? $totalAmount : null; ?>
            </td>
        </tr>
    <?php else: ?>
        <tr>
            <?php
            if( $printGrandTotalQty )
            {
                $totalAmount = $collectionPage['total_amount'];
            }
            else
            {
                $totalAmount = null;
            }
            $totalAmount = number_format($totalAmount, $priceFormatting[2], $priceFormatting[0], ( $amtCommaRemove ) ? '' : $priceFormatting[1]);
            ?>

            <td class="footer" style="padding-right:5px;" colspan="4">Carried forward to next Collection page</td>

            <td class="footerSumAmount" colspan="2">
                <?php echo ! $printNoPrice ? $totalAmount : null; ?>
            </td>
        </tr>
    <?php endif; ?>

    <tr>
        <td colspan="6">
            <table cellpadding="0" cellspacing="0" class="footer-table" style="width: 100%;">
                <tr>
                    <td style="width: 40%;"
                    <td style="width: 40%;" class="leftFooter">&nbsp;</td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">&nbsp;</td>
                    <td style="width: 40%;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width: 40%;" class="leftFooter">&nbsp;</td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">
                        <?php echo trim("{$pageNoPrefix} {$pageCount}"); ?>
                    </td>
                    <td style="width: 40%; text-align: right;">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>