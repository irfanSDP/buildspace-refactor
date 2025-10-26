<div class="modal fade" id="tenderInterviewModal" tabindex="-1" role="dialog" aria-labelledby="tenderInterviewLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-grey-e">
                <h6 class="modal-title" id="tenderInterviewLabel">
                    {{ trans('tenders.tenderInterview') }}
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">

                <div class="form-horizontal">
                    <div class="form-group">
                        <label for="venue-input" class="col-sm-2 control-label">{{ trans("tenders.meetingVenue") }}:</label>
                        <div class="col-sm-10">
                            <input id="venue-input" class="form-control" placeholder="{{ trans("tenders.meetingVenue") }}" value="{{{ $tender->tenderInterviewInfo->getVenue() }}}" {{{ !$modes['edit']? 'disabled' : '' }}}/>
                        </div>
                    </div>
                </div>

                <div class="form-horizontal">
                    <div class="form-group">
                        <label for="date-input" class="col-sm-2 control-label">{{ trans('general.date') }}:</label>
                        <div class="col-sm-10">
                            <input id="date-input" type="date" class="form-control" value="{{{ $tender->project->getProjectTimeZoneTime($tenderInterviewInformation->getDiscussionDate('dates.reversed_date')) }}}" placeholder="{{ trans("tenders.interviewDate") }}" {{{ !$modes['edit']? 'disabled' : '' }}}/>
                        </div>
                    </div>
                </div>

                <div class="form-horizontal">
                    <div class="form-group">
                        <label for="discussion-time-input" class="col-sm-2 control-label">{{ trans("tenders.discussionTime") }}:</label>
                        <div class="col-sm-10">
                            @if($modes['edit'])
                                <label class="input">
                                    <div class="input-group discussion-time-clockpicker" data-placement="left" data-align="right" data-autoclose="true">
                                        <input type="text" id="discussion-time-input" class="form-control time-input"
                                               value="{{{ $tender->project->getProjectTimeZoneTime($tenderInterviewInformation->getDiscussionTime()) }}}"
                                               placeholder="Discussion Time">
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-time"></span>
                                        </span>
                                    </div>
                                </label>
                            @else
                                <input type="text" id="discussion-time-input" class="form-control time-input"
                                       value="{{{ $tender->project->getProjectTimeZoneTime($tenderInterviewInformation->getDiscussionTime()) }}}" placeholder="{{ trans("tenders.discussionTime") }}" disabled>
                            @endif
                        </div>
                    </div>
                </div>

                <hr/>

                <table id="tenderInterviewTable" class="table ">
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th class="hasinput">
                            <input type="text" class="form-control" placeholder="{{ trans('tenders.tenderer') }}"/>
                        </th>
                        <th>&nbsp;</th>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th>{{ trans('general.number') }}</th>
                        <th>{{ trans('tenders.tenderer') }}</th>
                        <th>{{ trans('general.time') }}</th>
                        <th>{{ trans('general.status') }}</th>
                    </tr>
                    </thead>
                </table>

            </div>
            <div class="modal-footer">
                <button class="btn btn-info" data-dismiss="modal" aria-hidden="true" data-toggle="modal" data-target="#interviewerPreviewModal"><i class="fa fa-eye"></i> {{ trans('tenders.interviewerPreview') }}</button>
                <button class="btn btn-info" data-dismiss="modal" aria-hidden="true" data-toggle="modal" data-target="#intervieweePreviewModal"><i class="fa fa-eye"></i> {{ trans('tenders.intervieweePreview') }}</button>
                @include('tenders.partials.interview.saveAndEditButtons')
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->