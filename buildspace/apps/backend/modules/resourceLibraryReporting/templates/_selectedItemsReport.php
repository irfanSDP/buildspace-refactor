<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php $rowCount = 0; $headerCount = 6; ?>
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
        <td class="bqHeadCell" style="min-width:400px;width:auto;">Description</td>
        <td class="bqHeadCell" style="min-width:140px;width:140px;">Constant</td>
        <td class="bqHeadCell" style="min-width:140px;width:140px;">Unit</td>
        <td class="bqHeadCell" style="min-width:140px;width:140px;">Rate</td>
        <td class="bqHeadCell" style="min-width:140px;width:140px;">Wastage %</td>
    </tr>

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
            $numberColor   = 'color: black;';
            $constantColor = 'color: black;';
            $qtyColor      = 'color: black;';
            $rateColor     = 'color: black;';
            $wastageColor  = 'color: black;';

            $rowCount++;

            $constant         = $itemRow ? $itemRow[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_CONSTANT]['value'] : NULL;
            $rate             = $itemRow ? $itemRow[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_RATE]['value'] : NULL;
            $wastage          = $itemRow ? $itemRow[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_WASTAGE]['value'] : NULL;
            $unit             = $itemRow ? $itemRow[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_UNIT] : NULL;
            $itemId           = $itemRow ? $itemRow[0] : null;
            $fontColorStyling = 'colorBlack';

            if ( ! $firstItem )
            {
                $borderTopStyling = 'border-top: 1px solid black;';
            }

            if ($itemRow and ($itemRow[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_TYPE] == ResourceItem::TYPE_HEADER))
            {
                $headerClass = 'bqHead1';
                $headerStyle = 'text-decoration:underline;';
            }

			if ( $itemRow )
			{
				if ( $itemRow[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_CONSTANT]['has_formula'] )
				{
					$constantColor = 'color: #F78181;';
				}

				if ( $itemRow[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_RATE]['has_formula'] )
				{
					$rateColor = 'color: #F78181;';
				}

				if ( $itemRow[sfResourceLibraryItemReportGenerator::ROW_BILL_ITEM_WASTAGE]['has_formula'] )
				{
					$wastageColor = 'color: #F78181;';
				}
			}
        ?>
        <tr>
            <td class="bqCounterCell" style="<?php echo $borderTopStyling; ?>"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
            <td class="bqDescriptionCell" style="padding-left: <?php echo $itemPadding; ?>px; <?php echo $borderTopStyling; ?>">
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
            </td>

            <!-- Constant -->
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $constantColor; ?>">
                <?php echo ! $printNoPrice && $constant && $constant != 0 ? number_format($constant, 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>

            <!-- Unit -->
            <td class="bqUnitCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling; ?>">
                <?php echo $unit; ?>
            </td>

            <!-- Rate -->
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $rateColor; ?>">
                <?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>

            <!-- Wastage -->
            <td class="bqRateCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling, $wastageColor; ?>">
                <?php echo ! $printNoPrice && $wastage && $wastage != 0 ? number_format($wastage, 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]).'%' : null ?>
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