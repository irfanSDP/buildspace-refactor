    <?php if ( ! $printAmountOnly ): ?>
        <td class="bqRateCell">&nbsp;</td>
        <?php foreach($billColumnSettings as $billColumnSetting): ?>
        <td class="bqQtyCell">&nbsp;</td>
        <td class="bqAmountCell" style="text-align: center;">&nbsp;</td>
        <?php endforeach; ?>
    <?php else: ?>
        <?php foreach($billColumnSettings as $billColumnSetting): ?>
        <td class="bqAmountCell" colspan="2" style="text-align: center;">&nbsp;</td>
        <?php endforeach; ?>
    <?php endif; ?>