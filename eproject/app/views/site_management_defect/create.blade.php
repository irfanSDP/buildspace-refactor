@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('site-management-defect.index', 'Defect', array($project->id)) }}</li>
        <li>{{{ trans('siteManagementDefect.submit-defect') }}}</li>
    </ol>

@endsection

@section('content')

<div class="row">
<!-- NEW COL START -->
<article class="col-sm-12 col-md-12 col-lg-12">
    <!-- Widget ID (each widget will need unique ID)-->
    <div class="jarviswidget">
        <header>
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
             <h2>{{{ trans('siteManagementDefect.submit-defect') }}}</h2> 
        </header>

        <!-- widget div-->
        <div>
            <!-- widget content -->
            <div class="widget-body no-padding">
                 {{ Form::open(array('class'=>'smart-form','id'=>'defect-form','files' => true)) }}
                     <fieldset id="form">  
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label for="location" style="padding-left:5px;"><strong>{{{ trans('siteManagementDefect.location') }}}</strong>&nbsp;<span class="required">*</span></label>
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
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label for="trade" style="padding-left:5px;"><strong>{{{ trans('siteManagementDefect.trade') }}}</strong>&nbsp;<span class="required">*</span></label>
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
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label for="contractor" style="padding-left:5px;"><strong>{{{ trans('siteManagementDefect.contractor') }}}</strong>&nbsp;<span class="required">*</span></label>
                                <select name="contractor" id="contractor" class="form-control">
                                    <option selected disabled>Select</option>
                                </select>
                                {{ $errors->first('contractor', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label for="category" style="padding-left:5px;"><strong>{{{ trans('siteManagementDefect.category') }}}</strong>&nbsp;<span class="required">*</span></label>
                                <select name="category" id="category" class="form-control">
                                    <option selected disabled>Select</option>
                                </select>
                                {{ $errors->first('category', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label for="defect" style="padding-left:5px;"><strong>{{{ trans('siteManagementDefect.defect') }}}</strong></label>
                                <select name="defect" id="defect" class="form-control">
                                    <option selected disabled>Select</option>
                                </select>
                            </section>
                        </div>
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label for="remark" style="padding-left:5px;"><strong>{{{ trans('siteManagementDefect.remark') }}}</strong>&nbsp;<span class="required">*</span></label>
                            <textarea class="form-control" rows="5" name="remark" id="remark">{{{Input::old('remark')}}}</textarea>
                            {{ $errors->first('remark', '<em class="invalid">:message</em>') }}
                        </section>
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label class="label">{{{ trans('siteManagement.attachments') }}}:</label>
                            @include('file_uploads.partials.upload_file_modal')
                        </section>
                    </fieldset>
                    <footer>
                        {{ link_to_route('site-management-defect.index', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('siteManagementDefect.submit'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit'] )  }}
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
    <script>

        $(document).ready(function () {

            $('#defect-form').on('submit', function(){
                app_progressBar.toggle();
                app_progressBar.maxOut();
            });

            var tradeId = $('#trade').val();
            filterCategoryRecord(tradeId);
            filterContractor(tradeId);

            filterDefectRecord({{{Input::old('category')}}});

            var locationId = $('#locationLevel_0').val();
            getLocationByLevel(locationId);

            var locationFieldName = [];

            @foreach(Input::old() as $fieldName => $value)
                @if(preg_match('/^locationLevel_/', $fieldName))
                    
                    locationFieldName['{{{$fieldName}}}'] = '{{{$value}}}';

                @endif
            @endforeach

            // Populate starts here

            $('#locationLevel_0').on('change', function(){

                $('select[data-level]').filter(function(){

                    return $(this).data('level') > 0; 

                }).each(function(){

                    $(this).remove();   
                });

                var id = this.value; 
                getLocationByLevel(id); 

            });

            $('#trade').on('change', function(){

                var id = this.value; 
                $('#contractor').empty().append('<option selected disabled>Select</option>');
                $('#category').empty().append('<option selected disabled>Select</option>');
                filterContractor(id);
                filterCategoryRecord(id); 
            });

            $('#category').on('change', function(){

                var id = this.value; 
                $('#defect').empty().append('<option selected disabled>Select</option>');
                filterDefectRecord(id); 

            });

            function getLocationByLevel(id){

                var url = '{{ route('site-management-defect.getLocationByLevel', array($project->id)) }}';

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

            function filterCategoryRecord(id) {
                var url = '{{ route('site-management-defect.populateCategory', array($project->id)) }}';

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
                                $('#category').append($("<option/>", {
                                    value: value.id,
                                    text: value.name
                                }));
                            });
                        $('#category').val({{{Input::old('category')}}});
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }

            function filterDefectRecord(id) {
                var url = '{{ route('site-management-defect.populateDefect', array($project->id)) }}';

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
                                $('#defect').append($("<option/>", {
                                    value: value.id,
                                    text: value.name
                                }));
                            });

                        $('#defect').val({{{Input::old('defect')}}});
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }

        });
    </script>
@endsection