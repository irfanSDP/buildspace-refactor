<div class="modal" id="updatedByLogsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    {{{ $title }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>

            <div class="modal-body">
                @if ( $logs->isEmpty() )
                    <p class="required">Sorry, currently there are no log(s) available..</p>
                @endif

                @if ( !$logs->isEmpty() )
                    <ol>
                        <?php $count = 0; ?>

                        @foreach ( $logs as $log )
                            <li>{{ $log->present()->log_text_format($count) }}</li>

                            <?php $count ++; ?>
                        @endforeach
                    </ol>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>