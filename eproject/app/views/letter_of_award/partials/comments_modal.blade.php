<?php $canAddComments = $canAddComments ?? true; ?>
<div id="letter_of_award_clause_comment_modal" class="modal fade" role="dialog">
    <div class="modal-dialog" style="width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{ trans('letterOfAward.comments') }}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                @if ($canAddComments)
                    <div id="divCommentBox" class="well">
                        <textarea name="txtComments" id="txtComments" cols="30" rows="5" style="width:100%;border:1px solid #000;"></textarea>
                        <button type="button" id="btnPostComment" class="btn btn-primary" style="border: 1px solid #FFF;">{{ trans('letterOfAward.postComment') }}</button>
                    </div>
                @endif
                <table id="commentsTable" class="table  smallFont" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="text-middle text-center">{{ trans('letterOfAward.comments') }}</th>
                            <th class="text-middle text-center squeeze">{{ trans('letterOfAward.commentor') }}</th>
                            <th class="text-middle text-center squeeze">{{ trans('letterOfAward.date') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">{{ trans('letterOfAward.close') }}</button>
            </div>
        </div>
    </div>
</div>