<?php $headerCount = 8; ?>

<table cellpadding="0" cellspacing="0" class="mainTable">
	<tr>
		<td colspan="<?php echo $headerCount; ?>">
			<?php include_partial('printReport/postContractReportHeader', array('reportTitle' => $reportTitle, 'headerDescription' => $headerDescription, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
		</td>
	</tr>
	<tr>
		<td class="bqHeadCell" rowspan="2" style="min-width:70px;width:70px;">Bill Ref</td>
		<td class="bqHeadCell" rowspan="2" style="min-width:280px;width:280px;"><?php echo $descHeader; ?></td>
		<td class="bqHeadCell" rowspan="2" style="min-width:60px;width:60px;">Unit</td>
		<td class="bqHeadCell" rowspan="2" style="min-width:70px;width:70px;">Rate</td>
		<td class="bqHeadCell" colspan="2">Omission</td>
		<td class="bqHeadCell" colspan="2">Addition</td>
	</tr>
	<tr>
		<td class="bqHeadCell" style="min-width:60px;width:60px;">Qty</td>
		<td class="bqHeadCell" style="min-width:100px;width:100px;">Amount</td>
		<td class="bqHeadCell" style="min-width:60px;width:60px;">Qty</td>
		<td class="bqHeadCell" style="min-width:100px;width:100px;">Amount</td>
	</tr>
	<?php

		$rowCount = 0;
		$totalAmount = 0;

		for($x=0; $x <= $maxRows; $x++):

			$headerClass = null;
			$headerStyle = null;
			$itemPadding = 0;

			$itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

			$rowCount++;

			$unit        = $itemRow ? $itemRow[sfRemeasurementItemReportGenerator::ROW_BILL_ITEM_UNIT] : null;
			$rate        = $itemRow ? $itemRow[sfRemeasurementItemReportGenerator::ROW_BILL_ITEM_RATE] : null;

			$qtyOmission = $itemRow ? $itemRow[sfRemeasurementItemReportGenerator::ROW_BILL_ITEM_QTY_OMISSION] : null;
			$amtOmission = $itemRow ? $itemRow[sfRemeasurementItemReportGenerator::ROW_BILL_ITEM_AMT_OMISSION] : null;
			$qtyAddition = $itemRow ? $itemRow[sfRemeasurementItemReportGenerator::ROW_BILL_ITEM_QTY_ADDITION] : null;
			$amtAddition = $itemRow ? $itemRow[sfRemeasurementItemReportGenerator::ROW_BILL_ITEM_AMT_ADDITION] : null;

			$itemId = $itemRow ? $itemRow[0] : null;

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

			<?php if ( $itemRow[4] == BillItem::TYPE_ITEM_RATE_ONLY ): ?>
				<td class="bqRateCell">
					&nbsp;
				</td>
				<td class="bqRateCell">
					&nbsp;
				</td>
				<td class="bqRateCell">
					&nbsp;
				</td>
				<td class="bqRateCell">
					&nbsp;
				</td>
				<td class="bqRateCell">
					&nbsp;
				</td>
				<td class="bqRateCell">
					&nbsp;
				</td>
			<?php else: ?>
				<td class="bqUnitCell"><?php echo $unit; ?></td>
				<td class="bqRateCell">
					<?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate,2) : null ?>
				</td>
				<td class="bqRateCell">
					<?php echo ! $printNoPrice && $qtyOmission && $qtyOmission != 0 ? Utilities::number_clean($qtyOmission) : null ?>
				</td>
				<td class="bqRateCell">
					<?php echo ! $printNoPrice && $amtOmission && $amtOmission != 0 ? '('.number_format($amtOmission,2).')' : null ?>
				</td>
				<td class="bqRateCell">
					<?php echo ! $printNoPrice && $qtyAddition && $qtyAddition != 0 ? Utilities::number_clean($qtyAddition) : null ?>
				</td>
				<td class="bqRateCell">
					<?php echo ! $printNoPrice && $amtAddition && $amtAddition != 0 ? number_format($amtAddition, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
				</td>
			<?php endif; ?>
		</tr>
			<?php unset($itemPage[$x], $amount);?>
		<?php endfor; ?>

		<?php if($printGrandTotal) : $nett = $totalAdditionByElement - $totalOmissionByElement; ?>
			<tr>
				<td class="footer" style="padding-right:5px;" colspan="<?php echo 4; ?>">
					Total (<?php echo $currency; ?>) :
				</td>
				<td class="footerSumAmount" colspan="2">
					<?php echo ($totalOmissionByElement && $totalOmissionByElement != 0) ? '('.number_format($totalOmissionByElement, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]).')' : null ;?>
				</td>
				<td class="footerSumAmount" colspan="2">
					<?php echo ($totalAdditionByElement && $totalAdditionByElement != 0) ? number_format($totalAdditionByElement,2) : null ;?>
				</td>
			</tr>
			<tr>
				<td style="padding-right:5px;" colspan="<?php echo 4; ?>">
					Nett Addition/Omission (<?php echo $currency; ?>) :
				</td>
				<td class="footerSumAmount" colspan="4">
					<?php echo ($nett && $nett != 0) ?  number_format($nett, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
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