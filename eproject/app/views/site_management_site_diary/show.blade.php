@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('site-management-site-diary.index', 'Site Diary', array($project->id)) }}</li>
        <li>Site Diary General Form</li>
    </ol>

@endsection

@section('content')

<div class="row">
<!-- NEW COL START -->
<article class="col-sm-12 col-md-12 col-lg-12">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget">
        <div role="content">
            <div class="widget-body">
                <ul id="site-diary-tab" class="nav nav-tabs bordered">
                    <li class="active">
                        <a href="#general" data-toggle="tab">General</a>
                    </li>
                    <li>
                        <a href="#weather" data-toggle="tab">Weather</a>
                    </li>
                    <li>
                        <a href="#labour" data-toggle="tab">Labour</a>
                    </li>
                    <li>
                        <a href="#machinery" data-toggle="tab">Machinery</a>
                    </li>
                    <li>
                        <a href="#rejected_materials" data-toggle="tab">Rejected Materials</a>
                    </li>
                    <li>
                        <a href="#visitor" data-toggle="tab">Visitor</a>
                    </li>
                </ul>
                <div id="myTabContent1" class="tab-content" style="padding: 30px!important;">
                    <div class="tab-pane" id="labour">
                        <!-- widget content -->
                        <div class="widget-body no-padding" class="form-group">
                            {{ Form::model($generalForm, array('class'=>'smart-form', 'id'=>'labour-form','route' => array('site-management-site-diary.general-form.update', $project->id ,$generalForm->id), 'method' => 'PUT')) }}
                            <fieldset id="form"> 
                            {{ Form::hidden('form_type', 'labour') }}
                            @foreach($labours as $record)
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    {{ Form::hidden($record->name.'-labour-id', $record->id) }}
                                    <label for="{{$record->id}}" style="padding-left:5px;"><strong>{{$record->name}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    @if(isset($labourFormArray[$record->id]))
                                    {{ Form::number($record->name, $labourFormArray[$record->id], array('class' => 'form-control padded-less-left', 'readonly' => 'true', 'min' => 0)) }}
                                    @else
                                    {{ Form::number($record->name, Input::old($record->name), array('class' => 'form-control padded-less-left', 'readonly' => 'true', 'min' => 0)) }}
                                    @endif
                                </section>
                            @endforeach
                            </fieldset>
                            {{ Form::close() }}
                        </div>
                    </div>
                    <div class="tab-pane" id="machinery">
                        <!-- widget content -->
                        <div class="widget-body no-padding" class="form-group">
                            {{ Form::model($generalForm, array('class'=>'smart-form', 'id'=>'machinery-form','route' => array('site-management-site-diary.general-form.update', $project->id ,$generalForm->id), 'method' => 'PUT')) }}
                            <fieldset id="form"> 
                            {{ Form::hidden('form_type', 'machinery') }}
                            @foreach($machinery as $record)
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    {{ Form::hidden($record->name.'-machinery-id', $record->id) }}
                                    <label for="{{$record->id}}" style="padding-left:5px;"><strong>{{$record->name}}</strong></label>
                                </section>
                                <section class="col col-xs-12 col-md-6 col-lg-6">
                                    @if(isset($machineryFormArray[$record->id]))
                                    {{ Form::number($record->name, $machineryFormArray[$record->id], array('class' => 'form-control padded-less-left', 'readonly' => 'true', 'min' => 0)) }}
                                    @else
                                    {{ Form::number($record->name, Input::old($record->name), array('class' => 'form-control padded-less-left', 'readonly' => 'true', 'min' => 0)) }}
                                    @endif
                                </section>
                            @endforeach
                            </fieldset>
                            {{ Form::close() }}
                        </div>
                    </div>
                    <div class="tab-pane" id="rejected_materials">
                        <!-- widget content -->
                        <div class="widget-body" class="form-group">
                            @include('site_management_site_diary.partials.rejected_material_index', array('project'=> $project, 'siteDiaryId' => $siteDiaryId, 'rejectedMaterialForms'=>$rejectedMaterialForms, 'show' => $show))
                        </div>
                    </div>
                    <div class="tab-pane" id="visitor">
                        <!-- widget content -->
                        <div class="widget-body" class="form-group">
                            @include('site_management_site_diary.partials.visitor_index', array('project'=>$project, 'siteDiaryId' => $siteDiaryId, 'visitorForms'=>$visitorForms, 'show' => $show))
                        </div>
                    </div>
                    <div class="tab-pane" id="weather">
                        <!-- widget content -->
                        <div class="widget-body" class="form-group">
                            @include('site_management_site_diary.partials.weather_index', array('project'=> $project, 'siteDiaryId' => $siteDiaryId, 'weatherForms'=>$weatherForms, 'show' => $show))
                        </div>
                    </div>
                    <div class="tab-pane active" id="general">
                        <!-- widget content -->
                        <div class="widget-body no-padding" class="form-group">
                            {{ Form::model($generalForm, array('class'=>'smart-form', 'id'=>'general-form','route' => array('site-management-site-diary.general-form.update', $project->id ,$generalForm->id), 'method' => 'PUT')) }}
                            <fieldset id="form">  
                                {{ Form::hidden('form_type', 'general') }}
                                <div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label for="general_date" style="padding-left:5px;"><strong>Date&nbsp;<span class="required">*</span></strong></label>
                                        <label class="input">
                                            <i class="icon-append fa fa-calendar"></i>
                                            {{ Form::text('general_date', Input::old('general_date'), array('class' => 'form-control datetimepicker', 'disabled' => 'disabled')) }}
                                        </label>
                                        {{ $errors->first('general_date', '<em class="invalid">:message</em>') }}
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label for="general_day" style="padding-left:5px;"><strong>Day</strong></label>
                                        <select name="general_day" id="general_day" class="form-control" disabled>
                                            <option selected disabled>Select</option>                   
                                                @foreach($days as $day)
                                                    @if($generalForm->general_day == $day)
                                                        <option selected value="{{{ $day }}}">
                                                            {{{ $day }}}
                                                        </option>
                                                    @else
                                                        <option value="{{{ $day }}}">
                                                            {{{ $day }}}
                                                        </option>
                                                    @endif
                                                @endforeach
                                        </select>
                                    </section>
                                </div>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label for="general_time_in" style="padding-left:5px;"><strong>Time In</strong></label>
                                        {{ Form::select('general_time_in', PCK\Base\Helpers::generateTimeArray(), Input::old('general_time_in'), array('class' => 'form-control padded-less-left','readonly' => 'true')) }}
                                        {{ $errors->first('general_time_in', '<em class="invalid">:message</em>') }}
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label for="general_time_out" style="padding-left:5px;"><strong>Time Out</strong></label>
                                        {{ Form::select('general_time_out', PCK\Base\Helpers::generateTimeArray(), Input::old('general_time_out'), array('class' => 'form-control padded-less-left','readonly' => 'true')) }}
                                        {{ $errors->first('general_time_out', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label for="general_physical_progress" style="padding-left:5px;"><strong>Physical Progress</strong></label>
                                        {{ Form::number('general_physical_progress', Input::old('general_physical_progress'), array('class' => 'form-control padded-less-left','readonly' => 'true', 'min' => 0)) }}
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label for="general_plan_progress" style="padding-left:5px;"><strong>Plan Progress</strong></label>
                                        {{ Form::number('general_plan_progress', Input::old('general_plan_progress'), array('class' => 'form-control padded-less-left','readonly' => 'true', 'min' => 0)) }}
                                    </section>
                                </div>
                            </fieldset>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
            <footer>
                {{ link_to_route('site-management-site-diary.index', trans('forms.back'), [$project->id], ['class' => 'btn btn-default']) }}
            </footer>
            @if(!$isVerified)
                @if($isCurrentVerifier)
                    <div class="pull-right">
                        @include('verifiers.approvalForm', [
                            'object'	=> $generalForm,
                        ])
                    </div>
                @endif
            @endif
            <button id="btnViewLogs" type="button" class="btn btn-sm btn-success pull-right" style="margin-right:4px;">View Logs</button>
        </div>
    </div>
    <!-- end widget -->
</article>
<!-- END COL -->
</div>

@include('site_management_site_diary.partials.verifier_remarks_modal')
@include('site_management_site_diary.partials.verifier_log_modal')
    
@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#btnViewLogs').on('click', function(e) {
				e.preventDefault();
				$('#siteDiaryVerifierLogModal').modal('show');
			});

            $('#verifierForm button[name=approve], #verifierForm button[name=reject]').on('click', function(e) {
				e.preventDefault();

				if(this.name == 'reject') {
					$('#siteDiaryVerifierRejectRemarksModal').modal('show');
				}

				if(this.name == 'approve') {
					$('#siteDiaryVerifierApproveRemarksModal').modal('show');           
                } 
			});

            $('button#verifier_approve_site_diary-submit_btn, button#verifier_reject_site_diary-submit_btn').on('click', function(e) {
				e.preventDefault();

				var remarksId;

                console.log("button clicked");
				            
				switch(this.id) {
					case 'verifier_approve_site_diary-submit_btn':
						var input = $("<input>").attr("type", "hidden").attr("name", "approve").val(1);
						$('#verifierForm').append(input);
						remarksId = 'approve_verifier_remarks';
						break;
					case 'verifier_reject_site_diary-submit_btn':
						remarksId = 'reject_verifier_remarks';
						break;
				}

				if($('#'+remarksId)){
					$('#verifierForm').append($("<input>")
					.attr("type", "hidden")
					.attr("name", "verifier_remarks").val($('#'+remarksId).val()));
				}

                $('#verifierForm').submit();
            });

        });
    </script>
    
@endsection