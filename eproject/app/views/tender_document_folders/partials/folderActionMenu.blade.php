<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
    	@if($canDownload)
        <li>
            <a href="{{ route('tenderDocument.folderDownload', array($project->id, $node->id)) }}" class="btn btn-block btn-md btn-success">
                <i class="fas fa-download"></i>
                {{ trans('tenderDocumentFolders.downloadFolder') }}
            </a>
        </li>
        @endif
    </ul>
</div>