<div class="modal scrollable-modal" id="formOfTenderLogModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    Form Of Tender Log
                </h4>
            </div>

            <div class="modal-body">
                <label class="message"></label>
                @if( (! isset($log)) || ($log->count() < 1) )
                    No changes have been made.
                @else
                    <ol reversed>
                        @foreach($log as $logEntry)
                            <li>Edited by
                                <span class="blue">{{{ \PCK\Users\User::find($logEntry->user_id)->name }}}</span>
                                at
                                <span class="red">{{{ \Carbon\Carbon::parse($logEntry->created_at)->format(\Config::get('dates.submission_date_formatting')) }}}</span>
                                <span class="green">{{{ \Carbon\Carbon::parse($logEntry->created_at)->format(\Config::get('dates.time_only')) }}}</span>
                            </li>
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