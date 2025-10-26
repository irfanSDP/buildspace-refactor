@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification', trans('vendorManagement.registrationAndPreQualification'), array()) }}</li>
        <li>{{{ $vendorRegistration->company->name }}}</li>
        <li>{{{ trans('vendorManagement.assign') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-play"></i> {{{ trans('vendorManagement.assign') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ $vendorRegistration->company->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(array('route' => array('vendorManagement.approval.registrationAndPreQualification.assign', $vendorRegistration->id), 'class' => 'smart-form')) }}
                        @if($errors->first('form'))
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <div class="well border-danger text-danger">
                                        {{{ $errors->first('form') }}}
                                    </div>
                                </section>
                            </div>
                        @endif
                        <div class="row">
                            <section class="col col-xs-6 col-md-6 col-lg-6">
                                <label class="label">{{ trans('vendorManagement.assign') }}:</label>
                                <label class="input">
                                    <select id="assign-processor-input" name="processor_id" class="fill-horizontal">
                                        <?php $processorId = Input::old('processor_id', $currentProcessorId) ?>
                                        @if($processorId)
                                        <option value="{{ $processorId }}">{{ $processorNames[$processorId] }}</option>
                                        @else
                                        <option value="" selected="selected">{{ trans('forms.select') }}</option>
                                        @endif
                                    </select>
                                </label>
                                {{ $errors->first('processor_id', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ link_to_route('vendorManagement.approval.registrationAndPreQualification', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
                            {{ Form::button('<i class="fa fa-play"></i> '.trans('vendorManagement.assign'), ['type' => 'submit', 'class' => 'btn btn-warning'] )  }}
                        </footer>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
    <script>
        $(document).ready(function () {
            $('#assign-processor-input').select2({
                ajax: {
                    theme: "bootstrap",
                    url: "{{ route('vendorManagement.approval.registrationAndPreQualification.assignForm.processors', array($vendorRegistration->id))}}",
                    dataType: 'json'
                }
            });
        });
    </script>
@endsection