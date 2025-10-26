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
                <!-- widget content -->
                <div class="widget-body no-padding">
                    {{ Form::open(array('class'=>'smart-form','id'=>'general-form','route' => array('site-management-site-diary.general-form.create', $project->id),'files' => true)) }}
                    <fieldset id="form" class="form-group">  
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label for="general_date" style="padding-left:5px;"><strong>Date&nbsp;<span class="required">*</span></strong></label>
                                <label class="input">
                                    <i class="icon-append fa fa-calendar"></i>
                                    {{ Form::text('general_date', Input::old('general_date'), array('class' => 'form-control padded-less-left datetimepicker', 'id'=>'general_date')) }}
                                </label>
                                {{ $errors->first('general_date', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label for="general_day" style="padding-left:5px;"><strong>Day</strong></label>
                                <select name="general_day" id="general_day" class="form-control padded-less-left" readonly>
                                    <option selected disabled>Select</option>                   
                                        @foreach($days as $day)
                                            @if(Input::old('general_day') == $day)
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
                            <section class="form-group col col-xs-12 col-md-6 col-lg-6">
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
                        {{ Form::button('<i class="fa fa-save"></i> '."Save", ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit', 'id' => 'general'] )  }}
                    </footer>
                    {{ Form::close() }}
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
        });

    </script>
@endsection