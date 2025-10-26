<div id="emailComposer">
    <div class="modal fade" id="emailComposerModal" tabindex="-1" role="dialog" aria-labelledby="emailComposerLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-grey-e">
                    <h6 class="modal-title" id="emailComposerLabel"></h6>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                </div>
                <div class="modal-body">

                    <div class="form-horizontal">
                        <div class="form-group">
                            <label for="inviter-name-input" class="col-sm-2 control-label">{{ trans('tenders.inviterName') }}:</label>
                            <div class="col-sm-10">
                                <input id="inviter-name-input" class="form-control" value="{{{ ($project->subsidiary)? $project->subsidiary->name : $user->company->name }}}" placeholder="Name of Employer"/>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div class="col col-12 form-group">
                        <label for="message-input">{{ trans('tenders.message') }}:</label>
                        <div class="summernote" id="message-input">
                            {{ $tender->tenderReminder ? $tender->tenderReminder->message : trans('tenders.invitedToParticipateInTender') }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-info" data-action="saveAsDraft">{{ trans('forms.saveAsDraft') }}</button>
                    <button class="btn btn-default" id="composed-email-preview" data-toggle="modal" data-target="#emailComposerPreviewModal" v-on="click:updateMessage">{{ trans('general.preview') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    @include('tenders.partials.email_composer_preview_modal')

    @if ($tender->recommendationOfTendererInformation)
        @include('tenders.partials.email_composer_select_recipients_rot_modal')
    @endif

    @if ($tender->listOfTendererInformation)
        @include('tenders.partials.email_composer_select_recipients_lot_modal')
    @endif
</div>