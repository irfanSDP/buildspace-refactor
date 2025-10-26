<?php $showNotifyReviewerButton = $showNotifyReviewerButton ?? false; ?>
<?php $showSendCommentNotificationButton = $showSendCommentNotificationButton ?? false; ?>
<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        @if ($showNotifyReviewerButton)
            <li>
                <a href="#" target="_self" class="btn btn-block btn-md btn-danger" id="btnNotifyReviewer">
                    <i class="fa fa-envelope"></i>
                    {{ trans('letterOfAward.notifyReviewer') }}</a>
                </a>
            </li>
        @endif
        @if ($showSendCommentNotificationButton)
            <li class="divider"></li>
            <li>
                <a href="#" target="_self" class="btn btn-block btn-md btn-success" id="btnSendCommentNotification">
                    <i class="fa fa-envelope"></i>
                    {{ trans('letterOfAward.sendCommentNotification') }}</a>
                </a>
            </li>
        @endif
    </ul>
</div>