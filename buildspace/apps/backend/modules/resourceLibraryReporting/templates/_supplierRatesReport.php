<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php $rowCount = 0; $headerCount = 8; ?>
        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('scheduleOfRateReporting/bqReportHeader', array(
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
        <td class="bqHeadCell" style="min-width:400px;width:auto;">Supplier</td>
        <td class="bqHeadCell" style="min-width:120px;width:120px;">Project</td>
        <td class="bqHeadCell" style="min-width:120px;width:120px;">Country</td>
        <td class="bqHeadCell" style="min-width:120px;width:120px;">State</td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">Rate</td>
        <td class="bqHeadCell" style="min-width:120px;width:120px;">Remarks</td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">Date</td>
    </tr>

    <?php foreach ( $billItemInfos as $billItemInfo ): ?>
    <tr>
        <td class="bqCounterCell" style="border-right: none;">&nbsp;</td>
        <td class="bqDescriptionCell" style="border-right: none;"><pre class="description"><?php echo $billItemInfo[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_DESCRIPTION]; ?></pre></td>

        <td class="bqRateCell" style="border-right: none;">&nbsp;</td>
        <td class="bqRateCell" style="border-right: none;">&nbsp;</td>

	    <?php if ( $billItemInfo[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_ID] > 0 ): ?>
		    <td class="bqUnitCell" style="border-left: none; border-right: none;"><?php echo $billItemUOM; ?></td>
	    <?php else: ?>
		    <td class="bqUnitCell" style="border-right: none;">&nbsp;</td>
	    <?php endif; ?>

        <?php if ( $billItemInfo[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_ID] > 0 ): ?>
        <td class="bqRateCell" style="border-right: none;"><?php echo ! $printNoPrice && $billItemRateValue && $billItemRateValue != 0 ? number_format($billItemRateValue, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
        <?php else: ?>
        <td class="bqUnitCell" style="border-right: none;">&nbsp;</td>
        <?php endif; ?>

	    <?php if ( $billItemInfo[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_ID] > 0 ): ?>
		    <td class="bqCounterCell" style="border-left: none; border-right: none;"><?php echo $sortingType; ?></td>
	    <?php else: ?>
		    <td class="bqUnitCell" style="border-right: none;">&nbsp;</td>
	    <?php endif; ?>

	    <td class="bqRateCell">&nbsp;</td>
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
	        $isSelectedClass = null;

            $rowCount++;

            $projectTitle     = $itemRow ? $itemRow[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_PROJECT_TITLE] : NULL;
            $country          = $itemRow ? $itemRow[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_COUNTRY] : NULL;
            $state            = $itemRow ? $itemRow[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_STATE] : NULL;
            $rate             = $itemRow ? $itemRow[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_RATE] : NULL;
            $remarks          = $itemRow ? $itemRow[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_REMARKS] : NULL;
            $isSelected       = $itemRow ? $itemRow[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_IS_SELECTED] : false;

            $lastUpdated      = $itemRow ? $itemRow[sfResourceLibrarySupplierRatesReportGenerator::ROW_BILL_ITEM_LAST_UPDATED] : NULL;
            $itemId           = $itemRow ? $itemRow[0] : null;
            $fontColorStyling = 'colorBlack';

	        if ( $projectTitle )
	        {
		        $projectTitle = strlen($projectTitle) > 12 ? substr($projectTitle, 0, 12).'...' : $projectTitle;
	        }

	        if ( $country )
	        {
		        $country = strlen($country) > 12 ? substr($country, 0, 12).'...' : $country;
	        }

	        if ( $state )
	        {
		        $state = strlen($state) > 12 ? substr($state, 0, 12).'...' : $state;
	        }

	        if ( $remarks )
	        {
		        $remarks = strlen($remarks) > 12 ? substr($remarks, 0, 12).'...' : $remarks;
	        }

	        if ( $lastUpdated )
	        {
		        $lastUpdated = date('d-m-Y', strtotime($lastUpdated));
	        }

            if ( ! $firstItem )
            {
                $borderTopStyling = 'border-top: 1px solid black;';
            }

	        if ( $isSelected )
	        {
		        $isSelectedClass = ' color: blue;';
	        }
        ?>
        <tr>
            <td class="bqCounterCell" style="<?php echo $borderTopStyling, $isSelectedClass; ?>"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
            <td class="bqDescriptionCell" style="padding-left: <?php echo $itemPadding; ?>px; <?php echo $borderTopStyling, $isSelectedClass; ?>">
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
            </td>

            <!-- Project Title -->
            <td class="bqUnitCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $isSelectedClass; ?>">
                <?php echo $projectTitle; ?>
            </td>

            <!-- Country -->
            <td class="bqUnitCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $isSelectedClass; ?>">
                <?php echo $country; ?>
            </td>

            <!-- State -->
            <td class="bqUnitCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $isSelectedClass; ?>">
                <?php echo $state; ?>
            </td>

            <!-- Rate -->
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $isSelectedClass; ?>">
                <?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>

            <!-- Remarks -->
            <td class="bqUnitCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $isSelectedClass; ?>">
                <?php echo $remarks; ?>
            </td>

            <!-- Date -->
            <td class="bqUnitCell" style="<?php echo $borderTopStyling, $isSelectedClass; ?>">
                <?php echo $lastUpdated; ?>
            </td>
        </tr>
    <?php $firstItem = true; unset($itemRow, $itemPage[$x], $amount); endfor; ?>

	<tr>
		<td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
			Page <?php echo $pageCount; ?>
		</td>
	</tr>
</table>
</body>
</html>