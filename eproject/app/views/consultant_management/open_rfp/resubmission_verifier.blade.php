@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('consultant.management.contracts.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{{ $vendorCategoryRfp->vendorCategory->name }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-user-tie"></i> RFP Resubmission {{{ trans('verifiers.verifiers') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <div>
                <div class="widget-body">
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <h1 class="page-title txt-color-blueDark">{{{ trans('verifiers.verifierLogs') }}}</h1>
                            <div id="verifier_logs-table">
                        </section>
                    </div>
                    <hr class="simple">
                    @if($openRfp->status == PCK\ConsultantManagement\ConsultantManagementOpenRfp::STATUS_APPROVED && $user->isConsultantManagementCallingRfpEditor($consultantManagementContract))
                    {{ Form::open(['route' => ['consultant.management.rfp.resubmission.verifier.update', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
                    <div class="row">
                        <section class="col col-xs-12 col-md-6 col-lg-6">
                            @include('verifiers.select_verifiers', array(
                                'verifiers' => $verifiers,
                                'selectedVerifiers' => $selectedVerifiers,
                            ))
                            <label class="input {{{ ($errors->has('verifiers') or $errors->has('document_invalid')) ? 'state-error' : null }}}"></label>
                            {{ $errors->first('verifiers', '<em class="invalid">:message</em>') }}
                            {{ $errors->first('document_invalid', '<em class="invalid">:message</em>') }}
                        </section>
                    </div>
                    <footer>
                        {{ Form::hidden('id', $openRfp->id) }}
                        {{ link_to_route('consultant.management.open.rfp.show', trans('forms.back'), [$vendorCategoryRfp->id, $openRfp->id], ['class' => 'btn btn-default']) }}
                        {{ Form::button('<i class="fa fa-upload"></i> '.trans('forms.submit'), ['type' => 'submit', 'name'=>'send_to_verify', 'class' => 'btn btn-success'] )  }}
                        {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
                    </footer>
                    {{ Form::close() }}
                    @else

                        @if($openRfp->needApprovalFromUser($user))
                            {{ Form::open(['route' => ['consultant.management.open.rfp.verify', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                        <label class="label">{{{ trans('vendorManagement.remarks') }}}:</label>
                                        <label class="textarea {{{ $errors->has('remarks') ? 'state-error' : null }}}">
                                            {{ Form::textarea('remarks', Input::old('remarks'), ['rows' => 3]) }}
                                        </label>
                                        {{ $errors->first('remarks', '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                            <footer>
                                {{ Form::hidden('id', $openRfp->id) }}
                                {{ link_to_route('consultant.management.open.rfp.show', trans('forms.back'), [$vendorCategoryRfp->id, $openRfp->id], ['class' => 'btn btn-default']) }}
                                {{ Form::button('<i class="fa fa-times-circle"></i> '.trans('forms.reject'), ['type' => 'submit', 'name'=>'reject', 'class' => 'btn btn-danger'] )  }}
                                {{ Form::button('<i class="fa fa-check-circle"></i> '.trans('forms.approve'), ['type' => 'submit', 'name'=>'approve', 'class' => 'btn btn-success'] )  }}
                            </footer>
                            {{ Form::close() }}
                        @else
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <div class="pull-right">
                                    {{ link_to_route('consultant.management.open.rfp.show', trans('forms.back'), [$vendorCategoryRfp->id, $openRfp->id], ['class' => 'btn btn-default']) }}
                                </div>
                            </section>
                        </div>
                        @endif

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script type="text/javascript">
$(document).ready(function () {
    var verifierLogTable = new Tabulator('#verifier_logs-table', {
        height:280,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('consultant.management.rfp.resubmission.verifier.ajax.log', [$vendorCategoryRfp->id, $openRfp->id]) }}",
        ajaxConfig: "GET",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        layout:"fitColumns",
        columns:[
            {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false},
            {title:"{{ trans('documentManagementFolders.revision') }}", field:"version", width:80, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.status') }}", field:"status_txt", width:120, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
            {title:"{{ trans('general.remarks') }}", field:"remarks", width:380, hozAlign:'left', headerSort:false, formatter:'textarea'},
            {title:"{{ trans('general.updatedAt') }}", field:"updated_at", width:160, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false}
        ]
    });
});
</script>
@endsection