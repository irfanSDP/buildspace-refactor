<table cellpadding="0" cellspacing="0" class="mainTable">

    <!-- Report Header -->
    <tr>
        <?php
        // Item, Description, Unit, Estimate + (Contractor Rates)
        $headerCount = 4 + ( count($tenderers) );
        ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('bqReportHeader', array('reportTitle' => $reportTitle, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <!-- Report Header (End) -->

    <!-- Headers -->
    <tr>
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

        ?>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">Item</td>

        <td class="bqHeadCell" style="min-width:80px;width:<?php echo $descriptionWidth; ?>px">Description</td>

        <td class="bqHeadCell" style="min-width:70px;width:70px;">Unit</td>

        <td class="bqHeadCell" style="min-width:100px;width:100px;">Estimate (<?php echo $currency->currency_code; ?>)</td>

        <?php if ( count($tenderers) ): ?>
            <?php foreach($tenderers as $k => $tenderer) : ?>

                <td class="bqHeadCell" style="min-width:100px;width:100px;">
                    <?php $tendererName = (strlen($tenderer['shortname'])) ? $tenderer['shortname'] : ((strlen($tenderer['name']) > 15) ? substr($tenderer['name'],0,10).'...' : $tenderer['name']); ?>

                    <?php if ( isset($tenderer['selected']) AND $tenderer['selected'] ): ?>
                        <span style="color:red;">* </span>
                        <span style="color:blue;"><?php echo $tendererName; ?></span>
                    <?php else: ?>
                        <?php echo $tendererName; ?>
                    <?php endif; ?>
                </td>

            <?php endforeach; ?>
        <?php endif; ?>
    </tr>
    <!-- Headers (End) -->

    <?php
    for($rowCount=0; $rowCount <= $maxRows; $rowCount++):?>

        <!-- Item Row variables -->
        <?php
        $itemPadding = 0;

        $itemRow = array_key_exists($rowCount, $itemPage) ? $itemPage[$rowCount] : false;

        if($itemRow)
        {
            /*============================================================================================================================
                Print Element in Grid
            ===============================*/
            if ( $itemRow[sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_TYPE] == sfBuildspaceScheduleOfRateBillPageGenerator::ROW_TYPE_ELEMENT AND ! $printElementInGrid )
            {
                $rowCount++;
                continue;
            }

            if ( $printElementInGridOnce AND $itemRow[sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_TYPE] == sfBuildspaceScheduleOfRateBillPageGenerator::ROW_TYPE_ELEMENT AND $pageCount > 1 )
            {
                $rowCount++;
                continue;
            }
            /*===============================
                Print Element in Grid (End)
            ===============================*/

            /*============================================================================================================================
                Item Id
            ===============================*/

            $itemId = $itemRow ? $itemRow[sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_ID] : null;
            /*===============================
                Item Id (End)
            ===============================*/

            /*============================================================================================================================
                Description styling
            ===============================*/
            if ($itemRow[sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_HEADER OR $itemRow[sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_HEADER_N)
            {
                $headerClass = 'bqHead'.$itemRow[sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_LEVEL];
                $headerStyle = null;
            }

            elseif($itemRow and $itemRow[sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_TYPE] == sfBuildspaceScheduleOfRateBillPageGenerator::ROW_TYPE_ELEMENT)
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
            /*===============================
                Description styling (End)
            ===============================*/

            /*============================================================================================================================
                Estimate Rate
            ===============================*/
            $estimateRate = $itemRow[ sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_RATE ];
            if( ( ! $printNoPrice ) && $estimateRate && ( $estimateRate != 0 ) )
            {
                $estimateRate = number_format($estimateRate, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]);
            }
            else{
                $estimateRate = null;
            }
            /*===============================
                Estimate Rate (End)
            ===============================*/

            /*============================================================================================================================
                Contractor's Rate Style [For Highest and Lowest]
            ===============================*/
            $lowestStyle = '';
            $highestStyle = '';
            if($itemId > 0 && count($tenderers))
            {
                $lowestTendererId = null;
                $highestTendererId = null;

                $listOfRates = array();

                foreach($tenderers as $k => $tenderer)
                {
                    if(array_key_exists($tenderer['id'], $tendererRates) && array_key_exists($itemId, $tendererRates[$tenderer['id']]))
                    {
                        $listOfRates[$tenderer['id']] = $tendererRates[$tenderer['id']][$itemId];
                    }
                }

                $lowestRate = count($listOfRates) ? min($listOfRates) : 0;
                $highestRate = count($listOfRates) ? max($listOfRates) : 0;

                $lowestTendererId  = array_search($lowestRate, $listOfRates);
                $highestTendererId = array_search($highestRate, $listOfRates);

                if($lowestTendererId != $highestTendererId)
                {
                    $highestStyle = "font-weight:bold;color:#ee4559;font-style:italic;";
                    $lowestStyle = "font-weight:bold;font-style:italic;color:#adf393;text-decoration:underline;";
                }
            }
            /*===============================
                Contractor's Rate Style [For Highest and Lowest] (End)
            ==============================================================*/

            $itemPadding = 6;
        }
        ?>
        <!-- Item Row variables (End) -->

        <!-- Item rows -->
        <tr>
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_ROW_IDX] : '&nbsp;'?></td>
            <td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_DESCRIPTION]).'</pre>' : null?>
            </td>

            <td class="bqUnitCell">
                <?php echo $itemRow ? $itemRow[ sfBuildspaceScheduleOfRateBillPageGenerator::ROW_BILL_ITEM_UNIT ] : '&nbsp;' ?>
            </td>

            <td class="bqRateCell">
                <?php echo $estimateRate ?>
            </td>

            <?php if(count($tenderers)) : $counter = 1; ?>
                <?php foreach($tenderers as $k => $tenderer) : ?>
                    <?php
                    $tendererRate = null;
                    if( ( ! $printNoPrice ) && ($itemId > 0) && $tendererRates && array_key_exists($itemId, $tendererRates[$tenderer['id']]) && ($tendererRates[$tenderer['id']][$itemId] != 0))
                    {
                        $tendererRate = number_format($tendererRates[$tenderer['id']][$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]);
                    }
                    ?>
                    <td class="bqRateCell" style="<?php
                    if($tenderer['id'] == $lowestTendererId)
                    {
                        echo $lowestStyle;
                    }
                    else if($tenderer['id'] == $highestTendererId)
                    {
                        echo $highestStyle;
                    }
                    ?>">
                        <?php echo $tendererRate; ?>
                    </td>
                <?php endforeach;?>
            <?php endif;?>

        </tr>
        <!-- Item rows (End) -->

        <?php unset($itemPage[$rowCount]);?>
    <?php endfor; ?>

    <!-- Footer -->
    <tr>
        <td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
            Page <?php echo $pageCount; ?> of <?php echo $totalPages; ?>
        </td>
    </tr>
</table>
</body>
</html>

