@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('daily-labour-report.index', 'Daily Labour Reports', array($project->id)) }}</li>
        <li>{{{ trans('dailyLabourReports.submit_daily_labour_reports') }}}</li>
    </ol>

@endsection

@section('content')

<div class="row">
<!-- NEW COL START -->
<article class="col-sm-12 col-md-12 col-lg-12">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget">
        <!-- widget div-->
        <div>
            <!-- widget content -->
            <div class="widget-body no-padding">
                 {{ Form::open(array('class'=>'smart-form','id'=>'daily-labour-report-form','files' => true)) }}
                     <fieldset>  
                        <legend><h2>{{{ trans('dailyLabourReports.submit_daily_labour_reports') }}}</h2></legend>
                        <div>
                            <section class="col-md-6">
                            <label for="date" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.date') }}}</strong></label>
                            {{ Form::text('date', (Input::old('date') ? Input::old('date') : \Carbon\Carbon::now($project->timezone)->format('d-M-Y')), array('class' => 'datetimepicker form-control')) }}
                            {{ $errors->first('date', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col-md-6">
                                <label for="type" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.weather') }}}</strong>&nbsp;<span class="required">*</span></label>
                                <select name="weather" id="weather" class="form-control">
                                    <option selected disabled>Select</option>                   
                                        @foreach($weathers as $weather)
                                            @if(Input::old('weather') == $weather->id)
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
                                {{ $errors->first('weather', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <section class="col-xs-12 col-md-12 col-lg-12">
                            <label for="location" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.location') }}}</strong>&nbsp;<span class="required">*</span></label>
                            <select name="locationLevel_0" id="locationLevel_0" class="form-control">
                                <option selected disabled>Select Location</option>
                                @foreach($locations as $location)
                                    @if(Input::old('locationLevel_0') == $location->id)
                                        <option selected value="{{{ $location->id }}}">
                                            {{{ $location->description }}}
                                        </option>
                                    @else
                                        <option value="{{{ $location->id }}}">
                                            {{{ $location->description }}}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <div id="location_select">
                            </div>
                            {{ $errors->first('locationLevel_0', '<em class="invalid">:message</em>') }}
                        </section>
                        <div>
                            <section class="col-md-6">
                                <label for="trade" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.trade') }}}</strong>&nbsp;<span class="required">*</span></label>
                                <select name="trade" id="trade" class="form-control">
                                    <option selected disabled>Select</option>
                                    @if(!empty($trades))
                                        @foreach($trades as $key => $value)
                                            @if(Input::old('trade') == $value->id)
                                                <option selected value="{{{ $value->id }}}">
                                                    {{{ $value->name }}}
                                                </option>
                                            @else
                                                <option value="{{{ $value->id }}}">
                                                    {{{ $value->name }}}
                                                </option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                                {{ $errors->first('trade', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col-md-6">
                                <label for="contractor" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.contractor') }}}</strong>&nbsp;<span class="required">*</span></label>
                                <select name="contractor" id="contractor" class="form-control">
                                    <option selected disabled>Select</option>
                                </select>
                                {{ $errors->first('contractor', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><h2>{{{ trans('dailyLabourReports.labour_info') }}}</h2></legend>
                        <section class="col-xs-12 col-md-12 col-lg-12">
                            @include('daily_labour_reports.labour_info')
                        </section>
                        <section class="col-xs-12 col-md-12 col-lg-12">
                            <label for="work_description" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.work_description') }}}</strong>&nbsp;<span class="required">*</span>
                            </label>
                            <textarea class="form-control" rows="5" name="work_description" id="work_description">{{{Input::old('work_description')}}}</textarea>
                            {{ $errors->first('work_description', '<em class="invalid">:message</em>') }}
                        </section>
                        <section class="col-xs-12 col-md-12 col-lg-12">
                            <label for="remark" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.remark') }}}</strong>&nbsp;<span class="required">*</span></label>
                            <textarea class="form-control" rows="5" name="remark" id="remark">{{{Input::old('remark')}}}</textarea>
                            {{ $errors->first('remark', '<em class="invalid">:message</em>') }}
                        </section>
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('siteManagement.attachments') }}}:</label>
                            @include('file_uploads.partials.upload_file_modal')
                        </section>
                    </fieldset>
                    <footer>
                        {{ Form::submit(trans('dailyLabourReports.submit'), array('class' => 'btn btn-default', 'name' => 'Submit')) }}
                        {{ link_to_route('daily-labour-report.index', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
                    </footer>
                {{ Form::close() }}
            </div>
            <!-- end widget content -->
        </div>
        <!-- end widget div -->
    </div>
    <!-- end widget -->
</article>
<!-- END COL -->
</div>
    
@endsection


@section('js')
    <link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>

        $(document).ready(function () {
            $('.datetimepicker').datetimepicker({
                format: 'DD-MMM-YYYY',
                showTodayButton: true,
                allowInputToggle: true
            });

            $('#daily-labour-report-form').on('submit', function(){
                app_progressBar.toggle();
                app_progressBar.maxOut();
            });

            var tradeId = $('#trade').val();
            filterContractor(tradeId);

            var locationId = $('#locationLevel_0').val();
            getLocationByLevel(locationId);

            var locationFieldName = [];

            @foreach(Input::old() as $fieldName => $value)
                @if(preg_match('/^locationLevel_/', $fieldName))
                    
                    locationFieldName['{{{$fieldName}}}'] = '{{{$value}}}';

                @endif
            @endforeach

            // Populate starts here

            $('#trade').on('change', function(){

                var id = this.value; 
                $('#contractor').empty().append('<option selected disabled>Select</option>');
                filterContractor(id);
                filterProjectLabourRate(id);

            });

            $('#locationLevel_0').on('change', function(){

                $('select[data-level]').filter(function(){

                    return $(this).data('level') > 0; 

                }).each(function(){

                    $(this).remove();   
                });

                var id = this.value; 
                getLocationByLevel(id); 

            });

            function filterProjectLabourRate(id){
                var url = '{{ route('daily-labour-report.populateProjectLabourRate', array($project->id)) }}';

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        id: id
                    },
                    success: function (data, status, xhr) {

                       $.each(data, function(index, value) {

                           $('#normal_working_hours').val(value.normal_working_hours);
                           $('#normal_rate_per_hour_'+value.labour_type).val(value.normal_rate_per_hour);
                           $('#ot_rate_per_hour_'+value.labour_type).val(value.ot_rate_per_hour);

                        });
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }

            function filterContractor(id){
                var url = '{{ route('daily-labour-report.populateContractor', array($project->id)) }}';

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        id: id
                    },
                    success: function (data, status, xhr) {
                        var values = data; 
                        $.each(values, function(index, value) {
                            $('#contractor').append($("<option/>", {
                                value: value.id,
                                text: value.name
                            }));
                            $('#contractor').val(value.id);
                        });

                        $('#contractor').val({{{Input::old('contractor')}}});
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }

            function getLocationByLevel(id){

                var url = '{{ route('daily-labour-report.getLocationByLevel', array($project->id)) }}';

                var classIdentity = "locationLevel_"; 

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        id: id
                    },
                    success: function (data, status, xhr) {

                        var level = data.currentLevel; 

                        var currentClassIdentity = classIdentity.concat(level);

                        if((data.nextLocation).length < 1)
                        {
                            return;
                        }

                        $('#location_select')
                        .append('<select style="margin-top:10px" name="'+currentClassIdentity+'" id="'+currentClassIdentity+'" data-level="'+level+'" class="form-control"></select>');
                        $('#'+currentClassIdentity).append('<option selected disabled="">Select Next Location</option>');

                        var values = data.nextLocation;

                        $.each(values, function(index, value) {

                                $('#'+currentClassIdentity).append($("<option/>", {
                                    value: value.id,
                                    text: value.description
                                }));
                        });

                        $('#'+currentClassIdentity).on('change', function(){
                            $('select[data-level]').filter(function(){

                                return $(this).data('level') > level; 

                            }).each(function(){

                                $(this).remove();   
                            });
                            var id = this.value;
                            getLocationByLevel(id); 
                        });

                        if(locationFieldName['locationLevel_'+level] != null)
                        {
                            $('#'+currentClassIdentity).val(locationFieldName['locationLevel_'+level]);
                            var nextId = $('#'+currentClassIdentity).val();
                            getLocationByLevel(nextId); 
                        }

                    }
                });
            }

        });
    </script>
@endsection