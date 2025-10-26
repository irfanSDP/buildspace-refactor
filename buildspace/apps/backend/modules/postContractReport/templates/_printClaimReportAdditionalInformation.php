<?php

    $additionalProjectAmount = $additionalAutoBills[PostContractClaim::TYPE_VARIATION_ORDER]['overall_total_after_markup'];

    $overallProjectTotal = $totalProjectAmount + $additionalProjectAmount;

    $additionalClaimAmount = $additionalAutoBills[PostContractClaim::TYPE_VARIATION_ORDER]['up_to_date_amount'] + $additionalAutoBills[PostContractClaim::TYPE_MATERIAL_ON_SITE]['overall_total_after_markup'];

    $overallClaimTotal = $totalClaimAmount + $additionalClaimAmount;
?>

<style>
    .sides-bordered {
        border-left: 1px solid black;
        border-right: 1px solid black;
    }
</style>

<tr class="sides-bordered">
    <td colspan="2">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
</tr>
<tr class="sides-bordered">
    <td>&nbsp;</td>
    <td style="text-align: left; font-weight: bold; text-decoration: underline;">Additional</td>
    <td class="sides-bordered">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
</tr>
<tr class="sides-bordered">
    <td colspan="2">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
</tr>
<tr class="sides-bordered" style="border-bottom: 1px solid black;">
    <td>&nbsp;</td>
    <td style="text-align: left;">1) <?php echo $additionalAutoBills[PostContractClaim::TYPE_VARIATION_ORDER]['title']; ?></td>
    <td class="amountCell sides-bordered"><?php echo ($withPrice and $additionalAutoBills[PostContractClaim::TYPE_VARIATION_ORDER]['overall_total_after_markup'] != 0) ? number_format($additionalAutoBills[PostContractClaim::TYPE_VARIATION_ORDER]['overall_total_after_markup'], 2, '.', ',') : '&nbsp;'?></td>
    <td class="amountCell"><?php echo ($withPrice and $additionalAutoBills[PostContractClaim::TYPE_VARIATION_ORDER]['up_to_date_percentage'] != 0) ? number_format($additionalAutoBills[PostContractClaim::TYPE_VARIATION_ORDER]['up_to_date_percentage'], 2, '.', ',').'%' : '&nbsp;'?></td>
    <td class="amountCell"><?php echo ($withPrice and $additionalAutoBills[PostContractClaim::TYPE_VARIATION_ORDER]['up_to_date_amount'] != 0) ? number_format($additionalAutoBills[PostContractClaim::TYPE_VARIATION_ORDER]['up_to_date_amount'], 2, '.', ',') : '&nbsp;'?></td>
</tr>
<tr class="sides-bordered">
    <td colspan="2">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
</tr>
<tr class="sides-bordered">
    <td>&nbsp;</td>
    <td style="text-align: left;">2) <?php echo $additionalAutoBills[PostContractClaim::TYPE_MATERIAL_ON_SITE]['title']; ?></td>
    <td class="sides-bordered">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
    <td class="amountCell sides-bordered"><?php echo ($withPrice and $additionalAutoBills[PostContractClaim::TYPE_MATERIAL_ON_SITE]['overall_total_after_markup'] != 0) ? number_format($additionalAutoBills[PostContractClaim::TYPE_MATERIAL_ON_SITE]['overall_total_after_markup'], 2, '.', ',') : '&nbsp;'?></td>
</tr>
<tr class="sides-bordered">
    <td colspan="2">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
    <td class="sides-bordered">&nbsp;</td>
</tr>
<tr style="border: 1px solid black;">
    <td style="padding: 5px 0;">&nbsp;</td>
    <td style="text-align: center; border-right: 1px solid black;"><strong>TOTAL CARRIED TO CERTIFICATE (<?php echo $currency; ?>)</strong></td>
    <td class="amountCell"><?php echo ($withPrice and $overallProjectTotal != 0) ? number_format($overallProjectTotal, 2, '.', ',') : '&nbsp;'?></td>
    <td colspan="2" class="amountCell"><?php echo ($withPrice and $overallClaimTotal != 0) ? number_format($overallClaimTotal, 2, '.', ',') : '&nbsp;'?></td>
</tr>
<tr>
    <td colspan="5">&nbsp;</td>
</tr>