<div class="modal fade" id="bidConfirmationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header bg-grey-e">
                <h4 class="modal-title">{{ trans('general.confirmation') }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body" style="padding-top:0;">
                <div class="well" style="margin:0;">
                    <span>{{ trans('eBiddingConsole.bidConfirmation' . ucwords($bidMode->slug)) }}</span> <span class="confirm-msg-amt"></span>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary bid" data-type="" data-lnk="" data-amt="">{{ trans('forms.yes') }}</button>
                <button class="btn btn-default" data-dismiss="modal" data-action="actionNo">{{ trans('forms.no') }}</button>
            </div>
        </div>
    </div>
</div>