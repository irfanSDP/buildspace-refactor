<?php list($totalProjectAmount, $totalClaimAmount) = 0; ?>

<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
	    <td style="width:40px;">&nbsp;</td>
	    <td style="min-width:400px;width:400px;">&nbsp;</td>
	    <td style="min-width:160px;width:160px;">&nbsp;</td>
	    <td style="min-width:80px;width:80px;">&nbsp;</td>
	    <td style="min-width:160px;width:160px;">&nbsp;</td>
    </tr>
    <?php
    foreach($summaryTitleRows as $summaryTitleRow):
        ?>
        <tr>
            <td class="summaryTitle" colspan="5"><?php echo $summaryTitleRow->offsetGet(0)?></td>
        </tr>
    <?php
    endforeach;
    ?>
	<tr>
		<td colspan="3" style="font-weight: bold; text-align: left;">SUMMARY</td>
		<td colspan="2" style="font-weight: bold; text-align: right;">Interim Valuation No: <?php echo $revision['version']; ?></td>
	</tr>
	<tr>
		<td colspan="5" style="text-align: right;"><strong>Date of Printing:</strong> <?php echo date("d/M/Y")?></td>
	</tr>

	<tr>
		<td class="summaryTitle" colspan="5">&nbsp;</td>
	</tr>
    <tr>
        <td class="headCell" rowspan="2">Item</td>
        <td class="headCell" rowspan="2">Description</td>
	    <td class="headCell" rowspan="2">Contract Amount</td>
	    <td class="headCell" colspan="2">Work Done</td>
    </tr>
	<tr>
		<td class="headCell">%</td>
		<td class="headCell">Amount</td>
	</tr>
    <?php
    for($i=0; $i<=$MAX_ROWS - count($summaryTitleRows);$i++):
        $itemRow = array_key_exists($i, $itemPage) ? $itemPage[$i] : false;

	    $fontWeight     = $itemRow[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_TYPE] != ProjectStructure::TYPE_BILL ? "font-weight:bold;" : "";
	    $fontStyle      = NULL;
	    $textDecoration = NULL;

        $totalProjectAmount += $itemRow ? $itemRow[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL]: 0;
	    $totalClaimAmount   += $itemRow ? $itemRow[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT]: 0;
        ?>
    <tr>
        <td class="referenceCharCell" style="font-weight: normal;"><?php echo $itemRow ? $itemRow[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_CHAR_REF]: '&nbsp;'?></td>
        <td class="descriptionCell" style="<?php echo $fontWeight?><?php echo $fontStyle?><?php echo $textDecoration?>"><?php echo $itemRow ? $itemRow[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_TITLE]: '&nbsp;'?></td>
	    <td class="amountCell"><?php echo ($withPrice and $itemRow and $itemRow[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL] )? number_format($itemRow[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_CONTRACT_TOTAL], 2, '.', ',') : '&nbsp;'?></td>
        <td class="amountCell"><?php echo ($withPrice and $itemRow and $itemRow[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_PERCENTAGE] )? number_format($itemRow[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_PERCENTAGE], 2, '.', ',').'%' : '&nbsp;'?></td>
	    <td class="amountCell"><?php echo ($withPrice and $itemRow and $itemRow[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT] )? number_format($itemRow[sfBuildSpacePostContractClaimReportGenerator::SUMMARY_ITEM_PROPERTY_UP_TO_DATE_CLAIM_AMT], 2, '.', ','): '&nbsp;'?></td>
    </tr>
    <?php endfor; ?>

	<?php if ( ! $isLastPage ): ?>
		<tr>
			<td class="footer" colspan="2" style="border-top:1px solid #000;border-bottom:1px solid #000;padding: 5px 5px 5px 0;text-align: right;">
				<?php echo "Total Contract Amount ({$currency}) to Next Page"; ?>
			</td>
			<td class="footerSumAmount"><?php echo ($withPrice and $totalProjectAmount != 0) ? number_format($totalProjectAmount, 2, '.', ',') : '&nbsp;'?></td>
			<td class="footerSumAmount">&nbsp;</td>
			<td class="footerSumAmount"><?php echo ($withPrice and $totalClaimAmount != 0) ? number_format($totalClaimAmount, 2, '.', ',') : '&nbsp;'?></td>
		</tr>
	<?php else: ?>
		<tr>
			<td class="footer" colspan="2" style="border-top:1px solid #000;border-bottom:1px solid #000;padding: 5px 5px 5px 0;text-align: right;">
				<?php echo "Total Contract Amount ({$currency})"; ?>
			</td>
			<td class="footerSumAmount"><?php echo ($withPrice and $overallTotalProjectAmount != 0) ? number_format($overallTotalProjectAmount, 2, '.', ',') : '&nbsp;'?></td>
			<td class="footerSumAmount"><?php echo ($withPrice and $overallTotalClaimAmount != 0) ? number_format(Utilities::percent($overallTotalClaimAmount, $overallTotalProjectAmount), 2, '.', ',').'%' : '&nbsp;'?></td>
			<td class="footerSumAmount"><?php echo ($withPrice and $overallTotalClaimAmount != 0) ? number_format($overallTotalClaimAmount, 2, '.', ',') : '&nbsp;'?></td>
		</tr>
	<?php endif; ?>

    <?php if(!$isLastPage):
        for($x=0;$x<=11;$x++)://empty row so we can print page number at the bottom of page
    ?>
	    <tr>
	        <td colspan="5">&nbsp;</td>
	    </tr>
    <?php endfor; else: ?>
	    <?php include_partial('postContractReport/printClaimReportAdditionalInformation', array(
		    'currency'            => $currency,
		    'withPrice'           => $withPrice,
            'totalProjectAmount'  => $overallTotalProjectAmount,
		    'totalClaimAmount'    => $overallTotalClaimAmount,
		    'additionalAutoBills' => $additionalAutoBills,
	    )); ?>
    <?php endif; ?>

    <tr>
        <td class="pageNumberCell" colspan="5" style="line-height:12px;vertical-align:text-bottom">
            <?php echo $pageNumber; ?>
        </td>
    </tr>
</table>
</body>
</html>