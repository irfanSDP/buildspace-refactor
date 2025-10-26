<div id="emailComposer">
    <div class="modal fade" id="acknowledgementLetterModal" tabindex="-1" role="dialog" aria-labelledby="acknowledgementLetterModal" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-grey-e">
                    <h6 class="modal-title" id="acknowledgementLetterPreviewLabel">
                        {{ trans('tenders.acknowledgementLetter') }}
                    </h6>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                </div>
                <div class="modal-body">
                    <div class="col col-12 form-group">
                        <label for="message-input">{{ trans('tenders.thisLetterToBePrintedAsReference') }}:</label>
                        <div class="summernote" id="acknowledgement-letter-message-input">
                            {{ $tender->acknowledgementLetter ? $tender->acknowledgementLetter->letter_content : getenv('default_acknowledgement_letter_content') }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-sm-6" style="text-align: left">
                            <input type="checkbox" name="acknowledgement-letter-enable" id="acknowledgement-letter-enable" data-action="acknowledgement-letter-enable"> {{ trans('tenders.enableAcknowledgementLetter') }}
                        </div>
                        <div class="col-sm-6">
                            <button class="btn btn-primary" id="acknowledgement-letter-save-as-draft"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                            <button class="btn btn-info" id="acknowledgement-preview" data-toggle="modal" data-target="#acknowledgementLetterPreviewModal"><i class="fa fa-eye"></i> {{ trans('general.preview') }}</button>
                        </div>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
    @include('tenders.partials.acknowledgement_letter_modal_preview')
</div>