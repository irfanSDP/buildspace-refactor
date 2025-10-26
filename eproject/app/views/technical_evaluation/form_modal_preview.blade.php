<?php $editable = isset($editable) ? $editable : true ?>
<div class="modal scrollable-modal full-screen" id="technicalEvaluationForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-type="formModal"
     aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            {{ Form::open(array('v-on' => 'submit:disableSubmit')) }}
                <div class="modal-header bg-color-blue">
                    <h4 class="modal-title" id="myModalLabel">
                        <i class="fa fa-check-square"></i> {{{ trans('technicalEvaluation.technicalEvaluationForm') }}}
                    </h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                </div>

                <div class="modal-body" style="overflow-y:auto;width:auto;height:380px;">
                    <div class="tree smart-form">
                        <ul data-id="root-list">
                        </ul>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="row">
                        <div class="col col-md-6"><button type="button" class="btn btn-default" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
                        </div>
                    </div>
                </div>
            {{ Form::close() }}
        </div>
    </div>
    <div data-type="templates" hidden>
        <li data-type="node" data-level="aspect" data-id="">
            <span data-type="node-container" class="bg-grey-7 color-white" data-action="expandToggle" data-target=""> <strong data-type="node-description"></strong></span>
            <ul data-type="expandable" data-id="">
            </ul>
        </li>
        <li data-type="node" data-level="criteria" data-id="">
            <span data-type="node-container" class="bg-grey-c" data-action="expandToggle" data-target=""> <strong data-type="node-description"></strong></span>
            <ul data-type="expandable" data-id="">
            </ul>
        </li>
        <li data-type="node" data-level="item" data-id="">
            <span data-type="node-container" class="bg-blue-angel" data-action="expandToggle" data-target=""> <strong data-type="node-description"></strong></span>
            <ul data-type="expandable" data-id="">
            </ul>
        </li>
        <li data-type="node" data-level="option">
            <table>
                <tr>
                    <td>
                        <span>
                            <label class="radio">
                                <input data-type="option" name="" value=""/>
                            </label>
                        </span>
                    </td>
                    <td class="padded-left">
                        <span>
                            <label class="score">
                            </label>
                        </span>
                    </td>
                </tr>
            </table>
        </li>
    </div>
</div>

@include('templates.generic_table_modal', [
    'modalId'    => 'formResponseLogModal',
    'title'      => trans('technicalEvaluation.responseLog'),
    'showCancel' => true,
    'cancelText' => trans('forms.close'),
])

<script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
<script>
    var formResponsesStack = new ModalStack();

    var formModal = {
        technicalEvaluationData: {},
        init: function(data){
            this.technicalEvaluationData = data;

            this.setupSet();

            $('#technicalEvaluationForm form').prop('action', this.technicalEvaluationData.form_route);

            formResponsesStack.push('#technicalEvaluationForm');
        },
        setupSet: function(){
            var aspects = this.technicalEvaluationData['technical_evaluation_set']['children'];

            var rootList = $('#technicalEvaluationForm ul[data-id=root-list]');

            rootList.empty();

            for(aspect of aspects){
                this.setupAspect(rootList, aspect);
            }
        },
        setupAspect: function(element, node){
            var clone = $('#technicalEvaluationForm [data-type=templates] li[data-level=aspect]').clone();

            var expandableId = 'criteriaList-'+node.id;

            clone.attr('data-id', node.id);

            clone.children('[data-type="node-container"]').attr('data-target', expandableId);
            clone.children('[data-type="expandable"]').attr('data-id', expandableId);

            clone.find('[data-type="node-container"]>[data-type="node-description"]').html(node.name);
            element.append(clone);

            for(criteria of node['children']){
                this.setupCriteria($(clone).children('ul[data-id="'+expandableId+'"]'), criteria);
            }
        },
        setupCriteria: function(element, node){
            var clone = $('#technicalEvaluationForm [data-type=templates] li[data-level=criteria]').clone();

            var expandableId = 'itemsList-'+node.id;

            clone.attr('data-id', node.id);

            clone.children('[data-type="node-container"]').attr('data-target', expandableId);
            clone.children('[data-type="expandable"]').attr('data-id', expandableId);

            clone.find('[data-type="node-container"]>[data-type="node-description"]').html(node.name);

            element.append(clone);

            for(item of node['children']){
                this.setupItem($(clone).children('ul[data-id="'+expandableId+'"]'), item);
            }
        },
        setupItem: function(element, node){
            var clone = $('#technicalEvaluationForm [data-type=templates] li[data-level=item]').clone();

            var expandableId = 'optionsList-'+node.id;

            clone.attr('data-id', node.id);

            clone.children('[data-type="node-container"]').attr('data-target', expandableId);
            clone.children('[data-type="expandable"]').attr('data-id', expandableId);

            clone.find('[data-type="node-container"]>[data-type="node-description"]').html(node.name);

            element.append(clone);

            for(option of node['children']){
                this.setupOption($(clone).children('ul[data-id="'+expandableId+'"]'), option, node);
            }
        },
        setupOption: function(element, node, parentNode){
            var clone = $('#technicalEvaluationForm [data-type=templates] li[data-level=option]').clone();

            clone.find('[data-type="option"]').attr('name', "options["+parentNode.id+"]");
            clone.find('[data-type="option"]').attr('value', 'node.id');
            clone.find('label.radio').append(node.name);

            clone.find('label.score').append(node.value);

            element.append(clone);
        },
    };
</script>