@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('projects.addNew') }}</li>
    </ol>

    <span class="ribbon-button-alignment pull-right">
		<span class="label label-info">{{{ $defaultStatus }}}</span>
	</span>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-edit"></i> {{ trans('projects.addNew') }}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body no-padding">
                        {{ Form::open(array('class' => 'smart-form', 'id' => 'add-form')) }}
                            @include('projects.partials.projectFormDesign')

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

            $('#btnPreviewLetterOfAwardTemplatePDF').on('click', function(e) {
                e.preventDefault();
                var url = $('#letterOfAwardTemplateSelect option:selected').data('print_route');
                var win = window.open(url, '_blank');
                win.focus();
            });

            $('#btnPreviewFormOfTenderTemplatePDF').on('click', function(e) {
                e.preventDefault();
                var url = $('#formOfTenderTemplateSelect option:selected').data('print_route');
                var win = window.open(url, '_blank');
                win.focus();
            });
        });
    </script>
    @include('projects.js_partials.js_create')
@endsection