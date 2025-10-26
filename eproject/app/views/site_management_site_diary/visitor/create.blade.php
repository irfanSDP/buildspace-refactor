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
            <div class="widget-body">
                <!-- widget content -->
                <div class="widget-body no-padding">
                    {{ Form::open(array('class'=>'smart-form','id'=>'visitor-form','route' => array('site-management-site-diary-visitor.create', $project->id, $siteDiaryId),'files' => true)) }}
                    <fieldset id="form" class="form-group"> 
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label for="visitor_name" style="padding-left:5px;"><strong>Name</strong>&nbsp;<span class="required">*</span></label>
                                {{ Form::text('visitor_name', Input::old('visitor_name'), array('class' => 'form-control padded-less-left')) }}
                                {{ $errors->first('visitor_name', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label for="visitor_company_name" style="padding-left:5px;"><strong>Company Name</strong>&nbsp;<span class="required">*</span></label>
                                {{ Form::text('visitor_company_name', Input::old('visitor_company_name'), array('class' => 'form-control padded-less-left')) }}
                                {{ $errors->first('visitor_company_name', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label for="visitor_time_in" style="padding-left:5px;"><strong>Time In</strong></label>
                                {{ Form::select('visitor_time_in', PCK\Base\Helpers::generateTimeArrayInMinutes(), Input::old('visitor_time_in'), array('class' => 'form-control padded-less-left')) }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label for="visitor_time_out" style="padding-left:5px;"><strong>Time Out</strong></label>
                                {{ Form::select('visitor_time_out', PCK\Base\Helpers::generateTimeArrayInMinutes(), Input::old('visitor_time_out'), array('class' => 'form-control padded-less-left')) }}
                            </section>
                        </div>
                    </fieldset>
                    <footer>
                        {{ link_to_route('site-management-site-diary.index', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('siteManagementDefect.submit'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit'] )  }}
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

    </script>
@endsection