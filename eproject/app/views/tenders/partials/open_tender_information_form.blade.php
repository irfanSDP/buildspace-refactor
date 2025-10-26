<div class="row">
    <!-- NEW COL START -->
    <article class="col-sm-12 col-md-12 col-lg-12">
        <!-- Widget ID (each widget will need unique ID)-->
        <div class="jarviswidget">
            <div role="content">
                <div class="widget-body">
                    <!-- widget content -->
                    <div class="widget-body no-padding">
                        {{ Form::model($openTenderInfo, array('method' => 'PUT', 'route' => array('projects.tender.update_open_tender_info_page', $project->id, $tender->id), 'class' => 'smart-form')) }}
                        <fieldset id="form" class="form-group"> 
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.openTenderType') }}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                @if($disabled)
                                    {{ Form::select('open_tender_type', PCK\Tenders\OpenTenderPageInformation::openTenderType(), Input::old('open_tender_type'), array('class' => 'form-control padded-less-left', 'disabled' => "disabled")) }}
                                @else
                                    {{ Form::select('open_tender_type', PCK\Tenders\OpenTenderPageInformation::openTenderType(), Input::old('open_tender_type'), array('class' => 'form-control padded-less-left')) }}
                                @endif
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.tenderer') }}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <strong>{{$rootSubsidiary}}</strong>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.openTenderNumber') }}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                @if($disabled)
                                    {{ Form::text('open_tender_number', Input::old('open_tender_number'), array('class' => 'form-control padded-less-left', 'disabled' => 'disabled')) }}
                                @else
                                    {{ Form::text('open_tender_number', Input::old('open_tender_number'), array('class' => 'form-control padded-less-left')) }}
                                @endif
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.openTenderDate') }}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                @if($disabled)
                                    {{ Form::text('open_tender_date_from', Input::old('open_tender_date_from'), array('class' => 'form-control datetimepicker padded-less-left', 'disabled' => 'disabled')) }}
                                @else
                                    {{ Form::text('open_tender_date_from', Input::old('open_tender_date_from'), array('class' => 'form-control datetimepicker padded-less-left')) }}
                                @endif
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.callingDate') }}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                @if($disabled)
                                    {{ Form::text('calling_date', $tender->listOfTendererInformation->date_of_calling_tender, array('class' => 'form-control datetimepickerTimeSelection padded-less-left', 'disabled' => 'disabled')) }}
                                @else
                                    <?php
                                        $date = Input::old('calling_date', (isset($tender->listOfTendererInformation)) ? $tender->listOfTendererInformation->date_of_calling_tender : date('Y-m-d\TH:i:s'));
                                        $callingDate = date('Y-m-d\TH:i:s', strtotime($date));
                                    ?>
                                    <input id="calling_date" class="form-control" name="calling_date" type="datetime-local" value="{{ $callingDate }}" required>
                                @endif
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.closingDate') }}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                @if($disabled)
                                    {{ Form::text('closing_date', $tender->listOfTendererInformation->date_of_closing_tender, array('class' => 'form-control datetimepickerTimeSelection padded-less-left', 'disabled' => 'disabled')) }}
                                @else
                                    <?php
                                        $date = Input::old('closing_date', (isset($tender->listOfTendererInformation)) ? $tender->listOfTendererInformation->date_of_closing_tender : date('Y-m-d\TH:i:s'));
                                        $closingDate = date('Y-m-d\TH:i:s', strtotime($date));
                                    ?>
                                    <input id="closing_date" class="form-control" name="closing_date" type="datetime-local" value="{{ $closingDate }}" required>
                                @endif
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.deliveryAddress') }}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                @if($disabled)
                                    {{ Form::textArea('deliver_address', Input::old('deliver_address'), array('class' => 'form-control padded-less-left', 'disabled' => 'disabled')) }}
                                @else
                                    {{ Form::textArea('deliver_address', Input::old('deliver_address'), array('class' => 'form-control padded-less-left')) }}
                                @endif
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.specialPermission') }}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                @if($disabled)
                                    {{ Form::checkbox('special_permission', true, (isset($openTenderInfo)) ? $openTenderInfo->special_permission : Input::old('special_permission'), array('id' => 'special_permission', 'disabled' => 'disabled')) }}  
                                @else
                                    {{ Form::checkbox('special_permission', true, (isset($openTenderInfo)) ? $openTenderInfo->special_permission : Input::old('special_permission'), array('id' => 'special_permission')) }}  
                                @endif                            
                                </section>
                            </div>
                            <div id="special_permission_true" hidden>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.briefingTime') }}}</strong></label>
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                    @if($disabled)
                                        {{ Form::text('briefing_time', Input::old('briefing_time'), array('class' => 'form-control datetimepickerTimeSelection padded-less-left', 'disabled' => 'disabled')) }}
                                    @else
                                        <?php
                                            $date = Input::old('briefing_time', isset($openTenderInfo) ? $openTenderInfo->briefing_time : date('Y-m-d\TH:i:s'));
                                            $briefingTime = date('Y-m-d\TH:i:s', strtotime($date));
                                        ?>
                                        <input id="briefing_time" class="form-control" name="briefing_time" type="datetime-local" value="{{ $briefingTime }}" required>
                                    @endif
                                    </section>
                                </div>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.briefingAddress') }}}</strong></label>
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                    @if($disabled)
                                        {{ Form::textArea('briefing_address', Input::old('briefing_address'), array('class' => 'form-control padded-less-left', 'disabled' => 'disabled')) }}
                                    @else
                                        {{ Form::textArea('briefing_address', Input::old('briefing_address'), array('class' => 'form-control padded-less-left')) }}
                                    @endif
                                    </section>
                                </div>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.localCompanyOnly') }}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                @if($disabled)
                                    {{ Form::checkbox('local_company_only', true, (isset($openTenderInfo)) ? $openTenderInfo->local_company_only : Input::old('local_company_only'), array('id' => 'local_company_only', 'disabled' => 'disabled')) }}         
                                @else
                                    {{ Form::checkbox('local_company_only', true, (isset($openTenderInfo)) ? $openTenderInfo->local_company_only : Input::old('local_company_only'), array('id' => 'local_company_only')) }}        
                                @endif                      
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.openTenderPrice') }}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                @if($disabled)
                                    {{ Form::number('open_tender_price', Input::old('open_tender_price'), array('class' => 'form-control padded-less-left', 'disabled' => 'disabled', 'step' => 'any', 'min' => "0")) }}
                                @else
                                    {{ Form::number('open_tender_price', Input::old('open_tender_price'), array('class' => 'form-control padded-less-left', 'step' => 'any', 'min' => "0")) }}
                                @endif
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    <label for="tender" style="padding-left:5px;"><strong>{{{ trans('openTender.status') }}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                @if($disabled)
                                    {{ Form::select('open_tender_status', PCK\Tenders\OpenTenderPageInformation::openTenderStatus(), Input::old('open_tender_status'), array('class' => 'form-control padded-less-left', 'disabled' => 'disabled')) }}
                                @else
                                    {{ Form::select('open_tender_status', PCK\Tenders\OpenTenderPageInformation::openTenderStatus(), Input::old('open_tender_status'), array('class' => 'form-control padded-less-left')) }}
                                @endif
                                </section>
                            </div>
                        </fieldset>
                        <footer>
                            @if($approvalStatus != PCK\Tenders\OpenTenderPageInformation::STATUS_PENDING_FOR_APPROVAL)
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('openTender.save'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Save'] )  }}
                                @if(isset($openTenderInfo))
                                    <button id="btnViewLogs" type="button" class="btn btn-sm btn-success pull-right" style="margin-right:4px;">View Logs</button>
                                @endif
                            @endif
                        </footer>
                        {{ Form::close() }}
                        @if(isset($openTenderInfo))
                            @if(!$isVerified)
                                @if($isCurrentVerifier)
                                    <div style="padding-left:10px">
                                        @include('verifiers.approvalForm', [
                                            'object'	=> $openTenderInfo,
                                        ])
                                    </div>
                                @endif
                            @endif
                        @endif
                    </div>
                    @if(isset($openTenderInfo))
                        @if($approvalStatus != PCK\Tenders\OpenTenderPageInformation::STATUS_PENDING_FOR_APPROVAL)
                        <div class="widget-body" class="form-group">
                            <fieldset id="form">
                                {{ Form::open(array('class'=>'smart-form','id'=>'approve-form','route' => array('projects.tender.approve_open_tender_info_page', $project->id, $tender->id, $openTenderInfo->id))) }}
                                        @include('verifiers.select_verifiers', [
                                            'verifiers' => $verifiers,
                                        ])
                                    <footer>
                                        <button id="btnSubmitForApproval" type="submit" data-intercept="confirmation" data-intercept-condition="noVerifier" data-confirmation-message ="{{trans('general.submitWithoutVerifier')}}" class="btn btn-success pull-left"><i class="fa fa-save"></i> {{ trans('forms.submitForApproval') }}</button>
                                    </footer>
                                {{ Form::close() }}
                            </fieldset>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <!-- end widget -->
    </article>
    <!-- END COL -->
</div>
    
