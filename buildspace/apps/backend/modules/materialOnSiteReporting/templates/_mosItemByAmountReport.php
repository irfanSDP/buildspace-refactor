<?php $headerCount = 6; ?>

<div style="border-bottom: 1px solid #000000; margin-bottom: 18px; padding: 9px 0;">
	<div style="font-weight: bold;">
		<?php echo Utilities::truncateString($materialOnSitePrintSetting->project_name, 280); ?>
	</div>
</div>

<table cellpadding="0" cellspacing="0" class="mainTable">
	<?php
	include_partial('materialOnSiteReporting/mosReportTableHeader', array(
		'headerCount'                => $headerCount,
		'materialOnSitePrintSetting' => $materialOnSitePrintSetting
	));
	?>
	<tr>
		<td class="bqHeadCell" style="min-width:60px;width:60px;">
			Item
		</td>
		<td class="bqHeadCell" style="min-width:280px;width:280px;">
			Description
		</td>
		<td class="bqHeadCell" style="min-width:60px;width:60px;">
			Unit
		</td>
		<td class="bqHeadCell" style="min-width:100px;width:100px;">
			Qty
		</td>
		<td class="bqHeadCell" style="min-width:100px;width:100px;">
			Rate
		</td>
		<td class="bqHeadCell" style="min-width:100px;width:100px;">
			Amount
		</td>
	</tr>
	<?php

	$rowCount = 0;

	for ( $x = 0; $x <= $maxRows; $x ++ ):

		$itemPadding = 0;

		$itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

		$rowCount ++;

		$headerClass = null;
		$headerStyle = null;

		$itemId = $itemRow ? $itemRow[0] : null;
		$rate   = $itemRow ? $itemRow[6] : null;
		$unit   = $itemRow ? $itemRow[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_UNIT] : null;

		$deliveredQty = $itemRow[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_DELIVERED_QTY];
		$usedQty      = $itemRow[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_USED_QTY];
		$balanceQty   = $itemRow[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_BALANCE_QTY];
		$rate         = $itemRow[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_RATE];
		$amount       = $itemRow[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_AMOUNT];

		if ( $itemRow and ( $itemRow[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_TYPE] == ResourceItem::TYPE_HEADER ) )
		{
			$headerClass = 'bqHead' . $itemRow[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_LEVEL];
			$headerStyle = null;
		}
		elseif ( $itemRow and $itemRow[sfMaterialOnSiteNormalItemReportGenerator::ROW_BILL_ITEM_TYPE] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT )
		{
			$headerClass = 'elementHeader';
			$headerStyle = 'font-style: italic;';
		}

		$itemPadding = 6;

		?>
		<tr>
			<td class="bqCounterCell"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
			<td class="bqDescriptionCell <?php echo $headerClass?>"
			    style="padding-left: <?php echo $itemPadding; ?>px;">
				<?php $preClass = $headerClass ? $headerClass : 'description'?>
				<?php echo $itemRow ? '<pre class="' . $preClass . '" style="' . $headerStyle . '">' . trim($itemRow[2]) . '</pre>' : null?>
			</td>
			<td class="bqRateCell" style="text-align: center;">
				<?php echo $unit ? $unit : null?>
			</td>
			<td class="bqQtyCell">
				<?php echo !empty( $balanceQty ) ? number_format($balanceQty, $priceFormatting[2], $priceFormatting[0], ( $qtyCommaRemove ) ? '' : $priceFormatting[1]) : null; ?>
			</td>
			<td class="bqRateCell">
				<?php echo !empty( $rate ) ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null; ?>
			</td>
			<td class="bqQtyCell">
				<?php echo !empty( $amount ) ? number_format($amount, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : null; ?>
			</td>
		</tr>
		<?php unset( $itemPage[$x], $amount );?>
	<?php endfor; ?>

	<?php if ( $isLastPage ): ?>
		<?php $mosTotalAfterReduction = 0; ?>

		<?php if ( $reduction_percentage > 0 or $reduction_percentage < 0 ) : ?>
			<tr>
				<td class="footer" style="padding-right:5px;" colspan="4">
					<?php echo $materialOnSitePrintSetting->total_text; ?> (<?php echo $currency->currency_code; ?>):
				</td>
				<td class="footerSumAmount" colspan="2">
					<?php echo !empty( $mosTotal ) ? number_format($mosTotal, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : null; ?>
				</td>
			</tr>

			<?php $percentage       = $reduction_percentage / 100;
			$mosTotalAfterReduction = $mosTotal * $percentage; ?>

			<tr>
				<td style="padding-right:5px;" colspan="4">
					<?php echo number_format($reduction_percentage, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]); ?>
					<?php echo $materialOnSitePrintSetting->percentage_of_material_on_site_text; ?> (<?php echo $currency->currency_code; ?>) :
				</td>
				<td class="footerSumAmount" colspan="2">
					<?php echo !empty( $mosTotalAfterReduction ) ? '(' . number_format($mosTotalAfterReduction, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) . ')' : null; ?>
				</td>
			</tr>
			<tr>
				<td style="padding-right:5px;" colspan="4">
					<?php echo $materialOnSitePrintSetting->carried_to_final_summary_text; ?> (<?php echo $currency->currency_code; ?>):
				</td>
				<td class="footerSumAmount" colspan="2">
					<?php echo !empty( $mosTotal ) ? number_format($mosTotal - $mosTotalAfterReduction, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : null; ?>
				</td>
			</tr>
		<?php else: ?>
			<tr>
				<td class="footer" style="padding-right:5px;" colspan="4">
					<?php echo $materialOnSitePrintSetting->carried_to_final_summary_text; ?> (<?php echo $currency->currency_code; ?>):
				</td>
				<td class="footerSumAmount" colspan="2">
					<?php echo !empty( $mosTotal ) ? number_format($mosTotal - $mosTotalAfterReduction, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : null; ?>
				</td>
			</tr>
		<?php endif; ?>
	<?php else: ?>
		<tr>
			<td class="footer" colspan="<?php echo $headerCount; ?>">
				&nbsp;
			</td>
		</tr>
	<?php endif; ?>

	<?php if ( $isLastPage and $withSignature ): ?>
		<tr>
			<td colspan="<?php echo $headerCount; ?>">
				<?php
				include_partial('printReport/footerLayout', array(
					'leftText'  => $left_text,
					'rightText' => $right_text
				));
				?>
			</td>
		</tr>
	<?php endif; ?>

	<tr>
		<td style="padding-right:5px;padding-top:10px;text-align:center;"
		    colspan="<?php echo $headerCount; ?>">
			Page <?php echo $pageCount; ?>
		</td>
	</tr>
</table>
</body>
</html>