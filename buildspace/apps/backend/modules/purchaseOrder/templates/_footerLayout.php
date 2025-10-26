<?php $amounts = array(); ?>

<tr>
    <td class="footer" colspan="5" style="border:1px solid #000;padding:5px 3px;text-align:right;">Total (<?php echo $currencyCode; ?>):</td>
    <td class="footer" style="border:1px solid #000;padding:5px 3px;text-align:right;">
        <?php echo (empty((float) $grandTotal)) ? null : number_format($grandTotal, $printWithoutCents, $priceFormat[0], $priceFormat[1]); ?>
    </td>
</tr>

<?php foreach ( $taxes as $tax ): if ( empty($tax->tax_name) ) continue; ?>
    <?php
    $percentage = number_format($tax->percentage, 2, '.', '');

    $currentTaxAmount = number_format($grandTotal * ($percentage / 100), 2, '.', '');

    $grandTotal += $currentTaxAmount;

    $rowTaken++;
    ?>
    <tr>
        <td class="footer" colspan="5" style="border:1px solid #000;padding:5px 3px;text-align:right;"><?php echo "{$tax->tax_name} ({$percentage}%)"; ?>:</td>
        <td class="footer" style="border:1px solid #000;padding:5px 3px;text-align:right;">
            <?php echo (empty((float) $currentTaxAmount)) ? null : number_format($currentTaxAmount, $printWithoutCents, $priceFormat[0], $priceFormat[1]); ?>
        </td>
    </tr>
<?php endforeach; ?>

<tr>
    <td class="footer" colspan="5" style="border:1px solid #000;padding:5px 3px;text-align:right;">Grand Total After Tax (<?php echo $currencyCode; ?>):</td>
    <td class="footer" style="border:1px solid #000;padding:5px 3px;text-align:right;">
        <?php echo (empty((float) $grandTotal)) ? null : number_format($grandTotal, $printWithoutCents, $priceFormat[0], $priceFormat[1]); ?>
    </td>
</tr>

<?php $currentRow = $rowTaken - $MAX_ROWS; $endPageBlankRowHeight = 100 - (25 * $currentRow); ?>

<tr style="height:<?php echo $endPageBlankRowHeight; ?>px!important;"><td colspan="6">&nbsp;</td></tr>

<tr>
    <td class="leftText" colspan="3" style="padding: 0 15px 0 0;">
        Note:<br><br>
        <div style="border:1px solid black; padding: 10px 15px; height: 60px;"><?php echo empty($poInformation->note) ? "&nbsp;" : $poInformation->note; ?></div>
    </td>
    <td class="rightText" colspan="3" style="vertical-align: bottom;">
        <?php echo $poInformation->signature; ?>
    </td>
</tr>
<tr>
    <td class="pageNumberCell" colspan="6" style="padding: 20px 0 0 0;line-height:12px;vertical-align:text-bottom">
        Page <?php echo $pageCount; ?> of <?php echo $lastPageCount; ?>
    </td>
</tr>