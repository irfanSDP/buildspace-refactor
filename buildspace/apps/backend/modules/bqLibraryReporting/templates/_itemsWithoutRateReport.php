<?php $headerCount = 3; ?>

<table cellpadding="0" cellspacing="0" class="mainTable">
	<tr>
		<td colspan="<?php echo $headerCount; ?>">
			<?php include_partial('printReport/postContractReportHeader', array('reportTitle' => $reportTitle, 'headerDescription' => $headerDescription, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
		</td>
	</tr>
	<tr>
		<td class="bqHeadCell" style="min-width:70px;width:70px;">No</td>
		<td class="bqHeadCell" style="min-width:410px;width:410px;">Description</td>
		<td class="bqHeadCell" style="min-width:180px;width:180px;">Unit</td>
	</tr>
	<?php

		$rowCount = 0;
		$totalAmount = 0;

		for($x=0; $x <= $maxRows; $x++):

			$headerClass = null;
			$headerStyle = null;
			$itemPadding = 6;

			$itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

			$rowCount++;

			$unit   = $itemRow ? $itemRow[sfScheduleOfRateItemReportGenerator::ROW_BILL_ITEM_UNIT] : null;
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
		?>
		<tr>
			<td class="bqCounterCell"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
			<td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
				<?php $preClass = $headerClass ? $headerClass : 'description'?>
				<?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
			</td>
			<td class="bqUnitCell"><?php echo $unit; ?></td>
		</tr>
			<?php unset($itemPage[$x], $amount);?>
		<?php endfor; ?>

	<tr>
		<td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
			Page <?php echo $pageCount; ?>
		</td>
	</tr>
</table>
</body>
</html>