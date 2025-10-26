<table cellpadding="0" cellspacing="0" class="mainTable">
	<tr>
		<?php $rowCount = 0;
		$headerCount    = 12; ?>
		<td colspan="<?php echo $headerCount; ?>">
			<?php include_partial('scheduleOfRateReporting/bqReportHeader', array(
				'reportTitle'  => $printingPageTitle,
				'topLeftRow1'  => null,
				'topRightRow1' => $columnDescription,
				'topLeftRow2'  => $elementTitle,
				'topRightRow2' => $billDescription,
			));
			?>
		</td>
	</tr>
	<tr>
		<td class="bqHeadCell" style="min-width:60px;width:60px;">No</td>
		<td class="bqHeadCell" style="min-width:400px;width:auto;">Description</td>
		<td class="bqHeadCell" style="min-width:70px;width:70px;">Unit</td>
		<td class="bqHeadCell" style="min-width:120px;width:120px;">Invoice Qty</td>
		<td class="bqHeadCell" style="min-width:120px;width:120px;">DO Qty</td>
	</tr>

	<?php
	$firstItem   = false;
	$totalAmount = 0;

	for ( $x = 0; $x <= $maxRows; $x ++ ):

		$itemPadding      = 6;
		$itemRow          = isset( $itemPage[$x] ) ? $itemPage[$x] : false;
		$headerClass      = null;
		$headerStyle      = null;
		$borderTopStyling = null;

		$rowCount ++;

		$invoiceQty       = $itemRow ? $itemRow[sfBuildSpaceStockInDOItemReportPageGenerator::ROW_BILL_ITEM_INVOICE_QTY] : null;
		$doQty            = $itemRow ? $itemRow[sfBuildSpaceStockInDOItemReportPageGenerator::ROW_BILL_ITEM_DO_QTY] : null;
		$unit             = $itemRow ? $itemRow[sfBuildSpaceStockInDOItemReportPageGenerator::ROW_BILL_ITEM_UNIT] : null;
		$rate             = $itemRow ? $itemRow[sfBuildSpaceStockInDOItemReportPageGenerator::ROW_BILL_ITEM_RATE] : null;
		$itemId           = $itemRow ? $itemRow[0] : null;
		$fontColorStyling = 'colorBlack';

		if ( !$firstItem )
		{
			$borderTopStyling = 'border-top: 1px solid black;';
		}

		if ( $itemRow and ( $itemRow[sfBuildSpaceStockInDOItemReportPageGenerator::ROW_BILL_ITEM_TYPE] == ResourceItem::TYPE_HEADER ) )
		{
			$headerClass = 'bqHead1';
			$headerStyle = 'text-decoration:underline;';
		}
		?>
		<tr>
			<td class="bqCounterCell"
			    style="<?php echo $borderTopStyling; ?>"><?php echo $itemRow ? $itemRow[1] : '&nbsp;' ?></td>
			<td class="bqDescriptionCell"
			    style="padding-left: <?php echo $itemPadding; ?>px; <?php echo $borderTopStyling; ?>">
				<?php $preClass = $headerClass ? $headerClass : 'description' ?>
				<?php echo $itemRow ? '<pre class="' . $preClass . '" style="' . $headerStyle . '">' . trim($itemRow[2]) . '</pre>' : null ?>
			</td>

			<!-- Unit -->
			<td class="bqUnitCell <?php echo $fontColorStyling; ?>" style="<?php echo $borderTopStyling; ?>">
				<?php echo $unit; ?>
			</td>

			<!-- Invoice Qty -->
			<td class="bqRateCell <?php echo $fontColorStyling; ?>"
			    style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $invoiceQty && $invoiceQty != 0 ? number_format($invoiceQty, 2, $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null ?>
			</td>

			<!-- DO Qty -->
			<td class="bqRateCell <?php echo $fontColorStyling; ?>"
			    style="<?php echo $borderTopStyling; ?>">
				<?php echo !$printNoPrice && $doQty && $doQty != 0 ? number_format($doQty, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null ?>
			</td>
		</tr>
		<?php $firstItem = true;
		unset( $itemRow, $itemPage[$x], $amount ); endfor; ?>

	<tr>
		<td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;"
		    colspan="<?php echo $headerCount; ?>">
			Page <?php echo $pageCount; ?>
		</td>
	</tr>
</table>
</body>
</html>