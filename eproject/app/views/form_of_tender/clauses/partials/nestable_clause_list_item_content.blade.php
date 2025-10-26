<div class="dd-handle dd3-handle">&nbsp;</div>
<?php $color = ((!$isTemplate) && (!$clause->is_editable)) ? 'bg-grey-d' : 'bg-grey-f' ?>
<div class="dd3-content rounded-ne rounded-sw {{{ $color }}}">
    <table>
        <tr>
            <td class="text-top" data-category="clause-numbering">
                <span class="label" data-type="label" style="margin-right:2px;"></span>
            </td>
            <td class="fill-horizontal">
                @if(((!$isTemplate) && (!$clause->is_editable)))
                    <div class="well bg-grey-e">
                        @if(isset($clause))
                            {{ $clause->clause }}
                        @endif
                    </div>
                @else
                    <div class="summernote" item_type="content">
                        @if(isset($clause))
                            {{ $clause->clause }}
                        @endif
                    </div>
                @endif
            </td>
            <td class="padded-left padded-right">
                @if($isTemplate)
                    <div class="checkbox" title="Allow clause to be editable" data-toggle="tooltip" style="margin-left:2px;">
                        <label>
                            <input type="checkbox" class="checkbox style-0" name="is_editable" data-editable="true" {{{ $clause->is_editable ? 'checked' : '' }}}>
                            <span></span>
                        </label>
                    </div>
                @endif
            </td>
            <td>
                <div class="d-flex">
                    <button type="button" class="btn btn-xs btn-primary" style="margin-right:2px;" button_action="add_clause" data-toggle="tooltip" data-placement="left" title="{{{ trans('formOfTender.addNewClause') }}}" {{{ $disabled ? 'disabled' : '' }}}><i class="fa fa-plus"></i></button>
                    @if($isTemplate || ((!$isTemplate) && $clause->is_editable))
                    <button type="button" class="btn btn-xs btn-danger" button_action="delete_clause" data-toggle="tooltip" data-placement="left" title="{{{ trans('formOfTender.deleteClause') }}}" {{{ $disabled ? 'disabled' : '' }}}><i class="fa fa-times"></i></button>
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>