    <?php if ( ! $printAmountOnly ): ?>
        <?php if ( $toggleColumnArrangement ): ?>
        <td class="bqAmountCell" style="text-align: center;">&nbsp;</td>
        <td class="bqUnitCell"><?php echo $itemRow ? $itemRow[5] : '&nbsp;'?></td>
        <?php else: ?>
        <td class="bqUnitCell"><?php echo $itemRow ? $itemRow[5] : '&nbsp;'?></td>
        <td class="bqAmountCell" style="text-align: center;">&nbsp;</td>
        <?php endif; ?>

        <?php $count = ($printDollarAndCentColumn) ? 1 : 0; echo str_repeat('<td class="bqAmountCell" style="text-align: center;">&nbsp;</td>', 2 + $count); ?>
    <?php else: ?>
        <?php $count = ($printDollarAndCentColumn) ? 1 : 0; echo str_repeat('<td class="bqAmountCell" style="text-align: center;">&nbsp;</td>', 1 + $count); ?>
    <?php endif; ?>