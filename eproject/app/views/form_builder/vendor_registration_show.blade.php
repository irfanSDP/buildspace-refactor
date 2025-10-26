@extends('layout.main')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/css/smart_wizard_all.min.css" rel="stylesheet" type="text/css" />
    <style>
        .custom-footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            background-color: #ECECEC;
            color: white;
            text-align: center;
            padding-top:5px;
            padding-bottom:5px;
            padding-right:5px;
            border-top: 1px solid #C6C6C6;
            z-index: 999;
        }

        .spaced {
            margin-right: 5px;
        }
    </style>
@endsection

@section('breadcrumb')
@if($vendorCanSubmitForm)
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{ link_to_route('vendors.vendorRegistration.index', trans('vendorManagement.overview'), array()) }}</li>
        <li>{{{ trans('vendorManagement.registrationForm') }}}</li>
    </ol>
    @endif
    @if($canApproveVendorSubmission)
    <ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification', trans('vendorManagement.registrationAndPreQualification'), array()) }}</li>
        <li>{{ link_to_route('vendorManagement.approval.registrationAndPreQualification.show', $vendorRegistration->company->name, array($vendorRegistration->id)) }}</li>
        <li>{{ trans('vendorManagement.vendorRegistration') }}</li>
	</ol>
    @endif
@endsection
<?php use PCK\FormBuilder\Elements\Element; ?>
<?php use PCK\FormBuilder\Elements\DateTimePicker; ?>
<?php use PCK\FormBuilder\ElementDefinition; ?>
<?php use PCK\ObjectField\ObjectField; ?>
<?php $processerCanEditForm = isset($processerCanEditForm) ? $processerCanEditForm : false; ?>
<?php $canProcess = isset($canProcess) ? $canProcess : false; ?>
@section('content')
<div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2><i class="fa fa-list"></i> {{ trans('vendorManagement.vendorRegistration') }}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div class="row">
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <div id="smartwizard">
                                    <ul class="nav" id="wizard_header_container"></ul>
                                    <div class="tab-content" id="wizard_content_container" style="overflow: unset;"></div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="custom-footer">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            @if($canProcess)
            <button type="button" class="btn btn-info pull-left spaced" data-action="upload-item-attachments" 
                    data-route_attachment_list="{{ route('vendorManagement.approval.processor.attachments.list', [$vendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_REGISTRATION_FORM]) }}"
                    data-route_upload_attachments="{{ route('vendorManagement.approval.processor.attachments.upload', [$vendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_REGISTRATION_FORM]) }}"
                    data-route_attachment_count="{{ route('vendorManagement.approval.processor.attachments.count', [$vendorRegistration->id, ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_REGISTRATION_FORM]) }}"
                    data-name="{{ ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_REGISTRATION_FORM }}">
                <?php 
                    $record = ObjectField::findRecord($vendorRegistration, ObjectField::PROCESSOR_ATTACHMENTS_VENDOR_REGISTRATION_FORM);
                    $attachmentCount = is_null($record) ? 0 : $record->attachments->count();
                ?>
                <i class="fas fa-paperclip fa-lg"></i>&nbsp;{{ trans('vendorManagement.processorsAttachments') }}&nbsp;(<span data-component="attachment_upload_count">{{ $attachmentCount }}</span>)
            </button>
            @if( ! $isVendor )
            <button type="button" id="viewActionLogsButton" class="btn btn-primary pull-left">{{ trans('general.editLogs') }}</button>
            @endif
            @endif
            @if(!is_null($nextRoute))
            <a href="{{ $nextRoute }}" class="btn btn-info pull-right">{{ trans('forms.next') }}</a>
            @if($canApproveVendorSubmission && $hasRejectedElements)
            <form action="{{ route('vendor.form.submission.reject', [$vendorRegistration->id]) }}" method="POST">
                <input type="hidden" name="_token" value="{{{  csrf_token() }}}">
                <button type="submit" id="btnRejectVendorSubmission" class="btn btn-danger pull-right spaced" data-intercept="confirmation" data-confirmation-message="{{{ trans('general.stillWantToProceed') }}}" data-confirmation-title="{{{ trans('general.warning') }}}"><i class="fa fa-check"></i> {{trans('forms.reject')}}</button>
            </form>
            @endif
            @endif 
            @if(!is_null($backRoute))
            <a href="{{ $backRoute }}" class="btn btn-default pull-right spaced">{{ trans('forms.back') }}</a>
            @endif
        </div>
    </div>

    <div data-type="template" hidden>
        <table>
            @include('file_uploads.partials.uploaded_file_row_template')
        </table>
    </div>

    <div class="modal fade" id="uploadAttachmentModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">{{ trans('forms.attachments') }}</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    {{ Form::open(array('id' => 'attachment-upload-form', 'class' => 'smart-form', 'method' => 'post', 'files' => true)) }}
                        <section>
                            <label class="label">{{{ trans('forms.upload') }}}:</label>
                            {{ $errors->first('uploaded_files', '<em class="invalid">:message</em>') }}

                            @include('file_uploads.partials.upload_file_modal', array('id' => 'invoice-upload'))
                        </section>
                    {{ Form::close() }}
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" data-action="submit-attachments"><i class="fa fa-upload"></i> {{ trans('forms.submit') }}</button>
                </div>
            </div>
        </div>
    </div>

    @include('form_builder.partials.elements.element_templates', [
        'showRejectionButton'         => true,
        'showElementFileUploadButton' => true,
        'showSectionSaveButton'       => true,
    ])

    @include('form_builder.partials.element_rejection_modal', [
        'modalId'          => 'formRejectionModal',
        'canEditRejection' => $canApproveVendorSubmission,
    ])

    @include('templates.generic_table_modal', [
        'modalId'    => 'attachmentsModal',
        'title'      => trans('general.attachments'),
        'tableId'    => 'attachmentsTable',
        'showCancel' => true,
        'cancelText' => trans('forms.close'),
    ])

    @if( ! $isVendor )
    @include('templates.generic_table_modal', [
        'modalId'    => 'actionLogsModal',
        'title'      => trans('general.editLogs'),
        'tableId'    => 'actionLogsTable',
        'showCancel' => true,
        'cancelText' => trans('forms.close'),
    ])
    @endif
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/js/jquery.smartWizard.min.js" type="text/javascript"></script>
<script>
    $(document).ready(function() {
        var wizardHeaderContainer      = document.getElementById('wizard_header_container');
        var wizardContentContainer     = document.getElementById('wizard_content_container');

        var columnContainer            = document.getElementById('column_container');
        var subSectionTabHeaderContent = document.getElementById('subsection_tab_header_content');
        var subSectionTabContent       = document.getElementById('subsection_tab_content');

        var wizardHeaderTemplate =  document.getElementById('wizard_header');
        var wizardContentTemplate = document.getElementById('wizard_content');

        var requiredTemplate       = document.getElementById('required_template');
        var inputTemplate          = document.getElementById('input_template');
        var textareaTemplate       = document.getElementById('textarea_template');
        var radioboxTemplate       = document.getElementById('radiobox_template');
        var checkboxTemplate       = document.getElementById('checkbox_template');
        var dropdownTemplate       = document.getElementById('dropdown_template');
        var uploadFileTemplate     = document.getElementById('upload_file_template');
        var dateTimePickerTemplate = document.getElementById('date_time_picker_template');

        var rejectedColor = '#FDD6D6';
        var amendedColor  = '#FED797';

        fetchAndRenderFormContents();

        function fetchAndRenderFormContents() {
            app_progressBar.reset();
            app_progressBar.toggle();

            $.ajax({
                url: "{{ route('vendor.form.contents.get', [$form->id]) }}",
                method: 'GET',
                success: function(responseData) {
                    responseData.forEach(function(column, index) {
                        var wizardHeaderNode = wizardHeaderTemplate.cloneNode(true);
                        wizardHeaderNode.removeAttribute('id');
                        wizardHeaderNode.removeAttribute('style');
                        wizardHeaderNode.querySelector('a.nav-link').dataset.id = column.id;
                        wizardHeaderNode.querySelector('a.nav-link').href = `#column-${column.id}`;
                        wizardHeaderNode.querySelector('a.nav-link').innerText = column.name;

                        wizardHeaderContainer.appendChild(wizardHeaderNode);

                        var wizardContentNode = wizardContentTemplate.cloneNode(true);
                        wizardContentNode.removeAttribute('id');
                        wizardContentNode.removeAttribute('style');
                        wizardContentNode.id = `column-${column.id}`;

                        wizardContentContainer.appendChild(wizardContentNode);

                        fetchAndRenderColumnContents(wizardContentNode, column);
                    });

                    if((responseData.length > 0)) {
                        $('#smartwizard').smartWizard({
                            selected: 0,
                            theme: 'arrows',
                            autoAdjustHeight: false,
                            anchorSettings: {
                                anchorClickable: true,
                                enableAllAnchors: true
                            },
                            toolbarSettings: {
                                showNextButton: false,
                                showPreviousButton: false,
                            },
                            keyboardSettings: {
                                keyNavigation: false
                            }
                        });
                    }

                    initCustomComponents();

                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                },
                error  : function(jqXHR, textStatus, errorThrown) {
                    console.log('ERROR');
                    $('#smartwizard').smartWizard("loader", "hide");
                    app_progressBar.maxOut();
                    app_progressBar.toggle();
                }
            });
        }

        function fetchAndRenderColumnContents(wizardContentNode, column) {
            var columnNode = columnContainer.cloneNode(true);
            columnNode.id = column.id;
            columnNode.removeAttribute('style');
            columnNode.querySelector('[data-component="subsection_tab_header_container"]').removeAttribute('style');

            column.contents.forEach(function(data, index) {
                var tabHeader = subSectionTabHeaderContent.cloneNode(true);
                tabHeader.removeAttribute('id');
                tabHeader.removeAttribute('style');
                tabHeader.querySelector('a[data-toggle="tab"]').dataset.section_id = data.id;

                if(index == 0) {
                    tabHeader.classList.add('active');
                }

                tabHeader.querySelector('[data-toggle="tab"]').href = '#section-' + data.id;
                tabHeader.querySelector('[data-toggle="tab"]').innerHTML = data.name;

                columnNode.querySelector('[data-component="subsection_tab_header_container"]').appendChild(tabHeader);

                tabContent = subSectionTabContent.cloneNode(true);
                tabContent.id = 'section-' + data.id;
                tabContent.removeAttribute('style');
                tabContent.querySelector('[data-component="section-form"]').dataset.section_id = data.id;

                if(index == 0) {
                    tabContent.classList.add('in');
                    tabContent.classList.add('active');
                }

                if(data.contents.length > 0) {
                    clearChildNodes(tabContent.querySelector('[data-component="filler_node"]'), true);                    
                }

                data.contents.forEach(function(content, index) {
                    content.section_id = data.id;
                    generateElement(content, tabContent.querySelector('[data-component="section-form"] [data-component="element_container"]'));
                });

                if(data.hasOwnProperty('section_submit_url') && (data.section_submit_url != null)) {
                    tabContent.querySelector('[data-component="section-form"]').action = data.section_submit_url;
                }

                columnNode.querySelector('[data-component="subsection_tab_content_div"]').appendChild(tabContent);
            });

            wizardContentNode.appendChild(columnNode);
        }

        function generateElement(record, elementContainer) {
            if(record.element_type == "{{ Element::ELEMENT_TYPE_ID }}") {
                switch(record.class_identifier.toString()) {
                    case "{{ Element::TYPE_TEXT }}":
                    case "{{ Element::TYPE_EMAIL }}":
                    case "{{ Element::TYPE_URL }}":
                    case "{{ Element::TYPE_NUMBER }}":
                        generateTextboxElement(record, elementContainer);
                    break;
                    case "{{ Element::TYPE_MULTILINE_TEXT }}":
                        generateTextAreaElement(record, elementContainer);
                    break;
                    case "{{ Element::TYPE_RADIO }}":
                        generateRadioboxElement(record, elementContainer);
                    break;
                    case "{{ Element::TYPE_CHECKBOX }}":
                        generateCheckboxElement(record, elementContainer);
                    break;
                    case "{{ Element::TYPE_DROPDOWN }}":
                        generateDropdownElement(record, elementContainer);
                    break;
                    case "{{ Element::TYPE_FILE_UPLOAD }}":
                        generateFileUploadElement(record, elementContainer);
                    break;
                    case "{{ Element::TYPE_DATE_TIME }}":
                        generateDateTimePickerElement(record, elementContainer);
                    break;
                }
            } else {
                switch(record.element_render_identifier.toString()) {
                    case "{{ ElementDefinition::TYPE_RADIOBOX }}":
                        generateRadioboxElement(record, elementContainer);
                    break;
                    case "{{ ElementDefinition::TYPE_CHECKBOX }}":
                        generateCheckboxElement(record, elementContainer);
                    break;
                    case "{{ ElementDefinition::TYPE_DROPDOWN }}":
                        generateDropdownElement(record, elementContainer);
                    break;
                }
            }
        }

        function generateTextboxElement(record, elementContainer) {
            var node = inputTemplate.cloneNode(true);
            node.removeAttribute('id');
            node.classList.remove('hidden');
            node.dataset.id = record.mapping_id;
            node.dataset.class_identifier = record.class_identifier;
            node.dataset.get_element_url = record.route_getElement;
            node.dataset.name = record.attributes['name'];
            node.dataset.is_rejected = record.is_rejected ? 1 : 0;

            if(record.is_rejected) {
                node.style['background-color'] = rejectedColor;
            } else {
                @if($isVendor)
                node.querySelector('button[data-action="reject_element"]').style['display'] = 'none';
                @endif
            }

            if(record.is_amended) {
                node.style['background-color'] = amendedColor;
            }

            var labelNode = node.querySelector('[data-component="label"]');1
            labelNode.innerHTML = record.label;

            if(record.displayInstructions.trim() != '') {
                node.querySelector('[data-component="instructions"]').innerHTML = record.displayInstructions;
                node.querySelector('[data-component="instructions"]').style.removeProperty('display');
            }

            if(record.attributes.hasOwnProperty('required')) {
                var requiredNode = requiredTemplate.cloneNode(true);
                requiredNode.classList.remove('hidden');

                labelNode.parentElement.appendChild(requiredNode);
            }

            node.querySelector('[data-action="reject_element"]').dataset.route_get_rejection    = record.route_get_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_save_rejection   = record.route_save_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_delete_rejection = record.route_delete_rejection;

            for(const attr in record.attributes) {
                var value = (attr == 'required') ? true : record.attributes[attr];

                @if($processerCanEditForm)
                if(attr == 'disabled') {
                    continue;
                }
                @endif

                node.querySelector('[data-component="element"]').setAttribute(attr, value);
            }

            if(record.hasOwnProperty('has_attachments') && record.has_attachments) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_count = record.route_attachment_count;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_list = record.route_attachment_list;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_upload_attachments = record.route_upload_attachments;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.name = record.attributes.name;

                node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;
            } else {
                node.querySelector('[data-component="upload-attachment-button"]').style.display = 'none';
            }

            @if($vendorCanSubmitForm)
            if(!record.attributes.hasOwnProperty('disabled')) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            }
            @endif

            @if($processerCanEditForm)
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            @endif

            elementContainer.appendChild(node);
        }

        function generateTextAreaElement(record, elementContainer) {
            var node = textareaTemplate.cloneNode(true);
            node.removeAttribute('id');
            node.classList.remove('hidden');
            node.dataset.id = record.mapping_id;
            node.dataset.class_identifier = record.class_identifier;
            node.dataset.get_element_url = record.route_getElement;
            node.dataset.name = record.attributes['name'];
            node.dataset.is_rejected = record.is_rejected ? 1 : 0;

            if(record.is_rejected) {
                node.style['background-color'] = rejectedColor;
            } else {
                @if($isVendor)
                node.querySelector('button[data-action="reject_element"]').style['display'] = 'none';
                @endif
            }

            if(record.is_amended) {
                node.style['background-color'] = amendedColor;
            }

            var labelNode = node.querySelector('[data-component="label"]');
            labelNode.innerHTML = record.label;

            if(record.displayInstructions.trim() != '') {
                node.querySelector('[data-component="instructions"]').innerHTML = record.displayInstructions;
                node.querySelector('[data-component="instructions"]').style.removeProperty('display');
            }

            if(record.attributes.hasOwnProperty('required')) {
                var requiredNode = requiredTemplate.cloneNode(true);
                requiredNode.classList.remove('hidden');

                labelNode.parentElement.appendChild(requiredNode);
            }

            node.querySelector('[data-action="reject_element"]').dataset.route_get_rejection    = record.route_get_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_save_rejection   = record.route_save_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_delete_rejection = record.route_delete_rejection;

            node.querySelector('[data-component="element"]').removeAttribute('style');

            for(const attr in record.attributes) {
                var value = (attr == 'required') ? true : record.attributes[attr];

                @if($processerCanEditForm)
                if(attr == 'disabled') {
                    continue;
                }
                @endif

                node.querySelector('[data-component="element"]').setAttribute(attr, value);
            }

            if(record.hasOwnProperty('has_attachments') && record.has_attachments) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_count = record.route_attachment_count;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_list = record.route_attachment_list;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_upload_attachments = record.route_upload_attachments;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.name = record.attributes.name;

                node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;
            } else {
                node.querySelector('[data-component="upload-attachment-button"]').style.display = 'none';
            }

            @if($vendorCanSubmitForm)
            if(!record.attributes.hasOwnProperty('disabled')) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            }
            @endif

            @if($processerCanEditForm)
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            @endif

            node.querySelector('[data-component="element"]').innerHTML = record.value;

            elementContainer.appendChild(node);
        }

        function generateRadioboxElement(record, elementContainer) {
            var node = radioboxTemplate.cloneNode(true);
            node.removeAttribute('id');
            node.classList.remove('hidden');
            node.dataset.id = record.mapping_id;
            node.dataset.class_identifier = record.class_identifier;
            node.dataset.get_element_url = record.route_getElement;
            node.dataset.name = record.name;
            node.dataset.is_rejected = record.is_rejected ? 1 : 0;

            if(record.is_rejected) {
                node.style['background-color'] = rejectedColor;
            } else {
                @if($isVendor)
                node.querySelector('button[data-action="reject_element"]').style['display'] = 'none';
                @endif
            }

            if(record.is_amended) {
                node.style['background-color'] = amendedColor;
            }

            if(record.element_type == "{{ Element::ELEMENT_TYPE_ID }}") {
                node.dataset.class_identifier = record.class_identifier;
            }

            node.dataset.element_type = record.element_type;
            node.dataset.get_element_url = record.route_getElement;

            var labelNode = node.querySelector('[data-component="label"]');
            labelNode.innerHTML = record.label;

            if(record.displayInstructions.trim() != '') {
                node.querySelector('[data-component="instructions"]').innerHTML = record.displayInstructions;
                node.querySelector('[data-component="instructions"]').style.removeProperty('display');
            }

            if(record.attributes.hasOwnProperty('required')) {
                var requiredNode = requiredTemplate.cloneNode(true);
                requiredNode.classList.remove('hidden');

                labelNode.parentElement.appendChild(requiredNode);
            }

            node.querySelector('[data-action="reject_element"]').dataset.route_get_rejection    = record.route_get_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_save_rejection   = record.route_save_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_delete_rejection = record.route_delete_rejection;

            if(record.hasOwnProperty('has_attachments') && record.has_attachments) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_count = record.route_attachment_count;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_list = record.route_attachment_list;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_upload_attachments = record.route_upload_attachments;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.name = record.name;

                node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;
            } else {
                node.querySelector('[data-component="upload-attachment-button"]').style.display = 'none';
            }

            @if($vendorCanSubmitForm)
            if(!record.attributes.hasOwnProperty('disabled')) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            }
            @endif

            @if($processerCanEditForm)
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            @endif

            for(index in record.children) {
                var itemNode = document.getElementById('radio_input_container').cloneNode(true);
                itemNode.removeAttribute('id');
                itemNode.removeAttribute('style');
                itemNode.querySelector('[data-component="item_label"]').innerHTML = record.children[index].label;

                var otherOptionMarker = record.children[index].isOtherOption ? 1 : 0;

                itemNode.querySelector('[data-component="element"]').dataset.other_option = otherOptionMarker;

                for(key in record.children[index].attributes) {
                    var attributes = record.children[index].attributes;

                    @if($processerCanEditForm)
                    if(key == 'disabled') {
                        continue;
                    }
                    @endif

                    itemNode.querySelector('[data-component="element"]').setAttribute(key, attributes[key]);
                }

                node.querySelector('[data-component="radio_item_container"]').appendChild(itemNode);

                if(record.children[index].isOtherOption) {
                    var freeTextNode = document.getElementById('free_text_container').cloneNode(true);
                    freeTextNode.removeAttribute('id');
                    freeTextNode.removeAttribute('style');

                    freeTextNode.querySelector('[data-component="free_text"]').setAttribute('name', record.children[index].otherOption.name);
                    freeTextNode.querySelector('[data-component="free_text"]').setAttribute('value', record.children[index].otherOption.value);

                    node.querySelector('[data-component="radio_item_container"]').appendChild(freeTextNode);
                }
            }

            elementContainer.appendChild(node);
        }

        function generateCheckboxElement(record, elementContainer) {
            var node = checkboxTemplate.cloneNode(true);
            node.removeAttribute('id');
            node.classList.remove('hidden');
            node.dataset.id = record.mapping_id;
            node.dataset.class_identifier = record.class_identifier;
            node.dataset.get_element_url = record.route_getElement;
            node.dataset.name = record.name;
            node.dataset.is_rejected = record.is_rejected ? 1 : 0;

            if(record.is_rejected) {
                node.style['background-color'] = rejectedColor;
            } else {
                @if($isVendor)
                node.querySelector('button[data-action="reject_element"]').style['display'] = 'none';
                @endif
            }

            if(record.is_amended) {
                node.style['background-color'] = amendedColor;
            }

            if(record.element_type == "{{ Element::ELEMENT_TYPE_ID }}") {
                node.dataset.class_identifier = record.class_identifier;
            }

            node.dataset.element_type = record.element_type;
            node.dataset.get_element_url = record.route_getElement;

            var labelNode = node.querySelector('[data-component="label"]');
            labelNode.innerHTML = record.label;

            if(record.displayInstructions.trim() != '') {
                node.querySelector('[data-component="instructions"]').innerHTML = record.displayInstructions;
                node.querySelector('[data-component="instructions"]').style.removeProperty('display');
            }

            if(record.attributes.hasOwnProperty('required')) {
                var requiredNode = requiredTemplate.cloneNode(true);
                requiredNode.classList.remove('hidden');

                labelNode.parentElement.appendChild(requiredNode);
            }

            if(record.hasOwnProperty('has_attachments') && record.has_attachments) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_count = record.route_attachment_count;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_list = record.route_attachment_list;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_upload_attachments = record.route_upload_attachments;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.name = record.name;

                node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;
            } else {
                node.querySelector('[data-component="upload-attachment-button"]').style.display = 'none';
            }

            @if($vendorCanSubmitForm)
            if(!record.attributes.hasOwnProperty('disabled')) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            }
            @endif

            @if($processerCanEditForm)
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            @endif

            node.querySelector('[data-action="reject_element"]').dataset.route_get_rejection    = record.route_get_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_save_rejection   = record.route_save_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_delete_rejection = record.route_delete_rejection;

            for(index in record.children) {
                var itemNode = document.getElementById('checkbox_input_container').cloneNode(true);
                itemNode.removeAttribute('id');
                itemNode.removeAttribute('style');
                itemNode.querySelector('[data-component="item_label"]').innerHTML = record.children[index].label;

                var otherOptionMarker = record.children[index].isOtherOption ? 1 : 0;

                itemNode.querySelector('[data-component="element"]').dataset.other_option = otherOptionMarker;

                for(key in record.children[index].attributes) {
                    var attributes = record.children[index].attributes;

                    @if($processerCanEditForm)
                    if(key == 'disabled') {
                        continue;
                    }
                    @endif

                    itemNode.querySelector('[data-component="element"]').setAttribute(key, attributes[key]);
                }

                node.querySelector('[data-component="checkbox_item_container"]').appendChild(itemNode);

                if(record.children[index].isOtherOption) {
                    var freeTextNode = document.getElementById('free_text_container').cloneNode(true);
                    freeTextNode.removeAttribute('id');
                    freeTextNode.removeAttribute('style');

                    freeTextNode.querySelector('[data-component="free_text"]').setAttribute('name', record.children[index].otherOption.name);
                    freeTextNode.querySelector('[data-component="free_text"]').setAttribute('value', record.children[index].otherOption.value);

                    @if(!$processerCanEditForm)
                    if(record.children[index].otherOption.hasOwnProperty('disabled'))
                    {
                        freeTextNode.querySelector('[data-component="free_text"]').setAttribute('disabled', 'disabled');
                    }
                    @endif

                    node.querySelector('[data-component="checkbox_item_container"]').appendChild(freeTextNode);
                }
            }

            elementContainer.appendChild(node);
        }

        function generateFileUploadElement(record, elementContainer) {
            var node = uploadFileTemplate.cloneNode(true);
            node.removeAttribute('id');
            node.classList.remove('hidden');
            node.dataset.id = record.mapping_id;
            node.dataset.class_identifier = record.class_identifier;
            node.dataset.get_element_url = record.route_getElement;
            node.dataset.is_rejected = record.is_rejected ? 1 : 0;

            if(record.is_rejected) {
                node.style['background-color'] = rejectedColor;
            } else {
                @if($isVendor)
                node.querySelector('button[data-action="reject_element"]').style['display'] = 'none';
                @endif
            }

            if(record.is_amended) {
                node.style['background-color'] = amendedColor;
            }

            var labelNode = node.querySelector('[data-component="label"]');
            labelNode.innerHTML = record.label;

            if(record.displayInstructions.trim() != '') {
                node.querySelector('[data-component="instructions"]').innerHTML = record.displayInstructions;
                node.querySelector('[data-component="instructions"]').style.removeProperty('display');
            }

            if(record.attributes.hasOwnProperty('required')) {
                var requiredNode = requiredTemplate.cloneNode(true);
                requiredNode.classList.remove('hidden');

                labelNode.parentElement.appendChild(requiredNode);
            }

            node.querySelector('[data-action="reject_element"]').dataset.route_get_rejection    = record.route_get_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_save_rejection   = record.route_save_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_delete_rejection = record.route_delete_rejection;

            node.querySelector('[data-component="upload-attachment-button"]').dataset.element_id = record.id;
            node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_count = record.route_attachment_count;
            node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_list = record.route_attachment_list;
            node.querySelector('[data-component="upload-attachment-button"]').dataset.route_upload_attachments = record.route_upload_attachments;
            node.querySelector('[data-component="upload-attachment-button"]').dataset.name = record.upload_button_name;

            node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;

            @if($vendorCanSubmitForm)
            if(!record.attributes.hasOwnProperty('disabled')) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            }
            @endif

            @if($processerCanEditForm)
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            @endif

            elementContainer.appendChild(node);
        }

        function generateDropdownElement(record, elementContainer) {
            var node = dropdownTemplate.cloneNode(true);
            node.removeAttribute('id');
            node.classList.remove('hidden');
            node.dataset.id = record.mapping_id;
            node.dataset.class_identifier = record.class_identifier;
            node.dataset.get_element_url = record.route_getElement;
            node.dataset.name = record.attributes['name'].replaceAll('[', '').replaceAll(']', '');
            node.dataset.is_rejected = record.is_rejected ? 1 : 0;

            if(record.is_rejected) {
                node.style['background-color'] = rejectedColor;
            } else {
                @if($isVendor)
                node.querySelector('button[data-action="reject_element"]').style['display'] = 'none';
                @endif
            }

            if(record.is_amended) {
                node.style['background-color'] = amendedColor;
            }

            if(record.element_type == "{{ Element::ELEMENT_TYPE_ID }}") {
                node.dataset.class_identifier = record.class_identifier;
            }

            node.dataset.element_type = record.element_type;
            node.dataset.get_element_url = record.route_getElement;

            for(key in record.attributes) {
                // avoids focusing issues with select 2
                if(key == 'required') {
                    continue;
                }

                @if($processerCanEditForm)
                if(key == 'disabled') {
                    continue;
                }
                @endif

                node.querySelector('[data-component="option_container"]').setAttribute(key, record.attributes[key]);

                if((record.attributes[key] == 'multiple') && record.attributes.hasOwnProperty('multiple')) {
                    clearChildNodes(node.querySelector('[data-component="option_container"]'));
                }
            }

            var labelNode = node.querySelector('[data-component="label"]');
            labelNode.innerHTML = record.label;

            if(record.displayInstructions.trim() != '') {
                node.querySelector('[data-component="instructions"]').innerHTML = record.displayInstructions;
                node.querySelector('[data-component="instructions"]').style.removeProperty('display');
            }

            if(record.attributes.hasOwnProperty('required')) {
                var requiredNode = requiredTemplate.cloneNode(true);
                requiredNode.classList.remove('hidden');

                labelNode.parentElement.appendChild(requiredNode);
            }

            node.querySelector('[data-action="reject_element"]').dataset.route_get_rejection    = record.route_get_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_save_rejection   = record.route_save_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_delete_rejection = record.route_delete_rejection;

            if(record.hasOwnProperty('has_attachments') && record.has_attachments) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_count = record.route_attachment_count;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_list = record.route_attachment_list;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_upload_attachments = record.route_upload_attachments;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.name = record.name;

                node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;
            } else {
                node.querySelector('[data-component="upload-attachment-button"]').style.display = 'none';
            }

            @if($vendorCanSubmitForm)
            if(!record.attributes.hasOwnProperty('disabled')) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            }
            @endif

            @if($processerCanEditForm)
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            @endif

            for(index in record.children) {
                var itemNode = document.getElementById('dropdown_option_template').cloneNode(true);
                itemNode.removeAttribute('id');
                itemNode.classList.remove('hidden');
                itemNode.innerHTML = record.children[index].label;

                for(key in record.children[index].attributes) {
                    var attributes = record.children[index].attributes;
                    itemNode.setAttribute(key, attributes[key]);
                }

                node.querySelector('[data-component="option_container"]').appendChild(itemNode);
            }

            node.querySelector('[data-component="option_container"]').classList.add('select2');

            elementContainer.appendChild(node);
        }

        function generateDateTimePickerElement(record, elementContainer) {
            var node = dateTimePickerTemplate.cloneNode(true);
            node.removeAttribute('id');
            node.classList.remove('hidden');
            node.dataset.id = record.mapping_id;
            node.dataset.class_identifier = record.class_identifier;
            node.dataset.get_element_url = record.route_getElement;
            node.dataset.name = record.attributes['name'];
            node.dataset.is_rejected = record.is_rejected ? 1 : 0;

            if(record.is_rejected) {
                node.style['background-color'] = rejectedColor;
            } else {
                @if($isVendor)
                node.querySelector('button[data-action="reject_element"]').style['display'] = 'none';
                @endif
            }

            if(record.is_amended) {
                node.style['background-color'] = amendedColor;
            }

            var labelNode = node.querySelector('[data-component="label"]');
            labelNode.innerHTML = record.label;

            if(record.displayInstructions.trim() != '') {
                node.querySelector('[data-component="instructions"]').innerHTML = record.displayInstructions;
                node.querySelector('[data-component="instructions"]').style.removeProperty('display');
            }

            if(record.attributes.hasOwnProperty('required')) {
                var requiredNode = requiredTemplate.cloneNode(true);
                requiredNode.classList.remove('hidden');

                labelNode.parentElement.appendChild(requiredNode);
            }

            for(const attr in record.attributes) {
                var value = (attr == 'required') ? true : record.attributes[attr];

                @if($processerCanEditForm)
                if(attr == 'disabled') {
                    continue;
                }
                @endif

                node.querySelector('[data-component="date_time_picker"]').setAttribute(attr, value);
            }

            node.querySelector('[data-action="reject_element"]').dataset.route_get_rejection    = record.route_get_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_save_rejection   = record.route_save_rejection;
            node.querySelector('[data-action="reject_element"]').dataset.route_delete_rejection = record.route_delete_rejection;

            if(record.hasOwnProperty('has_attachments') && record.has_attachments) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_count = record.route_attachment_count;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_attachment_list = record.route_attachment_list;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.route_upload_attachments = record.route_upload_attachments;
                node.querySelector('[data-component="upload-attachment-button"]').dataset.name = record.attributes.name;

                node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;
            } else {
                node.querySelector('[data-component="upload-attachment-button"]').style.display = 'none';
            }

            @if($vendorCanSubmitForm)
            if(!record.attributes.hasOwnProperty('disabled')) {
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            }
            @endif

            @if($processerCanEditForm)
                node.querySelector('[data-component="upload-attachment-button"]').dataset.action = 'upload-item-attachments';
            @endif

            switch(parseInt(record.attributes.mode)) {
                case {{ DateTimePicker::MODE_BOTH }}:
                    node.querySelector('[data-component="date_time_picker"]').type = 'datetime-local';
                    break;
                case {{ DateTimePicker::MODE_DATE }}:
                    node.querySelector('[data-component="date_time_picker"]').type = 'date';
                    break;
                case {{ DateTimePicker::MODE_TIME }}:
                    node.querySelector('[data-component="date_time_picker"]').type = 'time';
                    break;
            }

            elementContainer.appendChild(node);
        }

        /**upload attachments */
        function addRowToUploadModal(fileAttributes){
            var clone = $('[data-type=template] tr.template-download').clone();
            var target = $('#uploadFileTable tbody.files');

            $(clone).find("a[data-category=link]").prop('href', fileAttributes['download_url']);
            $(clone).find("a[data-category=link]").prop('title', fileAttributes['filename']);
            $(clone).find("a[data-category=link]").prop('download', fileAttributes['filename']);
            $(clone).find("a[data-category=link]").html(fileAttributes['filename']);
            $(clone).find("input[name='uploaded_files[]']").val(fileAttributes['id']);
            $(clone).find("[data-category=size]").html(fileAttributes['size']);
            $(clone).find("button[data-action=delete]").prop('data-route', fileAttributes['deleteRoute']);
            $(clone).find("[data-category=created-at]").html(fileAttributes['createdAt']);

            target.append(clone);
        }

        $(document).on('click', '[data-action="upload-item-attachments"]', function(e) {
            e.preventDefault();

            var target = $('#uploadFileTable tbody.files').empty();
            var data   = $.get($(this).data('route_attachment_list'), function(data){
                for(var i in data){
                    addRowToUploadModal({
                        download_url: data[i]['download_url'],
                        filename: data[i]['filename'],
                        imgSrc: data[i]['imgSrc'],
                        id: data[i]['id'],
                        size: data[i]['size'],
                        deleteRoute: data[i]['deleteRoute'],
                        createdAt: data[i]['createdAt'],
                    });
                }
            });

            $('[data-action=submit-attachments]').data('id', $(this).data('element_id'));
            $('[data-action=submit-attachments]').data('updated-attachment-count-url', $(this).data('route_attachment_count'));
            $('#uploadAttachmentModal').modal('show');
            $('#attachment-upload-form').prop('action',$(this).data('route_upload_attachments'));
        });

        $(document).on('click', '[data-action=submit-attachments]', function(){
            var updatedAttachmentCountUrl = $(this).data('updated-attachment-count-url');
            var uploadedFilesInput        = [];

            $('form#attachment-upload-form input[name="uploaded_files[]"]').each(function(index){
                uploadedFilesInput.push($(this).val());
            });

            app_progressBar.show();

            $.post($('form#attachment-upload-form').prop('action'),{
                _token: _csrf_token,
                uploaded_files: uploadedFilesInput
            })
            .done(function(data){
                if(data.success){
                    $('#uploadAttachmentModal').modal('hide');

                    $.get(updatedAttachmentCountUrl, {},function(resp) {
                        $(document).find('[data-name="' + resp.name + '"]').find('[data-component="attachment_upload_count"]').text(resp.attachmentCount);
                    });

                    app_progressBar.maxOut(null, function(){ app_progressBar.hide(); });
                }
            })
            .fail(function(data){
                console.error('failed');
            });
        });

        var attachmentsTable = null;

        var attachmentDownloadButtonFormatter = function(cell, formatterParams, onRendered) {
            var data = cell.getRow().getData();

            var downloadButton = document.createElement('a');
            downloadButton.dataset.toggle = 'tooltip';
            downloadButton.className = 'btn btn-xs btn-primary';
            downloadButton.innerHTML = '<i class="fas fa-download"></i>';
            downloadButton.style['margin-right'] = '5px';
            downloadButton.href = data.download_url;
            downloadButton.download = data.filename;

            return downloadButton;
        }

        $(document).on('click', '[data-action="view-attachments"]', function(e) {
            e.preventDefault();

            $('#attachmentsModal').data('url', $(this).data('route_attachment_list'));
            $('#attachmentsModal').modal('show');
        });

        $('#attachmentsModal').on('shown.bs.modal', function(e) {
            e.preventDefault();

            var url = $(this).data('url');

            attachmentsTable = new Tabulator('#attachmentsTable', {
                height:500,
                pagination:"local",
                columns: [
                    { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('general.attachments') }}", field: 'filename', headerSort:false, headerFilter:"input" },
                    { title:"{{ trans('vendorManagement.proofOfPayment') }}", width: 120, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false, formatter: attachmentDownloadButtonFormatter },
                ],
                layout:"fitColumns",
                ajaxURL: url,
                movableColumns:true,
                placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                columnHeaderSortMulti:false,
            });
        });
        /**upload attachments end */

        /**
         * section submit
         */
        $(document).on('submit', '[data-component="section-form"]', function(e) {
            e.preventDefault();

            $('#smartwizard').smartWizard("loader", "show");

            var submitButton = $(this).find('button[type="submit"]');

            var url  = $(this).attr('action');
            var data = $(this).serializeArray();
            var sectionId = $(this).data('section_id');

            clearElementValidationErrors(sectionId);

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                success: function(response) {
                    if(response.success) {
                        // app_progressBar.maxOut();
                        SmallErrorBox.saved("{{ trans('general.success') }}", "{{ trans('forms.saved') }}");
                        markSectionElementsAsAmended(sectionId);
                    } else {
                        var keys = Object.keys(response.errors);

                        keys.forEach(function(key, index) {
                            var errorMessage = response.errors[key][0];

                            $(`[data-name="${key}"]`).closest('[data-component="template_root"]').find('em.invalid').html(errorMessage);
                        });

                        SmallErrorBox.formValidationError("{{ trans('forms.formValidationError') }}", "{{ trans('forms.correctErrorAndResubmit') }}");
                    }

                    $('#smartwizard').smartWizard("loader", "hide");
                },
                error  : function(jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);
                    console.log(textStatus);
                    console.log(errorThrown);
                    $('#smartwizard').smartWizard("loader", "hide");
                    SmallErrorBox.refreshAndRetry();
                }
            });
        });
        /**section submit ends */

        /**rejection */
        $(document).on('click', '[data-action="reject_element"]', function(e) {
            e.preventDefault();

            var getRejectionUrl    = $(this).data('route_get_rejection');
            var saveRejectionUrl   = $(this).data('route_save_rejection');
            var deleteRejectionUrl = $(this).data('route_delete_rejection');

            $('#formRejectionModal').data('url', getRejectionUrl);
            $('#formRejectionModal').find('[data-action="save_rejection"]').data('url', saveRejectionUrl);
            $('#formRejectionModal').find('[data-action="resolve_rejection"]').data('url', deleteRejectionUrl);

            $('#formRejectionModal').modal('show');
        });

        $(document).on('shown.bs.modal', '#formRejectionModal', function(e) {
            e.preventDefault();

            var url = $(this).data('url')

            $(this).find('[name="remarks"]').removeAttr('style');

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    $('#formRejectionModal').find('[name="remarks"]').val(response.remarks.trim());

                    if(response.updator) {
                        $('#formRejectionModal').find('[data-component="updator_container"]').show();
                        $('#formRejectionModal').find('[data-component="updator_name"]').text(response.updator.trim());
                    }
                },
                error: function(jqXHR,textStatus, errorThrown ) {
                    // error
                    console.error(errorThrown);
                },
            });
        });

        $(document).on('hidden.bs.modal', '#formRejectionModal', function(e) {
            e.preventDefault();

            $('#formRejectionModal').find('[name="remarks"]').val('');
            $('#formRejectionModal').find('[data-component="updator_container"]').hide();
            $('#formRejectionModal').find('[data-component="error_message"]').text('');
        });

        $(document).on('click', '#formRejectionModal [data-action="save_rejection"]', function(e) {
            e.preventDefault();

            var url = $(this).data('url');
            var remarks = $('#formRejectionModal').find('[name="remarks"]').val();
            
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    remarks: remarks.trim(),
                    _token: '{{{ csrf_token() }}}',
                },
                success: function(response) {
                    if(response.success) {
                        $('#formRejectionModal').modal('hide');
                        location.reload();
                    } else {
                        $('#formRejectionModal').find('[data-component="error_message"]').text(response.errors.remarks[0]);
                    }
                },
                error: function(jqXHR,textStatus, errorThrown ) {
                    // error
                    console.error(errorThrown);
                },
            });
        });

        $(document).on('click', '#formRejectionModal [data-action="resolve_rejection"]', function(e) {
            e.preventDefault();

            var url = $(this).data('url');

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                },
                success: function(response) {
                    if(response.success) {
                        $('#formRejectionModal').modal('hide');
                        location.reload();
                    }
                },
                error: function(jqXHR,textStatus, errorThrown ) {
                    // error
                    console.error(errorThrown);
                },
            });
        });
        /**rejection ends */

        /**view edit logs */
        @if( ! $isVendor )
        var actionLogsTable = null;

        $(document).on('click', '#viewActionLogsButton', function(e) {
            e.preventDefault();

            $('#actionLogsModal').modal('show');
        });

        $('#actionLogsModal').on('shown.bs.modal', function(e) {
            e.preventDefault();

            actionLogsTable = new Tabulator('#actionLogsTable', {
                height:400,
                pagination:"local",
                columns: [
                    { title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('users.name') }}", field: 'user', cssClass:"text-center text-middle", headerSort:false, headerFilter:"input" },
                    { title:"{{ trans('general.action') }}", field: 'action', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                    { title:"{{ trans('general.date') }}", field: 'datetime', width: 180, hozAlign: 'center', cssClass:"text-center text-middle", headerSort:false },
                ],
                layout:"fitColumns",
                ajaxURL: "{{ route('vendor.form.submission.action.logs.get', [$vendorRegistration->id]) }}",
                movableColumns:true,
                placeholder:"{{ trans('formBuilder.noFormsAvailable') }}",
                columnHeaderSortMulti:false,
            });
        });
        @endif
        /**view eidt logs end */

        function markSectionElementsAsAmended(sectionId) {
            var sectionElements = $(`form[data-section_id="${sectionId}"] [data-component="template_root"]`);

            sectionElements.each(function(index) {
                if($(this).data('is_rejected') == 1) {
                    $(this).css('background-color', amendedColor);
                }
            });
        }

        function clearElementValidationErrors(sectionId) {
            // clear all <em class="invalid"></em> in all elements in a given section
            var sectionElements = $(`form[data-section_id="${sectionId}"] [data-component="template_root"]`);
            
            sectionElements.each(function(index) {
                $(this).find('em.invalid').html('');
            });
        }

        function clearChildNodes(node) {
            while(node.firstChild) {
                node.removeChild(node.lastChild);
            }
        }

        function initCustomComponents() {
            $('.select2').select2();
        }
    });
</script>
@endsection