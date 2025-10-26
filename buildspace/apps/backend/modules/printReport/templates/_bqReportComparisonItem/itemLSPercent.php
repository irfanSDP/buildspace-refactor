    <?php if ( ! $printAmountOnly ): ?>
        <?php if (! empty($itemRow[0])): ?>
            <?php if($printQty) : ?>
                <?php if ( $toggleColumnArrangement ): ?>
                <td class="bqQtyCell"><?php echo ! $printNoPrice ? "{$quantity}" : null; ?></td>
                <?php else: ?>
                <td class="bqQtyCell"><?php echo ! $printNoPrice ? "{$quantity}" : null; ?></td>
                <?php endif; ?>
            <?php endif; ?>
            <td class="bqRateCell"><?php echo ($rate != 0) ? $rate : null; ?></td>
            <?php if($participate) : ?>
                <td class="bqRateCell"><?php echo ! $printNoPrice  && $itemId > 0 && $rationalizedRates && array_key_exists($itemId, $rationalizedRates) && $rationalizedRates[$itemId] != 0 ? number_format($rationalizedRates[$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
            <?php else:?>
                <td class="bqRateCell"><?php echo ! $printNoPrice  && $itemId > 0 && $selectedRates && array_key_exists($itemId, $selectedRates) && $selectedRates[$itemId] != 0 ? number_format($selectedRates[$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
            <?php endif; ?>

            <?php if($participate) : ?>
                <td class="bqRateCell" style="text-align:center;"><?php echo ! $printNoPrice  && $itemId > 0 && $rationalizedRates && array_key_exists($itemId, $rationalizedRates) && $rationalizedRates[$itemId] != 0 ? number_format(($rationalizedRates[$itemId] - $rate) / $rate * 100, 2).'%' : null ?></td>
            <?php else:?>
                <td class="bqRateCell" style="text-align:center;"><?php echo (! $printNoPrice  && $rate > 0 && $itemId > 0 && $selectedRates && array_key_exists($itemId, $selectedRates) && $selectedRates[$itemId] != 0) ?  number_format(($selectedRates[$itemId] - $rate) / $rate * 100, 2).'%' : null ?></td>
            <?php endif; ?>

            <?php if($participate) : ?>
                <td class="bqRateCell"><?php echo ! $printNoPrice  && $itemId > 0 && $rationalizedRates && array_key_exists($itemId, $rationalizedRates) && $rationalizedRates[$itemId] != 0 ? number_format(($rationalizedRates[$itemId] - $rate), $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
            <?php else:?>
                <td class="bqRateCell"><?php echo ! $printNoPrice  && $itemId > 0 && $selectedRates && array_key_exists($itemId, $selectedRates) && $selectedRates[$itemId] != 0 ? number_format(($selectedRates[$itemId] - $rate), $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
            <?php endif; ?>
        <?php else: ?>
            <?php if($printQty) : ?>
                <td class="bqQtyCell">&nbsp;</td>
            <?php endif; ?>
            <td class="bqRateCell" style="text-align: center;">&nbsp;</td>
        <?php endif; ?>
    <?php endif; ?>