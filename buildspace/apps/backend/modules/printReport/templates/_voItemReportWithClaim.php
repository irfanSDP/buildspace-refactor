<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php $headerCount = 9; ?>
        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('printReport/bqReportHeader', array('reportTitle' => $reportTitle,'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" rowspan="2" style="min-width:60px;width:60px;">
            Item
        </td>
        <td class="bqHeadCell" rowspan="2" style="min-width:280px;width:280px;">
            <?php echo $descHeader; ?>
        </td>
        <td class="bqHeadCell" rowspan="2" style="min-width:120px;width:120px;">
            <?php echo "Nett Omission/Addition"; ?>
        </td>
        <td class="bqHeadCell" colspan="2">
            <?php echo "Previous Payment"; ?>
        </td>
        <td class="bqHeadCell" colspan="2">
            <?php echo "Work Done"; ?>
        </td>
        <td class="bqHeadCell" colspan="2">
            <?php echo "Current Payment"; ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:60px;width:60px;">
            <?php echo "%" ?>
        </td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">
            <?php echo "Amount"; ?>
        </td>
        <td class="bqHeadCell" style="min-width:60px;width:60px;">
            <?php echo "%" ?>
        </td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">
            <?php echo "Amount"; ?>
        </td>
        <td class="bqHeadCell" style="min-width:60px;width:60px;">
            <?php echo "%" ?>
        </td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">
            <?php echo "Amount"; ?>
        </td>
    </tr>
    <?php

    $rowCount = 0;
    $totalAmount = 0;

    for($x=0; $x <= $maxRows; $x++):

        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        if ( $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND ! $printElementInGrid )
        {
            $x++;
            continue;
        }

        if ( $printElementInGridOnce AND $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $pageCount > 1 )
        {
            $x++;
            continue;
        }

        $rowCount++;

        $itemId = $itemRow ? $itemRow[0] : null;
        $net    = $itemRow ? $itemRow[9] : null;

        $previousPercentage  = $itemRow && is_array($itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_PREVIOUS]) && array_key_exists('percentage', $itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_PREVIOUS]) ? $itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_PREVIOUS]['percentage'] : null;
        $previousAmount      = $itemRow && is_array($itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_PREVIOUS]) && array_key_exists('amount', $itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_PREVIOUS]) ? $itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_PREVIOUS]['amount'] : null;

        $workdonePercentage  = $itemRow && is_array($itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_WORKDONE]) && array_key_exists('percentage', $itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_WORKDONE]) ? $itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_WORKDONE]['percentage'] : null;
        $workdoneAmount      = $itemRow && is_array($itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_WORKDONE]) && array_key_exists('amount', $itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_WORKDONE]) ? $itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_WORKDONE]['amount'] : null;

        $currentPercentage  = $itemRow && is_array($itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_CURRENT]) && array_key_exists('percentage', $itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_CURRENT]) ? $itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_CURRENT]['percentage'] : null;
        $currentAmount      = $itemRow && is_array($itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_CURRENT]) && array_key_exists('amount', $itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_CURRENT]) ? $itemRow[sfBuildspaceVOItemsWithClaimReportGenerator::ROW_CLAIM_CURRENT]['amount'] : null;

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

        if ( $indentItem AND $itemRow AND ($itemRow[4] != sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $itemRow[4] != BillItem::TYPE_HEADER) )
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
            <td class="bqRateCell">
                <?php echo ! $printNoPrice && $net && $net != 0 ? number_format($net, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
            <td class="bqRateCell" style="text-align:center;">
                <?php echo $previousPercentage && $previousPercentage != 0 ? number_format($previousPercentage,2).'%' : null ?>
            </td>
            <td class="bqRateCell">
                <?php echo ! $printNoPrice && $previousAmount && $previousAmount != 0 ? number_format($previousAmount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
            <td class="bqRateCell" style="text-align:center;">
                <?php echo $workdonePercentage && $workdonePercentage != 0 ? number_format($workdonePercentage,2).'%' : null ?>
            </td>
            <td class="bqRateCell">
                <?php echo ! $printNoPrice && $workdoneAmount && $workdoneAmount != 0 ? number_format($workdoneAmount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
            <td class="bqRateCell" style="text-align:center;">
                <?php echo $currentPercentage && $currentPercentage != 0 ? number_format($currentPercentage,2).'%' : null ?>
            </td>
            <td class="bqRateCell">
                <?php echo ! $printNoPrice && $currentAmount && $currentAmount != 0 ? number_format($currentAmount, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
        </tr>
            <?php unset($itemPage[$x], $amount);?>
        <?php endfor; ?>

        <?php if($printGrandTotal) : ?>
            <tr>
                <td class="footer" style="padding-right:5px;" colspan="2">
                    Total: (<?php echo $currency->currency_code; ?>) :
                </td>
                <td class="footerSumAmount">
                    <?php echo ($variationTotal && array_key_exists('net', $variationTotal)) ? number_format($variationTotal['net'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                </td>
                <td class="footerSumAmount">
                    <?php echo $variationTotal && array_key_exists('net', $variationTotal) && array_key_exists('net', $variationTotal) ? number_format(($variationTotal['previous_amount'] / $variationTotal['net']) * 100, 2).' %' : null ?>
                </td>
                <td class="footerSumAmount">
                    <?php echo ($variationTotal && array_key_exists('previous_amount', $variationTotal)) ? number_format($variationTotal['previous_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                </td>
                <td class="footerSumAmount">
                    <?php echo $variationTotal && array_key_exists('net', $variationTotal) && array_key_exists('net', $variationTotal) ? number_format(($variationTotal['workdone_amount'] / $variationTotal['net']) * 100, 2).' %' : null ?>
                </td>
                <td class="footerSumAmount">
                    <?php echo ($variationTotal && array_key_exists('workdone_amount', $variationTotal)) ? number_format($variationTotal['workdone_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                </td>
                <td class="footerSumAmount">
                    <?php echo $variationTotal && array_key_exists('net', $variationTotal) && array_key_exists('net', $variationTotal) ? number_format(($variationTotal['current_amount'] / $variationTotal['net']) * 100, 2).' %' : null ?>
                </td>
                <td class="footerSumAmount">
                    <?php echo ($variationTotal && array_key_exists('current_amount', $variationTotal)) ? number_format($variationTotal['current_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                </td>
            </tr>
        <?php else: ?>
            <tr>
                <td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
                    Page <?php echo $pageCount; ?> of <?php echo $totalPage; ?>
                </td>
            </tr>
        <?php endif;?>

        <?php if($isLastPage): ?>
            <tr>
                <td colspan="<?php echo $headerCount; ?>">
                <?php
                    include_partial('printReport/footerLayout', array(
                        'leftText' => $left_text,
                        'rightText' => $right_text
                    ));
                ?>
                </td>
            </tr>
        <?php endif; ?>
</table>
</body>
</html>