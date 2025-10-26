@extends('layout.main')

@section('breadcrumb')

    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('site-management-site-diary.index', 'Site Diary', array($project->id)) }}</li>
        <li>Site Diary Rejected Material Form</li>
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
                {{ Form::model($rejectedMaterialForm, array('class'=>'smart-form', 'id'=>'rejected-material-form','route' => array('site-management-site-diary-rejected_material.update', $project->id, $siteDiaryId, $rejectedMaterialForm->id), 'method' => 'PUT')) }}
                <fieldset id="form">  
                    {{ Form::hidden('form_type', 'rejected_material') }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <label for="type" style="padding-left:5px;"><strong>Rejected Materials</strong>&nbsp;<span class="required">*</span></label>
                            <select name="rejected_material_id" id="rejected_material_id" class="form-control">
                                <option selected disabled>Select</option>                   
                                    @foreach($rejected_materials as $rejected_material)
                                        @if($rejectedMaterialForm->rejected_material_id == $rejected_material->id)
                                            <option selected value="{{{ $rejected_material->id }}}">
                                                {{{ $rejected_material->name }}}
                                            </option>
                                        @else
                                            <option value="{{{ $rejected_material->id }}}">
                                                {{{ $rejected_material->name }}}
                                            </option>
                                        @endif
                                    @endforeach
                            </select>
                            {{ $errors->first('rejected_material_id', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                </fieldset>
                <footer>
                    {{ link_to_route('site-management-site-diary.index', trans('forms.cancel'), [$project->id], ['class' => 'btn btn-default']) }}
                    {{ Form::button('<i class="fa fa-save"></i> '.trans('siteManagementDefect.update'), ['type' => 'submit', 'class' => 'btn btn-primary', 'name' => 'Submit'] )  }}
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