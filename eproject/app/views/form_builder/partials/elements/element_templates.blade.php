<?php use PCK\FormBuilder\Elements\Element; ?>
<?php use PCK\FormBuilder\Elements\DateTimePicker; ?>
<?php use PCK\FormBuilder\Elements\SystemModuleElement; ?>
<?php use PCK\FormBuilder\ElementDefinition; ?>
<?php use PCK\FormBuilder\Elements\FormBuilderElementCommon; ?>
<?php $showRejectionButton = isset($showRejectionButton) ? $showRejectionButton : false; ?>
<?php $showElementFileUploadButton = isset($showElementFileUploadButton) ? $showElementFileUploadButton : false; ?>
<?php $showSectionSaveButton = isset($showSectionSaveButton) ? $showSectionSaveButton : false ?>
<?php $designMode = isset($designMode) ? $designMode : false; ?>
<?php $widthClass = $designMode ? 'col-xs-10' : 'col-xs-11'; ?>
<?php $rightWidthClass = $designMode ? 'col-xs-2' : 'col-xs-1'; ?>

<!-- wizard header -->
<!-- example -->
<!-- <li>
    <a class="nav-link" href="#column-123" data-id="column-123">column name</a>
</li> -->
<li id="wizard_header" style="display:none;" class="section_drag_handle"><a class="nav-link" href=""></a>
</li>

<!-- wizard content -->
<!-- example -->
<!-- <div id="column-123" class="tab-pane" role="tabpanel"></div> -->
<div id="wizard_content" class="tab-pane" role="tabpanel" style="display:none;"></div>

<div data-component="edit_controls_div" id="edit_controls_div" style="display:none;" class="pull-right">
    <button class="btn btn-xs btn-warning" data-action="edit_resource"><i class="fas fa-edit"></i></button>
    <button class="btn btn-xs btn-danger" data-component="delete_button"><i class="fas fa-trash"></i></button>
</div>

<!-- tab components for inner tabs-->
<div id="column_container" class="tab-pane" style="display:none;">
    @if($designMode)
    <div class="row" style="margin-bottom:5px;">
        <div class="col col-xs-12">
            <button class="btn btn-primary pull-right" data-action="create_resource" data-target="#editorModal" data-toggle="modal" data-title="{{ trans('formBuilder.addSubSection') }}"><i class="fa fa-plus"></i> {{ trans('formBuilder.addSubSection') }}</button>
        </div>
    </div>
    @endif
    <ul class="nav nav-tabs bordered" data-component="subsection_tab_header_container"></ul>
    <div class="tab-content padding-10" data-component="subsection_tab_content_div"></div>
</div>
<!-- inner tab headers -->
<li id="subsection_tab_header_content" class="sub_section_drag_handle" style="display:none;">
    <a href="" data-toggle="tab"></a>
</li>
<!-- inner tab contents -->
<div class="tab-pane fade" id="subsection_tab_content" style="display:none;">
    @if($designMode)
    <div class="row">
        <div class="col-xs-12">
            <div class="pull-right">
                <button class="btn btn-primary" data-action="add_element"><i class="fa fa-plus"></i> {{ trans('formBuilder.addElement') }}</button>
            </div>
		</div>
	</div>
    @endif
    <div class="row" data-component="filler_node">
        <div class="col-xs-12">
            <div style="height:100px;"></div>
		</div>
	</div>
    <form action="" method="POST" data-component="section-form" class="smart-form">
        <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
        <div data-component="element_container"></div>
        @if($showSectionSaveButton)
        <footer>
            <button type="submit" class="btn btn-primary pull-right"><i class="fas fa-save"></i> {{ trans('general.save') }}</button>
        </footer>
        @endif
    </form>
</div>

<!-- required template -->
<span class="required hidden" id="required_template"> *</span>

<!-- input type=text template -->
<div id="input_template" data-component="template_root" class="row hidden">
    <section class="col {{ $widthClass }}">
        <label class="label element_drag_handle">
            <span data-component="label"></span>
        </label>
        <div data-component="instructions" class="label padded label-success text-white" style="display:none;"></div>
        <label class="input">
            <input data-component="element">
        </label>
        <em class="invalid"></em>
    </section>
    @if($designMode || $showRejectionButton || $showElementFileUploadButton)
    <section class="col {{ $rightWidthClass }}" data-component="element_buttons_panel">
        <label class="label">&nbsp;</label>
        @if($designMode)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="delete_element" title="{{ trans('formBuilder.deleteElement') }}"><i class="fas fa-trash"></i></button>
        <button type="button" class="btn btn-warning btn-xs pull-right" data-action="edit_element" style="margin-right:5px;" title="{{ trans('formBuilder.editElement') }}"><i class="fas fa-edit"></i></button>
        @endif
        @if($showRejectionButton)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="reject_element" style="margin-right:5px;" title="{{ trans('formBuilder.rejectElement') }}"><i class="fas fa-exclamation-triangle"></i></button>
        @endif
        @if($showElementFileUploadButton)
        <button type="button" class="btn btn-info btn-xs pull-right" data-component="upload-attachment-button" data-action="view-attachments" style="margin-right:5px;"><i class="fas fa-paperclip"></i>&nbsp;(<span data-component="attachment_upload_count">0</span>)</button>
        @endif
    </section>
    @endif
</div>

<!-- textarea template -->
<div id="textarea_template" data-component="template_root" class="row hidden">
    <section class="col {{ $widthClass }}">
        <label class="label element_drag_handle">
            <span data-component="label"></span>
        </label>
        <div data-component="instructions" class="label padded label-success text-white" style="display:none;"></div>
        <label class="textarea">
            <textarea rows="10" data-component="element" style="resize:none;height:200px;overflow-y:scroll;"></textarea>
        </label>
        <em class="invalid"></em>
    </section>
    @if($designMode || $showRejectionButton || $showElementFileUploadButton)
    <section class="col {{ $rightWidthClass }}" data-component="element_buttons_panel">
        <label class="label">&nbsp;</label>
        @if($designMode)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="delete_element" title="{{ trans('formBuilder.deleteElement') }}"><i class="fas fa-trash"></i></button>
        <button type="button" class="btn btn-warning btn-xs pull-right" data-action="edit_element" style="margin-right:5px;" title="{{ trans('formBuilder.editElement') }}"><i class="fas fa-edit"></i></button>
        @endif
        @if($showRejectionButton)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="reject_element" style="margin-right:5px;" title="{{ trans('formBuilder.rejectElement') }}"><i class="fas fa-exclamation-triangle"></i></button>
        @endif
        @if($showElementFileUploadButton)
        <button type="button" class="btn btn-info btn-xs pull-right" data-component="upload-attachment-button" data-action="view-attachments" style="margin-right:5px;"><i class="fas fa-paperclip"></i>&nbsp;(<span data-component="attachment_upload_count">0</span>)</button>
        @endif
    </section>
    @endif
</div>

<!-- radiobox template -->
<div id="radiobox_template" data-component="template_root" class="row hidden">
    <section class="col {{ $widthClass }}">
        <label class="label element_drag_handle">
            <span data-component="label"></span>
        </label>
        <em class="invalid"></em>
        <div data-component="instructions" class="label padded label-success text-white" style="display:none;"></div>
        <div class="row">
            <div class="col" data-component="radio_item_container"></div>
        </div>
    </section>
    @if($designMode || $showRejectionButton || $showElementFileUploadButton)
    <section class="col {{ $rightWidthClass }}" data-component="element_buttons_panel">
        <label class="label">&nbsp;</label>
        @if($designMode)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="delete_element" title="{{ trans('formBuilder.deleteElement') }}"><i class="fas fa-trash"></i></button>
        <button type="button" class="btn btn-warning btn-xs pull-right" data-action="edit_element" style="margin-right:5px;" title="{{ trans('formBuilder.editElement') }}"><i class="fas fa-edit"></i></button>
        @endif
        @if($showRejectionButton)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="reject_element" style="margin-right:5px;" title="{{ trans('formBuilder.rejectElement') }}"><i class="fas fa-exclamation-triangle"></i></button>
        @endif
        @if($showElementFileUploadButton)
        <button type="button" class="btn btn-info btn-xs pull-right" data-component="upload-attachment-button" data-action="view-attachments" style="margin-right:5px;"><i class="fas fa-paperclip"></i>&nbsp;(<span data-component="attachment_upload_count">0</span>)</button>
        @endif
    </section>
    </section>
    @endif
</div>

<!-- radio input container template -->
<label id="radio_input_container" class="radio" data-component="radio_input_container" style="display:none;">
    <input type="radio" data-component="element"><i></i><span data-component="item_label"></span>
</label>

<!-- free text container -->
<label id="free_text_container" class="input" style="display:none;">
    <input type="text" data-component="free_text">
</label>

<!-- checkbox template -->
<div id="checkbox_template" data-component="template_root" class="row hidden">
    <section class="col {{ $widthClass }}">
        <label class="label element_drag_handle">
            <span data-component="label"></span>
        </label>
        <em class="invalid"></em>
        <div data-component="instructions" class="label padded label-success text-white" style="display:none;"></div>
        <div class="row">
            <div class="col" data-component="checkbox_item_container"></div>
        </div>
    </section>
    @if($designMode || $showRejectionButton || $showElementFileUploadButton)
    <section class="col {{ $rightWidthClass }}" data-component="element_buttons_panel">
        <label class="label">&nbsp;</label>
        @if($designMode)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="delete_element" title="{{ trans('formBuilder.deleteElement') }}"><i class="fas fa-trash"></i></button>
        <button type="button" class="btn btn-warning btn-xs pull-right" data-action="edit_element" style="margin-right:5px;" title="{{ trans('formBuilder.editElement') }}"><i class="fas fa-edit"></i></button>
        @endif
        @if($showRejectionButton)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="reject_element" style="margin-right:5px;" title="{{ trans('formBuilder.rejectElement') }}"><i class="fas fa-exclamation-triangle"></i></button>
        @endif
        @if($showElementFileUploadButton)
        <button type="button" class="btn btn-info btn-xs pull-right" data-component="upload-attachment-button" data-action="view-attachments" style="margin-right:5px;"><i class="fas fa-paperclip"></i>&nbsp;(<span data-component="attachment_upload_count">0</span>)</button>
        @endif
    </section>
    @endif
</div>

<!-- checkbox input container template -->
<label id="checkbox_input_container" class="checkbox" data-component="checkbox_input_container" style="display:none;">
    <input type="checkbox" data-component="element"><i></i><span data-component="item_label"></span>
</label>

<!-- dropdown template -->
<div id="dropdown_template" data-component="template_root" class="row hidden">
    <section class="col {{ $widthClass }}">
        <label class="label element_drag_handle">
            <span data-component="label"></span>
        </label>
        <em class="invalid"></em>
        <div data-component="instructions" class="label padded label-success text-white" style="display:none;"></div>
        <label class="fill-horizontal">
            <select class="form-control" data-component="option_container" style="width:100%;">
                <option value="" data-component="default_option">{{ trans('formBuilder.selectAnOption') }}</option>
            </select>
        </label>
    </section>
    @if($designMode || $showRejectionButton || $showElementFileUploadButton)
    <section class="col {{ $rightWidthClass }}" data-component="element_buttons_panel">
        <label class="label">&nbsp;</label>
        @if($designMode)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="delete_element" title="{{ trans('formBuilder.deleteElement') }}"><i class="fas fa-trash"></i></button>
        <button type="button" class="btn btn-warning btn-xs pull-right" data-action="edit_element" style="margin-right:5px;" title="{{ trans('formBuilder.editElement') }}"><i class="fas fa-edit"></i></button>
        @endif
        @if($showRejectionButton)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="reject_element" style="margin-right:5px;" title="{{ trans('formBuilder.rejectElement') }}"><i class="fas fa-exclamation-triangle"></i></button>
        @endif
        @if($showElementFileUploadButton)
        <button type="button" class="btn btn-info btn-xs pull-right" data-component="upload-attachment-button" data-action="view-attachments" style="margin-right:5px;"><i class="fas fa-paperclip"></i>&nbsp;(<span data-component="attachment_upload_count">0</span>)</button>
        @endif
    </section>
    @endif
</div>

<!-- dropdown option container template -->
<option id="dropdown_option_template" data-component="dropdown_option" value="" class="hidden"></option>

<!-- upload file template -->
<div id="upload_file_template" data-component="template_root" class="row hidden">
    <section class="col {{ $widthClass }}">
        <label class="label element_drag_handle">
            <span data-component="label"></span>
        </label>
        <em class="invalid"></em>
        <div data-component="instructions" class="label padded label-success text-white" style="display:none;"></div>
        <div data-component="file_upload_div">
            <div class="input">
                <button type="button" class="btn btn-info" data-component="upload-attachment-button" data-action="view-attachments"><i class="fas fa-paperclip"></i>&nbsp;<span data-component="attachment_upload_count">0</span> {{ trans('formBuilder.filesUploaded') }}</button>
            </div>
        </div>
        <label class="label">&nbsp;</label>
        <table class="table table-bordered" style="display:none;">
            <thead>
                <tr>
                    <th>{{ trans('formBuilder.files') }}</th>
                    <th style="width:40px;">&nbsp;</th>
                </tr>
            </thead>
            <tbody data-component="uploaded_files_container">
                <tr>
                    <td>Row 1</td>
                    <td>
                        <button class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>
    @if($designMode || $showRejectionButton)
    <section class="col {{ $rightWidthClass }}" data-component="element_buttons_panel">
        <label class="label">&nbsp;</label>
        @if($designMode)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="delete_element" title="{{ trans('formBuilder.deleteElement') }}"><i class="fas fa-trash"></i></button>
        <button type="button" class="btn btn-warning btn-xs pull-right" data-action="edit_element" style="margin-right:5px;" title="{{ trans('formBuilder.editElement') }}"><i class="fas fa-edit"></i></button>
        @endif
        @if($showRejectionButton)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="reject_element" style="margin-right:5px;" title="{{ trans('formBuilder.rejectElement') }}"><i class="fas fa-exclamation-triangle"></i></button>
        @endif
    </section>
    @endif
</div>

<!-- date time picker template -->
<div id="date_time_picker_template" data-component="template_root" class="row hidden">
    <section class="col {{ $widthClass }}">
        <label class="label element_drag_handle">
            <span data-component="label"></span>
        </label>
        <em class="invalid"></em>
        <div data-component="instructions" class="label padded label-success text-white" style="display:none;"></div>
        <label class="input">
            <input type="" data-component="date_time_picker">
        </label>
    </section>
    @if($designMode || $showRejectionButton || $showElementFileUploadButton)
    <section class="col {{ $rightWidthClass }}" data-component="element_buttons_panel">
        <label class="label">&nbsp;</label>
        @if($designMode)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="delete_element" title="{{ trans('formBuilder.deleteElement') }}"><i class="fas fa-trash"></i></button>
        <button type="button" class="btn btn-warning btn-xs pull-right" data-action="edit_element" style="margin-right:5px;" title="{{ trans('formBuilder.editElement') }}"><i class="fas fa-edit"></i></button>
        @endif
        @if($showRejectionButton)
        <button type="button" class="btn btn-danger btn-xs pull-right" data-action="reject_element" style="margin-right:5px;" title="{{ trans('formBuilder.rejectElement') }}"><i class="fas fa-exclamation-triangle"></i></button>
        @endif
        @if($showElementFileUploadButton)
        <button type="button" class="btn btn-info btn-xs pull-right" data-component="upload-attachment-button" data-action="view-attachments" style="margin-right:5px;"><i class="fas fa-paperclip"></i>&nbsp;(<span data-component="attachment_upload_count">0</span>)</button>
        @endif
    </section>
    @endif
</div>