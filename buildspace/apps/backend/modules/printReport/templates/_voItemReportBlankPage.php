<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php $headerCount = 8; ?>
        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('bqReportHeader', array('reportTitle' => $reportTitle,'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" rowspan="2" style="min-width:60px;width:60px;">
            Item
        </td>
        <td class="bqHeadCell" rowspan="2" style="min-width:280px;width:280px;">
            <?php echo $descHeader; ?>
        </td>
        <td class="bqHeadCell" rowspan="2" style="min-width:60px;width:60px;">
            <?php echo "Unit"; ?>
        </td>
        <td class="bqHeadCell" rowspan="2" style="min-width:100px;width:100px;">
            <?php echo "Rate"; ?>
        </td>
        <td class="bqHeadCell" colspan="2">
            <?php echo "Omission"; ?>
        </td>
        <td class="bqHeadCell" colspan="2">
            <?php echo "Addition"; ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:70px;width:70px;">
            <?php echo "Qty" ?>
        </td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">
            <?php echo "Amount"; ?>
        </td>
        <td class="bqHeadCell" style="min-width:70px;width:70px;">
            <?php echo "Qty" ?>
        </td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">
            <?php echo "Amount"; ?>
        </td>
    </tr>
    <?php

    $rowCount = 0;

    for($x=0; $x <= $maxRows; $x++):

        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        if ( $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND ! $printElementInGrid )
        {
            $x++;
            continue;
        }

        if ( $printElementInGridOnce AND $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $pageCount > 1 )
        {
            $x++;
            continue;
        }

        $rowCount++;
        if ($itemRow and ($itemRow[4] == BillItem::TYPE_HEADER))
        {
            $headerClass = 'bqHead'.$itemRow[3];
            $headerStyle = null;
        }
        elseif($itemRow and $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT)
        {
            $headerClass = 'elementHeader';
            $headerStyle = 'font-style: italic;';

            if ( $alignElementTitleToTheLeft )
            {
                $headerClass .= ' alignLeft';
            }
            else
            {
                $headerClass .= ' alignCenter';
            }
        }
        else
        {
            $headerClass = null;
            $headerStyle = null;
        }

        if ( $indentItem AND $itemRow AND ($itemRow[4] != sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $itemRow[4] != BillItem::TYPE_HEADER) )
        {
            $itemPadding = 15;
        }
        else
        {
            $itemPadding = 6;
        }

        ?>
        <tr>
            <td class="bqCounterCell">&nbsp;</td>
            <td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                &nbsp;
            </td>
            <td class="bqRateCell">&nbsp;</td>
            <td class="bqRateCell">&nbsp;</td>
            <td class="bqRateCell">&nbsp;</td>
            <td class="bqRateCell">&nbsp;</td>
            <td class="bqRateCell">&nbsp;</td>
            <td class="bqRateCell">&nbsp;</td>
        </tr>
            <?php unset($itemPage[$x], $amount);?>
        <?php endfor; ?>

        <tr>
            <td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
                &nbsp;
            </td>
        </tr>

        <?php if($isLastPage): ?>
            <tr>
                <td colspan="<?php echo $headerCount; ?>">
                <?php
                    include_partial('footerLayout', array(
                        'leftText' => $left_text,
                        'rightText' => $right_text
                    ));
                ?>
                </td>
            </tr>
        <?php endif; ?>
</table>
</body>
</html>

