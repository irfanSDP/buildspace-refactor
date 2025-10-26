define('buildspace/apps/CostData/CostDataInformationForm',[
    'dojo/_base/declare',
    'dojo/query',
    "dojo/html",
    "dojo/dom",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/CostDataInformationForm.html",
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, query, html, dom, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls){

    var Form = declare("buildspace.apps.CostData.CostDataInformationForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        costData: null,
        nls: nls,
        startup: function(){
            this.inherited(arguments);

            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "costData/getCostDataInformationForm",
                    handleAs: "json",
                    content: {
                        cost_data_id: self.costData.id,
                    },
                    load: function(data) {
                        self.costDataInformationForm.setFormValues(data.formValues);

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
        },
        save: function(){
            var self = this,
                values = dojo.formToObject(this.costDataInformationForm.id);
            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.submitting+'. '+nls.pleaseWait+'...'
            });

            if(this.costDataInformationForm.validate()){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'costData/updateCostDataInformationForm/cost_data_id/'+self.costData.id+'/_csrf_token/'+self.costData._csrf_token,
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="cost_data_error-"]').forEach(function(node){
                                node.innerHTML = '';
                            });

                            if(resp.success) {
                                self.costDataInformationForm.setFormValues(resp.formValues);
                                self.updateGridHeaders(resp.values);
                            }
                            else{
                                for(var key in resp.errors){
                                    var msg = resp.errors[key];
                                    html.set(dom.byId("cost_data_error-"+key), msg);
                                }
                            }

                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }
        },
        updateHeaders: function(grid, values){
            var newLayout = grid.structure;
            var headerRow;
            for(var i in newLayout){
                if(i == 'cells'){
                    for(var structureIndex in newLayout.cells){
                        headerRow = newLayout.cells[structureIndex];
                        for(columnIndex in headerRow){
                            if(headerRow[columnIndex].originalName == nls.budget && values['cost_data[approved_date]']){
                                headerRow[columnIndex].name = nls.budget+' ('+values['cost_data[approved_date]']+')';
                            }
                            else if(headerRow[columnIndex].originalName == nls.contractSum && values['cost_data[awarded_date]']){
                                headerRow[columnIndex].name = nls.contractSum+' ('+values['cost_data[awarded_date]']+')';
                            }
                            else if(headerRow[columnIndex].originalName == nls.adjustedSum && values['cost_data[adjusted_date]']){
                                headerRow[columnIndex].name = nls.adjustedSum+' ('+values['cost_data[adjusted_date]']+')';
                            }
                        }
                    }
                }
            }

            grid.setStructure(newLayout);
        },
        updateGridHeaders: function(values){
            for(var i in values){
                this.costData.approved_date = values['cost_data[approved_date]'];
                this.costData.awarded_date = values['cost_data[awarded_date]'];
                this.costData.adjusted_date = values['cost_data[adjusted_date]'];
            }

            var grid;

            var gridIds = [
                'costData-breakdownGrid',
                'costData-workCategoryGrid',
                'costData-elementGrid',
                'costData-provisionalSumGrid',
            ];

            for(var i in gridIds){
                grid = dijit.byId(gridIds[i]);
                if(grid) this.updateHeaders(grid, values);
            }
        }
    });

    return declare('buildspace.apps.CostData.CostDataInformationFormContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
        form: null,
        postCreate: function(){
            this.inherited(arguments);

            this.form = new Form({
                costData: this.costData,
            });

            this.addChild(this.createToolbar());

            this.addChild(this.form);
        },
        createToolbar: function(){
            var self = this;
            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(self.form, 'save')
                })
            );

            return toolbar;
        }
    });
});
