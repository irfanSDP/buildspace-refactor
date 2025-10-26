@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('site-management-site-diary.index', 'Site Diary', array($project->id)) }}</li>
        <li>{{trans('siteManagementSiteDiary.site_diary')}}</li>
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
                        <a href="#general" data-toggle="tab">{{trans('siteManagementSiteDiary.general')}}</a>
                    </li>
                    <li>
                        <a href="#weather" data-toggle="tab">{{trans('siteManagementSiteDiary.weather')}}</a>
                    </li>
                    <li>
                        <a href="#labour" data-toggle="tab">{{trans('siteManagementSiteDiary.labour')}}</a>
                    </li>
                    <li>
                        <a href="#machinery" data-toggle="tab">{{trans('siteManagementSiteDiary.machinery')}}</a>
                    </li>
                    <li>
                        <a href="#rejected_material" data-toggle="tab">{{trans('siteManagementSiteDiary.rejected_materials')}}</a>
                    </li>
                    <li>
                        <a href="#visitor" data-toggle="tab">{{trans('siteManagementSiteDiary.visitor')}}</a>
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
                                    {{ Form::number($record->name, $labourFormArray[$record->id], array('class' => 'form-control padded-less-left', 'min' => 0)) }}
                                    @else
                                    {{ Form::number($record->name, Input::old($record->name), array('class' => 'form-control padded-less-left', 'min' => 0)) }}
                                    @endif
                                </section>
                            @endforeach
                            </fieldset>
                            <footer>
                                {{ link_to_route('site-management-site-diary.index', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('siteManagementDefect.save'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit'] )  }}
                            </footer>
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
                                    {{ Form::number($record->name, $machineryFormArray[$record->id], array('class' => 'form-control padded-less-left', 'min' => 0)) }}
                                    @else
                                    {{ Form::number($record->name, Input::old($record->name), array('class' => 'form-control padded-less-left', 'min' => 0)) }}
                                    @endif
                                </section>
                            @endforeach
                            </fieldset>
                            <footer>
                                {{ link_to_route('site-management-site-diary.index', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('siteManagementDefect.save'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit'] )  }}
                            </footer>
                            {{ Form::close() }}
                        </div>
                    </div>
                    <div class="tab-pane" id="rejected_material">
                        <!-- widget content -->
                        <div class="widget-body" class="form-group">
                            @include('site_management_site_diary.partials.rejected_material_index', array('project'=> $project, 'siteDiaryId' => $siteDiaryId, 'rejectedMaterialForms'=>$rejectedMaterialForms))
                        </div>
                    </div>
                    <div class="tab-pane" id="visitor">
                        <!-- widget content -->
                        <div class="widget-body" class="form-group">
                            @include('site_management_site_diary.partials.visitor_index', array('project'=>$project, 'siteDiaryId' => $siteDiaryId, 'visitorForms'=>$visitorForms))
                        </div>
                    </div>
                    <div class="tab-pane" id="weather">
                        <!-- widget content -->
                        <div class="widget-body" class="form-group">
                            @include('site_management_site_diary.partials.weather_index', array('project'=> $project, 'siteDiaryId' => $siteDiaryId, 'weatherForms'=>$weatherForms))
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
                                            {{ Form::text('general_date', Input::old('general_date'), array('class' => 'form-control padded-less-left datetimepicker')) }}
                                        </label>
                                        {{ $errors->first('general_date', '<em class="invalid">:message</em>') }}
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label for="general_day" style="padding-left:5px;"><strong>Day</strong></label>
                                        <select name="general_day" id="general_day" class="form-control padded-less-left" readonly>
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
                                        {{ Form::select('general_time_in', PCK\Base\Helpers::generateTimeArrayInMinutes(), Input::old('general_time_in'), array('class' => 'form-control padded-less-left')) }}
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label for="general_time_out" style="padding-left:5px;"><strong>Time Out</strong></label>
                                        {{ Form::select('general_time_out', PCK\Base\Helpers::generateTimeArrayInMinutes(), Input::old('general_time_out'), array('class' => 'form-control padded-less-left')) }}
                                    </section>
                                </div>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label for="general_physical_progress" style="padding-left:5px;"><strong>Physical Progress (%)</strong></label>
                                        {{ Form::number('general_physical_progress', Input::old('general_physical_progress'), array('class' => 'form-control padded-less-left', 'min' => 0, 'max' => 100)) }}
                                    </section>
                                    <section class="col col-xs-12 col-md-6 col-lg-6">
                                        <label for="general_plan_progress" style="padding-left:5px;"><strong>Plan Progress (%)</strong></label>
                                        {{ Form::number('general_plan_progress', Input::old('general_plan_progress'), array('class' => 'form-control padded-less-left', 'min' => 0, 'max' => 100)) }}
                                    </section>
                                </div>
                            </fieldset>
                            <footer>
                                {{ link_to_route('site-management-site-diary.index', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('siteManagementDefect.save'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Save', 'id' => 'general'] )  }}
                            </footer>
                            {{ Form::close() }}
                            @if($approvalStatus != PCK\SiteManagement\SiteDiary\SiteManagementSiteDiaryGeneralFormResponse::STATUS_PENDING_FOR_APPROVAL)
                            <div class="widget-body no-padding" class="form-group">
                                <fieldset id="form">
                                    {{ Form::open(array('class'=>'smart-form','id'=>'approve-general-form','route' => array('site-management-site-diary.submitGeneralFormForApproval', $project->id, $siteDiaryId))) }}
                                            @include('verifiers.select_verifiers', [
                                                'verifiers' => $verifiers,
                                            ])
                                        <footer>
                                            <button id="btnSubmitForApproval" type="submit" data-intercept="confirmation" data-intercept-condition="noVerifier" data-confirmation-message ="{{trans('general.submitWithoutVerifier')}}" class="btn btn-warning pull-left"><i class="fa fa-save"></i> {{ trans('forms.submitForApproval') }}</button>
                                        </footer>
                                    {{ Form::close() }}
                                </fieldset>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end widget -->
</article>
<!-- END COL -->
</div>
    
@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('.datetimepicker').datepicker({
                dateFormat : 'dd-mm-yy',
                prevText : '<i class="fa fa-chevron-left"></i>',
                nextText : '<i class="fa fa-chevron-right"></i>',
                onSelect: function(selectedDate) {
                    $.ajax({
                        url: "{{route('site-management-site-diary.getDayFromCalendar', array($project->id))}}",
                        type: 'GET',
                        data: { date: selectedDate },
                        success: function(response) {
                            console.log(response);
                            $('#general_day').val(response);
                        },
                        error: function(error) {
                            console.error(error);
                        }
                    });
                }
            });
            
            $("form").on('submit', function(){
                app_progressBar.toggle();
                app_progressBar.maxOut();
            });

            var formType = {{json_encode($form)}}
            console.log(formType);

            $('a[href="#' + formType + '"]').tab('show');

            if({{json_encode($errors->first('general_date'))}} || {{json_encode($errors->first('general_time_in'))}} || {{json_encode($errors->first('general_time_out'))}})
            {
                $('a[href="#general"]').tab('show');
            }
            else if({{json_encode($errors->first('labour_project_manager'))}} || {{json_encode($errors->first('labour_site_agent'))}} || {{json_encode($errors->first('labour_supervisor'))}})
            {
                $('a[href="#labour"]').tab('show');
            }
            else if({{json_encode($errors->first('machinery_excavator'))}} || {{json_encode($errors->first('machinery_backhoe'))}} || {{json_encode($errors->first('machinery_crane'))}})
            {
                $('a[href="#machinery"]').tab('show');
            }
        });

        function noVerifier(e){
            var form = $(e.target).closest('form');
            var input = form.find(':input[name="verifiers[]"]').serializeArray();

            return !input.some(function(element){
                return (element.value > 0);
            });
        }

    </script>
@endsection