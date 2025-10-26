    <?php $extendColumn = ($printAmountOnly) ? 0 : 1; ?>    
    <tr>
        <td class="leftHeader" colspan="<?php echo ($printAmountOnly) ? 2 : 3; ?>"><?php echo $topLeftRow1; ?></td>
        <td class="rightHeader" colspan="<?php echo (count($billColumnSettings) * 2 + $extendColumn)?>"><?php echo $topRightRow1; ?></td>
    </tr>
    <tr>
        <?php 

            if($printAmountOnly)
            {
                $maxChar = (count($billColumnSettings) <= 3) ?  63 : 85;
            }
            else
            {
                $maxChar = (count($billColumnSettings) <= 3) ?  75 : 100;
            }

        ?>
        <td class="leftHeader" colspan="<?php echo ($printAmountOnly) ? 2 : 3; ?>"><?php echo $topLeftRow2; ?></td>
        <td class="rightPageElement" colspan="<?php echo (count($billColumnSettings) * 2 + $extendColumn)?>"><?php echo $printElementTitle ? (strlen($elementHeaderDescription) > $maxChar) ? substr($elementHeaderDescription,0,$maxChar).'...' : $elementHeaderDescription : "&nbsp;"; ?></td>
    </tr>