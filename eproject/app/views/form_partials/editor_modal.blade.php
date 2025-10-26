<?php
$modalId = $modalId ?? 'editorModal';
?>
<div class="modal fade" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="editorLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-grey-e">
                <h6 class="modal-title" data-id="editorLabel">
                    <!-- Title -->
                </h6>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>

            <div class="modal-body" data-category="form">
                {{ Form::open(array('route' => null, 'class' => 'smart-form')) }}
                    <input name="_method" type="hidden" value="POST"/>
                    <input name="id" type="hidden"/>
                    @foreach($fieldGroups as $fieldGroup)
                        <div class="row">
                            @foreach($fieldGroup as $field)
                                <div class="form-group col col-xs-{{{ $field['colSize'] }}}">
                                    <label for="{{{ $field['name'] }}}" class="control-label">{{{ $field['displayName'] }}}:</label>
                                    <input name="{{{ $field['name'] }}}" class="form-control" placeholder="{{{ $field['placeholder'] ?? '' }}}" value="{{{ Input::old($field['name']) }}}"/>
                                    {{ $errors->first($field['name'], '<em class="invalid" data-category="error">:message</em>') }}
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                {{ Form::close() }}
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" data-action="submit">{{{ trans('forms.save') }}}</button>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->