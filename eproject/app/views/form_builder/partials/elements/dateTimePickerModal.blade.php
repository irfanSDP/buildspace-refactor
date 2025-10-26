<?php use PCK\FormBuilder\Elements\DateTimePicker; ?>
<?php $modalId = isset($modalId) ? $modalId : 'dateTimePickerModal'; ?>
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="editorLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
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
                        <div class="row">
                            <section class="col col-9">
                                <label class="label">{{ trans('formBuilder.label') }}</label>
                                <label class="input">
                                    <input type="text" name="label">
                                    <em data-control="label-error" style="color:#F00;"></em>
                                </label>
                            </section>
                            <section class="col col-3">
                                <label class="label">{{ trans('formBuilder.type') }}</label>
                                <label class="fill-horizontal">
                                    <select class="form-control" data-component="date_time_picker_mode">
                                    @foreach(DateTimePicker::getDateTimePickerModes() as $value => $description)
                                        <option value="{{ $value }}">{{ $description }}</option>
                                    @endforeach
                                    </select>
                                </label>
                            </section>
                        </div>
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
                    </fieldset>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" data-action="submit">{{{ trans('forms.save') }}}</button>
            </div>
        </div>
    </div>
</div>