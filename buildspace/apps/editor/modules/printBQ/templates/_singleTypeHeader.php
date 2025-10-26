<table cellpadding="0" cellspacing="0" class="headerTable" style="width: 100%;">
    <tr>
        <td class="leftHeader" style="width: 45%;"><?php echo (strlen($topLeftRow1) > 45) ? substr($topLeftRow1,0,45).'...' : $topLeftRow1; ?></td>
        <td class="rightHeader" style="width: 50%;"><?php echo (strlen($topRightRow1) > 42) ? substr($topRightRow1,0,42).'...' : $topRightRow1; ?></td>
    </tr>
    <tr>
        <td class="leftHeader" style="width: 45%;"><?php echo (strlen($topLeftRow2) > 45) ? substr($topLeftRow2,0,45).'...' : $topLeftRow2; ?></td>
        <td class="rightPageElement" style="width: 50%;"><?php echo $printElementTitle ? (strlen($elementHeaderDescription) > 42) ? substr($elementHeaderDescription,0,42).'...' : $elementHeaderDescription : "&nbsp;"; ?></td>
    </tr>
</table>