@extends('layout.main')

@section('css')
    <link href="{{ asset('js/summernote-master/dist/summernote.css') }}" rel="stylesheet">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.loginRequestFormSettings') }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-gear"></i> {{{ trans('vendorManagement.loginRequestFormSettings') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('vendorManagement.loginRequestFormSettings') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        {{ Form::model($settings, array('route' => array('loginRequestForm.settings.update'), 'class' => 'smart-form', 'id' => 'instructionsForm')) }}
                            <input type="hidden" name="instructions">
                            <input type="hidden" name="disclaimer">
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <label class="label">{{ trans('vendorManagement.instructionsToVendors') }}:</label>
                                    <div name="txtInstructions" class="summernote">{{ $settings->instructions }}</div>
                                </section>
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <label class="checkbox">
                                        {{ Form::checkbox('include_instructions', 1, Input::old('include_instructions')) }}

                                        <i></i>{{ trans('vendorManagement.includeInstructions') }}
                                    </label>
                                </section>
                            </div>
                            <hr/>
                            <br/>
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <label class="label">{{ trans('vendorManagement.includeVendorDisclaimer') }}:</label>
                                    <div name="txtDisclaimer" class="summernote">{{ $settings->disclaimer }}</div>
                                </section>
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <label class="checkbox">
                                        {{ Form::checkbox('include_disclaimer', 1, Input::old('include_disclaimer')) }}

                                        <i></i>{{ trans('vendorManagement.includeVendorDisclaimer') }}
                                    </label>
                                </section>
                            </div>

                            <footer>
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                            </footer>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script src="{{ asset('js/summernote/summernote.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
<script>
    $(document).ready(function(e) {
        $('.summernote').summernote({
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview']],
            ],
        });

        $('#instructionsForm').on('submit', function(e) {
            e.preventDefault();

            var instructions = $('[name="txtInstructions"]').summernote('code');
            var disclaimer   = $('[name="txtDisclaimer"]').summernote('code');

            $('[name="instructions"]').val(instructions);
            $('[name="disclaimer"]').val(disclaimer);

            $(this)[0].submit();
        })
    });
</script>
@endsection