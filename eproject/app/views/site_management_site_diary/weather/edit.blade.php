@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('site-management-site-diary.index', 'Site Diary', array($project->id)) }}</li>
        <li>Site Diary Visitor Form</li>
    </ol>

@endsection


@section('content')

<div class="row">
<!-- NEW COL START -->
<article class="col-sm-12 col-md-12 col-lg-12">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget">
        <div role="content">
            <div class="widget-body no-padding" class="form-group">
                {{ Form::model($weatherForm, array('class'=>'smart-form', 'id'=>'weather-form','route' => array('site-management-site-diary-weather.update', $project->id, $siteDiaryId, $weatherForm->id), 'method' => 'PUT')) }}
                <fieldset id="form">
                    {{ Form::hidden('form_type', 'weather') }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-6 col-lg-6">
                            <label for="weather_time_from" style="padding-left:5px;"><strong>Time</strong></label>
                            {{ Form::select('weather_time_from', PCK\Base\Helpers::generateTimeArrayInMinutes(), Input::old('weather_time_from'), array('class' => 'form-control')) }}
                        </section>
                        <section class="col col-xs-12 col-md-6 col-lg-6">
                            <label for="weather_time_to" style="padding-left:5px;"><strong>Time</strong></label>
                            {{ Form::select('weather_time_to', PCK\Base\Helpers::generateTimeArrayInMinutes(), Input::old('weather_time_to'), array('class' => 'form-control')) }}
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label for="type" style="padding-left:5px;"><strong>Weather</strong>&nbsp;<span class="required">*</span></label>
                            <select name="weather_id" id="weather_id" class="form-control">
                                <option selected disabled>Select</option>                   
                                    @foreach($weathers as $weather)
                                        @if($weatherForm->weather_id == $weather->id)
                                            <option selected value="{{{ $weather->id }}}">
                                                {{{ $weather->name }}}
                                            </option>
                                        @else
                                            <option value="{{{ $weather->id }}}">
                                                {{{ $weather->name }}}
                                            </option>
                                        @endif
                                    @endforeach
                            </select>
                            {{ $errors->first('weather_id', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                </fieldset>
                <footer>
                    {{ link_to_route('site-management-site-diary.index', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
                    {{ Form::button('<i class="fa fa-save"></i> '.trans('siteManagementDefect.update'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit', 'id' => 'weather'] )  }}
                </footer>
                {{ Form::close() }}
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
                onSelect: function(){
                    var selected = $(this).val();
                    $(this).attr('value', selected);
                }
            });
            
            $("form").on('submit', function(){
                app_progressBar.toggle();
                app_progressBar.maxOut();
            });
        });

    </script>
@endsection