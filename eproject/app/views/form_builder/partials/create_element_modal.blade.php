<?php use PCK\FormBuilder\Elements\Element; ?>
<?php $modalId = isset($modalId) ? $modalId : 'elementModal'; ?>

<div class="modal fade" id="{{{ $modalId }}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{{ trans('formBuilder.addElement') }}}
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <div class="widget-body">
                    <div class="smart-form">
                        @foreach(Element::getElementTypesByIdentifer() as $identifier => $elementName)
                        <div class="row">
                            <section class="col col-xs-12">
                                <button type="button" class="btn btn-primary btn-block" data-class_identifier="{{ $identifier }}" data-action="create_element">{{ $elementName }}</a>
                            </section>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>