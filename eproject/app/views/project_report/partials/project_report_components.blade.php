<?php
    $canEditReport = isset($canEditReport) ? $canEditReport : false;
    $canEditTemplate = isset($canEditTemplate) ? $canEditTemplate : false;
    $width   = $canEditTemplate ? 10 : 12;
    $readonly = $canEditReport ? '' : 'readonly';

?>

<div class="hidden handle" id="column-template">
    <div class="row">
        <div class="col col-xs-12">
            <div class="col-xs-{{ $width }}" style="margin-bottom: 0;">
                <label class="label" data-component="column-title"></label>
            </div>
            @if($canEditTemplate)
            <div class="col-xs-2 buttons-row" data-component="parent_buttons_container">
                <button type="button" class="btn btn-warning btn-xs edit-button pull-right" data-action="edit_column" title="{{ trans('projectReport.editColumn') }}"><i class="fas fa-edit"></i></button>
                <button type="button" class="btn btn-danger btn-xs delete-button pull-right" data-action="delete_column" title="{{ trans('projectReport.deleteColumn') }}"><i class="fas fa-trash"></i></button>
            </div>
            @endif
        </div>
        <section class="col col-xs-12" data-component="content_container"></section>
    </div>
</div>

<div class="column-group hidden" id="column-group-container-template"></div>

<div class="column-group empty-block hidden" id="empty_column_group_template">
    <span>No fields at the moment.</span>
</div>

<label class="textarea hidden" id="textarea-template">
    <textarea rows="2" data-component="column-content" {{ $readonly }}></textarea>
</label>

<label class="input hidden" id="number-input-template">
    <input type="number" data-component="column-content" step="0.01" {{ $readonly }}>
</label>

<label class="input hidden" id="date-input-template">
    <input type="date" data-component="column-content" {{ $readonly }}>
</label>

<label class="input hidden" id="select-opt-template">
    @if(! empty($projectProgressOptions))
        <select class="form-control" data-component="column-content">
            @foreach ($projectProgressOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    @else
        <input type="text" data-component="column-content" {{ $readonly }}>
    @endif
</label>

<button type="button" class="btn btn-primary btn-xs add-button pull-right hidden" data-action="add_sub_column" title="{{ trans('projectReport.addSubColumn') }}" id="add_sub_column_template"><i class="fas fa-plus"></i></button>