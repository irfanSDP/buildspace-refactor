<?php $editable = isset($editable) ? $editable : true ?>
<div class="modal scrollable-modal full-screen" id="technicalEvaluationForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-type="formModal"
     aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            {{ Form::open(array('v-on' => 'submit:disableSubmit')) }}
                <div class="modal-header bg-color-blue">
                    <h4 class="modal-title" id="myModalLabel">
                        <i class="fa fa-check-square"></i> {{{ trans('technicalEvaluation.technicalEvaluationForm') }}}
                        <div class="well" style="margin-top:6px;margin-bottom:0;">
                            <strong style="font-size:12px;color:#333;" data-type="company-name"></strong>
                        </div>
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
                        <div class="col col-md-4">
                            <div class="progress progress-sm progress-striped active">
                                <div class="progress-bar @{{ progressBarClass }}" role="progressbar" data-type="progress-bar" data-name="completed-items" style="width: @{{ completionPercentage }}%"></div>
                            </div>
                        </div>
                        <div class="col col-md-2">
                            <div class="text-left @{{ itemCountClass }}">
                                <strong>
                                    @{{ completedItemCount }} / @{{ totalItemCount }}
                                </strong>
                            </div>
                        </div>
                        <div class="col col-md-6">
                            @if($editable)
                                {{ Form::button('<i class="fa fa-save"></i> '.trans('forms.save'), ['type' => 'submit', 'class' => 'btn btn-primary', 'data-action' => 'save-response'] )  }}
                            @endif

                            <button type="button" class="btn btn-info" data-action="view-log"><i class="fa fa-search"></i> {{ trans('technicalEvaluation.viewLog') }}</button>

                            <button type="button" class="btn btn-default" data-dismiss="modal">{{{ trans('forms.close') }}}</button>
                        </div>
                    </div>

                </div>
            {{ Form::close() }}
        </div>
    </div>
    <div data-type="templates" hidden>
        <li data-type="node" data-level="aspect" data-id="">
            <span data-type="node-container" class="bg-grey-7 color-white" data-action="expandToggle" data-target=""> <strong data-type="node-description"></strong></span>
            <i class="fa fa-check text-success" data-type="node-check" data-for="complete"></i>
            <i class="fa fa-circle-o-notch text-danger" data-type="node-check" data-for="incomplete"></i>
            <ul data-type="expandable" data-id="">
            </ul>
        </li>
        <li data-type="node" data-level="criteria" data-id="">
            <span data-type="node-container" class="bg-grey-c" data-action="expandToggle" data-target=""> <strong data-type="node-description"></strong></span>
            <i class="fa fa-check text-success" data-type="node-check" data-for="complete"></i>
            <i class="fa fa-circle-o-notch text-danger" data-type="node-check" data-for="incomplete"></i>
            <ul data-type="expandable" data-id="">
            </ul>
        </li>
        <li data-type="node" data-level="item" data-id="">
            <span data-type="node-container" class="bg-blue-angel" data-action="expandToggle" data-target=""> <strong data-type="node-description"></strong></span>
            <i class="fa fa-check text-success" data-type="node-check" data-for="complete"></i>
            <i class="fa fa-circle-o-notch text-danger" data-type="node-check" data-for="incomplete"></i>
            <ul data-type="expandable" data-id="">
            </ul>
        </li>
        <li data-type="node" data-level="option">
            <table>
                <tr>
                    <td>
                        <span>
                            <label class="radio">
                                <input @if(!$editable) disabled @endif type="radio" data-type="option" name="" value=""/>
                                <i></i>
                            </label>
                        </span>
                    </td>
                    <td class="padded-left">
                        <div class="txt-color-orange" data-type="remarks-section">
                            {{ trans('technicalEvaluation.remarks') }}
                            <label class="text">
                                <input type="text" class="form-control" data-type="remarks" name="" style="padding-left:5px; min-width: 350px;" maxlength="100" @if(!$editable) disabled @endif/>
                            </label>
                        </div>
                    </td>
                </tr>
            </table>
        </li>
    </div>
</div>

@include('templates.generic_table_modal', [
    'modalId'    => 'formResponseLogModal',
    'title'      => trans('technicalEvaluation.responseLog'),
    'tableId'    => 'formResponseLogModalTable',
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
            this.reset();

            $('#technicalEvaluationForm [data-type="company-name"]').html(this.technicalEvaluationData.company_name);
            $('#technicalEvaluationForm form').prop('action', this.technicalEvaluationData.form_route);

            formResponseLogModalTable.setData(this.technicalEvaluationData.log_route);

            formResponsesStack.push('#technicalEvaluationForm');
        },
        reset: function(){
            var self = this;
            var remarksSections = $('[data-type=formModal] [data-type=remarks-section]');
            remarksSections.hide();

            var radioInputs= $('[data-type=formModal]  input[type=radio][data-type=option]' );

            radioInputs.each(function(){
                self.toggleRemarksVisibility($(this ));
            });

            this.refreshNodeMarkers();

            technicalEvaluationFormVue.updateProgressBar();
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
            clone.find('[data-type="option"]').attr('value', node.id);
            clone.find('[data-type="option"]').prop('checked', this.technicalEvaluationData['selected_options'].includes(node.id));

            clone.find('label.radio').append(node.name);

            clone.find('input[type="text"][data-type="remarks"]').attr('name', "remarks["+node.id+"]");
            clone.find('input[type="text"][data-type="remarks"]').attr('value', (node.id in this.technicalEvaluationData['option_remarks']) ? this.technicalEvaluationData['option_remarks'][node.id] : '');

            element.append(clone);
        },
        toggleRemarksVisibility: function(radioInput, focus)
        {
            var self = this;
            if(!self.isChecked(radioInput)) return;

            // Hide all remarks.
            var itemNode = radioInput.closest('[data-type=node][data-level=item]');

            itemNode.find('[data-type=remarks-section]' ).hide();

            // Show remark of current option.
            var optionNode = radioInput.closest('[data-type=node][data-level=option]');
            optionNode.find('[data-type=remarks-section]' ).show();

            if(focus) optionNode.find('input[type=text][data-type=remarks]' ).select();
        },
        isChecked: function(radioInput)
        {
            return radioInput.is(':checked');
        },
        refreshNodeMarkers: function()
        {
            var self = this;

            $('[data-type=formModal] [data-type=node]' ).each(function(){
                self.markNode($(this), true);
            });

            self.markNodes(self.getIncompleteNodes());
        },
        getItemsByState: function(completed){
            var self = this;
            var items = [];

            $('#technicalEvaluationForm[data-type=formModal] [data-id="root-list"] [data-type=node][data-level=item]' ).each(function(){
                var item = $(this);
                var itemComplete = false;

                item.find('input[type=radio][data-type=option]' ).each(function(){
                    if(self.isChecked($(this))) itemComplete = true;
                });

                if(completed && itemComplete) items.push($(this ).data('id'));
                if(!completed && !itemComplete) items.push($(this ).data('id'));
            });

            return items;
        },
        getIncompleteNodes: function()
        {
            var self = this;

            var incompleteAspects = {};
            var incompleteCriteria = {};
            var incompleteItems = {};
            var aspect, criteria, item, option, itemComplete;

            $('#technicalEvaluationForm [data-id="root-list"] [data-type=node][data-level=aspect]' ).each(function(){
                aspect = $(this);
                incompleteCriteria = {};

                aspect.find('[data-type=node][data-level=criteria]' ).each(function(){
                    criteria = $(this);
                    incompleteItems = {};

                    criteria.find('[data-type=node][data-level=item]' ).each(function(){
                        item = $(this);
                        itemComplete = false;

                        item.find('input[type=radio][data-type=option]' ).each(function(){
                            if(self.isChecked($(this))) itemComplete = true;
                        });

                        if(!itemComplete) incompleteItems[item.data('id')] = {};
                    });

                    if(Object.keys(incompleteItems).length > 0) incompleteCriteria[criteria.data('id')] = incompleteItems;
                });

                if(Object.keys(incompleteCriteria).length > 0) incompleteAspects[aspect.data('id')] = incompleteCriteria;
            });

            return incompleteAspects;
        },
        markNodes: function(incompleteNodes)
        {
            var self = this;

            var nodeId;

            for(nodeId in incompleteNodes)
            {
                var node = $('[data-type=formModal] [data-type=node][data-id='+nodeId+']');

                self.markNode(node, false);

                self.markNodes(incompleteNodes[nodeId]);
            }
        },
        markNode: function(node, complete)
        {
            var completeIcon = node.children('[data-type=node-check][data-for=complete]' );
            var incompleteIcon = node.children('[data-type=node-check][data-for=incomplete]' );

            completeIcon.hide();
            incompleteIcon.show();

            if(complete)
            {
                completeIcon.show();
                incompleteIcon.hide();
            }
        }
    };

    var technicalEvaluationFormVue = new Vue({
        el: '#technicalEvaluationForm',

        data: {
            completionPercentage: 0,
            progressBarClass: '',
            itemCountClass: '',
            completedItemCount: 0,
            totalItemCount: 0
        },

        methods: {
            updateProgressBar: function(){
                var completedItemCount = formModal.getItemsByState(true ).length;
                var incompleteItemCount = formModal.getItemsByState(false ).length;
                var completionPercentage =  completedItemCount / (completedItemCount + incompleteItemCount) * 100;

                this.progressBarClass = 'bg-color-orange';
                this.itemCountClass = 'text-warning';

                // If all completed.
                if(incompleteItemCount <= 0)
                {
                    this.progressBarClass = 'bg-color-success';
                    this.itemCountClass = 'text-success';
                }

                this.completionPercentage = completionPercentage;
                this.completedItemCount = completedItemCount;
                this.totalItemCount = completedItemCount + incompleteItemCount;
            },
            disableSubmit: function(event){
                var submitButtons = $('[data-type=formModal] [data-action=save-response]');
                submitButtons.prop('disabled', true);
            }
        }
    });

    var formResponseLogModalTable = new Tabulator('#formResponseLogModalTable', {
        height:460,
        columns: [
            { title: "{{ trans('general.no') }}", field: 'counter', width: 50, headerSort:false, align: 'center', cssClass: 'text-center text-top' },
            { title: "{{ trans('users.name') }}", field: 'name', headerSort:false, align: 'left', headerFilter: 'input', headerFilterPlaceHolder: "{{ trans('general.filter') }}"},
            { title: "{{ trans('general.date') }}", field: 'timestamp', width: 300, headerSort: false, align: 'center', cssClass: 'text-center text-top' },
        ],
        layout:"fitColumns",
        paginationSize: 100,
        pagination: "remote",
        ajaxFiltering:true,
        ajaxConfig: "GET",
        placeholder:"{{ trans('general.noRecordsFound') }}",
    });

    $('#technicalEvaluationForm').on('change', 'input[type=radio][data-type=option]', function(){
        formModal.toggleRemarksVisibility($(this), true);

        formModal.refreshNodeMarkers();

        technicalEvaluationFormVue.updateProgressBar();
    });

    $('#technicalEvaluationForm').on('click', '[data-action="view-log"]', function(){
        formResponseLogModalTable.setData(formModal.technicalEvaluationData.log_route);

        formResponsesStack.push('#formResponseLogModal');
    });

</script>