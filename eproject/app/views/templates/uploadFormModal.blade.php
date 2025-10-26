<?php $modalId = isset($modalId) ? $modalId : 'uploadFormModal' ?>
<?php $formId = isset($formId) ? $formId : $modalId . "-form" ?>
<?php $title = isset($title) ? $title : trans('forms.upload') ?>
<?php $formRoute = isset($formRoute) ? $formRoute : '' ?>
<?php $confirmBeforeSubmit = isset($confirmBeforeSubmit) ? $confirmBeforeSubmit : false ?>
<?php $confirmBeforeSubmitMessage = isset($confirmBeforeSubmitMessage) ? $confirmBeforeSubmitMessage : null ?>

<div class="modal" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-type="formModal"
     aria-hidden="true">
    <div class="modal-dialog modal-xs">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <i class="fa fa-paperclip"></i>
                    {{{ $title }}}
                </h4>
            </div>

            <div class="modal-body">
                {{ Form::open(array('url' => $formRoute, 'id' => $formId, 'class' => 'smart-form', 'files' => true)) }}
                    <fieldset>
                        <div class="row">
                            @foreach($fields as $fieldLabel => $field)
                            <div class="col col-xs-12 col-md-12 col-lg-12">
                                <section>
                                    <label class="label">{{{ $fieldLabel }}}:</label>
                                    <label class="input {{{ $errors->has($field) ? 'state-error' : null }}}">
                                        {{ Form::file($field, array('style' => 'height:100%')) }}
                                    </label>
                                    {{ $errors->first($field, '<em class="invalid">:message</em>') }}
                                </section>
                            </div>
                            @endforeach
                        </div>
                    </fieldset>
                {{ Form::close() }}
            </div>
            <div class="modal-footer">
                @if(!$confirmBeforeSubmit)
                    <button class="btn btn-primary" data-action="form-submit" data-target-id="{{{ $formId }}}">
                        <i class="fa fa-upload"></i>
                        {{ trans('forms.submit') }}
                    </button>
                @else
                    <button class="btn btn-primary" data-action="form-submit" data-target-id="{{{ $formId }}}" data-intercept="confirmation" data-form-id="{{{ $modalId . '-form' }}}" @if(isset($confirmBeforeSubmitMessage)) data-confirmation-message="{{{ $confirmBeforeSubmitMessage }}}" @endif>
                        <i class="fa fa-upload"></i>
                        {{ trans('forms.submit') }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>