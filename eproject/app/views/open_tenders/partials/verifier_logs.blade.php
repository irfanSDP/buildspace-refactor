<?php $messageRemarks = isset($messageRemarks) ? $messageRemarks : null; ?>
<div class="modal" id="verifierLogsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    {{{ $title }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>

            <div class="modal-body">
                @if(!is_null($messageRemarks))
                <div class="smart-form">
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{ trans('general.remarks') }}</label>
                            <label>{{ nl2br($messageRemarks) }}</label>
                        </section>
                    </div>
                </div>
                @endif
                <ol>
                    @foreach ( $logs as $log )
                        <li>{{ $log->present()->log_text_format() }}</li>
                    @endforeach
                </ol>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>