<table cellpadding="0" cellspacing="0" class="headerTable" style="width: 100%;">
    <tr>
        <td class="leftHeader" colspan="2" style="width: 100%; text-align:center;">
            <?php echo (strlen($reportTitle) > 93) ? substr($reportTitle,0,90).'...' : $reportTitle; ?>
        </td>
    </tr>
    <tr>
        <td class="leftHeader" style="width: 50%;">
            <?php echo (strlen($topLeftRow1) > 43) ? substr($topLeftRow1,0,40).'...' : $topLeftRow1; ?>
        </td>
        <td class="rightPageElement" style="width: 50%;">
            <?php echo (strlen($topLeftRow2) > 43) ? substr($topLeftRow2,0,40).'...' : $topLeftRow2; ?>
        </td>
    </tr>
</table>