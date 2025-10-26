<?php $editable = $clause->is_editable ?? true ?>
<li class="dd-item bg-color-purple rounded-ne rounded-sw" {{{ $editable ? 'data-is_editable="true"' : '' }}} data-id="{{{ $clause->id ?? 0 }}}">

    <div class="dd-handle dd3-handle">&nbsp;</div>
    <div class="dd3-content rounded-ne rounded-sw">
        <table>
            <tr>
                <td class="text-top" data-category="clause-numbering">
                    <span class="label" data-type="label">
                    </span>
                </td>
                <td class="fill-horizontal">
                    @if($clause)
                        @if(!$clause->isEditable())
                            <div class="well bg-grey-e txt-color-darken">
                                {{ $clause->content }}
                            </div>
                        @else
                            <div data-category="editable-content">
                                <div class="summernote" data-type="content" data-category="editor" hidden>
                                    @if($clause)
                                        {{ $clause->content }}
                                    @else
                                        <div style="text-align: justify;"><br></div>
                                    @endif
                                </div>
                                <div class="well bg-white txt-color-greenDark" data-category="display">
                                    @if($clause)
                                        {{ $clause->content }}
                                    @endif
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="summernote" data-type="content">
                            <div style="text-align: justify;"><br></div>
                        </div>
                    @endif
                </td>
                <td class="padded-left padded-right">
                    @if($isTemplate)
                        <div class="checkbox" title="Allow clause to be editable" data-toggle="tooltip">
                            <label>
                                <input type="checkbox" class="checkbox style-0" name="is_editable" data-editable="true" {{{ ($clause->is_editable ?? true) ? 'checked' : '' }}}>
                                <span></span>
                            </label>
                        </div>
                    @endif
                </td>
                <td style="margin:0">
                    <ul style="list-style-type: none; padding-left:0">
                        <li><button type="button" class="btn btn-xs btn-primary" data-action="add_clause" data-level="child" data-toggle="tooltip" data-placement="left" title="Add a new Clause"><i class="fa fa-plus"></i></button></li>
                        <?php
                        $isDisabled = false;
                        if(isset($clause) && (!$clause->isDeletable())) $isDisabled = true;
                        ?>
                        <li><button type="button" class="btn btn-xs btn-danger" data-action="delete_clause" data-toggle="tooltip" data-placement="left" title="Delete this Clause" {{{ $isDisabled ? 'disabled' : '' }}}><i class="fa fa-times"></i></button></li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>

    @if($clause)
        @if($clause->children->count() > 0)
            <ol class="dd-list">
                @foreach($clause->children->sortBy('priority') as $child)
                    @include('structured_documents.clause', array('clause' => $child, 'isTemplate' => $isTemplate))
                @endforeach
            </ol>
        @endif
    @endif
</li>

