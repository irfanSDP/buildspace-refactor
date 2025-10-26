<?php list( $totalProjectAmount, $totalClaimAmount ) = 0;
$summaryTitleRowCount = count($summaryTitleRows); ?>

<table cellpadding="0" cellspacing="0" class="mainTable">
	<tr>
		<td style="width:40px;">&nbsp;</td>
		<td style="min-width:400px;width:400px;">&nbsp;</td>
		<td style="min-width:160px;width:160px;">&nbsp;</td>
		<td style="min-width:80px;width:80px;">&nbsp;</td>
		<td style="min-width:160px;width:160px;">&nbsp;</td>
	</tr>
	<?php foreach ( $summaryTitleRows as $summaryTitleRow ): ?>
		<tr>
			<td class="summaryTitle" colspan="5"><?php echo $summaryTitleRow->offsetGet(0) ?></td>
		</tr>
	<?php endforeach; ?>

	<tr>
		<td class="summaryTitle" colspan="5">&nbsp;</td>
	</tr>
	<tr>
		<td class="headCell" rowspan="2">Item</td>
		<td class="headCell" rowspan="2">Description</td>
		<td class="headCell" rowspan="2">Contract Amount</td>
		<td class="headCell" colspan="2">Total Claimed</td>
	</tr>
	<tr>
		<td class="headCell">%</td>
		<td class="headCell">Amount</td>
	</tr>
	<?php
	for ( $i = 0; $i <= $MAX_ROWS - $summaryTitleRowCount; $i ++ ):
		$itemRow = array_key_exists($i, $itemPage) ? $itemPage[$i] : false;

		$border    = $itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_TYPE] == \sfBuildSpacePostContractClaimWithSubPackageReportGenerator::TOTAL_ROW_TYPE ? 'border:1px solid black;' : null;
		$textAlign = $itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_TYPE] == \sfBuildSpacePostContractClaimWithSubPackageReportGenerator::TOTAL_ROW_TYPE ? 'text-align: right;' : null;

		$totalProjectAmount += $itemRow ? $itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL] : 0;
		$totalClaimAmount += $itemRow ? $itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT] : 0;
		?>

		<?php if ( $itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_TYPE] == sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUBPACKAGE_HEADER_TYPE ): ?>
		<tr>
			<td colspan="5"
			    style="text-align: center; border:1px solid black; font-weight: bold;"><?php echo $itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_TITLE]; ?></td>
		</tr>
	<?php else: ?>
		<tr>
			<td class="referenceCharCell"
			    style="font-weight: normal;<?php echo $border; ?>"><?php echo $itemRow ? $itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_CHAR_REF] : '&nbsp;' ?></td>
			<td class="descriptionCell"
			    style="<?php echo $border, $textAlign; ?>"><?php echo $itemRow ? $itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_TITLE] : '&nbsp;' ?></td>
			<td class="amountCell"
			    style="<?php echo $border; ?>"><?php echo ( $itemRow and $itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL] ) ? number_format($itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL], 2, '.', ',') : '&nbsp;' ?></td>
			<td class="amountCell"
			    style="<?php echo $border; ?>"><?php echo ( $itemRow and $itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_PERCENTAGE] ) ? number_format($itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_PERCENTAGE], 2, '.', ',') . '%' : '&nbsp;' ?></td>
			<td class="amountCell"
			    style="<?php echo $border; ?>"><?php echo ( $itemRow and $itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT] ) ? number_format($itemRow[sfBuildSpacePostContractClaimWithSubPackageReportGenerator::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT], 2, '.', ',') : '&nbsp;' ?></td>
		</tr>
	<?php endif; ?>
	<?php endfor; ?>

	<?php if ( $isLastPage ): ?>
		<tr>
			<td class="footer" colspan="2"
			    style="border-top:1px solid #000;border-bottom:1px solid #000;padding: 5px 5px 5px 0;text-align: right;">
				<?php echo "Total Sub Packages ({$currency})"; ?>
			</td>
			<td class="footerSumAmount"><?php echo ( $overallSubPackageContractAmount != 0 ) ? number_format($overallSubPackageContractAmount, 2, '.', ',') : '&nbsp;' ?></td>
			<td class="footerSumAmount">&nbsp;</td>
			<td class="footerSumAmount"><?php echo ( $overallSubPackageClaimAmount != 0 ) ? number_format($overallSubPackageClaimAmount, 2, '.', ',') : '&nbsp;' ?></td>
		</tr>
		<tr>
			<td class="footer" colspan="2"
			    style="border-top:1px solid #000;border-bottom:1px solid #000;padding: 5px 5px 5px 0;text-align: right;">
				<?php echo "Nett ({$currency})"; ?>
			</td>
			<td class="footerSumAmount" style="font-weight: bold;">
				<?php echo ( $overallSubPackageContractAmount != 0 ) ? number_format($overallContractAmount - $overallSubPackageContractAmount, 2, '.', ',') : '&nbsp;' ?>
			</td>
			<td class="footerSumAmount" style="font-weight: bold;">
				&nbsp;
			</td>
			<td class="footerSumAmount" style="font-weight: bold;">
				<?php echo ( $overallSubPackageClaimAmount != 0 ) ? number_format($overallContractClaimAmount - $overallSubPackageClaimAmount, 2, '.', ',') : '&nbsp;' ?>
			</td>
		</tr>
	<?php else: ?>
		<tr>
			<td colspan="5" style="border-top:1px solid #000;">&nbsp;</td>
		</tr>
	<?php endif; ?>

	<tr>
		<td class="pageNumberCell" colspan="5" style="line-height:12px;vertical-align:text-bottom;padding: 20px 0 0 0;">
			<?php echo $pageNumber; ?>
		</td>
	</tr>
</table>
</body>
</html>