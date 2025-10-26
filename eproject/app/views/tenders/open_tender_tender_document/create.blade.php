@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.tender.open_tender.get', trans('openTender.openTender'), array($project->id, $tenderId, "personInCharge")) }}</li>
        <li>{{{ trans('openTender.tenderDocuments') }}}</li>
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
                    {{ Form::open(array('class'=>'smart-form','id'=>'pic-form','route' => array('open-tender-documents.store', $project->id, $tenderId))) }}
                    <fieldset id="form" class="form-group"> 
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label for="description" style="padding-left:5px;"><strong>{{{ trans('openTender.description') }}}</strong>&nbsp;<span class="required">*</span></label>
                                {{ Form::text('description', Input::old('description'), array('class' => 'form-control padded-less-left')) }}
                                {{ $errors->first('description', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                    </fieldset>
                    <fieldset id="form" class="form-group"> 
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                @include('file_uploads.partials.upload_file_modal')
                            </section>
                        </div>
                    </fieldset>
                    <footer>
                        {{ link_to_route('projects.tender.open_tender.get', trans('forms.cancel'), array($project->id, $tenderId, "tenderDocuments"), ['class' => 'btn btn-default']) }}
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('openTender.submit'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit'] )  }}
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