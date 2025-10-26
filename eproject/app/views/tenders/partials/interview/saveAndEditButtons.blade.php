<span class="saveAndEditButtons" id="footer-buttons">
    @if($modes['send'])
        <button class="btn btn-success" data-action="send-tender-interview-requests">
            <i class="fa fa-paper-plane"></i>
            {{ trans('general.saveAndSend') }}
        </button>
    @endif
    @if($modes['edit'])
        <button class="btn btn-primary" data-action="save-tender-interview-info">
            <i class="fa fa-save"></i>
            {{ trans('general.save') }}
        </button>
    @endif
</span>