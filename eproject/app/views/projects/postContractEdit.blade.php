@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        <li>Edit Post Contract Information</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-edit"></i> Edit Post Contract Information
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget">
            <div>
                <div class="widget-body no-padding">
                    {{ Form::open(['route'=>['projects.postContract.info.store', $project->id], 'class' => 'smart-form', 'method' => 'POST']) }}
                        @if($project->contract->type == \PCK\Contracts\Contract::TYPE_PAM2006)
                            <fieldset>
                            @include('projects.partials.postContractForms.projectFormPostContractEdit')
                            </fieldset>
                        @endif
                        <footer>
                            {{ link_to_route('projects.show', trans('forms.back'), [$project->id], ['class' => 'btn btn-default']) }}
                            {{ Form::button('<i class="fa fa-fw fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
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
<script src="{{ asset('js/plugin/bootstrap-timepicker/bootstrap-timepicker.min.js') }}"></script>
<script>
$(document).ready(function() {
    $.each(["cpc_date", "extension_of_time_date", "certificate_of_making_good_defect_date", "cnc_date", "performance_bond_validity_date", "insurance_policy_coverage_date"], function(idx, val) {
        $('#'+val).datepicker({
            dateFormat : 'dd-M-yy',
            prevText : '<i class="fa fa-chevron-left"></i>',
            nextText : '<i class="fa fa-chevron-right"></i>',
            autoclose: true
        });
        
        if($('#'+val).val().length){
            var defaultDate = new Date(Date.parse($('#'+val).val()));
            $('#'+val).datepicker('setDate', defaultDate);
        }
    });
});
</script>
@endsection