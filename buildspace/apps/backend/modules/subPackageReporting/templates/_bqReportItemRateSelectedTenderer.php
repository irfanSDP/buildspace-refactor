<table cellpadding="0" cellspacing="0" class="mainTable">
	<tr>
		<?php $headerCount = 7; ?>

		<td colspan="<?php echo $headerCount; ?>">
			<?php include_partial('printReport/bqReportHeader', array('reportTitle' => $reportTitle,'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
		</td>
	</tr>
	<tr>
		<td class="bqHeadCell" style="min-width:80px;width:80px;">Bill Ref</td>
		<td class="bqHeadCell" style="min-width:400px;width:400px;"><?php echo $descHeader; ?></td>
		<td class="bqHeadCell" style="min-width:80px;width:80px;">Unit</td>
		<td class="bqHeadCell" style="min-width:100px;width:100px;">Estimate</td>

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
	$rowCount    = 0;
	$totalAmount = 0;

	for($x=0; $x <= $maxRows; $x++):
		$itemPadding    = 0;
		$itemRow        = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;
		$headerClass    = null;
		$headerStyle    = null;
		$itemPadding    = 6;
		$itemId         = $itemRow ? $itemRow[0] : null;
		$selectedRates  = $itemRow ? $itemRow[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_SUB_CON_RATE] : null;
		$diffPercentage = $itemRow ? $itemRow[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_DIFF_PERCENT] : null;
		$diffAmt        = $itemRow ? $itemRow[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_DIFF_AMT] : null;

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

		if($itemRow[4] == BillItem::TYPE_ITEM_NOT_LISTED)
		{
			$rate = $itemRow ? $itemRow[6][0] : null;
		}
		else
		{
			$rate = $itemRow ? $itemRow[6] : null;
		}

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

		if ( $indentItem AND $itemRow AND ($itemRow[4] != sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $itemRow[4] != BillItem::TYPE_HEADER AND $itemRow[4] != BillItem::TYPE_HEADER_N) )
		{
			$itemPadding = 15;
		}
		?>
		<tr>
			<td class="bqCounterCell"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
			<td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
				<?php if($itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_PC_RATE):?>
				<?php include_partial('printBQ/primeCostRateTable', array('currency'=>$currency, 'itemRow'=>$itemRow, 'priceFormatting'=> $priceFormatting, 'printNoPrice' => $printNoPrice)) ?>
				<?php else:?>
				<?php $preClass = $headerClass ? $headerClass : 'description'?>
				<?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
				<?php endif?>
			</td>

			<td class="bqUnitCell"><?php echo ($itemRow AND $itemId) ? $itemRow[sfSubPackageItemRateSelectedTendererPageGenerator::ROW_BILL_ITEM_UNIT] : null; ?></td>

			<td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>

			<td class="bqRateCell"><?php echo ! $printNoPrice && $itemId > 0 && $selectedRates != 0 ? number_format($selectedRates, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>

			<td class="bqRateCell"><?php echo ! $printNoPrice && $itemId > 0 && $diffPercentage != 0 ? number_format($diffPercentage, 2, $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]).' %' : null ?></td>

			<td class="bqRateCell"><?php echo ! $printNoPrice && $itemId > 0 && $diffAmt != 0 ? number_format($diffAmt, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
		</tr>
		<?php unset($itemPage[$x], $amount);?>
	<?php endfor; ?>

	<?php if($printGrandTotal) : ?>
		<?php
		$elementTotal       = isset($estimateElementTotal[$elementId]) ? $estimateElementTotal[$elementId] : null;
		$elementSubConTotal = isset($estimateElementSubConTotal[$elementId]) ? $estimateElementSubConTotal[$elementId] : null;
		?>
		<tr>
			<td class="footer" style="padding-right:5px;" colspan="<?php echo 3;?>">
				<?php echo $elementHeaderDescription; ?> (<?php echo $currency; ?>) :
			</td>
			<td class="footerSumAmount">
				<?php echo ($elementTotal) ? number_format($elementTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
			</td>
			<td class="footerSumAmount">
				<?php echo ($elementSubConTotal) ? number_format($elementSubConTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
			</td>
			<td class="footerSumAmount">
				<?php echo ($elementTotal && $elementTotal > 0) ? number_format(($elementSubConTotal - $elementTotal) / $elementTotal * 100, 2).' %' : null; ?>
			</td>
			<td class="footerSumAmount">
				<?php echo ($elementTotal) ? number_format($elementSubConTotal - $elementTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
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