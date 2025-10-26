@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('daily-labour-report.index', 'Daily Labour Reports', array($project->id)) }}</li>
        <li>{{{ trans('dailyLabourReports.edit_daily_labour_reports') }}}</li>
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

                 {{ Form::model($dailyLabourReport, array('class'=>'smart-form','route' => array('daily-labour-report.update', $project->id ,$dailyLabourReport->id), 'files' => true, 'method' => 'PUT')) }}
                     <fieldset>  
                        <legend><h2>{{{ trans('dailyLabourReports.edit_daily_labour_reports') }}}</h2></legend>
                        <section>
                            <label for="date" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.date') }}}</strong></label>
                            {{ Form::text('date', Input::old('date') ?? \Carbon\Carbon::parse($project->getProjectTimeZoneTime($dailyLabourReport->date))->format('d-M-Y'), array('class' => 'datetimepicker form-control')) }}
                            {{ $errors->first('date', '<em class="invalid">:message</em>') }}
                        </section>
                        <section>
                            <label for="type" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.weather') }}}</strong></label>
                            <select name="weather" id="weather" class="form-control">
                                <option selected disabled="">Select</option>                   
                                    @foreach($weathers as $weather)
                                        @if(Input::old('weather')??$dailyLabourReport->weather_id == $weather->id)
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
                        <section>
                            <label for="type" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.type') }}}</strong></label>
                            <select name="type" id="type" class="form-control">
                                <option selected disabled="">Select</option>                   
                                    @foreach($type as $values)
                                        @foreach($values as $value)
                                            @if(Input::old('type')??$dailyLabourReport->bill_column_setting_id == $value->id)
                                                <option selected value="{{{ $value->id}}}">
                                                    {{{ $value->name }}}
                                                </option>
                                            @else
                                                <option value="{{{ $value->id}}}">
                                                    {{{ $value->name }}}
                                                </option>
                                            @endif
                                        @endforeach
                                    @endforeach
                            </select>
                        </section>
                        <section>
                            <label for="unit" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.unit') }}}</strong></label>
                            <select name="unit" id="unit" class="form-control">
                                <option selected disabled="">Select</option>
                            </select>
                        </section>
                        <section>
                            <label for="location" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.location') }}}</strong>&nbsp;<span class="required">*</span></label>
                            <select name="locationLevel_0" id="locationLevel_0" class="form-control">
                                <option selected disabled="">Select Location</option>
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
                        <section>
                            <label for="trade" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.trade') }}}</strong>&nbsp;<span class="required">*</span></label>
                            <select name="trade" id="trade" class="form-control">
                                <option selected disabled="">Select</option>
                                @foreach($trades as $trade)
                                    @if(Input::old('trade')??$dailyLabourReport->pre_defined_location_code_id == $trade->id)
                                        <option selected value="{{{ $trade->id }}}">
                                            {{{ $trade->name }}}
                                        </option>
                                    @else
                                        <option value="{{{ $trade->id }}}">
                                            {{{ $trade->name }}}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            {{ $errors->first('trade', '<em class="invalid">:message</em>') }}
                        </section>
                        <section>
                            <label for="contractor" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.contractor') }}}</strong>&nbsp;<span class="required">*</span></label>
                            <select name="contractor" id="contractor" class="form-control">
                                <option selected disabled="">Select</option>
                                @foreach($contractors as $contractor)
                                @if(Input::old('contractor')??$dailyLabourReport->contractor_id == $contractor->id)
                                    <option selected value="{{{ $contractor->id }}}">
                                        {{{ $contractor->name }}}
                                    </option>
                                @else
                                    <option value="{{{ $contractor->id }}}">
                                        {{{ $contractor->name }}}
                                    </option>
                                @endif
                                @endforeach
                            </select>
                            {{ $errors->first('contractor', '<em class="invalid">:message</em>') }}
                        </section>
                    </fieldset>
                    <fieldset>
                        <legend><h2>{{{ trans('dailyLabourReports.labour_info') }}}</h2></legend>
                        <section>
                            @include('daily_labour_reports.labour_info_edit')
                        </section>
                        <section>
                            <label for="work_description" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.work_description') }}}</strong>&nbsp;<span class="required">*</span>
                            </label>
                            <textarea class="form-control" rows="5" name="work_description" id="work_description">{{{Input::old('work_description')??$dailyLabourReport->work_description}}}</textarea>
                            {{ $errors->first('work_description', '<em class="invalid">:message</em>') }}
                        </section>
                        <section>
                            <label for="remark" style="padding-left:5px;"><strong>{{{ trans('dailyLabourReports.remark') }}}</strong>&nbsp;<span class="required">*</span></label>
                            <textarea class="form-control" rows="5" name="remark" id="remark">{{{Input::old('remark')??$dailyLabourReport->remark}}}</textarea>
                            {{ $errors->first('remark', '<em class="invalid">:message</em>') }}
                        </section>
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('requestForInformation.attachments') }}}:</label>

                            @include('file_uploads.partials.upload_file_modal')
                        </section>
                    </fieldset>
                    <footer>
                        {{ Form::submit(trans('dailyLabourReports.edit'), array('class' => 'btn btn-default', 'name' => 'Edit')) }}
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

            $('input[type=submit]').on('click', function(){
                app_progressBar.toggle();
                app_progressBar.maxOut();

            });

            var typeId = $('#type').val();
            filterUnitRecord(typeId);

            var locationId = $('#locationLevel_0').val();
            getLocationByLevel(locationId);

            var locationFieldName = [];

            @foreach(Input::old() as $fieldName => $value)
                @if(preg_match('/^locationLevel_/', $fieldName))
                    
                    locationFieldName['{{{$fieldName}}}'] = '{{{$value}}}';

                @endif
            @endforeach

            // Populate starts here

            $('#type').on('change', function(){

                var id = this.value; 
                $('#unit').empty().append('<option selected disabled>Select</option>');
                filterUnitRecord(id); 

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

            function filterUnitRecord(id) {
                var url = '{{ route('daily-labour-report.populateUnit', array($project->id)) }}';

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{{ csrf_token() }}}',
                        id: id
                    },
                    success: function (data, status, xhr) {
                    
                        $.each(data, function(index, value) {

                            $('#unit').append($("<option/>", {
                                value: value,
                                text: value
                            }));

                        });
                        $('#unit').val({{{Input::old('unit')??$dailyLabourReport->unit}}});
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
                        .append('<select name="'+currentClassIdentity+'" id="'+currentClassIdentity+'" data-level="'+level+'" class="form-control"></select>');
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