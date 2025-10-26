<div class="row">
    <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h4>Appendix</h4>
    </div>
</div>
<hr class="simple">
@if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
{{ Form::open(['route' => ['consultant.management.approval.document.section.appendix.store', $vendorCategoryRfp->id], 'id'=>'appendix_details-form', 'class' => 'smart-form']) }}
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">{{{ trans('general.title') }}} <span class="required">*</span>:</label>
        <label class="input {{{ $errors->has('title') ? 'state-error' : null }}}">
            {{ Form::text('title', Input::old('title'), ['id'=>'appendix_details-title', 'required' => 'required', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $errors->first('title', '<em class="invalid">:message</em>') }}
    </section>
</div>
<footer>
    {{ Form::hidden('open_rfp_id', $openRfp->id) }}
    {{ Form::hidden('id', -1, ['id'=>'appendix_details-id']) }}
    {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
</footer>
{{ Form::close() }}
<hr class="simple">
@endif
<div class="row">
    <div class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div id="appendix-table"></div>
    </div>
</div>