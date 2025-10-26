<?php $numberOfBlankRows = 2; ?>
<table style="width:100%">
    <?php $rowCount = 0; ?>
    @if(isset($amountInText))
        @foreach($amountInText as $line)
            <tr>
                <td class="amount-line blank-space" style="border-bottom: 1px solid black;">{{{ $line }}}</td>
            </tr>
            <?php $rowCount++; ?>
        @endforeach
    @endif
    @for(; $rowCount < $numberOfBlankRows; $rowCount++)
        <tr>
            <td class="amount-line blank-space">&nbsp;</td>
        </tr>
    @endfor
</table>
<table style="width:50%;">
    <tr>
        <td class="{{{ ($amount > 0) ? 'occupy-min' : '' }}}">( <strong>{{ $currencySymbol }}</strong> {{{ ( $amount > 0 ) ? number_format($amount, 2) : '-' }}} )</td>
    </tr>
</table>