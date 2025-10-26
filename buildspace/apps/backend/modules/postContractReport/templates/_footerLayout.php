<?php $firstKey = key($additionalDescriptions); $lastKey = key( array_slice( $additionalDescriptions, -1, 1, TRUE ) ); foreach ( $additionalDescriptions as $key => $additionalDescription ): ?>

	<?php $border = ( $key == $firstKey OR $key == $lastKey ) ? NULL : 'border-bottom: 1px dotted black; padding: 5px;'; ?>

	<tr><td colspan="4" style="text-align: left;<?php echo $border; ?>"><?php echo $additionalDescription[0]; ?></td></tr>
<?php endforeach; ?>

<tr style="height:100px!important;">
    <td colspan="4">&nbsp;</td>
</tr>
<tr>
    <td class="leftText" colspan="2"><?php echo $leftText?></td>
    <td class="rightText" colspan="2"><?php echo $rightText?></td>
</tr>