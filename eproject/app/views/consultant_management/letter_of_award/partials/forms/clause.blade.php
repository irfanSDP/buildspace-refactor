<div class="row" style="margin: 0 10px;text-align:right;">
    <button type="button" button_action="add_clause_top" class="btn btn-danger"><i class="fa fa-plus"></i>
        &nbsp;
        {{ trans('letterOfAward.addNewClause') }}
    </button>
</div>
<div class="row bg-color-magenta rounded-less" style="margin:10px;min-height:420px;">
    <div class="row" style="margin: 10px">
        <div class="dd scrollable" id="activeClauseList" style="min-width:100%;overflow-y:auto;">
            <ol class="dd-list root-list" style="min-height:380px;"></ol>
        </div>
    </div>
</div>

<div data-category="templates" hidden>
    <div data-type="clause">
        <li class="dd-item bg-color-purple rounded-ne rounded-sw">
            <div class="dd-handle dd3-handle">&nbsp;</div>
            <div class="dd3-content rounded-ne rounded-sw">
                <table>
                    <tr>
                        <td class="fill-horizontal">
                            <div class="clause-summernote" item_type="content"></div>
                        </td>
                        <td class="padded-left padded-right">
                            <div class="checkbox" style="margin-left:2px;" title="{{ trans('letterOfAward.displayClauseNumbering') }}" data-toggle="tooltip" >
                                <label>
                                    <input type="checkbox" class="checkbox style-0" name="display_numbering">
                                    <span></span>
                                </label>
                            </div>
                        </td>
                        <td style="margin:0">
                            <div class="d-flex">
                                <button type="button" class="btn btn-xs btn-success" style="margin-right:2px;" button_action="add_clause_bottom" data-toggle="tooltip" data-placement="left" title="Add a new Clause"><i class="fa fa-plus"></i></button>
                                <button type="button" class="btn btn-xs btn-danger" style="margin-right:2px;" button_action="delete_clause" data-toggle="tooltip" data-placement="left" title="Delete this Clause"><i class="fa fa-times"></i></button>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <ol class="dd-list root-list nested"></ol>
        </li>
    </div>
</div>

<div class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="dd" id="inactiveClauseList" style="height: 100%;">
                    <ol class="dd-list"></ol>
                </div>
            </div>
        </div>
    </div>
</div>