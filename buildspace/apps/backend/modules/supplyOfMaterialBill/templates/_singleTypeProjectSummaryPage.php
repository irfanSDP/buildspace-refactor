<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <td colspan="<?php echo ($printDollarAndCentColumn) ? 5 : 4; ?>">
            <table cellpadding="0" cellspacing="0" class="headerTable" style="width: 100%;">
                <tr>
                    <td class="leftHeader" style="width: 40%;"><?php echo $topLeftRow1; ?></td>
                    <td class="rightHeader" style="width: 40%;"><?php echo $topRightRow1; ?></td>
                </tr>
                <tr>
                    <td class="leftHeader" style="width: 60%;"><?php echo $topLeftRow2; ?></td>
                    <td class="rightPageElement" style="width: 60%;"><?php echo $summaryHeaderDescription; ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:40px;width:40px;"></td>
        <td class="bqHeadCell" style="min-width:400px;width:400px;"><?php echo $descHeader; ?></td>

        <?php if ( $printDollarAndCentColumn ): ?>
        <td class="bqHeadCell" style="min-width:115px;width:215px;"><?php echo $currencyFormat[0]; ?></td>
        <td class="bqHeadCell" style="min-width:40px;width:40px;"><?php echo $currencyFormat[1]; ?></td>
        <?php else: ?>
        <td class="bqHeadCell" style="min-width:155px;width:255px;"><?php echo $amtHeader; ?></td>
        <?php endif; ?>
    </tr>
    <?php
    $rowCount = 0;

    for($x=0; $x < $maxRows; $x++):
        $itemRow = array_key_exists($x, $summaryPage) ? $summaryPage[$x] : false;
        $rowCount++;
    ?>
        <tr>
            <td class="bqCounterCell">&nbsp;</td>
            <td class="<?php echo ($itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_SUMMARY_PAGE_TITLE) ? 'summaryPageInGridHeader' : 'bqDescriptionCell'; ?>" style="border:none!important;">
                <pre><?php echo trim($itemRow[0]); ?></pre>
            </td>

            <?php
                $elementAmount = ($itemRow[2]) ? $itemRow[2] : 0;
                $elementAmount = number_format($elementAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]);
            ?>

            <?php if ( $printDollarAndCentColumn ): $elementAmount = explode($priceFormatting[0], $elementAmount); ?>
            <td class="bqAmountCell" style="border-left:1px solid #000;">
                <?php echo ! $printNoPrice && isset($elementAmount[0]) && $elementAmount[0] != 0 ? $elementAmount[0] : null; ?>
            </td>
            <td class="bqAmountCell" style="border-left:1px solid #000;">
                <?php echo ! $printNoPrice && isset($elementAmount[1]) && $elementAmount[0] != 0 ? $elementAmount[1] : null; ?>
            </td>
            <?php else: ?>
            <td class="bqAmountCell" style="border-left:1px solid #000;">
                <?php echo ! $printNoPrice && $elementAmount != 0 ? $elementAmount : null; ?>
            </td>
            <?php endif; ?>
        </tr>
        <?php unset($elementAmount); endfor; ?>
    <tr style="border-bottom: 1px solid black;">
        <td class="bqCounterCell">&nbsp;</td>
        <td class="bqDescriptionCell" style="border:none!important;"></td>

        <?php if ( $printDollarAndCentColumn ): ?>
        <td class="bqAmountCell" style="border-left:1px solid #000;"></td>
        <td class="bqAmountCell" style="border-left:1px solid #000;"></td>
        <?php else: ?>
        <td class="bqAmountCell" style="border-left:1px solid #000;"></td>
        <?php endif; ?>
    </tr>

    <tr>
        <td colspan="<?php echo ($printDollarAndCentColumn) ? 5 : 4; ?>">
            <table cellpadding="0" cellspacing="0" class="footer-table" style="width: 100%;">
                <tr>
                    <td style="width: 30%;" class="leftFooter"><?php echo $botLeftRow1; ?></td>
                    <td style="width: 40%; text-align: center;" class="pageFooter">&nbsp;</td>
                    <td style="width: 30%;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width: 30%;" class="leftFooter"><?php echo $botLeftRow2; ?></td>
                    <td style="width: 40%; text-align: center;" class="pageFooter"><?php echo $pageNo; ?></td>
                    <td style="width: 30%;">&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>