<div class="dropdown {{{ $classes ?? 'pull-right' }}}">
    <a data-type="action-button-menu" role="button" data-toggle="dropdown" class="btn btn-primary" data-target="#" href="javascript:void(0);"> {{ trans('general.actions') }} <span class="caret"></span> </a>
    <ul data-type="action-button-menu-list" class="dropdown-menu" role="menu">
        @if ( ! $currentUser->hasCompanyProjectRole($thread->project, \PCK\ContractGroups\Types\Role::CONTRACTOR) && ( $thread->isTypePublic() || $thread->isTypePrivate() ) )
            <li>
                {{ Form::open(array('route' => array('form.threads.privacy.toggle', $project->id, $thread->id), 'id' => 'togglePrivacySettingForm', 'data-intercept' => 'confirmation', 'data-event' => 'submit', 'data-confirmation-message' => ($thread->is_public ? trans('forum.privacyUpdateWarning.setPrivate') : trans('forum.privacyUpdateWarning.setPublic')))) }}
                {{ Form::close() }}
                <a href="javascript:void(0);" class="btn btn-block btn-md btn-info" data-action="form-submit" data-target-id="togglePrivacySettingForm">
                    @if($thread->isTypePublic())
                        <i class="far fa-check-square"></i>
                    @else
                        <i class="far fa-square"></i>
                    @endif
                    {{ trans('forum.publicThread') }}
                    <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="{{{ trans('forum.publicThreadTooltip') }}}"></i>
                </a>
            </li>
            <li>
                <button type="button" class="btn btn-block btn-md btn-warning" data-toggle="modal" data-tooltip data-target="#privacySettingsLog">
                    <i class="fas fa-list"></i> {{ trans('forum.privacySettingsLog') }}
                </button>
            </li>
        @endif
    </ul>
</div>