<div class="row">
    <div class="col col-lg-12">
        <h4>Section B - {{{ trans('projects.projectDescription')}}}</h4>
    </div>
</div>
<hr class="simple">
@if($approvalDocument->status == PCK\ConsultantManagement\ApprovalDocument::STATUS_DRAFT)
{{ Form::open(['route' => ['consultant.management.approval.document.section.b.store', $vendorCategoryRfp->id], 'class' => 'smart-form']) }}
<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">Project Brief :</label>
        <label class="textarea {{{ $errors->has('project_brief') ? 'state-error' : null }}}">
            {{ Form::textarea('project_brief', Input::old('project_brief', $approvalDocument->sectionB->project_brief), ['rows' => '1', 'autofocus' => 'autofocus']) }}
        </label>
        {{ $errors->first('project_brief', '<em class="invalid">:message</em>') }}
    </section>
</div>
<footer>
    {{ Form::hidden('id', $approvalDocument->id) }}
    {{ Form::hidden('open_rfp_id', $openRfp->id) }}
    {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary'] )  }}
</footer>
{{ Form::close() }}
@else
<div class="row">
    <div class="col col-lg-12">
        <dl class="dl-horizontal no-margin">
            <dt>Project Brief:</dt>
            <dd>
                <div class="well">
                    {{ nl2br($approvalDocument->sectionB->project_brief) }}
                </div>
            </dd>
            <dt>&nbsp;</dt>
            <dd>&nbsp;</dd>
        </dl>
    </div>
</div>
@endif