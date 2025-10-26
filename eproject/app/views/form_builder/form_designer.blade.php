@extends('layout.main')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/css/smart_wizard_all.min.css" rel="stylesheet" type="text/css" />
    <style>
        .custom-footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            background-color: #DFDFDF;
            color: white;
            text-align: center;
            padding-top:5px;
            padding-bottom:5px;
            padding-right:5px;
            border-top: 1px solid #C6C6C6;
        }

        .float-right {
            position: relative;
            left: 0;
            top: 0;
            z-index: 999;
            margin-top: 5px;
            margin-right:5px;
        }

        .highlighted {
            background-color: #9AB6F1;
        }

        .spaced {
            margin-right: 5px;
        }
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li><a href="{{ $form->getIndexRouteByIdentifier() }}">{{ trans('formBuilder.formsLibrary') }}</a></li>
		<li>{{ $form->name }}</li>
	</ol>
@endsection
<?php use PCK\FormBuilder\Elements\Element; ?>
<?php use PCK\FormBuilder\Elements\DateTimePicker; ?>
<?php use PCK\FormBuilder\Elements\SystemModuleElement; ?>
<?php use PCK\FormBuilder\ElementDefinition; ?>
<?php use PCK\FormBuilder\Elements\FormBuilderElementCommon; ?>
@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2><i class="fa fa-list"></i> {{ $form->name }}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        @if($canEditFormDesign)
                        <div class="row">
                            <div class="col-xs-12">
                                <a href="#" data-action="create_resource" class="btn btn-primary btn-md pull-right header-btn" data-target="#editorModal" data-toggle="modal" data-url="{{ route('form.column.store', [$form->id]) }}" data-title="{{ trans('formBuilder.addSection') }}">
                                    <i class="fa fa-plus"></i> {{ trans('formBuilder.addSection') }}
                                </a>
                            </div>
                        </div>
                        <p></p>
                        @endif
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
            @if($canEditFormDesign)
            <button type="button" data-action="submit_form_design_for_approval" class="btn btn-primary pull-right">{{ trans('formBuilder.submitFormDesignforApproval') }}</button>
            @endif
            @if($canApproveFormDesign)
                <div class="pull-right spaced">
                @include('verifiers.approvalForm', [
                    'formId'            => 'verifierForm',
                    'object'            => $form,
                    'showApproveButton' => (!$hasRejectedElements),
                    'showRejectButton'  => $hasRejectedElements,
                ])
                </div>
            @endif
            <a href="{{ $form->getIndexRouteByIdentifier() }}" class="btn btn-default pull-right spaced">{{ trans('forms.back') }}</a>
        </div>
    </div>

    @include('form_builder.partials.elements.element_templates', [
        'showEditElementControls'     => true,
        'showElementFileUploadButton' => true,
        'designMode'                  => $canEditFormDesign,
        'showRejectionButton'         => true,
    ])
    @include('templates.yesNoModal', [
        'modalId'   => 'deleteSectionModal',
        'titleId'   => 'deleteSectionModalTitle',
        'title'     => trans('general.confirmation'),
        'message'   => trans('formBuilder.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed'),
    ])
    @include('templates.yesNoModal', [
        'modalId'   => 'deleteSubSectionModal',
        'titleId'   => 'deleteSubSectionModalTitle',
        'title'     => trans('general.confirmation'),
        'message'   => trans('formBuilder.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed'),
    ])
    @include('templates.yesNoModal', [
        'modalId'   => 'deleteElementModal',
        'titleId'   => 'deleteElementModalTitle',
        'title'     => trans('general.confirmation'),
        'message'   => trans('general.sureToProceed'),
    ])
    @include('form_builder.partials.element_rejection_modal', [
        'modalId'          => 'formRejectionModal',
        'canEditRejection' => $canApproveFormDesign,
    ])

    @if($canEditFormDesign)
        @include('templates.generic_input_modal', [
            'modalId' => 'editorModal',
            'labelId' => 'inputEditorLabel',
        ])
        @include('form_builder.partials.elements.textboxModal', [
            'modalId' => 'textboxModal',
        ])
        @include('form_builder.partials.elements.textboxModal', [
            'modalId'         => 'numberTextboxModal',
            'isNumberTextbox' => true,
        ])
        @include('form_builder.partials.elements.multiInputModal', [
            'modalId'        => 'radioboxModal',
            'hasOtherOption' => 'true',
        ])
        @include('form_builder.partials.elements.multiInputModal', [
            'modalId'        => 'checkboxModal',
            'hasOtherOption' => 'true',
        ])
        @include('form_builder.partials.elements.multiInputModal', [
            'modalId'    => 'dropdownModal',
            'isDropdown' => true,
        ])
        @include('form_builder.partials.elements.fileUploadModal', [
            'modalId' => 'fileUploadModal',
        ])
        @include('form_builder.partials.elements.dateTimePickerModal', [
            'modalId' => 'dateTimePickerModal',
        ])
        @include('form_builder.partials.elements.textareaModal')
        @include('form_builder.partials.create_element_modal', [
            'modalId' => 'elementModal',    
        ])
        @include('form_builder.partials.selectVerifiersModal', [
            'modalId'   => 'selectVerifiersModal',
            'verifiers' => $formDesignApprovers,
        ])
        @include('templates.warning_modal', [
            'modalId' => 'warningModal',
            'message' => trans('formBuilder.verifiersRequired'),
        ])
    @endif

    @if($canApproveFormDesign)
        @include('templates.verifier_remarks_modal', [
            'verifierApproveModalId' => 'verifierApproveModal',
            'verifierRejectModalId'  => 'verifierRejectModal',
            'verifierApproveTitle'   => trans('forms.approve'),
            'verifierRejectTitle'    => trans('forms.reject'),
        ])
    @endif
@endsection

@section('js')
<link rel="stylesheet" href="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css') }}" />
    <script type="text/javascript" src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/smartwizard@5/dist/js/jquery.smartWizard.min.js" type="text/javascript"></script>
    <script>
        $(document).ready(function() {
            var wizardHeaderContainer      = document.getElementById('wizard_header_container');
            var wizardContentContainer     = document.getElementById('wizard_content_container');

            var editControlsDiv             = document.getElementById('edit_controls_div');

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

            fetchAndRenderFormContents();

            function fetchAndRenderFormContents() {
                app_progressBar.reset();
                app_progressBar.toggle();

                $.ajax({
                    url: "{{ route('form.contents.get', [$form->id]) }}",
                    method: 'GET',
                    success: function(responseData) {
                        responseData.forEach(function(column, index) {
                            var wizardHeaderNode = wizardHeaderTemplate.cloneNode(true);
                            wizardHeaderNode.removeAttribute('id');
                            wizardHeaderNode.removeAttribute('style');
                            wizardHeaderNode.querySelector('a.nav-link').dataset.id = column.id;
                            wizardHeaderNode.querySelector('a.nav-link').href = `#column-${column.id}`;
                            wizardHeaderNode.querySelector('a.nav-link').innerText = column.name;

                            @if($canEditFormDesign)
                            var editSectionControls = editControlsDiv.cloneNode(true);
                            editSectionControls.removeAttribute('id');
                            editSectionControls.removeAttribute('style');
                            editSectionControls.querySelector('[data-action="edit_resource"]').dataset.title = "{{ trans('formBuilder.editSectionName') }}";
                            editSectionControls.querySelector('[data-action="edit_resource"]').dataset.value = column.name;
                            editSectionControls.querySelector('[data-action="edit_resource"]').dataset.url = column.route_update_column;
                            editSectionControls.querySelector('[data-component="delete_button"]').dataset.url = column.route_delete_column;
                            editSectionControls.querySelector('[data-component="delete_button"]').dataset.action = 'delete_section';

                            wizardHeaderNode.querySelector('a.nav-link').appendChild(editSectionControls);
                            @endif

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
                                    enableAllAnchors: true,
                                    markDoneStep: false,
                                    markAllPreviousStepsAsDone: false,
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

                        @if($canEditFormDesign)
                        Sortable.create(wizardHeaderContainer, {
                            group: "section_group",
                            swap: true,
                            swapClass: "highlighted",
                            handle: ".section_drag_handle",
                            animation: 200,
                            easing: "cubic-bezier(1, 0, 0, 1)",
                            swapThreshold: .1,
                            onUpdate: function (evt) {
                                var draggedColumnId = evt.item.querySelector('a.nav-link').dataset.id;
                                var swappedColumnId = evt.swapItem.querySelector('a.nav-link').dataset.id;

                                $.ajax({
                                    url: "{{ route('form.column.swap', [$form->id]); }}",
                                    method: "POST",
                                    data: {
                                        draggedColumnId: draggedColumnId,
                                        swappedColumnId: swappedColumnId,
                                        _token: '{{{ csrf_token() }}}'
                                    },
                                    success: function(response) {
                                        if(response.success) {
                                            console.log('section swap is successful');
                                        }
                                    },
                                    error: function(jqXHR,textStatus, errorThrown ) {
                                        // error
                                        console.error(errorThrown);
                                    }
                                });
                            },
                        });
                        @endif

                        initCustomComponents();

                        app_progressBar.maxOut();
                        app_progressBar.toggle();
                    },
                    error  : function(jqXHR, textStatus, errorThrown) {
                        console.log('ERROR');
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

                @if($canEditFormDesign)
                columnNode.querySelector('[data-action="create_resource"]').dataset.url = column.route_new_subsection;
                @endif

                column.contents.forEach(function(data, index) {
                    @if($canEditFormDesign)
                    var editSubSectionControls = editControlsDiv.cloneNode(true);
                    editSubSectionControls.removeAttribute('id');
                    editSubSectionControls.removeAttribute('style');
                    editSubSectionControls.style['margin-left'] = '5px';
                    editSubSectionControls.querySelector('[data-action="edit_resource"]').dataset.title = "{{ trans('formBuilder.editSubSectionName') }}";
                    editSubSectionControls.querySelector('[data-action="edit_resource"]').dataset.value = data.name;
                    editSubSectionControls.querySelector('[data-action="edit_resource"]').dataset.url = data.route_update_section;
                    editSubSectionControls.querySelector('[data-component="delete_button"]').dataset.url = data.route_delete_section;
                    editSubSectionControls.querySelector('[data-component="delete_button"]').dataset.action = 'delete_sub_section';
                    @endif

                    var tabHeader = subSectionTabHeaderContent.cloneNode(true);
                    tabHeader.removeAttribute('id');
                    tabHeader.removeAttribute('style');
                    tabHeader.querySelector('a[data-toggle="tab"]').dataset.section_id = data.id;

                    if(index == 0) {
                        tabHeader.classList.add('active');
                    }

                    tabHeader.querySelector('[data-toggle="tab"]').href = '#section-' + data.id;
                    tabHeader.querySelector('[data-toggle="tab"]').innerHTML = data.name;

                    @if($canEditFormDesign)
                    tabHeader.querySelector('[data-toggle="tab"]').appendChild(editSubSectionControls);
                    @endif

                    columnNode.querySelector('[data-component="subsection_tab_header_container"]').appendChild(tabHeader);

                    tabContent = subSectionTabContent.cloneNode(true);
                    tabContent.id = 'section-' + data.id;
                    tabContent.removeAttribute('style');
                    tabContent.querySelector('[data-component="section-form"]').dataset.section_id = data.id;

                    @if($canEditFormDesign)
                    tabContent.querySelectorAll('[data-action="add_element"]').forEach(function(el, index) {
                        el.dataset.section_id = data.id;
                        el.dataset.url = data.route_new_element;
                    });
                    @endif

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

                    @if($canEditFormDesign)
                    if(data.hasOwnProperty('section_submit_url') && (data.section_submit_url != null)) {
                        tabContent.querySelector('[data-component="section-form"]').action = data.section_submit_url;
                    }
                    @endif

                    columnNode.querySelector('[data-component="subsection_tab_content_div"]').appendChild(tabContent);

                    @if($canEditFormDesign)
                    Sortable.create(tabContent.querySelector('[data-component="section-form"] [data-component="element_container"]'), {
                        group: 'element_group_' + data.id,
                        swap: true,
                        swapClass: 'highlighted',
                        handle: '.element_drag_handle',
                        animation: 200,
                        easing: "cubic-bezier(1, 0, 0, 1)",
                        onUpdate: function (evt) {
                            var draggedElementMappingId = evt.item.dataset.id;
                            var swappedElementMappingId = evt.swapItem.dataset.id;

                            $.ajax({
                                url: data.route_swap_element,
                                method: "POST",
                                data: {
                                    draggedElementMappingId: draggedElementMappingId,
                                    swappedElementMappingId: swappedElementMappingId,
                                    _token: '{{{ csrf_token() }}}'
                                },
                                success: function(response) {
                                    if(response.success) {
                                        console.log('element swap is successful');
                                    }
                                },
                                error: function(jqXHR,textStatus, errorThrown ) {
                                    // error
                                    console.error(errorThrown);
                                }
                            });
                        },
                    });
                    @endif
                });

                if(column.contents.length <= 0) {
                    columnNode.querySelector('[data-component="subsection_tab_header_container"]').style.display = 'none';
                    columnNode.querySelector('[data-component="subsection_tab_content_div"]').style.display = 'none';
                }

                wizardContentNode.appendChild(columnNode);

                @if($canEditFormDesign)
                Sortable.create(columnNode.querySelector('[data-component="subsection_tab_header_container"]'), {
                    group: "sub_section_group_" + column.id,
                    swap: true,
                    swapClass: "highlighted",
                    handle: ".sub_section_drag_handle",
                    animation: 200,
                    easing: "cubic-bezier(1, 0, 0, 1)",
                    swapThreshold: .1,
                    onUpdate: function (evt) {
                        var draggedSectionId = evt.item.querySelector('[data-toggle="tab"]').dataset.section_id;
                        var swappedSectionId = evt.swapItem.querySelector('[data-toggle="tab"]').dataset.section_id;

                        $.ajax({
                            url: column.route_swap_section,
                            method: "POST",
                            data: {
                                draggedSectionId: draggedSectionId,
                                swappedSectionId: swappedSectionId,
                                _token: '{{{ csrf_token() }}}'
                            },
                            success: function(response) {
                                if(response.success) {
                                    console.log('sub-section swap is successful');
                                }
                            },
                            error: function(jqXHR,textStatus, errorThrown ) {
                                // error
                                console.error(errorThrown);
                            }
                        });
                    },
                });
                @endif
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
                node.dataset.section_id = record.section_id;

                if(record.is_rejected) {
                    node.style['background-color'] = rejectedColor;
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

                    node.querySelector('[data-component="element"]').setAttribute(attr, value);
                }

                if(record.hasOwnProperty('has_attachments') && record.has_attachments) {
                    node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;
                } else {
                    node.querySelector('[data-component="upload-attachment-button"]').style.display = 'none';
                }

                @if($canEditFormDesign)
                node.querySelector('[data-action="edit_element"]').dataset.url = record.route_update;
                node.querySelector('[data-action="edit_element"]').dataset.element_type = record.element_type;

                node.querySelector('[data-action="delete_element"]').dataset.url = record.route_delete;
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
                node.dataset.section_id = record.section_id;

                if(record.is_rejected) {
                    node.style['background-color'] = rejectedColor;
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

                node.querySelector('[data-component="element"]').removeAttribute('style');

                node.querySelector('[data-action="reject_element"]').dataset.route_get_rejection    = record.route_get_rejection;
                node.querySelector('[data-action="reject_element"]').dataset.route_save_rejection   = record.route_save_rejection;
                node.querySelector('[data-action="reject_element"]').dataset.route_delete_rejection = record.route_delete_rejection;

                for(const attr in record.attributes) {
                    var value = (attr == 'required') ? true : record.attributes[attr];

                    node.querySelector('[data-component="element"]').setAttribute(attr, value);
                }

                if(record.hasOwnProperty('has_attachments') && record.has_attachments) {
                    node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;
                } else {
                    node.querySelector('[data-component="upload-attachment-button"]').style.display = 'none';
                }

                @if($canEditFormDesign)
                node.querySelector('[data-component="element"]').innerHTML = record.value;

                node.querySelector('[data-action="edit_element"]').dataset.url = record.route_update;
                node.querySelector('[data-action="edit_element"]').dataset.element_type = record.element_type;

                node.querySelector('[data-action="delete_element"]').dataset.url = record.route_delete;
                @endif

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
                node.dataset.section_id = record.section_id;

                if(record.is_rejected) {
                    node.style['background-color'] = rejectedColor;
                }

                if(record.element_type == "{{ Element::ELEMENT_TYPE_ID }}") {
                    node.dataset.class_identifier = record.class_identifier;
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
                    node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;
                }

                for(index in record.children) {
                    var itemNode = document.getElementById('radio_input_container').cloneNode(true);
                    itemNode.removeAttribute('id');
                    itemNode.removeAttribute('style');
                    itemNode.querySelector('[data-component="item_label"]').innerHTML = record.children[index].label;

                    var otherOptionMarker = record.children[index].isOtherOption ? 1 : 0;

                    itemNode.querySelector('[data-component="element"]').dataset.other_option = otherOptionMarker;

                    for(key in record.children[index].attributes) {
                        var attributes = record.children[index].attributes;

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

                @if($canEditFormDesign)
                node.querySelector('[data-action="edit_element"]').dataset.url = record.route_update;
                node.querySelector('[data-action="edit_element"]').dataset.element_type = record.element_type;

                node.querySelector('[data-action="delete_element"]').dataset.url = record.route_delete;
                @endif

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
                node.dataset.section_id = record.section_id;

                if(record.is_rejected) {
                    node.style['background-color'] = rejectedColor;
                }

                if(record.element_type == "{{ Element::ELEMENT_TYPE_ID }}") {
                    node.dataset.class_identifier = record.class_identifier;
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
                    node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;
                } else {
                    node.querySelector('[data-component="upload-attachment-button"]').style.display = 'none';
                }

                for(index in record.children) {
                    var itemNode = document.getElementById('checkbox_input_container').cloneNode(true);
                    itemNode.removeAttribute('id');
                    itemNode.removeAttribute('style');
                    itemNode.querySelector('[data-component="item_label"]').innerHTML = record.children[index].label;

                    var otherOptionMarker = record.children[index].isOtherOption ? 1 : 0;

                    itemNode.querySelector('[data-component="element"]').dataset.other_option = otherOptionMarker;

                    for(key in record.children[index].attributes) {
                        var attributes = record.children[index].attributes;

                        itemNode.querySelector('[data-component="element"]').setAttribute(key, attributes[key]);
                    }

                    node.querySelector('[data-component="checkbox_item_container"]').appendChild(itemNode);

                    if(record.children[index].isOtherOption) {
                        var freeTextNode = document.getElementById('free_text_container').cloneNode(true);
                        freeTextNode.removeAttribute('id');
                        freeTextNode.removeAttribute('style');

                        freeTextNode.querySelector('[data-component="free_text"]').setAttribute('name', record.children[index].otherOption.name);
                        freeTextNode.querySelector('[data-component="free_text"]').setAttribute('value', record.children[index].otherOption.value);

                        node.querySelector('[data-component="checkbox_item_container"]').appendChild(freeTextNode);
                    }
                }

                @if($canEditFormDesign)
                node.querySelector('[data-action="edit_element"]').dataset.url = record.route_update;
                node.querySelector('[data-action="edit_element"]').dataset.element_type = record.element_type;

                node.querySelector('[data-action="delete_element"]').dataset.url = record.route_delete;
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
                node.dataset.section_id = record.section_id;

                if(record.is_rejected) {
                    node.style['background-color'] = rejectedColor;
                }

                for(key in record.attributes) {
                    // avoids focusing issues with select 2
                    if(key == 'required') {
                        continue;
                    }

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
                    node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;
                } else {
                    node.querySelector('[data-component="upload-attachment-button"]').style.display = 'none';
                }

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

                @if($canEditFormDesign)
                node.querySelector('[data-action="edit_element"]').dataset.url = record.route_update;
                node.querySelector('[data-action="edit_element"]').dataset.element_type = record.element_type;

                node.querySelector('[data-action="delete_element"]').dataset.url = record.route_delete;
                @endif

                elementContainer.appendChild(node);
            }

            function generateFileUploadElement(record, elementContainer) {
                var node = uploadFileTemplate.cloneNode(true);
                node.removeAttribute('id');
                node.classList.remove('hidden');
                node.dataset.id = record.mapping_id;
                node.dataset.class_identifier = record.class_identifier;
                node.dataset.get_element_url = record.route_getElement;
                node.dataset.section_id = record.section_id;

                if(record.is_rejected) {
                    node.style['background-color'] = rejectedColor;
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

                node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;

                @if($canEditFormDesign)
                node.querySelector('[data-action="edit_element"]').dataset.url = record.route_update;
                node.querySelector('[data-action="edit_element"]').dataset.element_type = record.element_type;

                node.querySelector('[data-action="delete_element"]').dataset.url = record.route_delete;
                @endif

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
                node.dataset.section_id = record.section_id;

                if(record.is_rejected) {
                    node.style['background-color'] = rejectedColor;
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

                for(const attr in record.attributes) {
                    var value = (attr == 'required') ? true : record.attributes[attr];

                    node.querySelector('[data-component="date_time_picker"]').setAttribute(attr, value);
                }

                if(record.hasOwnProperty('has_attachments') && record.has_attachments) {
                    node.querySelector('[data-component="attachment_upload_count"]').innerText = record.attachment_count;
                } else {
                    node.querySelector('[data-component="upload-attachment-button"]').style.display = 'none';
                }

                node.querySelector('[data-component="date_time_picker"]').classList.add('date-timepicker-mode-' + record.attributes.mode);

                @if($canEditFormDesign)
                node.querySelector('[data-action="edit_element"]').dataset.url = record.route_update;
                node.querySelector('[data-action="edit_element"]').dataset.element_type = record.element_type;

                node.querySelector('[data-action="delete_element"]').dataset.url = record.route_delete;
                @endif

                elementContainer.appendChild(node);
            }

            function clearChildNodes(node, includeCurrentNode = false) {
                if(includeCurrentNode) {
                    node.remove();
                } else {
                    while(node.firstChild) {
                        node.removeChild(node.lastChild);
                    }
                }
            }

            function initCustomComponents() {
                $('.select2').select2();

                $('.date-timepicker-mode-{{ DateTimePicker::MODE_BOTH }}').datetimepicker({
                    format: 'DD-MMM-YYYY hh:mm A',
                    stepping: '{{{ \Config::get('tender.MINUTES_INTERVAL') }}}',
                    showTodayButton: false,
                    allowInputToggle: true,
                    widgetPositioning: {
                        horizontal: 'auto',
                        vertical: 'auto'
                    }
                });

                $('.date-timepicker-mode-{{ DateTimePicker::MODE_DATE }}').datetimepicker({
                    format: 'DD-MMM-YYYY',
                    stepping: '{{{ \Config::get('tender.MINUTES_INTERVAL') }}}',
                    showTodayButton: false,
                    allowInputToggle: true,
                    widgetPositioning: {
                        horizontal: 'auto',
                        vertical: 'auto'
                    }
                });

                $('.date-timepicker-mode-{{ DateTimePicker::MODE_TIME }}').datetimepicker({
                    format: 'hh:mm A',
                    stepping: '{{{ \Config::get('tender.MINUTES_INTERVAL') }}}',
                    showTodayButton: false,
                    allowInputToggle: true,
                    widgetPositioning: {
                        horizontal: 'auto',
                        vertical: 'auto'
                    }
                });
            }

            @if($canEditFormDesign)
            /**create and edit elements */
            $(document).on('click', '[data-action="add_element"]', function(e) {
                e.preventDefault();

                var sectionId = $(this).data('section_id');
                var url       = $(this).data('url');

                $('#elementModal').data('section_id', sectionId);
                $('#elementModal').data('url', url);
                $('#elementModal').modal('show');
            });

            $(document).on('click', '[data-action="create_element"]', function(e) {
                e.preventDefault();

                $('#elementModal').modal('hide');

                var classIdentifier = $(this).data('class_identifier').toString();
                var title = "{{ trans('formBuilder.createNew') }} ";
                var url = $('#elementModal').data('url');
                var sectionId = $('#elementModal').data('section_id');

                switch(classIdentifier) {
                    case "{{ Element::TYPE_TEXT }}":
                    case "{{ Element::TYPE_EMAIL }}":
                    case "{{ Element::TYPE_URL }}":
                        populateInputTypeModal(classIdentifier, title, url, sectionId);
                        $('#textboxModal').modal('show');
                        break;
                    case "{{ Element::TYPE_MULTILINE_TEXT }}":
                        populateTextareaModal(classIdentifier, title, url, sectionId);
                        $('#textareaModal').modal('show');
                        break;
                    case "{{ Element::TYPE_NUMBER }}":
                        populateNumberTextBoxModal(classIdentifier, title, url, sectionId);
                        $('#numberTextboxModal').modal('show');
                        break;
                    case "{{ Element::TYPE_RADIO }}":
                        populateRadioBoxModal(classIdentifier, title, url, sectionId);
                        $('#radioboxModal').modal('show');
                        break;
                    case "{{ Element::TYPE_CHECKBOX }}":
                        populateCheckBoxModal(classIdentifier, title, url, sectionId);
                        $('#checkboxModal').modal('show');
                        break;
                    case "{{ Element::TYPE_DROPDOWN }}":
                        populateDropdownModal(classIdentifier, title, url, sectionId);
                        $('#dropdownModal').modal('show');
                        break;
                    case "{{ Element::TYPE_FILE_UPLOAD }}":
                        populateFileUploadModal(classIdentifier, title, url, sectionId);
                        $('#fileUploadModal').modal('show');
                        break;
                    case "{{ Element::TYPE_DATE_TIME }}":
                        populateDateTimePickerModal(classIdentifier, title, url, sectionId);
                        $('#dateTimePickerModal').modal('show');
                        break;
                }
            });

            $(document).on('click', '[data-action="edit_element"]', function(e) {
                e.preventDefault();

                var parentNode      = $(this).closest('[data-component="template_root"]');
                var elementType     = $(this).data('element_type');;
                var title           = "{{ trans('general.edit') }} ";
                var getElementUrl   = parentNode.data('get_element_url');
                var sectionId       = parentNode.data('section_id');

                $.ajax({
                    url: getElementUrl,
                    method: "GET",
                    success: function(data) {
                        var url = data.route_update;

                        if(elementType == "{{ Element::ELEMENT_TYPE_ID }}") {
                            var classIdentifier = data.class_identifier.toString();

                            switch(classIdentifier.toString()) {
                                case "{{ Element::TYPE_TEXT }}":
                                case "{{ Element::TYPE_EMAIL }}":
                                case "{{ Element::TYPE_URL }}":
                                    populateInputTypeModal(classIdentifier, title, url, sectionId, data);
                                    $('#textboxModal').modal('show');
                                    break;
                                case "{{ Element::TYPE_MULTILINE_TEXT }}":
                                    populateTextareaModal(classIdentifier, title, url, sectionId, data);
                                    $('#textareaModal').modal('show');
                                    break;
                                case "{{ Element::TYPE_NUMBER }}":
                                    populateNumberTextBoxModal(classIdentifier, title, url, sectionId, data);
                                    $('#numberTextboxModal').modal('show');
                                    break;
                                case "{{ Element::TYPE_RADIO }}":
                                    populateRadioBoxModal(classIdentifier, title, url, sectionId, data);
                                    $('#radioboxModal').modal('show');
                                    break;
                                case "{{ Element::TYPE_CHECKBOX }}":
                                    populateCheckBoxModal(classIdentifier, title, url, sectionId, data);
                                    $('#checkboxModal').modal('show');
                                    break;
                                case "{{ Element::TYPE_DROPDOWN }}":
                                    populateDropdownModal(classIdentifier, title, url, sectionId, data);
                                    $('#dropdownModal').modal('show');
                                    break;
                                case "{{ Element::TYPE_FILE_UPLOAD }}":
                                    populateFileUploadModal(classIdentifier, title, url, sectionId, data);
                                    $('#fileUploadModal').modal('show');
                                    break;
                                case "{{ Element::TYPE_DATE_TIME }}":
                                    populateDateTimePickerModal(classIdentifier, title, url, sectionId, data);
                                    $('#dateTimePickerModal').modal('show');
                                    break;
                            }
                        } else {
                            switch(data.element_render_identifier.toString()) {
                                case "{{ ElementDefinition::TYPE_RADIOBOX }}":
                                    populateSystemRadioBoxModal(title, url, sectionId, data);
                                    $('#radioboxModal [data-component="other_option_section"]').hide();
                                    $('#radioboxModal').modal('show');
                                    break;
                                case "{{ ElementDefinition::TYPE_CHECKBOX }}":
                                    populateSystemCheckBoxModal(title, url, sectionId, data);
                                    $('#checkboxModal [data-component="other_option_section"]').hide();
                                    $('#checkboxModal').modal('show');
                                    break;
                                case "{{ ElementDefinition::TYPE_DROPDOWN }}":
                                    populateSystemDropdownModal(title, url, sectionId, data);
                                    $('#dropdownModal').modal('show');
                                    break;
                            }
                        }
                    },
                    error: function(jqXHR,textStatus, errorThrown ) {
                        // error
                        console.error(errorThrown);
                    }
                });
            });

            function populateInputTypeModal(classIdentifier, title, url, sectionId, data = null) {
                $('#textboxModal [data-action="submit"]').data('url', url);
                $('#textboxModal [data-action="submit"]').data('section_id', sectionId);

                switch(classIdentifier) {
                    case "{{ Element::TYPE_TEXT }}":
                        $('#textboxModal [name="class_identifier"]').val(classIdentifier);
                        $('#textboxModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_TEXT) }}");

                        break;
                    case "{{ Element::TYPE_EMAIL }}":
                        $('#textboxModal [name="class_identifier"]').val(classIdentifier);
                        $('#textboxModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_EMAIL) }}");

                        break;
                    case "{{ Element::TYPE_URL }}":
                        $('#textboxModal [name="class_identifier"]').val(classIdentifier);
                        $('#textboxModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_URL) }}");

                        break;
                }

                if(data) {
                    $('#textboxModal [name="label"]').val(data.label);
                    $('#textboxModal [name="instructions"]').val(data.instructions);      

                    for(attr in data.attributes) {
                        var node = $(`#textboxModal [name="${attr}"]`);

                        if(node && (attr != 'required')) {
                            $(`#textboxModal [name="${attr}"]`).val(data.attributes[attr]);
                        }
                    }

                    if(data.attributes.hasOwnProperty('required')) {
                        $('#textboxModal [name="required"]').prop('checked', true);
                    }

                    if(data.hasOwnProperty('is_key_information') && data.is_key_information) {
                        $('#textboxModal [name="key_information"]').prop('checked', true);
                    }

                    if(data.hasOwnProperty('has_attachments') && data.has_attachments) {
                        $('#textboxModal [name="attachments"]').prop('checked', true);
                    }
                }
            }

            function populateTextareaModal(classIdentifier, title, url, sectionId, data = null) {
                $('#textareaModal [data-action="submit"]').data('url', url);
                $('#textareaModal [data-action="submit"]').data('section_id', sectionId);
                $('#textareaModal [name="class_identifier"]').val(classIdentifier);
                $('#textareaModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_MULTILINE_TEXT) }}");
                

                if(data) {
                    $('#textareaModal [name="label"]').val(data.label);
                    $('#textareaModal [name="instructions"]').val(data.instructions);

                    for(attr in data.attributes) {
                        var node = $(`#textareaModal [name="${attr}"]`);

                        if(node && (attr != 'required')) {
                            $(`#textareaModal [name="${attr}"]`).val(data.attributes[attr]);
                        }
                    }

                    if(data.attributes.hasOwnProperty('required')) {
                        $('#textareaModal [name="required"]').prop('checked', true);
                    }

                    if(data.hasOwnProperty('is_key_information') && data.is_key_information) {
                        $('#textareaModal [name="key_information"]').prop('checked', true);
                    }

                    if(data.hasOwnProperty('has_attachments') && data.has_attachments) {
                        $('#textareaModal [name="attachments"]').prop('checked', true);
                    }
                }
            }

            function populateNumberTextBoxModal(classIdentifier, title, url, sectionId, data = null)
            {
                $('#numberTextboxModal [data-action="submit"]').data('url', url);
                $('#numberTextboxModal [data-action="submit"]').data('section_id', sectionId);
                $('#numberTextboxModal [name="class_identifier"]').val(classIdentifier);
                $('#numberTextboxModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_NUMBER) }}");
                

                if(data) {
                    $('#numberTextboxModal [name="label"]').val(data.label);
                    $('#numberTextboxModal [name="instructions"]').val(data.instructions);

                    for(attr in data.attributes) {
                        var node = $(`#numberTextboxModal [name="${attr}"]`);

                        if(node && (attr != 'required')) {
                            $(`#numberTextboxModal [name="${attr}"]`).val(data.attributes[attr]);
                        }
                    }

                    if(data.attributes.hasOwnProperty('required')) {
                        $('#numberTextboxModal [name="required"]').prop('checked', true);
                    }

                    if(data.hasOwnProperty('is_key_information') && data.is_key_information) {
                        $('#numberTextboxModal [name="key_information"]').prop('checked', true);
                    }

                    if(data.hasOwnProperty('has_attachments') && data.has_attachments) {
                        $('#numberTextboxModal [name="attachments"]').prop('checked', true);
                    }
                }
            }

            function populateRadioBoxModal(classIdentifier, title, url, sectionId, data = null) {
                $('#radioboxModal [data-action="submit"]').data('url', url);
                $('#radioboxModal [data-action="submit"]').data('section_id', sectionId);
                $('#radioboxModal [name="class_identifier"]').val(classIdentifier);
                $('#radioboxModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_RADIO) }}");

                if(data) {
                    $('#radioboxModal [name="label"]').val(data.label);
                    $('#radioboxModal [name="instructions"]').val(data.instructions);

                    if(data.attributes.hasOwnProperty('required')) {
                        $('#radioboxModal [name="required"]').prop('checked', true);
                    }

                    if(data.hasOwnProperty('is_key_information') && data.is_key_information) {
                        $('#radioboxModal [name="key_information"]').prop('checked', true);
                    }

                    if(data.hasOwnProperty('has_attachments') && data.has_attachments) {
                        $('#radioboxModal [name="attachments"]').prop('checked', true);
                    }

                    var hasOtherOption = false;

                    for(index in data.children) {
                        var children = data.children[index];

                        if(children.isOtherOption == true) {
                            hasOtherOption = true;

                            continue;
                        }

                        radioboxModal_object.addEntry(children.label, children.attributes.value);
                    }

                    $('#radioboxModal [data-component="otherOption"]').prop('checked', hasOtherOption);
                }
            }

            function populateSystemRadioBoxModal(title, url, sectionId, data = null) {
                $('#radioboxModal [data-action="submit"]').data('url', url);
                $('#radioboxModal [data-action="submit"]').data('section_id', sectionId);
                $('#radioboxModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_RADIO) }}");
                $('#radioboxModal [name="label"]').val(data.label);
                $('#radioboxModal [data-component="custom_element_container"]').hide();
                $('#radioboxModal [data-component="element_type"]').val(data.module_class_identifer);
                $('#radioboxModal [name="instructions"]').val(data.instructions);

                if(data.attributes.hasOwnProperty('required')) {
                    $('#radioboxModal [name="required"]').prop('checked', true);
                }

                if(data.hasOwnProperty('is_key_information') && data.is_key_information) {
                    $('#radioboxModal [name="key_information"]').prop('checked', true);
                }

                if(data.hasOwnProperty('has_attachments') && data.has_attachments) {
                    $('#radioboxModal [name="attachments"]').prop('checked', true);
                }
            }

            function populateCheckBoxModal(classIdentifier, title, url, sectionId, data = null) {
                $('#checkboxModal [data-action="submit"]').data('url', url);
                $('#checkboxModal [data-action="submit"]').data('section_id', sectionId);
                $('#checkboxModal [name="class_identifier"]').val(classIdentifier);
                $('#checkboxModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_CHECKBOX) }}");

                if(data) {
                    $('#checkboxModal [name="label"]').val(data.label);
                    $('#checkboxModal [name="instructions"]').val(data.instructions);

                    if(data.attributes.hasOwnProperty('required')) {
                        $('#checkboxModal [name="required"]').prop('checked', true);
                    }

                    if(data.hasOwnProperty('is_key_information') && data.is_key_information) {
                        $('#checkboxModal [name="key_information"]').prop('checked', true);
                    }

                    if(data.hasOwnProperty('has_attachments') && data.has_attachments) {
                        $('#checkboxModal [name="attachments"]').prop('checked', true);
                    }

                    var hasOtherOption = false;

                    for(index in data.children) {
                        var children = data.children[index];

                        if(children.isOtherOption == true) {
                            hasOtherOption = true;

                            continue;
                        }

                        checkboxModal_object.addEntry(children.label, children.attributes.value);
                    }

                    $('#checkboxModal [data-component="otherOption"]').prop('checked', hasOtherOption);
                }
            }

            function populateSystemCheckBoxModal(title, url, sectionId, data = null) {
                $('#checkboxModal [data-action="submit"]').data('url', url);
                $('#checkboxModal [data-action="submit"]').data('section_id', sectionId);
                $('#checkboxModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_CHECKBOX) }}");
                $('#checkboxModal [name="label"]').val(data.label);
                $('#checkboxModal [data-component="custom_element_container"]').hide();
                $('#checkboxModal [data-component="element_type"]').val(data.module_class_identifer);
                $('#checkboxModal [name="instructions"]').val(data.instructions);

                if(data.attributes.hasOwnProperty('required')) {
                    $('#checkboxModal [name="required"]').prop('checked', true);
                }

                if(data.hasOwnProperty('is_key_information') && data.is_key_information) {
                    $('#checkboxModal [name="key_information"]').prop('checked', true);
                }

                if(data.hasOwnProperty('has_attachments') && data.has_attachments) {
                    $('#checkboxModal [name="attachments"]').prop('checked', true);
                }
            }

            function populateDropdownModal(classIdentifier, title, url, sectionId, data = null) {
                $('#dropdownModal [data-action="submit"]').data('url', url);
                $('#dropdownModal [data-action="submit"]').data('section_id', sectionId);
                $('#dropdownModal [name="class_identifier"]').val(classIdentifier);
                $('#dropdownModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_DROPDOWN) }}");

                if(data) {
                    $('#dropdownModal [name="label"]').val(data.label);
                    $('#dropdownModal [name="instructions"]').val(data.instructions);

                    if(data.attributes.hasOwnProperty('required')) {
                        $('#dropdownModal [data-control="required_checkbox"]').prop('checked', true);
                    } else {
                        $('#dropdownModal [data-control="required_checkbox"]').prop('checked', false);
                    }

                    if(data.attributes.hasOwnProperty('multiple')) {
                        $('#dropdownModal [data-component="dropdown_select_type"]').val("{{ FormBuilderElementCommon::DROPDOWN_MULTIPLE_SELECT }}");
                    } else {
                        $('#dropdownModal [data-component="dropdown_select_type"]').val("{{ FormBuilderElementCommon::DROPDOWN_SINGLE_SELECT }}");
                    }

                    if(data.hasOwnProperty('is_key_information') && data.is_key_information) {
                        $('#dropdownModal [name="key_information"]').prop('checked', true);
                    }

                    if(data.hasOwnProperty('has_attachments') && data.has_attachments) {
                        $('#dropdownModal [name="attachments"]').prop('checked', true);
                    }

                    for(index in data.children) {
                        var children = data.children[index];

                        dropdownModal_object.addEntry(children.label, children.attributes.value);
                    }
                }
            }

            function populateSystemDropdownModal(title, url, sectionId, data = null) {
                $('#dropdownModal [data-action="submit"]').data('url', url);
                $('#dropdownModal [data-action="submit"]').data('section_id', sectionId);
                $('#dropdownModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_DROPDOWN) }}");
                $('#dropdownModal [name="label"]').val(data.label);
                $('#dropdownModal [data-component="custom_element_container"]').hide();
                $('#dropdownModal [data-component="element_type"]').val(data.module_class_identifer);
                $('#dropdownModal [name="instructions"]').val(data.instructions);

                if(data.attributes.hasOwnProperty('required')) {
                    $('#dropdownModal [data-control="required_checkbox"]').prop('checked', true);
                } else {
                    $('#dropdownModal [data-control="required_checkbox"]').prop('checked', false);
                }

                if(data.attributes.hasOwnProperty('multiple')) {
                    $('#dropdownModal [data-component="dropdown_select_type"]').val("{{ FormBuilderElementCommon::DROPDOWN_MULTIPLE_SELECT }}");
                } else {
                    $('#dropdownModal [data-component="dropdown_select_type"]').val("{{ FormBuilderElementCommon::DROPDOWN_SINGLE_SELECT }}");
                }

                if(data.hasOwnProperty('is_key_information') && data.is_key_information) {
                    $('#dropdownModal [name="key_information"]').prop('checked', true);
                }

                if(data.hasOwnProperty('has_attachments') && data.has_attachments) {
                    $('#dropdownModal [name="attachments"]').prop('checked', true);
                }
            }

            function populateFileUploadModal(classIdentifier, title, url, sectionId, data = null) {
                $('#fileUploadModal [data-action="submit"]').data('url', url);
                $('#fileUploadModal [data-action="submit"]').data('section_id', sectionId);
                $('#fileUploadModal [name="class_identifier"]').val(classIdentifier);
                $('#fileUploadModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_FILE_UPLOAD) }}");

                if(data) {
                    $('#fileUploadModal [name="label"]').val(data.label);
                    $('#fileUploadModal [name="instructions"]').val(data.instructions);

                    if(data.attributes.hasOwnProperty('required')) {
                        $('#fileUploadModal [data-control="required_checkbox"]').prop('checked', true);
                    } else {
                        $('#fileUploadModal [data-control="required_checkbox"]').prop('checked', false);
                    }

                    if(data.hasOwnProperty('is_key_information') && data.is_key_information) {
                        $('#fileUploadModal [name="key_information"]').prop('checked', true);
                    }
                }
            }

            function populateDateTimePickerModal(classIdentifier, title, url, sectionId, data = null) {
                $('#dateTimePickerModal [data-action="submit"]').data('url', url);
                $('#dateTimePickerModal [data-action="submit"]').data('section_id', sectionId);
                $('#dateTimePickerModal [name="class_identifier"]').val(classIdentifier);
                $('#dateTimePickerModal [data-control="modal_title"]').html(title + "{{ Element::getElementTypesByIdentifer(Element::TYPE_DATE_TIME) }}");

                if(data) {
                    $('#dateTimePickerModal [name="label"]').val(data.label);
                    $('#dateTimePickerModal [name="instructions"]').val(data.instructions);

                    var mode = data.attributes.hasOwnProperty('mode') ? data.attributes.mode : null;

                    if(mode) {
                        $('#dateTimePickerModal [data-component="date_time_picker_mode"]').val(mode);
                    }

                    if(data.attributes.hasOwnProperty('required')) {
                        $('#dateTimePickerModal [data-control="required_checkbox"]').prop('checked', true);
                    } else {
                        $('#dateTimePickerModal [data-control="required_checkbox"]').prop('checked', false);
                    }

                    if(data.hasOwnProperty('is_key_information') && data.is_key_information) {
                        $('#dateTimePickerModal [name="key_information"]').prop('checked', true);
                    }

                    if(data.hasOwnProperty('has_attachments') && data.has_attachments) {
                        $('#dateTimePickerModal [name="attachments"]').prop('checked', true);
                    }
                }
            }

            $('#textboxModal [data-action="submit"]').on('click', function(e) {
                e.preventDefault();

                var self = $(this);
                self.prop('disabled', true);

                var url = $(this).data('url');
                var sectionId = $(this).data('section_id');
                var formData = $('#textboxModal [data-control="form_body"]').serializeArray();
                var data = {};

                formData.forEach(function(item, index) {
                    data[item.name] = DOMPurify.sanitize(item.value.trim());
                });

                data['element_type'] = "{{ Element::ELEMENT_TYPE_ID }}";
                data['section_id'] = sectionId;
                data['_token'] = '{{{ csrf_token() }}}';

                $.ajax({
                    url: url,
                    method: "POST",
                    data: data,
                    success: function(response) {
                        if(response.success) {
                            $('#textboxModal').modal('hide');
                            window.location.reload();
                        } else {
                            $('#textboxModal [data-control="label-error"]').text(response.errors.label[0]);
                            self.prop('disabled', false);
                        }
                    },
                    error: function(jqXHR,textStatus, errorThrown ) {
                        // error
                        console.error(errorThrown);
                    }
                });
            });

            $('#textareaModal [data-action="submit"]').on('click', function(e) {
                e.preventDefault();

                var url = $(this).data('url');
                var sectionId = $(this).data('section_id');
                var formData = $('#textareaModal [data-control="form_body"]').serializeArray();
                var data = {};

                var self = $(this);
                self.prop('disabled', true);

                formData.forEach(function(item, index) {
                    data[item.name] = DOMPurify.sanitize(item.value.trim());
                });

                data['element_type'] = "{{ Element::ELEMENT_TYPE_ID }}";
                data['section_id']   = sectionId;
                data['_token']       = '{{{ csrf_token() }}}';

                $.ajax({
                    url: url,
                    method: "POST",
                    data: data,
                    success: function(response) {
                        if(response.success) {
                            $('#textareaModal').modal('hide');
                            window.location.reload();
                        } else {
                            $('#textareaModal [data-control="label-error"]').text(response.errors.label[0]);
                            self.prop('disabled', false);
                        }
                    },
                    error: function(jqXHR,textStatus, errorThrown ) {
                        // error
                        console.error(errorThrown);
                    }
                });
            });

            $('#numberTextboxModal [data-action="submit"]').on('click', function(e) {
                e.preventDefault();

                var self = $(this);
                self.prop('disabled', true);

                var url = $(this).data('url');
                var sectionId = $(this).data('section_id');
                var formData = $('#numberTextboxModal [data-control="form_body"]').serializeArray();
                var data = {};

                formData.forEach(function(item, index) {
                    data[item.name] = DOMPurify.sanitize(item.value.trim());
                });

                data['element_type'] = "{{ Element::ELEMENT_TYPE_ID }}";
                data['section_id']   = sectionId;
                data['_token'] = '{{{ csrf_token() }}}';

                $.ajax({
                    url: url,
                    method: "POST",
                    data: data,
                    success: function(response) {
                        if(response.success) {
                            $('#numberTextboxModal').modal('hide');
                            window.location.reload();
                        } else {
                            $('#numberTextboxModal [data-control="label-error"]').text(response.errors.label[0]);
                            self.prop('disabled', false);
                        }
                    },
                    error: function(jqXHR,textStatus, errorThrown ) {
                        // error
                        console.error(errorThrown);
                    }
                });
            });

            $('#radioboxModal [data-action="submit"]').on('click', function(e) {
                var url                 = $(this).data('url');
                var sectionId           = $(this).data('section_id');
                var selectedElementType = $('#radioboxModal [data-component="element_type"] option:selected').val();
                var formData            = {};

                var self = $(this);
                self.prop('disabled', true);

                var serializedData = $('#radioboxModal [data-control="form_body"]').serializeArray();

                $(serializedData).each(function(i, field){
                    formData[field.name] = DOMPurify.sanitize(field.value);
                });

                delete formData['class_identifier'];

                var elementType = (selectedElementType.toString() == "{{ Element::ELEMENT_TYPE_ID }}".toString()) ? "{{ Element::ELEMENT_TYPE_ID }}" : "{{ SystemModuleElement::ELEMENT_TYPE_ID }}";
                
                formData['element_type'] = elementType;
                formData['section_id'] = sectionId;
                formData['_token'] = '{{{ csrf_token() }}}';
                formData['class_identifier'] = "{{ Element::TYPE_RADIO }}";

                if(elementType == "{{ SystemModuleElement::ELEMENT_TYPE_ID }}") {
                    formData['element_render_identifier'] = "{{ ElementDefinition::TYPE_RADIOBOX }}";
                    formData['system_module_identifier'] = selectedElementType;
                } else {
                    var entryContainer = $('#radioboxModal').find('div[data-control="entry_container"]');
                    var originalInput  = $('#radioboxModal').find('input[data-control="original"]');

                    var existingItems = {};
                    var newItems      = [];
                    
                    entryContainer.children().each(function() {
                        var radioNode = $(this).find('input[data-control="element"]');
                        var itemId = radioNode.data('id');
                        var label  = DOMPurify.sanitize(radioNode.val().trim());

                        if(itemId) {
                            existingItems[itemId] = label;
                        } else {
                            newItems.push(label);
                        }
                    });

                    if(originalInput.val().trim() != '') {
                        newItems.push(DOMPurify.sanitize(originalInput.val().trim()));
                    }

                    formData['existingItems'] = existingItems;
                    formData['newItems'] = newItems;
                }

                $.ajax({
                    url: url,
                    method: "POST",
                    data: formData,
                    success: function(response) {
                        if(response.success) {
                            $('#radioboxModal').modal('hide');
                            window.location.reload();
                        } else {
                            $('#radioboxModal [data-control="label-error"]').text(response.errors.label[0]);
                            self.prop('disabled', false);
                        }
                    },
                    error: function(jqXHR,textStatus, errorThrown ) {
                        // error
                        console.error(errorThrown);
                    }
                });
            });

            $('#checkboxModal [data-action="submit"]').on('click', function(e) {
                var url                 = $(this).data('url');
                var sectionId           = $(this).data('section_id');
                var selectedElementType = $('#checkboxModal [data-component="element_type"] option:selected').val();
                var formData            = {};

                var self = $(this);
                self.prop('disabled', true);

                var serializedData = $('#checkboxModal [data-control="form_body"]').serializeArray();

                $(serializedData).each(function(i, field){
                    formData[field.name] = DOMPurify.sanitize(field.value);
                });

                delete formData['class_identifier'];

                var elementType = (selectedElementType.toString() == "{{ Element::ELEMENT_TYPE_ID }}".toString()) ? "{{ Element::ELEMENT_TYPE_ID }}" : "{{ SystemModuleElement::ELEMENT_TYPE_ID }}";
                
                formData['element_type'] = elementType;
                formData['section_id'] = sectionId;
                formData['_token'] = '{{{ csrf_token() }}}';
                formData['class_identifier'] = "{{ Element::TYPE_CHECKBOX }}";

                if(elementType == "{{ SystemModuleElement::ELEMENT_TYPE_ID }}") {
                    formData['element_render_identifier'] = "{{ ElementDefinition::TYPE_CHECKBOX }}";
                    formData['system_module_identifier'] = selectedElementType;
                } else {
                    var entryContainer = $('#checkboxModal').find('div[data-control="entry_container"]');
                    var originalInput  = $('#checkboxModal').find('input[data-control="original"]');

                    var existingItems = {};
                    var newItems      = [];
                    
                    entryContainer.children().each(function() {
                        var checkboxNode = $(this).find('input[data-control="element"]');
                        var itemId = checkboxNode.data('id');
                        var label  = DOMPurify.sanitize(checkboxNode.val().trim());

                        if(itemId) {
                            existingItems[itemId] = label;
                        } else {
                            newItems.push(label);
                        }
                    });

                    if(originalInput.val().trim() != '') {
                        newItems.push(DOMPurify.sanitize(originalInput.val().trim()));
                    }

                    formData['existingItems'] = existingItems;
                    formData['newItems'] = newItems;
                }

                $.ajax({
                    url: url,
                    method: "POST",
                    data: formData,
                    success: function(response) {
                        if(response.success) {
                            $('#checkboxModal').modal('hide');
                            window.location.reload();
                        } else {
                            $('#checkboxModal [data-control="label-error"]').text(response.errors.label[0]);
                            self.prop('disabled', false);
                        }
                    },
                    error: function(jqXHR,textStatus, errorThrown ) {
                        // error
                        console.error(errorThrown);
                    }
                });
            });

            $('#dropdownModal [data-action="submit"]').on('click', function(e) {
                var url                 = $(this).data('url');
                var sectionId           = $(this).data('section_id');
                var selectedElementType = $('#dropdownModal [data-component="element_type"] option:selected').val();
                var formData            = {};

                var self = $(this);
                self.prop('disabled', true);

                var serializedData = $('#dropdownModal [data-control="form_body"]').serializeArray();

                $(serializedData).each(function(i, field){
                    formData[field.name] = DOMPurify.sanitize(field.value);
                });

                delete formData['class_identifier'];

                var elementType = (selectedElementType.toString() == "{{ Element::ELEMENT_TYPE_ID }}".toString()) ? "{{ Element::ELEMENT_TYPE_ID }}" : "{{ SystemModuleElement::ELEMENT_TYPE_ID }}";
                
                formData['element_type'] = elementType;
                formData['section_id'] = sectionId;
                formData['_token'] = '{{{ csrf_token() }}}';
                formData['class_identifier'] = "{{ Element::TYPE_DROPDOWN }}";


                if(elementType == "{{ SystemModuleElement::ELEMENT_TYPE_ID }}") {
                    formData['element_render_identifier'] = "{{ ElementDefinition::TYPE_DROPDOWN }}";
                    formData['system_module_identifier'] = selectedElementType;
                } else {
                    var entryContainer = $('#dropdownModal').find('div[data-control="entry_container"]');
                    var originalInput  = $('#dropdownModal').find('input[data-control="original"]');

                    var existingItems = {};
                    var newItems      = [];
                    
                    entryContainer.children().each(function() {
                        var checkboxNode = $(this).find('input[data-control="element"]');
                        var itemId = checkboxNode.data('id');
                        var label  = DOMPurify.sanitize(checkboxNode.val().trim());

                        if(itemId) {
                            existingItems[itemId] = label;
                        } else {
                            newItems.push(label);
                        }
                    });

                    if(originalInput.val().trim() != '') {
                        newItems.push(DOMPurify.sanitize(originalInput.val().trim()));
                    }

                    formData['existingItems'] = existingItems;
                    formData['newItems'] = newItems;
                }

                $.ajax({
                    url: url,
                    method: "POST",
                    data: formData,
                    success: function(response) {
                        if(response.success) {
                            $('#dropdownModal').modal('hide');
                            window.location.reload();
                        } else {
                            $('#dropdownModal [data-control="label-error"]').text(response.errors.label[0]);
                            self.prop('disabled', false);
                        }
                    },
                    error: function(jqXHR,textStatus, errorThrown ) {
                        // error
                        console.error(errorThrown);
                    }
                });
            });

            $('#fileUploadModal [data-action="submit"]').on('click', function(e) {
                e.preventDefault();

                var url       = $(this).data('url');
                var sectionId = $(this).data('section_id');
                var formData  = $('#fileUploadModal [data-control="form_body"]').serializeArray();
                var data      = {};

                var self = $(this);
                self.prop('disabled', true);

                formData.forEach(function(item, index) {
                    data[item.name] = DOMPurify.sanitize(item.value.trim());
                });

                var fileUploadType = $('#fileUploadModal [data-component="file_upload_type"] option:selected').val();

                data['uploadType'] = fileUploadType;
                data['element_type'] = "{{ Element::ELEMENT_TYPE_ID }}";
                data['section_id'] = sectionId;
                data['_token'] = '{{{ csrf_token() }}}';

                $.ajax({
                    url: url,
                    method: "POST",
                    data: data,
                    success: function(response) {
                        if(response.success) {
                            $('#fileUploadModal').modal('hide');
                            window.location.reload();
                        } else {
                            $('#fileUploadModal [data-control="label-error"]').text(response.errors.label[0]);
                            self.prop('disabled', false);
                        }
                    },
                    error: function(jqXHR,textStatus, errorThrown ) {
                        // error
                        console.error(errorThrown);
                    }
                });
            });

            $('#dateTimePickerModal [data-action="submit"]').on('click', function(e) {
                e.preventDefault();

                var url       = $(this).data('url');
                var sectionId = $(this).data('section_id');
                var formData  = $('#dateTimePickerModal [data-control="form_body"]').serializeArray();
                var data      = {};

                var self = $(this);
                self.prop('disabled', true);

                formData.forEach(function(item, index) {
                    data[item.name] = DOMPurify.sanitize(item.value.trim());
                });

                var mode = $('#dateTimePickerModal [data-component="date_time_picker_mode"] option:selected').val();

                data['mode'] = mode;
                data['element_type'] = "{{ Element::ELEMENT_TYPE_ID }}";
                data['section_id'] = sectionId;
                data['_token'] = '{{{ csrf_token() }}}';

                $.ajax({
                    url: url,
                    method: "POST",
                    data: data,
                    success: function(response) {
                        if(response.success) {
                            $('#dateTimePickerModal').modal('hide');
                            window.location.reload();
                        } else {
                            $('#dateTimePickerModal [data-control="label-error"]').text(response.errors.label[0]);
                            self.prop('disabled', false);
                        }
                    },
                    error: function(jqXHR,textStatus, errorThrown ) {
                        // error
                        console.error(errorThrown);
                    }
                });
            });

            $(document).on('click', '[data-action="delete_element"]', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                $('#deleteElementModal [data-action="actionYes"]').data('url', url);
                $('#deleteElementModal').modal('show');
            });

            $('#deleteElementModal [data-action="actionYes"]').on('click', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                $.ajax({
                    url: url,
                    method: "POST",
                    data: {
                        _token: '{{{ csrf_token() }}}',
                    },
                    success: function(response) {
                        if(response.success) {
                            $('#yesNoModal').modal('hide');
                            window.location.reload();
                        }
                    },
                    error: function(jqXHR,textStatus, errorThrown ) {
                        // error
                        console.error(errorThrown);
                    }
                });
            });

            $('#textboxModal').on('hidden.bs.modal', function (e) {
                $('#textboxModal [name="label"]').val('');
                $('#textboxModal [data-control="required_checkbox"]').prop('checked', false);
                $('#textboxModal [data-control="key_information_checkbox"]').prop('checked', false);
                $('#textboxModal [data-control="attachments_checkbox"]').prop('checked', false);
                $('#textboxModal [data-control="label-error"]').text('');
                $('#textboxModal [name="instructions"]').val('');
            });

            $('#numberTextboxModal').on('hidden.bs.modal', function (e) {
                $('#numberTextboxModal [name="label"]').val('');
                $('#numberTextboxModal [data-control="required_checkbox"]').prop('checked', false);
                $('#numberTextboxModal [data-control="key_information_checkbox"]').prop('checked', false);
                $('#numberTextboxModal [data-control="attachments_checkbox"]').prop('checked', false);
                $('#numberTextboxModal [data-control="label-error"]').text('');
                $('#numberTextboxModal [name="min"]').val('');
                $('#numberTextboxModal [name="max"]').val('');
                $('#numberTextboxModal [name="instructions"]').val('');
            });

            $('#textareaModal').on('hidden.bs.modal', function (e) {
                $('#textareaModal [name="label"]').val('');
                $('#textareaModal [data-control="required_checkbox"]').prop('checked', false);
                $('#textareaModal [data-control="key_information_checkbox"]').prop('checked', false);
                $('#textareaModal [data-control="attachments_checkbox"]').prop('checked', false);
                $('#textareaModal [data-control="label-error"]').text('');
                $('#textareaModal [name="instructions"]').val('');
            });

            $('#radioboxModal').on('hidden.bs.modal', function (e) {
                $('#radioboxModal [name="label"]').val('');
                $('#radioboxModal input[data-control="original"]').val('');
                $('#radioboxModal div[data-control="entry_container"]').empty();
                $('#radioboxModal [data-component="custom_element_container"]').show();
                $('#radioboxModal [data-component="element_type"]').val("{{ Element::ELEMENT_TYPE_ID }}");
                $('#radioboxModal [data-control="required_checkbox"]').prop('checked', false);
                $('#radioboxModal [data-control="key_information_checkbox"]').prop('checked', false);
                $('#radioboxModal [data-control="attachments_checkbox"]').prop('checked', false);
                $('#radioboxModal [data-control="label-error"]').text('');
                $('#radioboxModal [data-component="otherOption"]').prop('checked', false);
                $('#radioboxModal [name="instructions"]').val('');
            });

            $('#checkboxModal').on('hidden.bs.modal', function (e) {
                $('#checkboxModal [name="label"]').val('');
                $('#checkboxModal input[data-control="original"]').val('');
                $('#checkboxModal div[data-control="entry_container"]').empty();
                $('#checkboxModal [data-component="custom_element_container"]').show();
                $('#checkboxModal [data-component="element_type"]').val("{{ Element::ELEMENT_TYPE_ID }}");
                $('#checkboxModal [data-control="required_checkbox"]').prop('checked', false);
                $('#checkboxModal [data-control="key_information_checkbox"]').prop('checked', false);
                $('#checkboxModal [data-control="attachments_checkbox"]').prop('checked', false);
                $('#checkboxModal [data-control="label-error"]').text('');
                $('#checkboxModal [data-component="otherOption"]').prop('checked', false);
                $('#checkboxModal [name="instructions"]').val('');
            });

            $('#dropdownModal').on('hidden.bs.modal', function (e) {
                $('#dropdownModal [name="label"]').val('');
                $('#dropdownModal input[data-control="original"]').val('');
                $('#dropdownModal div[data-control="entry_container"]').empty();
                $('#dropdownModal [data-component="dropdown_select_type"]').val("{{ FormBuilderElementCommon::DROPDOWN_SINGLE_SELECT }}");
                $('#dropdownModal [data-component="custom_element_container"]').show();
                $('#dropdownModal [data-component="element_type"]').val("{{ Element::ELEMENT_TYPE_ID }}");
                $('#dropdownModal [data-control="required_checkbox"]').prop('checked', false);
                $('#dropdownModal [data-control="key_information_checkbox"]').prop('checked', false);
                $('#dropdownModal [data-control="attachments_checkbox"]').prop('checked', false);
                $('#dropdownModal [data-control="label-error"]').text('');
                $('#dropdownModal [name="instructions"]').val('');
            });

            $('#fileUploadModal').on('hidden.bs.modal', function (e) {
                $('#fileUploadModal [name="label"]').val('');
                $('#fileUploadModal [data-control="required_checkbox"]').prop('checked', false);
                $('#fileUploadModal [data-control="label-error"]').text('');
                $('#fileUploadModal [data-component="otherOption"]').prop('checked', false);
                $('#fileUploadModal [data-control="key_information_checkbox"]').prop('checked', false);
                $('#fileUploadModal [name="instructions"]').val('');
            });

            $('#dateTimePickerModal').on('hidden.bs.modal', function (e) {
                $('#dateTimePickerModal [name="label"]').val('');
                $('#dateTimePickerModal [data-component="date_time_picker_mode"]').val("{{ DateTimePicker::MODE_BOTH }}");
                $('#dateTimePickerModal [data-control="required_checkbox"]').prop('checked', false);
                $('#dateTimePickerModal [data-control="label-error"]').text('');
                $('#dateTimePickerModal [data-component="otherOption"]').prop('checked', false);
                $('#dateTimePickerModal [data-control="key_information_checkbox"]').prop('checked', false);
                $('#dateTimePickerModal [data-control="attachments_checkbox"]').prop('checked', false);
                $('#dateTimePickerModal [name="instructions"]').val('');
            });
            /**create and edit element ends */

            /**pop-up editor */
            $('#editorModal').on('shown.bs.modal', function (e) {
                selectInputField();
                disableSubmit(false);
            });

			function selectInputField() {
                $('#template-name-input').select();
            }

			function disableSubmit(disable) {
                $('#submit-button').prop('disabled', disable);
            }

			$(document).on('click', '#submit-button', function () {
                disableSubmit(true);
                submit($(this).data('url'), getInputValue());
            });

			function changeEditorModalTitle(title) {
                $('#inputEditorLabel').text(title);
            }

			function setInputValue(name) {
                $('#template-name-input').val(name);
            }

			function getInputValue() {
                return $('#template-name-input').val();
            }

			function setSubmitButtonURL(url) {
				$('#submit-button').data('url', url);
			}

			function getSubmitButtonURL() {
				return $('#submit-button').data('url');
			}

			function showInputModal() {
                $('#editorModal').modal('show');
            }

			function hideInputModal() {
				$('#editorModal').modal('hide');
			}

			/* Errors */
			function setTemplateNameError(error) {
                $('#template-name-error').text(error);
            }

			/* Create */
            $(document).on('click', '[data-action="create_resource"]', function (e) {
				e.preventDefault();

                var url = $(this).data('url');
                var title = $(this).data('title');

                changeEditorModalTitle(title);
				setInputValue('');
				setTemplateNameError('');
				setSubmitButtonURL(url);
            });

			/* Edit */
			$(document).on('click', '[data-action="edit_resource"]', function(e) {
				e.preventDefault();

                var title = $(this).data('title');
                var value = $(this).data('value');
                var url   = $(this).data('url');

				changeEditorModalTitle(title);
				setInputValue(value);
				setSubmitButtonURL(url);
				setTemplateNameError('');
				showInputModal();
			});

            /**delete section */
            $(document).on('click', '[data-action="delete_section"]', function(e) {
                e.preventDefault();

                $('#deleteSectionModal button[data-action="actionYes"]').data('url', $(this).data('url'));
                $('#deleteSectionModal').modal('show');
            });

            $(document).on('click', '#deleteSectionModal button[data-action="actionYes"]', function(e) {
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
                            window.location.reload();
                        }
                    },
                    error: function(jqXHR,textStatus, errorThrown ) {
                        // error
                        console.error(errorThrown);
                    },
                });
            });
            /**delete section ends */

            /**delete sub section */
            $(document).on('click', '[data-action="delete_sub_section"]', function(e) {
                e.preventDefault();

                $('#deleteSubSectionModal button[data-action="actionYes"]').data('url', $(this).data('url'));
                $('#deleteSubSectionModal').modal('show');
            });

            $(document).on('click', '#deleteSubSectionModal button[data-action="actionYes"]', function(e) {
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
                            window.location.reload();
                        }
                    },
                    error: function(jqXHR,textStatus, errorThrown ) {
                        // error
                        console.error(errorThrown);
                    },
                });
            });
            /**delete section ends */

			function submit(url, inputValue) {
                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        name: inputValue.trim(),
                        _token: '{{{ csrf_token() }}}',
                    },
                    success: function (data) {
                        if (data['success']) {
                            hideInputModal();
							window.location.reload();
                        }
                        else {
                            setTemplateNameError(data['errors']['name']);
                            disableSubmit(false);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }
            /**pop-up editor ends*/
            @endif

            /**form design approval */
            $(document).on('click', '[data-action="submit_form_design_for_approval"]', function(e) {
                e.preventDefault();

                $('#selectVerifiersModal').modal('show');
            });

            function noVerifier(e){
                var form = $(e.target).closest('form');
                var input = form.find('[name="verifiers[]"]').serializeArray();

                return !input.some(function(element){
                    return (element.value > 0);
                });
            }

            $('#btnSubmitFormDesignForApproval').on('click', function(e) {
                if(noVerifier(e)) {
                    $('#warningModal').modal('show');
                    return false;
                }
            });

            @if($canApproveFormDesign)
            $('#verifierForm button[name="approve"], #verifierForm button[name="reject"]').on('click', function(e) {
				e.preventDefault();

				if(this.name == 'reject') {
					$('#verifierRejectModal').modal('show');           
				}

				if(this.name == 'approve') {
					$('#verifierApproveModal').modal('show');
                } 
			});

            $('#verifierApproveModal button[type="submit"]').on('click', function(e) {
                e.preventDefault();

                var input = $("<input>").attr("type", "hidden").attr("name", "approve").val(1);
                $('#verifierForm').append(input);

                var remarks = $('#verifierForm').append($("<input>").attr("type", "hidden").attr("name", "verifier_remarks").val($('#verifierApproveModal [name="verifier_remarks"]').val()));
                $('#verifierForm').append(remarks);

                $('#verifierForm').submit();
            });

            $('#verifierRejectModal button[type="submit"]').on('click', function(e) {
                e.preventDefault();

                var remarks = $('#verifierForm').append($("<input>").attr("type", "hidden").attr("name", "verifier_remarks").val($('#verifierRejectModal [name="verifier_remarks"]').val()));
                $('#verifierForm').append(remarks);

                $('#verifierForm').submit();
            });
            @endif
            /**form design approval ends */

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
        });
    </script>
@endsection