<hr/>
<?php $disclaimerLine_1 = trans('email/base.disclaimerLine_1'); ?>
<?php $disclaimerLine_2 = trans('email/base.disclaimerLine_2'); ?>
<?php $disclaimerLine_3 = trans('email/base.disclaimerLine_3'); ?>

@if(isset($recipientLocale))
    <?php $disclaimerLine_1 = trans('email/base.disclaimerLine_1', [], 'messages', $recipientLocale ); ?>
    <?php $disclaimerLine_2 = trans('email/base.disclaimerLine_2', [], 'messages', $recipientLocale ); ?>
    <?php $disclaimerLine_3 = trans('email/base.disclaimerLine_3', [], 'messages', $recipientLocale ); ?>
@else
@endif
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 10px;">
    {{{ $disclaimerLine_1 }}}
</p>
<em style="font-family: Arial, Helvetica, sans-serif; font-size: 10px;">
    {{{ $disclaimerLine_2 }}}

    {{{ $disclaimerLine_3 }}}
</em>