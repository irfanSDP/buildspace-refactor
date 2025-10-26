<div class="modal fade" id="acknowledgementLetterPreviewModal" tabindex="-1" role="dialog" aria-labelledby="acknowledgementLetterPreviewLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-grey-e">
                <h6 class="modal-title" id="acknowledgementLetterPreviewLabel">
                    {{ trans('tenders.acknowledgementLetterPreview') }}
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                @include('tenders.template.acknowledgement_letter_template', array(
                    'projectTitle'                 => $project->title,
                    'companyName'                  => '[Tenderer Company Name]',
                    'dateTime'                     => '[Printing Date & Time]',
                    'content'                      => null
                ))
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" id="acknowledgement-letter-save-as-draft"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                <button class="btn btn-default" data-dismiss="modal" aria-hidden="true" data-toggle="modal" data-target="#acknowledgementLetterModal">{{ trans('forms.back') }}</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->