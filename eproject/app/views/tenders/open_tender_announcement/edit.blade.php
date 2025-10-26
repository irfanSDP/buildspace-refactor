@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.tender.open_tender.get', trans('openTender.openTender'), array($project->id, $tenderId, "announcementInfo")) }}</li>
        <li>{{{ trans('openTender.openTenderAnnouncement') }}}</li>
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
                    {{ Form::model($announcementInfo, array('class'=>'smart-form', 'id'=>'pic-form','route' => array('open-tender-announcement.update', $project->id, $tenderId, $announcementInfo->id), 'method' => 'PUT')) }}
                    <fieldset id="form" class="form-group"> 
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label for="date" style="padding-left:5px;"><strong>{{{ trans('openTender.date') }}}</strong>&nbsp;<span class="required">*</span></label>
                                {{ Form::text('date', Input::old('date'), array('class' => 'form-control datetimepicker padded-less-left')) }}
                                {{ $errors->first('date', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label for="description" style="padding-left:5px;"><strong>{{{ trans('openTender.description') }}}</strong>&nbsp;<span class="required">*</span></label>
                                {{ Form::textArea('description', Input::old('description'), array('class' => 'form-control padded-less-left')) }}
                                {{ $errors->first('description', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                    </fieldset>
                    <footer>
                        {{ link_to_route('projects.tender.open_tender.get', trans('forms.cancel'), array($project->id, $tenderId, "personInCharge"), ['class' => 'btn btn-default']) }}
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
                dateFormat : 'yy-mm-dd',
                prevText : '<i class="fa fa-chevron-left"></i>',
                nextText : '<i class="fa fa-chevron-right"></i>',
                showTodayButton: true,
            });
            
            $("form").on('submit', function(){
                app_progressBar.toggle();
                app_progressBar.maxOut();
            });
        });

    </script>
@endsection