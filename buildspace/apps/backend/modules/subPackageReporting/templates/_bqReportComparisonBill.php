<table cellpadding="0" cellspacing="0" class="mainTable">
	<tr>
		<?php $headerCount = 6; ?>

		<td colspan="<?php echo $headerCount; ?>">
			<?php include_partial('printReport/bqReportHeader', array('reportTitle' => $reportTitle, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
		</td>
	</tr>
	<tr>
		<td class="bqHeadCell" style="min-width:80px;width:80px;">No</td>
		<td class="bqHeadCell" style="min-width:480px;width:480px;"><?php echo $descHeader; ?></td>
		<td class="bqHeadCell" style="min-width:100px;width:100px;"><?php echo "Estimate"; ?></td>
		<td class="bqHeadCell" style="min-width:100px;width:100px;">
			<span style="color: red;">* </span>
			<span style="color: blue;">
			<?php
				if($selectedSubCon)
				{
					if(strlen($selectedSubCon['shortname']))
					{
						echo $selectedSubCon['shortname'];
					}
					else
					{
						echo (strlen($selectedSubCon['name']) > 15) ? substr($selectedSubCon['name'],0,12).'...' : $selectedSubCon['name'];
					}
				}
				else
				{
					echo "";
				}
			?>
			</span>
		</td>

		<td class="bqHeadCell" style="min-width:120px;width:120px;"><?php echo "Difference (%)"; ?></td>
		<td class="bqHeadCell" style="min-width:120px;width:120px;"><?php echo "Difference (Amount)"; ?></td>

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

		$itemRow     = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

		$rowCount++;

		$rate           = $itemRow ? $itemRow[6] : null;
		$itemId         = $itemRow ? $itemRow[0] : null;
		$estimateAmt    = $itemRow ? $itemRow[sfSubPackageBillSummarySelectedTendererPageGenerator::ROW_BILL_ITEM_ESTIMATE_AMOUNT] : null;
		$subConAmt      = $itemRow ? $itemRow[sfSubPackageBillSummarySelectedTendererPageGenerator::ROW_BILL_ITEM_AMOUNT] : null;
		$diffPercentage = $itemRow ? $itemRow[sfSubPackageBillSummarySelectedTendererPageGenerator::ROW_BILL_ITEM_DIFF_PERCENT] : null;
		$diffAmt        = $itemRow ? $itemRow[sfSubPackageBillSummarySelectedTendererPageGenerator::ROW_BILL_ITEM_DIFF_AMOUNT] : null;
		$headerClass    = null;
		$headerStyle    = null;
		$itemPadding    = 6;
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

			<td class="bqRateCell">
				<?php echo ! $printNoPrice && $estimateAmt && $estimateAmt != 0 ? number_format($estimateAmt, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
			</td>
			<td class="bqRateCell">
				<?php echo ! $printNoPrice && $subConAmt && $subConAmt != 0 ? number_format($subConAmt, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
			</td>
			<td class="bqRateCell">
				<?php echo ! $printNoPrice && $diffPercentage && $diffPercentage != 0 ? number_format($diffPercentage, 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]).' %' : null ?>
			</td>
			<td class="bqRateCell">
				<?php echo ! $printNoPrice && $diffAmt && $diffAmt != 0 ? number_format($diffAmt, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
			</td>
		</tr>
	<?php unset($itemPage[$x], $amount);?>
	<?php endfor; ?>

	<?php if($lastPage) : ?>

	<?php
		$diffAmt        = $totalSubConAmt - $totalEstimateAmt;
		$diffPercentage = 0;

		if ( $totalEstimateAmt != 0 )
		{
			$diffPercentage = Utilities::prelimRounding(Utilities::percent($diffAmt, $totalEstimateAmt));
		}
	?>
		<tr>
			<td class="footer" style="padding-right:5px;" colspan="<?php echo 2; ?>">
				<?php echo "Total "; ?> (<?php echo $currency; ?>) :
			</td>
			<td class="footerSumAmount">
				<?php echo ! $printNoPrice && $totalEstimateAmt && $totalEstimateAmt != 0 ? number_format($totalEstimateAmt, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
			</td>
			<td class="footerSumAmount">
				<?php echo ! $printNoPrice && $totalSubConAmt && $totalSubConAmt != 0 ? number_format($totalSubConAmt, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
			</td>
			<td class="footerSumAmount">
				<?php echo ! $printNoPrice && $diffPercentage && $diffPercentage != 0 ? number_format($diffPercentage, 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]).' %' : null ?>
			</td>
			<td class="footerSumAmount">
				<?php echo ! $printNoPrice && $diffAmt && $diffAmt != 0 ? number_format($diffAmt, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
			</td>
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