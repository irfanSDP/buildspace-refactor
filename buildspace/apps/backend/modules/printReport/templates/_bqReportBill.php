<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php
            $headerCount = 3+(count($tenderers));
        ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('bqReportHeader', array('reportTitle' => $reportTitle, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2 )); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">No</td>

        <?php
            switch(count($tenderers))
            {
                case 0 :
                    $descriptionWidth = 420;
                    break;
                case 1 :
                    $descriptionWidth = 325;
                    break;
                default :
                    $descriptionWidth = 340;
            }

            if(count($tenderers) > 3)
            {
                $descriptionWidth = 360;
            }

            $descriptionWidth+=80;
        ?>

        <td class="bqHeadCell" style="min-width:400px;width:<?php echo $descriptionWidth; ?>;"><?php echo $descHeader; ?></td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;"><?php echo "Estimate"; ?></td>

        <?php if ( count($tenderers) ): ?>
            <?php foreach($tenderers as $k => $tenderer) : ?>
                <td class="bqHeadCell" style="min-width:100px;width:100px;">
                    <?php if ( isset($tenderer['selected']) AND $tenderer['selected'] ) echo '<span style="color:red;">* </span>'; ?>

                    <?php $tendererName = (strlen($tenderer['shortname'])) ? $tenderer['shortname'] : ((strlen($tenderer['name']) > 15) ? substr($tenderer['name'],0,12).'...' : $tenderer['name']); ?>

                    <?php if ( isset($tenderer['selected']) AND $tenderer['selected'] ): ?>
                        <span style="color: blue;"><?php echo $tendererName; ?></span>
                    <?php else: ?>
                        <?php echo $tendererName; ?>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
        <?php endif; ?>
    </tr>
    <?php
    /*
     * 0 - id
     * 1 - row index
     * 2 - description
     * 3 - level
     * 4 - type
     * 5 - unit
     */
    $rowCount = 0;
    $totalAmount = 0;

    for($x=0; $x <= $maxRows; $x++):

        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        if ( $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND ! $printBillInGrid )
        {
            $x++;
            continue;
        }

        $rowCount++;
        $rate = $itemRow ? $itemRow[6] : null;

        $itemId = $itemRow ? $itemRow[0] : null;

        if($itemId > 0 && count($tenderers))
        {
            $lowestTendererId = null;
            $highestTendererId = null;

            $listOfRates = array();

            foreach($tenderers as $k => $tenderer)
            {
                if(array_key_exists($itemId, $tenderersBillTotals) && array_key_exists($tenderer['id'], $tenderersBillTotals[$itemId]))
                {
                    $listOfRates[$tenderer['id']] = $tenderersBillTotals[$itemId][$tenderer['id']];
                }
            }

            $lowestRate = count($listOfRates) ? min($listOfRates) : 0;
            $highestRate = count($listOfRates) ? max($listOfRates) : 0;

            $lowestTendererId  = array_search($lowestRate, $listOfRates);
            $highestTendererId = array_search($highestRate, $listOfRates);

            if($lowestTendererId == $highestTendererId)
            {
                $lowestStyle = '';
                $highestStyle = '';
            }
            else
            {
                $highestStyle = "font-weight:bold;color:#ee4559;font-style:italic;";
                $lowestStyle = "font-weight:bold;font-style:italic;color:#adf393;text-decoration:underline;";
            }

            $counter++;
        }
        else
        {
            $lowestStyle = '';
            $highestStyle = '';
        }

        $headerClass = null;
        $headerStyle = null;
        $itemPadding = 6;
        ?>
        <tr>
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
            <td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php if($itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_PC_RATE):?>
                <?php include_partial('printBQ/bqReportItem/primeCostRateTable', array('currency'=>$currency, 'itemRow'=>$itemRow, 'priceFormatting'=> $priceFormatting, 'printNoPrice' => $printNoPrice)) ?>
                <?php else:?>
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
                <?php endif?>
            </td>

            <td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
            <?php if(count($tenderers)) : $counter = 1; ?>
                <?php foreach($tenderers as $k => $tenderer) : ?>
                    <td class="bqRateCell" style="<?php
                                if($tenderer['id'] == $lowestTendererId)
                                {
                                    echo $lowestStyle;
                                }
                                else if($tenderer['id'] == $highestTendererId)
                                {
                                    echo $highestStyle;
                                }
                                else
                                {
                                    echo "";
                                }
                            ?>">
                        <?php echo ! $printNoPrice  && $itemId > 0 && $tenderersBillTotals && array_key_exists($tenderer['id'], $tenderersBillTotals[$itemId]) && $tenderersBillTotals[$itemId][$tenderer['id']] != 0 ? number_format($tenderersBillTotals[$itemId][$tenderer['id']], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                    </td>
                <?php endforeach;?>
            <?php endif;?>
        </tr>
        <?php unset($itemPage[$x], $amount);?>
        <?php endfor; ?>
        <?php if($printGrandTotal) : ?>
            <tr>
                <td class="footer" style="padding-right:5px;" colspan="<?php echo 2; ?>">
                    <?php echo "Total "; ?> (<?php echo $currency->currency_code; ?>) :
                </td>
                <td class="footerSumAmount">
                    <?php echo ($estimateProjectGrandTotal && array_key_exists('value', $estimateProjectGrandTotal) && $estimateProjectGrandTotal['value'] != 0) ?  number_format($estimateProjectGrandTotal['value'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                </td>
                <?php foreach($tenderers as $k => $tenderer) : ?>
                    <td class="footerSumAmount">
                        <?php echo ($contractorProjectGrandTotals && array_key_exists($tenderer['id'], $contractorProjectGrandTotals) && $contractorProjectGrandTotals[$tenderer['id']] != 0) ?  number_format($contractorProjectGrandTotals[$tenderer['id']], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
                    </td>
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

