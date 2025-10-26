<?php $modalId        = isset($modalId) ? $modalId : 'multiInputModal'; ?>
<?php $isDropdown     = isset($isDropdown) ? $isDropdown : false; ?>
<?php $hasOtherOption = isset($hasOtherOption) ? $hasOtherOption : false; ?>
<?php use PCK\FormBuilder\Elements\SystemModuleElement; ?>
<?php use PCK\FormBuilder\Elements\Element; ?>
<?php use PCK\FormBuilder\Elements\FormBuilderElementCommon; ?>

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" style="overflow-y:auto;" role="dialog" aria-labelledby="editorLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header bg-grey-e">
                <h6 data-control="modal_title"></h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body">
                <form class="smart-form" data-control="form_body">
                    <input type="hidden" name="class_identifier">
                    <fieldset>
                        <section>
                            <label class="label">{{ trans('formBuilder.label') }}</label>
                            <label class="input">
                                <input type="text" name="label">
                                <em data-control="label-error" style="color:#F00;"></em>
                            </label>
                        </section>
                        <div class="row">
                            <section class="col col-xs-12">
                                <label class="label">{{ trans('formBuilder.instructions') }}</label>
                                <label class="textarea">
                                    <textarea rows="5" name="instructions" style="height: 100%; resize: none; white-space: pre; overflow-x: scroll;"></textarea>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-xs-6">
                                <label class="checkbox">
                                    <input type="checkbox" name="required" data-control="required_checkbox"><i></i>{{ trans('formBuilder.required') }}
                                </label>
                            </section>
                            <section class="col col-xs-6">
                                <label class="checkbox">
                                    <input type="checkbox" name="key_information" data-control="key_information_checkbox"><i></i>{{ trans('formBuilder.keyInformation') }}
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-6">
                                <label class="checkbox">
                                    <input type="checkbox" name="attachments" data-control="attachments_checkbox"><i></i>{{ trans('general.attachments') }}
                                </label>
                            </section>
                        </div>
                        <hr/>
                        <div class="row">
                             @if($isDropdown)
                             <section class="col col-xs-12 col-md-12 col-lg-6">
                                 <label class="label">{{ trans('formBuilder.selectionType') }}</label>
                                 <select class="form-control" data-component="dropdown_select_type" name="dropdown_select_type">
                                     @foreach(FormBuilderElementCommon::getDropdownSelectTypes() as $id => $name)
                                         <option value="{{ $id }}">{{ $name }}</option>
                                     @endforeach
                                 </select>
                             </section>
                             @endif
                             <section class="col col-xs-12 col-md-12 col-lg-6">
                                 <label class="label">{{ trans('formBuilder.type') }}</label>
                                 <select class="form-control" data-component="element_type" name="element_type">
                                     <option value="{{ Element::ELEMENT_TYPE_ID }}">{{ trans('formBuilder.custom') }}</option>
                                     @foreach(SystemModuleElement::getSystemModuleElementTypeByIdentifier() as $identifier => $elementName)
                                         <option value="{{ $identifier }}">{{ $elementName }}</option>
                                     @endforeach
                                 </select>
                             </section>
                        </div>
                        <div data-component="custom_element_container">
                            <p>
                                <label class="label">{{ trans('formBuilder.items') }}</label>
                            </p>
                            <div data-control="entry_container"></div>
                            <div class="row" data-control="original_input_div">
                                <section class="form-group col col-md-11 col-lg-11">
                                    <label class="input">
                                        <input type="text" name="" value="" data-control="original">
                                    </label>
                                </section>
                                <section class="col col-1">
                                    <label class="input">
                                        <button type="button" class="btn btn-success pull-right rounded" data-action="add_entry"><i class="fa fa-plus"></i></button>
                                    </label>
                                </section>
                            </div>
                            <div class="row" data-control="add_entry_template" style="display:none;">
                                <section class="form-group col col-md-11 col-lg-11">
                                    <label class="input">
                                        <input type="text" name="" value="" data-control="element">
                                    </label>
                                </section>
                                <section class="col col-1">
                                    <label class="input">
                                        <button type="button" class="btn btn-danger pull-right rounded" data-action="remove_entry"><i class="fa fa-minus"></i></button>
                                    </label>
                                </section>
                            </div>
                        </div>
                        @if($hasOtherOption)
                        <section data-component="other_option_section">
                            <label class="checkbox"><input type="checkbox" data-component="otherOption" name="otherOption"><i></i>{{ trans('formBuilder.addOtherOption') }}</label>
                        </section>
                        @endif
                    </fieldset>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" data-action="submit">{{{ trans('forms.save') }}}</button>
            </div>
        </div>
    </div>
</div>
<script>
    var {{{ $modalId }}}_object = {
        addEntry: function(value, id = null)
        {
            var newEntry = $('#{{ $modalId }} [data-control="add_entry_template"]').clone();
            newEntry.removeAttr('data-control');

            var entryContainer = $('#{{ $modalId }} [data-control="entry_container"]');

            var itemId = (id == null) ? '' : id;

            newEntry.find('input[data-control="element"]').data('id', itemId);
            newEntry.find('input[data-control="element"]').attr('value', value);

            newEntry.appendTo(entryContainer);
            newEntry.show();
        },
    };

    $('#{{ $modalId }} [data-action="add_entry"]').on('click', function(e) {
        e.preventDefault();

        var originalInput = $(this).closest('[data-control="original_input_div"]').find('input[data-control="original"]');

        {{{ $modalId }}}_object.addEntry(originalInput.val());

        originalInput.val('');
    });

    $(document).on('click', '#{{ $modalId }} [data-action="remove_entry"]', function(e) {
        e.preventDefault();

        var node = $(this).closest('div.row');
        node.remove();
    });

    $('#{{ $modalId }} [data-component="element_type"]').on('click', function(e) {
        e.preventDefault();

        var selectionOption = $(this).find('option:selected').val();
        
        if(selectionOption == "{{ Element::ELEMENT_TYPE_ID }}") {
            $('#{{ $modalId }} [data-component="custom_element_container"]').show();
            @if($hasOtherOption)
                $('[data-component="other_option_section"]').show();
            @endif
        } else {
            $('#{{ $modalId }} [data-component="custom_element_container"]').hide();
            @if($hasOtherOption)
                $('[data-component="other_option_section"]').hide();
            @endif
        }
    });
</script>