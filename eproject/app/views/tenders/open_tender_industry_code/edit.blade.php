@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.tender.open_tender.get', trans('openTender.openTender'), array($project->id, $tenderId, "industryCode")) }}</li>
        <li>{{{ trans('openTender.industryCode') }}}</li>
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
                    {{ Form::model($industryCode, array('class'=>'smart-form','id'=>'pic-form','route' => array('open-tender-industry-code.update', $project->id, $tenderId, $industryCode->id), 'method' => 'PUT')) }}
                    <fieldset id="form" class="form-group"> 
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label for="vendor_category" style="padding-left:5px;"><strong>{{{ trans('openTender.vendorCategory') }}}</strong>&nbsp;<span class="required">*</span></label>
                                <select name="vendor_category_id" id="vendor_category_id" class="form-control">
                                    @foreach($vendorCategories as $vendorCategory)
                                        @if($industryCode->vendor_category_id == $vendorCategory->id)
                                            <option selected value="{{{ $vendorCategory->id }}}">
                                                {{{ $vendorCategory->name }}}
                                            </option>
                                        @else
                                            <option value="{{{ $vendorCategory->id }}}">
                                                {{{ $vendorCategory->name }}}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>                                
                                {{ $errors->first('vendor_category_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label for="vendor_category" style="padding-left:5px;"><strong>{{{ trans('openTender.vendorWorkCategory') }}}</strong>&nbsp;<span class="required">*</span></label>
                                <select name="vendor_work_category_id" id="vendor_work_category_id" class="form-control">
                                    @foreach($vendorWorkCategories as $vendorWorkCategory)
                                        @if($industryCode->vendor_work_category_id == $vendorWorkCategory->id)
                                            <option selected value="{{{ $vendorWorkCategory->id }}}">
                                                {{{ $vendorWorkCategory->name }}}
                                            </option>
                                        @else
                                            <option value="{{{ $vendorWorkCategory->id }}}">
                                                {{{ $vendorWorkCategory->name }}}
                                            </option>
                                        @endif
                                    @endforeach                   
                                </select>                                
                                {{ $errors->first('vendor_category_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label for="cidb_grade" style="padding-left:5px;"><strong>{{{ trans('openTender.cidbGrade') }}}</strong>&nbsp;<span class="required">*</span></label>
                                <select name="cidb_grade_id" id="cidb_grade_id" class="form-control">
                                    @foreach($cidbGrades as $cidbGrade)
                                        @if($industryCode->cidb_grade_id == $cidbGrade->id)
                                            <option selected value="{{{ $cidbGrade->id }}}">
                                                {{{ $cidbGrade->grade }}}
                                            </option>
                                        @else
                                            <option value="{{{ $cidbGrade->id }}}">
                                                {{{ $cidbGrade->grade }}}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>                                
                                {{ $errors->first('cidb_grade_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label for="cidb_code" style="padding-left:5px;"><strong>{{{ trans('openTender.cidbCode') }}}</strong>&nbsp;<span class="required">*</span></label>
                                <select name="cidb_code_id" id="cidb_code_id" class="form-control">
                                    @foreach($cidbCodes as $cidbCode)
                                        @if($industryCode->cidb_code_id == $cidbCode->id)
                                            @if($cidbCode->child)
                                                <option selected value="{{{ $cidbCode->id }}}">
                                                    &nbsp;&nbsp;{{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                                                </option>
                                            @else($cidbCode->subChild)
                                                <option selected value="{{{ $cidbCode->id }}}">
                                                    &nbsp;&nbsp;&nbsp;&nbsp;{{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                                                </option>
                                            @endif
                                        @else
                                            @if($cidbCode->parent && !$cidbCode->child)
                                                <option disabled value="{{{ $cidbCode->id }}}">
                                                    {{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                                                </option>
                                            @elseif($cidbCode->parent && $cidbCode->child)
                                                <option disabled value="{{{ $cidbCode->id }}}">
                                                    &nbsp;&nbsp;{{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                                                </option>
                                            @elseif($cidbCode->child)
                                                <option value="{{{ $cidbCode->id }}}">
                                                    &nbsp;&nbsp;{{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                                                </option>
                                            @else($cidbCode->subChild)
                                                <option value="{{{ $cidbCode->id }}}">
                                                    &nbsp;&nbsp;&nbsp;&nbsp;{{{ $cidbCode->code }}} &nbsp; <p>({{{ $cidbCode->description }}})</p>
                                                </option>
                                            @endif
                                        @endif
                                    @endforeach
                                </select>           
                                {{ $errors->first('cidb_code_id', '<em class="invalid">:message</em>') }}                     
                            </section>
                        </div>
                    </fieldset>
                    <footer>
                        {{ link_to_route('projects.tender.open_tender.get', trans('forms.cancel'), array($project->id, $tenderId, "industryCode"), ['class' => 'btn btn-default']) }}
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('openTender.update'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit'] )  }}
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

        $('#vendor_category_id').change(function(){
            console.log($(this).val());
            $.ajax({
                url: "{{route('open-tender-industry-code.get_vendor_work_categories', array($project->id, $tenderId))}}",
                type: 'GET',
                data: { vendor_category_id: $(this).val() },
                success: function(response) {
                    var $vendor_work_category_id = $("#vendor_work_category_id");
                    $vendor_work_category_id.empty(); // remove old options

                    $.each(response, function(key,value) {
                    $vendor_work_category_id.append($("<option></option>")
                        .attr("value", value.id).text(value.name));
                    });
                },
                error: function(error) {
                    console.error(error);
                }
            });
        });

    </script>
@endsection