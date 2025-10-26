<table cellpadding="0" cellspacing="0" class="mainTable">
	<tr>
		<?php $headerCount = 3+(count($subCons)); ?>

		<td colspan="<?php echo $headerCount; ?>">
			<?php include_partial('printReport/bqReportHeader', array('reportTitle' => $reportTitle, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2 )); ?>
		</td>
	</tr>
	<tr>
		<td class="bqHeadCell" style="min-width:80px;width:80px;">No</td>

		<?php
			switch(count($subCons))
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

			if(count($subCons) > 3)
			{
				$descriptionWidth = 360;
			}

			$descriptionWidth+=80;
		?>

		<td class="bqHeadCell" style="min-width:400px;width:<?php echo $descriptionWidth; ?>;"><?php echo $descHeader; ?></td>
		<td class="bqHeadCell" style="min-width:100px;width:100px;"><?php echo "Estimate"; ?></td>

		<?php if ( count($subCons) ): ?>
			<?php foreach($subCons as $k => $subCon) : ?>
				<td class="bqHeadCell" style="min-width:100px;width:100px;">
					<?php if ( isset($subCon['selected']) AND $subCon['selected'] ) echo '<span style="color:red;">* </span>'; ?>

					<?php $subConName = CompanyTable::formatCompanyName($subCon); ?>

					<?php if ( isset($subCon['selected']) AND $subCon['selected'] ): ?>
						<span style="color:blue;"><?php echo $subConName; ?></span>
					<?php else: ?>
						<?php echo $subConName; ?>
					<?php endif; ?>
				</td>
			<?php endforeach; ?>
		<?php endif; ?>
	</tr>
	<?php
	$rowCount    = 0;
	$totalAmount = 0;
	$subConTotal = array();

	foreach($subCons as $k => $subCon)
	{
		$subConTotal[$subCon['id']] = 0;
	}

	for($x=0; $x <= $maxRows; $x++):

		$rowCount++;

		$itemPadding  = 0;
		$itemRow      = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;
		$rate         = $itemRow ? $itemRow[sfSubPackageBillSummaryAllTendererPageGenerator::ROW_BILL_ITEM_ESTIMATE_AMOUNT] : null;
		$itemId       = $itemRow ? $itemRow[0] : null;
		$lowestStyle  = '';
		$highestStyle = '';

		if($itemId > 0 && count($subCons))
		{
			$lowestSubConId  = null;
			$highestSubConId = null;

			$listOfRates = array();

            foreach($subCons as $k => $subCon)
            {
                if( array_key_exists($subCon['id'], $subConsBillTotals) && array_key_exists($itemId, $subConsBillTotals[ $subCon['id'] ]) )
                {
                    array_push($listOfRates, $subConsBillTotals[ $subCon['id'] ][ $itemId ]);
                }
            }

			$lowestRate      = count($listOfRates) ? min($listOfRates) : 0;
			$highestRate     = count($listOfRates) ? max($listOfRates) : 0;

			$lowestSubConId  = $subCons[array_search($lowestRate, $listOfRates)]['id'];
			$highestSubConId = $subCons[array_search($highestRate, $listOfRates)]['id'];

			if ($lowestSubConId != $highestSubConId)
			{
				$highestStyle = "font-weight:bold;color:#ee4559;font-style:italic;";
				$lowestStyle  = "font-weight:bold;font-style:italic;color:#adf393;text-decoration:underline;";
			}

			$counter++;
		}

		$headerClass = null;
		$headerStyle = null;
		$itemPadding = 6;
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

			<td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
			<?php if(count($subCons)) : $counter = 1; ?>
                <?php foreach($subCons as $k => $subCon) : $subConTotal[$subCon['id']] += isset($subConsBillTotals[$subCon['id']][$itemId]) ? $subConsBillTotals[$subCon['id']][$itemId] : 0; ?>
					<td class="bqRateCell" style="<?php
						if($subCon['id'] == $lowestSubConId)
						{
							echo $lowestStyle;
						}
						else if($subCon['id'] == $highestSubConId)
						{
							echo $highestStyle;
						}
						else
						{
							echo "";
						}
					?>">
                        <?php echo ! $printNoPrice  && $itemId > 0 && $subConsBillTotals && isset($subConsBillTotals[$subCon['id']][$itemId]) && $subConsBillTotals[$subCon['id']][$itemId] != 0 ? number_format($subConsBillTotals[$subCon['id']][$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
					</td>
				<?php endforeach;?>
			<?php endif;?>
		</tr>
		<?php unset($itemPage[$x], $amount);?>
		<?php endfor; ?>

		<?php if($lastPage) : ?>
			<tr>
				<td class="footer" style="padding-right:5px;" colspan="<?php echo 2; ?>">
					<?php echo "Total "; ?> (<?php echo $currency; ?>) :
				</td>
				<td class="footerSumAmount">
					<?php echo ($totalEstimateAmt && $totalEstimateAmt != 0) ?  number_format($totalEstimateAmt, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ;?>
				</td>
				<?php foreach($subCons as $k => $subCon) : ?>
					<td class="footerSumAmount">
                        <?php echo ( isset( $subConTotal[ $subCon['id'] ] ) && $subConTotal[ $subCon['id'] ] != 0 ) ? number_format($subConTotal[ $subCon['id'] ], $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null; ?>
					</td>
				<?php endforeach;?>
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