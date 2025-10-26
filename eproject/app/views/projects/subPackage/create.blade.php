@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{{ trans('projects.addSubPackage') }}}</li>
    </ol>

    <span class="ribbon-button-alignment pull-right">
		<span class="label label-info">{{{ $defaultStatus }}}</span>
	</span>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-edit"></i> {{ trans('projects.addSubPackage') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::model($templateProject, array('class' => 'smart-form', 'id' => 'add-form', 'files' => true)) }}
                            <fieldset>
                                <div class="row">
                                    <div class="col col-xs-12 col-md-3 col-lg-4">
                                        <section>
                                            <h3>{{{ trans('projects.subPackageFile') }}}</h3>
                                        </section>

                                        <section>
                                            <label class="label">{{{ trans('forms.upload') }}} <span class="required">*</span>:</label>
                                            <label class="input {{{ $errors->has('ebqFile') ? 'state-error' : null }}}">
                                                {{ Form::file('ebqFile', array('style' => 'height:100%')) }}
                                            </label>
                                            {{ $errors->first('ebqFile', '<em class="invalid">:message</em>') }}
                                        </section>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                @include('projects.partials.contractNumberFields', array(
                                    'labelsOnly' => array(
                                        'subsidiary' => $fixedSubsidiary->name,
                                        'contract' => $templateProject->contract->name,
                                    )
                                ))
                            </fieldset>
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <label class="label">{{{ trans('projects.projectDescription') }}} <span class="required">*</span>:</label>
                                        <label class="textarea {{{ $errors->has('description') ? 'state-error' : null }}}">
                                            {{ Form::textarea('description', Input::old('description'), array('required' => 'required', 'rows' => 3)) }}
                                        </label>
                                        {{ $errors->first('description', '<em class="invalid">:message</em>') }}
                                    </section>
                                </div>
                            </fieldset>
                            <footer>
                                {{ link_to_route('projects.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/app/app.countrySelect.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#add-form').validate({
                errorPlacement : function(error, element) {
                    error.insertAfter(element.parent());
                },
                submitHandler: function(form)
                {
                    app_progressBar.toggle();
                    app_progressBar.maxOut(500, function(){
                        form.submit();
                    });
                }
            });
            $('#subsidiary_select').val("{{{ $fixedSubsidiary->id }}}").change();
        });
    </script>
    @include('projects.js_partials.js_create')
@endsection