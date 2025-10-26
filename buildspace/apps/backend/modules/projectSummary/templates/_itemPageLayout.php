<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <td style="min-width:40px;width:40px;"></td>
        <td style="min-width:400px;width:400px;"></td>
        <td style="min-width:150px;width:150px;"></td>
        <td style="min-width:200px;width:200px;"></td>
    </tr>
    <?php
    foreach($summaryTitleRows as $summaryTitleRow):
        ?>
        <tr>
            <td class="summaryTitle" colspan="4"><?php echo $summaryTitleRow->offsetGet(0)?></td>
        </tr>
    <?php
    endforeach;
    ?>
    <tr>
        <td class="headCell" style="min-width:40px;width:40px;">Item</td>
        <td class="headCell" style="min-width:400px;width:400px;">Description</td>
        <td class="headCell" style="min-width:150px;width:150px;">Page</td>
        <td class="headCell" style="min-width:200px;width:200px;">Amount (<?php echo $currency?>)</td>
    </tr>
    <?php
    for($i=0; $i<=$MAX_ROWS - count($summaryTitleRows);$i++):
        $itemRow = array_key_exists($i, $itemPage) ? $itemPage[$i] : false;

        $fontWeight = $itemRow[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_STYLE_IS_BOLD] ? "font-weight:bold;" : "";
        $fontStyle = $itemRow[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_STYLE_IS_ITALIC] ? "font-style:italic;" : "";
        $textDecoration = $itemRow[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_STYLE_IS_UNDERLINE] ? "text-decoration:underline;" : "";

        $totalProjectAmount += $itemRow ? $itemRow[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_TOTAL_AMOUNT]: 0;
        ?>
    <tr>
        <td class="referenceCharCell"><?php echo $itemRow ? $itemRow[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_CHAR_REF]: '&nbsp;'?></td>
        <td class="descriptionCell" style="<?php echo $fontWeight?><?php echo $fontStyle?><?php echo $textDecoration?>"><?php echo $itemRow ? $itemRow[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_TITLE]: '&nbsp;'?></td>
        <td class="pageCell"><?php echo $itemRow ? $itemRow[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_LATEST_SUMMARY_PAGE]: '&nbsp;'?></td>
        <td class="amountCell"><?php echo ($withPrice and $itemRow and $itemRow[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_TOTAL_AMOUNT] )? number_format($itemRow[sfBuildspaceProjectSummaryGenerator::SUMMARY_ITEM_PROPERTY_TOTAL_AMOUNT], 2, '.', ','): '&nbsp;'?></td>
    </tr>
    <?php
    endfor;
    ?>
    <?php if($includeTax && $withPrice) { ?>
        <tr>
            <td class="footer" colspan="3" style="border-top:1px solid #000;padding-top:5px;">Total Amount</td>
            <td class="footerSumAmount"><?php echo ($withPrice and $totalProjectAmount != 0) ? number_format($totalProjectAmount, 2, '.', ',') : '&nbsp;'?></td>
        </tr>
        <tr>
            <td class="footer" colspan="3" style="border-top:1px solid #000;padding-top:5px;">
                <?php echo $taxName . ' ' . number_format($taxPercentage, 2, '.', ',') . '%'; ?>
            </td>
            <?php $taxAmount = $totalProjectAmount * ($taxPercentage / 100); ?>
            <td class="footerSumAmount"><?php echo number_format($taxAmount, 2, '.', ','); ?></td>
        </tr>
    <?php } ?>
    <tr>
        <td class="footer" colspan="3" style="border-top:1px solid #000;padding-top:5px;">
            <?php echo $isLastPage ? $projectSummaryFooter->first_row_text: ""; ?>
        </td>
        <?php if($includeTax) { $totalProjectAmount +=  $taxAmount; } ?>
        <td class="footerSumAmount" rowspan="2"><?php echo ($withPrice and $totalProjectAmount != 0) ? number_format(Utilities::getRoundedAmount($eProjectOriginId, $totalProjectAmount), 2, '.', ',') : '&nbsp;'?></td>
    </tr>
    <tr>
        <td class="footer" colspan="3" style="border-bottom:1px solid #000;padding-bottom:5px;">
            <?php echo $isLastPage ? $projectSummaryFooter->second_row_text: $carriedToNextPageText;?>
        </td>
    </tr>
    <?php
    if($isLastPage)
    {
        include_partial('projectSummary/footerLayout', array(
            'leftText'               => $projectSummaryFooter->left_text,
            'rightText'              => $projectSummaryFooter->right_text,
            'additionalDescriptions' => $additionalDescriptions,
        ));
    }
    ?>
    <?php if(!$isLastPage):
        for($x=0;$x<=20;$x++)://empty row so we can print page number at the bottom of page
    ?>
    <tr>
        <td colspan="4">&nbsp;</td>
    </tr>
    <?php endfor;endif?>
    <tr>
        <td colspan="4">&nbsp;</td>
    </tr>
    <tr>
        <td class="pageNumberCell" colspan="4" style="line-height:12px;vertical-align:text-bottom">
            <?php
                echo $pageNumber;

                if($includePrintingDate):
            ?>
            <div style="float:right;font-size:10px;"><b>Date of Printing:</b> <?php echo date("d/M/Y")?></div>
            <?php endif; ?>
        </td>
    </tr>
</table>
<?php if($isLastPage):?>
</body>
</html>
<?php endif; ?>