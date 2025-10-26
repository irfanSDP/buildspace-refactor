<?php $tendererCounts = count($tenderers); ?>

<table cellpadding="0" cellspacing="0" class="mainTable">
	<tr>
		<?php
		$headerCount = 3 + ( $tendererCounts );
		?>

		<td colspan="<?php echo $headerCount; ?>">
			<?php include_partial('bqReportHeader', array( 'reportTitle' => $reportTitle, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2 )); ?>
		</td>
	</tr>
	<tr>
		<td class="bqHeadCell" style="min-width:80px;width:80px;">No</td>

		<?php
		switch ($tendererCounts)
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

		if ( $tendererCounts > 3 )
		{
			$descriptionWidth = 360;
		}

		$descriptionWidth += 80;
		?>

		<td class="bqHeadCell" style="min-width:400px;width: <?php echo $descriptionWidth; ?>px;"><?php echo $descHeader; ?></td>
		<td class="bqHeadCell" style="min-width:100px;width:100px;"><?php echo "Estimate"; ?></td>
		<?php if ( $tendererCounts ): ?>
			<?php foreach ( $tenderers as $k => $tenderer ) : ?>
				<td class="bqHeadCell" style="min-width:100px;width:100px;">
					<?php if ( isset( $tenderer['selected'] ) AND $tenderer['selected'] )
					{
						echo '<span style="color:red;">* </span>';
					} ?>

					<?php $tendererName = ( strlen($tenderer['shortname']) ) ? $tenderer['shortname'] : ( ( strlen($tenderer['name']) > 15 ) ? substr($tenderer['name'], 0, 12) . '...' : $tenderer['name'] ); ?>

					<?php if ( isset( $tenderer['selected'] ) AND $tenderer['selected'] ): ?>
						<span style="color:blue;"><?php echo $tendererName; ?></span>
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
	$rowCount    = 0;
	$totalAmount = 0;

	for ( $x = 0; $x <= $maxRows; $x ++ ):

		$itemPadding = 0;
		$itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

		$rowCount ++;

		$rate                       = $itemRow ? $itemRow[6] : null;
		$itemId                     = $itemRow ? $itemRow[0] : null;
		$estimateAmount             = ( $itemRow AND isset( $itemRow[sfBuildSpaceReportElementByTendererAndType::ROW_BILL_ITEM_ESTIMATE_TOTALS][$billColumnSetting->id] ) ) ? $itemRow[sfBuildSpaceReportElementByTendererAndType::ROW_BILL_ITEM_ESTIMATE_TOTALS][$billColumnSetting->id] : null;
		$contractorPrintableAmounts = array();

		if ( $itemId > 0 && $tendererCounts && isset( $itemRow[sfBuildSpaceReportElementByTendererAndType::ROW_BILL_ITEM_CONTRACTOR_TOTALS][$billColumnSetting->id] ) )
		{
			$contractorAmounts = $itemRow[sfBuildSpaceReportElementByTendererAndType::ROW_BILL_ITEM_CONTRACTOR_TOTALS][$billColumnSetting->id];

			foreach ( $tenderers as $k => $tenderer )
			{
				$contractorPrintableAmounts[$tenderer['id']] = isset( $contractorAmounts[$tenderer['id']] ) ? $contractorAmounts[$tenderer['id']] : 0;
			}
		}

		$headerClass = null;
		$itemPadding = 6;

		?>
		<tr>
			<td class="bqCounterCell"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
			<td class="bqDescriptionCell <?php echo $headerClass?>"
			    style="padding-left: <?php echo $itemPadding; ?>px;">
				<?php $preClass = $headerClass ? $headerClass : 'description'?>
				<?php echo $itemRow ? '<pre class="' . $preClass . '">' . trim($itemRow[2]) . '</pre>' : null?>
			</td>
			<td class="bqRateCell">
				<?php echo !$printNoPrice && $itemId > 0 && $estimateAmount != 0 ? number_format($estimateAmount, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null ?>
			</td>

			<?php foreach ( $tenderers as $k => $tenderer ): ?>
				<?php if ( isset( $contractorPrintableAmounts[$tenderer['id']] ) ): ?>
					<td class="bqRateCell">
						<?php echo !$printNoPrice && $itemId > 0 && $contractorPrintableAmounts[$tenderer['id']] != 0 ? number_format($contractorPrintableAmounts[$tenderer['id']], $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null ?>
					</td>
				<?php else: ?>
					<td class="bqRateCell">&nbsp;</td>
				<?php endif; ?>
			<?php endforeach; ?>
		</tr>
		<?php unset( $itemPage[$x], $amount );?>
	<?php endfor; ?>

	<?php if ( $printGrandTotal ) : ?>
		<tr>
			<td class="footer" style="padding-right:5px;" colspan="<?php echo 2; ?>">
				<?php echo "Total "; ?> (<?php echo $currency->currency_code; ?>) :
			</td>
			<td class="footerSumAmount">
				<?php echo ( isset( $estimateOverAllTotal[$billColumnSetting->id] ) && $estimateOverAllTotal[$billColumnSetting->id] != 0 ) ? number_format($estimateOverAllTotal[$billColumnSetting->id], $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null; ?>
			</td>
			<?php foreach ( $tenderers as $k => $tenderer ) : ?>
				<td class="footerSumAmount">
					<?php echo ( isset( $contractorOverAllTotal[$billColumnSetting->id][$tenderer['id']] ) && $contractorOverAllTotal[$billColumnSetting->id][$tenderer['id']] != 0 ) ? number_format($contractorOverAllTotal[$billColumnSetting->id][$tenderer['id']], $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null; ?>
				</td>
			<?php endforeach; ?>
		</tr>
        <tr>
            <td style="padding-right: 5px;" colspan="2">Units :</td>
            <td class="footerSumAmount" colspan="<?php echo 1 + count($tenderers); ?>"><?php echo $billColumnSetting->quantity; ?></td>
        </tr>
        <tr>
            <td style="padding-right: 5px;" colspan="2">Final Total (<?php echo $currency->currency_code; ?>) :</td>
            <td class="footerSumAmount">
                <?php echo ( isset( $estimateOverAllTotal[ $billColumnSetting->id ] ) && $estimateOverAllTotal[ $billColumnSetting->id ] != 0 ) ? number_format($estimateOverAllTotal[ $billColumnSetting->id ] * $billColumnSetting->quantity, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null; ?>
            </td>
            <?php foreach ( $tenderers as $k => $tenderer ) : ?>
                <td class="footerSumAmount">
                    <?php echo ( isset( $contractorOverAllTotal[ $billColumnSetting->id ][ $tenderer['id'] ] ) && $contractorOverAllTotal[ $billColumnSetting->id ][ $tenderer['id'] ] != 0 ) ? number_format($contractorOverAllTotal[ $billColumnSetting->id ][ $tenderer['id'] ] * $billColumnSetting->quantity, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null; ?>
                </td>
            <?php endforeach; ?>
        </tr>
		<tr>
			<td style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
				Page <?php echo $pageCount; ?>
			</td>
		</tr>
	<?php else: ?>
		<tr>
			<td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;"
			    colspan="<?php echo $headerCount; ?>">
				Page <?php echo $pageCount; ?>
			</td>
		</tr>
	<?php endif; ?>
</table>
</body>
</html>