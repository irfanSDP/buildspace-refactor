<table cellpadding="0" cellspacing="0" class="mainTable">

    <?php include_partial('multiTypeHeader', array('printAmountOnly' => true, 'topLeftRow1' => $topLeftRow1, 'topRightRow1' => $topRightRow1, 'topLeftRow2' => $topLeftRow2, 'elementHeaderDescription' => $elementHeaderDescription, 'billColumnSettings' => $billColumnSettings, 'printElementTitle' => $printElementTitle)); ?>

    <tr>
        <td class="bqHeadCell" style="min-width:35px;width:35px;" rowspan="2"></td>
        <td class="bqHeadCell" style="min-width:395px;width:395px;border-right:none;" rowspan="2"><?php echo $descHeader; ?></td>
        <?php $gapWidth = (count($billColumnSettings) <= 2) ? 85 : 15;  ?>
        <td class="bqHeadCell" style="min-width:<?php echo $gapWidth; ?>;width:<?php echo $gapWidth; ?>;border-left:none;" rowspan="2"></td>
        <td class="bqHeadCell" colspan="<?php echo count($billColumnSettings)?>"><?php echo $amtHeader; ?></td>
    </tr>
    <tr>
        <?php foreach($billColumnSettings as $billColumnSetting):?>
            <?php $amountWidth = (count($billColumnSettings) <= 2) ? 230 : 180;  ?>
            <td class="bqHeadCell" style="min-width:<?php echo $amountWidth; ?>;width:<?php echo $amountWidth; ?>;"><?php echo $billColumnSetting['name']?></td>
        <?php endforeach?>
    </tr>
    <?php
    $rowCount = 0;
    for($x=0; $x < $maxRows; $x++):
        $itemRow = array_key_exists($x, $collectionPage) ? $collectionPage[$x] : false;
        $rowCount++;

        if ( $itemRow AND $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND ! $printElementInGrid )
        {
            $x++;
            continue;
        }

        if($itemRow and $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT)
        {
            $descriptionClass = 'elementHeader';

            if ( $alignElementTitleToTheLeft )
            {
                $descriptionClass .= ' alignLeft';
            }
            else
            {
                $descriptionClass .= ' alignCenter';
            }
        }
        elseif($itemRow and $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_LAST_COLLECTION)
        {
            $descriptionClass = 'bqDescriptionCell collectionLastDescription';
        }
        elseif($itemRow and $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_COLLECTION_TITLE)
        {
            $descriptionClass = 'bqDescriptionCell collectionTitle';
        }
        else
        {
            $descriptionClass = 'bqDescriptionCell';
        }
        ?>
        <tr>
            <td class="bqCounterCell">&nbsp;</td>
            <td colspan="2" class="<?php echo $descriptionClass?>" style="padding-left: 6px; border-right: 1px solid black;">
                <pre><?php echo $itemRow ? trim($itemRow[0]) : '&nbsp;'?></pre>
            </td>
            <?php foreach($billColumnSettings as $idx => $billColumnSetting):?>
            <td class="bqAmountCell">
                <?php
                if ( $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_COLLECTION_TITLE )
                {
                    $rowAmount = (isset ($itemRow[2][$billColumnSetting['id']])) ? $itemRow[2][$billColumnSetting['id']] : 0;

                    echo ! $printNoPrice && $rowAmount != 0 ? number_format($rowAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]) : null;
                }
                else
                {
                    if ($itemRow[2] instanceof SplFixedArray && $itemRow[2][$idx][0] == $billColumnSetting['id'])
                    {
                        $rowAmount = $itemRow[2][$idx][1];
                        echo ! $printNoPrice && $rowAmount != 0 ? number_format($rowAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]) : null;
                    }
                }
                ?>
            </td>
            <?php endforeach?>
        </tr>
        <?php endfor; ?>
    <tr>
        <td class="bqCounterCell">&nbsp;</td>
        <td colspan="2" class="bqDescriptionCell"></td>
        <?php foreach($billColumnSettings as $billColumnSetting):?>
        <td class="bqAmountCell"></td>
        <?php endforeach?>
    </tr>
    <?php if ( $isLastPage ): ?>
    <tr>
        <td class="footer" colspan="3" style="padding-right:5px;">Carried To Summary (<?php echo $currency->currency_code; ?>)</td>
        <?php foreach($billColumnSettings as $billColumnSetting):?>
        <td class="footerSumAmount"><?php
            $totalAmount = array_key_exists($billColumnSetting['id'], $collectionPage['total_amount']) ? $collectionPage['total_amount'][$billColumnSetting['id']] : 0;

            echo ! $printNoPrice ? number_format($totalAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]) : null; ?></td>
        <?php endforeach?>
    </tr>
    <?php else: ?>
    <tr>
        <td class="footer" colspan="3" style="padding-right:5px;">Carried forward to next Collection page</td>
        <?php foreach($billColumnSettings as $billColumnSetting):?>
        <td class="footerSumAmount"><?php
            $totalAmount = array_key_exists($billColumnSetting['id'], $collectionPage['total_amount']) ? $collectionPage['total_amount'][$billColumnSetting['id']] : 0;

            echo ! $printNoPrice ? number_format($totalAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]) : null; ?></td>
        <?php endforeach?>
    </tr>
    <?php endif; ?>
    <tr>
        <td class="pageFooter" colspan="<?php echo count($billColumnSettings) + 4?>">
            <table cellpadding="0" cellspacing="0" class="footer-table" style="width: 100%;">
                <tr>
                    <td style="width: 40%;" class="leftFooter"><?php echo $botLeftRow1; ?></td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">&nbsp;</td>
                    <td style="width: 40%;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width: 40%;" class="leftFooter"><?php echo $botLeftRow2; ?></td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">
                        <?php echo trim("{$pageNoPrefix}{$elementCount}/{$pageCount}"); ?>
                    </td>
                    <td style="width: 40%; text-align: right;"><?php echo $printDateOfPrinting ? '<span style="font-weight:bold;">Date of Printing: </span><span style="font-style: italic;">'.date('d/M/Y').'</span>' : '&nbsp;'; ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body></html>