<div id="folderShareModal" class="modal fade">
    <div class="modal-dialog modal-dmf">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"
                    id="shareFolderLabel">{{ trans('documentManagementFolders.shareFolder') }}
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>
            <div class="modal-body">

                <div class="table-responsive">
                    <table class="table  table-condensed table-hover smart-form has-tickbox">
                        <thead>
                        <tr>
                            <th>
                                <label class="checkbox">
                                    <input type="checkbox" name="checkbox-inline" class="checkall"
                                           id="share-select_all_groups">
                                    <i></i> &nbsp;
                                </label>
                            </th>
                            <th>{{ trans('documentManagementFolders.group') }}</th>
                        </tr>
                        </thead>
                        <tbody>

                        <?php $noOfGroup = 0; ?>
                        @foreach($contractGroups as $contractGroup)
                            @if($myContractGroup->id != $contractGroup->id)
                                <?php $noOfGroup ++; ?>
                                <tr>
                                    <td>
                                        <label class="checkbox">
                                            <input type="checkbox" name="checkbox_inline_group_share_folder"
                                                   class="checkbox-select_group"
                                                   id="{{{$contractGroup->id}}}-checkbox_group_share_folder"
                                                   value="{{{$contractGroup->id}}}">
                                            <i></i>
                                        </label>
                                    </td>
                                    <td>{{{ $project->getCompanyByGroup($contractGroup->group)->name ?? $project->getRoleName($contractGroup->group) }}}</td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-primary"
                        id="folderShareBtn">{{ trans('documentManagementFolders.share') }}</button>

                <button type="button" data-dismiss="modal"
                        class="btn btn-default">{{ trans('files.cancel') }}</button>
            </div>
        </div>
    </div>
</div>