<?php $modalId = isset($modalId) ? $modalId : 'technicalEvaluationAttachmentUploadModal' ?>
<?php $editable = isset($editable) ? $editable : true ?>

<div class="modal scrollable-modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-type="formModal"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <i class="fa fa-paperclip"></i>
                    {{{ trans('technicalEvaluation.attachments') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            </div>

            {{ Form::open(array('route' => array('technicalEvaluation.attachments.upload', $project->id, $company->id), 'files' => true)) }}
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="item_table">
                            <thead>
                            <tr>
                                <th style="width:20px" class="text-center">{{{ trans('technicalEvaluation.no') }}}</th>
                                <th style="width:auto" class="text-center">{{{ trans('technicalEvaluation.name') }}}</th>
                                <th style="" class="text-center occupy-min">{{{ trans('technicalEvaluation.mandatory') }}}</th>
                                <th style="" colspan="2" class="text-center occupy-min">{{{ trans('technicalEvaluation.uploadedFile') }}}</th>
                                @if($editable)
                                    <th style="" class="text-center" style="width:80px;">{{{ trans('technicalEvaluation.upload') }}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            <?php $index = 1; ?>
                            @foreach($setReference->attachmentListItems as $listItem)
                                <tr class="{{{ ($listItem->compulsory && (!$listItem->attachmentSubmitted($company))) ? 'warning' : '' }}}">
                                    <td class="text-center text-middle">
                                        {{{ $index++ }}}
                                    </td>
                                    <td class="text-left text-middle">
                                        {{{ $listItem->description }}}
                                    </td>
                                    <td class="text-center text-middle">
                                        @if($listItem->compulsory)
                                            <i class="fa fa-check text-success"></i>
                                        @endif
                                    </td>
                                    @if($attachment = $listItem->getCompanyAttachment($company))
                                        <td class="text-right occupy-min" @if(!$editable) colspan="2" @endif>
                                            <a href="{{ route('technicalEvaluation.results.fileDownload', array($project->id, $company->id, $attachment->id)) }}" class="plain"
                                               data-toggle="tooltip" data-placement="top" title="{{{ $attachment->getPresentableFileName() }}}">
                                                {{{ $attachment->getPresentableFileName(15) }}}
                                            </a>
                                            </td>
                                            @if($editable)
                                            <td class="text-center occupy-min">
                                                <a href="{{ route('technicalEvaluation.attachments.delete', array($project->id, $company->id, $attachment->id)) }}" class="btn btn-danger btn-xs"
                                                   data-method="delete"
                                                   data-csrf_token="{{{ csrf_token() }}}">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                            @endif
                                        </td>
                                    @else
                                        <td class="text-center occupy-min" colspan="2">
                                            -
                                        </td>
                                    @endif
                                    @if($editable)
                                        <td class="text-center text-middle">
                                            <section>
                                                <label class="input">
                                                    {{ Form::file('attachments['.$listItem->id.']', array('style' => 'height:100%')) }}
                                                </label>
                                            </section>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($editable)
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-upload"></i>
                            {{ trans('technicalEvaluation.uploadAttachments') }}
                        </button>
                    </div>
                @endif
            {{ Form::close() }}
        </div>
    </div>
</div>

<!-- Javascript -->
<script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>