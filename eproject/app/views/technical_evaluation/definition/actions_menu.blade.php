<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        <li>
            <button id="createItem" class="btn btn-block btn-md btn-primary" data-target="#creatorModal" data-toggle="modal">
                <i class="fa fa-plus"></i> {{{ trans('technicalEvaluation.add') }}}
            </button>
        </li>
        <li>
            <a href="{{ route('contractLimit.index') }}" class="btn btn-block btn-md btn-warning">
                <i class="fa fa-list"></i>
                {{{ trans('contractLimit.contractLimits') }}}
            </a>
        </li>
    </ul>
</div>