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
                    {{ Form::model($tenderDocument, array('class'=>'smart-form', 'id'=>'tender-document-form','route' => array('open-tender-documents.update', $project->id, $tenderId, $tenderDocument->id), 'method' => 'PUT')) }}
                    <fieldset id="form" class="form-group"> 
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <label for="description" style="padding-left:5px;"><strong>{{{ trans('openTender.description') }}}</strong>&nbsp;<span class="required">*</span></label>
                                {{ Form::text('description', Input::old('description'), array('class' => 'form-control padded-less-left', 'readonly' => true)) }}
                                {{ $errors->first('description', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                    </fieldset>
                    <footer>
                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#downloadModal" data-action="get-downloads" data-get-downloads="{{ route('open-tender-documents.attachements.get',[$project->id,  $tenderId, $tenderDocument->id]) }}">
                            <i class="fa fa-paperclip"></i> {{ trans('general.attachments') }} ({{$attachmentsCount}})
                        </button>
                        {{ link_to_route('projects.tender.open_tender.get', trans('forms.back'), array($project->id, $tenderId, "tenderDocuments"), ['class' => 'btn btn-default']) }}
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

@include('uploads.downloadModal')
    
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