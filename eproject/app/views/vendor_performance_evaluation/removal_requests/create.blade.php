@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorPerformanceEvaluation') }}}</li>
        <li>{{ link_to_route('vendorPerformanceEvaluation.index', trans('vendorManagement.evaluations'), array()) }}</li>
        <li>{{ trans('vendorManagement.evaluationRemovalRequest') }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-backspace"></i> {{{ trans('vendorManagement.evaluationRemovalRequest') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.evaluationRemovalRequest') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    {{ Form::open(array('route' => array('vendorPerformanceEvaluation.evaluations.removalRequest.store', $evaluation->id), 'class' => 'smart-form')) }}
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label">{{{ trans('vendorManagement.reason') }}} <span class="required">*</span></label>
                                <label class="fill-horizontal {{{ $errors->has('vendor_performance_evaluation_project_removal_reason_id') ? 'state-error' : null }}}">
                                    {{ Form::select('vendor_performance_evaluation_project_removal_reason_id', $reasons, Input::old('vendor_performance_evaluation_project_removal_reason_id'), ['class' => 'select2 fill-horizontal'])}}
                                </label>
                                {{ $errors->first('vendor_performance_evaluation_project_removal_reason_id', '<em class="invalid">:message</em>') }}
                            </section>
                            <section class="col col-xs-12 col-md-6 col-lg-6" id="vendor_performance_evaluation_project_removal_reason_text" hidden>
                                <label class="label">&nbsp;</label>
                                <label class="input {{{ $errors->has('vendor_performance_evaluation_project_removal_reason_text') ? 'state-error' : null }}}">
                                    {{ Form::text('vendor_performance_evaluation_project_removal_reason_text', Input::old('vendor_performance_evaluation_project_removal_reason_text')) }}
                                </label>
                                {{ $errors->first('vendor_performance_evaluation_project_removal_reason_text', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-12 col-md-6 col-lg-6">
                                <label class="label {{{ $errors->has('remarks') ? 'state-error' : null }}}">{{ trans('general.remarks') }}</label>
                                    <label class="textarea"><textarea name="remarks" id="remarks" rows="4"></textarea>
                                </label>
                                {{ $errors->first('remarks', '<em class="invalid">:message</em>') }}
                            </section>
                        </div>
                        <footer>
                            {{ link_to_route('vendorPerformanceEvaluation.index', trans('forms.back'), array(), array('class' => 'btn btn-default')) }}
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
    <script>
        if($('select[name=vendor_performance_evaluation_project_removal_reason_id]').prop('value')=='others'){
            $('#vendor_performance_evaluation_project_removal_reason_text').show();
        }
        else{
            $('#vendor_performance_evaluation_project_removal_reason_text').hide();
        }

        $('select[name=vendor_performance_evaluation_project_removal_reason_id]').on('change', function(){
            if($('select[name=vendor_performance_evaluation_project_removal_reason_id]').prop('value')=='others'){
                $('#vendor_performance_evaluation_project_removal_reason_text').show();
            }
            else{
                $('#vendor_performance_evaluation_project_removal_reason_text').hide();
            }
        });
    </script>
@endsection('js')