<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php $headerCount = 4; ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('postContractReportHeader', array('reportTitle' => $reportTitle, 'headerDescription' => $headerDescription, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" rowspan="2" style="min-width:40px;width:40px;">
            No.
        </td>
        <td class="bqHeadCell" rowspan="2" style="min-width:400px;width:400px;">
            <?php echo $descHeader; ?>
        </td>
        <td class="bqHeadCell" colspan="2">
            <?php echo "Amount"; ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:130px;width:130px;">
            <?php echo "Omission" ?>
        </td>
        <td class="bqHeadCell" style="min-width:130px;width:130px;">
            <?php echo "Addition"; ?>
        </td>
    </tr>
    <?php

    $rowCount = 0;

    for($x=0; $x <= $maxRows; $x++):
        
        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        $rowCount++;

        $headerClass = null;
        $headerStyle = null;

        if ( $indentItem AND $itemRow AND ($itemRow[4] != sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $itemRow[4] != BillItem::TYPE_HEADER AND $itemRow[4] != BillItem::TYPE_HEADER_N) )
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
        </tr>
            <?php unset($itemPage[$x]);?>

        <?php endfor; ?>

        <tr>
            <td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
                &nbsp;
            </td>
        </tr>

        <?php if($isLastPage): ?>
            <tr>
                <?php
                    include_partial('footerLayout', array(
                        'leftText' => $left_text,
                        'rightText' => $right_text
                    ));
                ?>
            </tr>
        <?php endif; ?>
</table>
</body>
</html>

