define('buildspace/apps/ProjectBuilder/BillForm',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/form/Form",
    "dijit/form/ValidationTextBox",
    "dijit/form/Textarea",
    "dijit/form/Select",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/billForm.html",
    'dojo/i18n!buildspace/nls/ProjectBuilder',
    "dojo/on"
], function(declare, html, dom, keys, domStyle, Form, ValidateTextBox, Textarea, Select, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls, on){


    var BillFormWidget = declare("buildspace.apps.ProjectBuilder.BillFormWidget", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        billId: -1,
        parentId: null,
        projectBreakdownGrid: null,
        billType : null,
        nls: nls,
        style: "padding:5px;overflow:auto;",
        constructor:function(args){
            var BillTypeConstantArray = this.BillTypeConstantArray = [];
            this.BILL_TYPE_STANDARD = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_STANDARD;
            this.BILL_TYPE_STANDARD_TEXT = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_STANDARD_TEXT;
            this.BILL_TYPE_STANDARD_DESCRIPTION = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_STANDARD_DESCRIPTION;
            this.BILL_TYPE_PRELIMINARY = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRELIMINARY;
            this.BILL_TYPE_PRELIMINARY_TEXT = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRELIMINARY_TEXT;
            this.BILL_TYPE_PRELIMINARY_DESCRIPTION = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRELIMINARY_DESCRIPTION;
            this.BILL_TYPE_PROVISIONAL = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PROVISIONAL;
            this.BILL_TYPE_PROVISIONAL_TEXT = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PROVISIONAL_TEXT;
            this.BILL_TYPE_PROVISIONAL_DESCRIPTION = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PROVISIONAL_DESCRIPTION;
            this.BILL_TYPE_PRIMECOST = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRIMECOST;
            this.BILL_TYPE_PRIMECOST_TEXT = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRIMECOST_TEXT;
            this.BILL_TYPE_PRIMECOST_DESCRIPTION = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRIMECOST_DESCRIPTION;

            this.RoundingTypeConstants = RoundingTypeConstants = [{
                label: buildspace.constants.ROUNDING_TYPE_DISABLED_TEXT,
                value: buildspace.constants.ROUNDING_TYPE_DISABLED
            },{
                label: buildspace.constants.ROUNDING_TYPE_UPWARD_TEXT,
                value: buildspace.constants.ROUNDING_TYPE_UPWARD
            },{
                label: buildspace.constants.ROUNDING_TYPE_DOWNWARD_TEXT,
                value: buildspace.constants.ROUNDING_TYPE_DOWNWARD
            },{
                label: buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER_TEXT,
                value: buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER
            },{
                label: buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH_TEXT,
                value: buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH
            }];

            this.BillTypeConstants = BillTypeConstants = [{
                label: buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_STANDARD_TEXT,
                value: buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_STANDARD,
                description: buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_STANDARD_DESCRIPTION
            },{
                label: buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRELIMINARY_TEXT,
                value: buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRELIMINARY,
                description: buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRELIMINARY_DESCRIPTION
            },{
                label: buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PROVISIONAL_TEXT,
                value: buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PROVISIONAL,
                description: buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PROVISIONAL_DESCRIPTION
            },{
                label: buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRIMECOST_TEXT,
                value: buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRIMECOST,
                description: buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRIMECOST_DESCRIPTION
            }];

            BillTypeConstantArray[this.BILL_TYPE_STANDARD] = {
                title: this.BILL_TYPE_STANDARD_TEXT,
                description: this.BILL_TYPE_STANDARD_DESCRIPTION
            };
            BillTypeConstantArray[this.BILL_TYPE_PRELIMINARY] = {
                title:this.BILL_TYPE_PRELIMINARY_TEXT,
                description: this.BILL_TYPE_PRELIMINARY_DESCRIPTION
            };
            BillTypeConstantArray[this.BILL_TYPE_PROVISIONAL] = {
                title: this.BILL_TYPE_PROVISIONAL_TEXT,
                description : this.BILL_TYPE_PROVISIONAL_DESCRIPTION
            };
            BillTypeConstantArray[this.BILL_TYPE_PRIMECOST] = {
                title: this.BILL_TYPE_PRIMECOST_TEXT,
                description: this.BILL_TYPE_PRIMECOST_DESCRIPTION
            };

            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);
            //get Unittypes
            var unitTypeSelectStore = new dojo.data.ItemFileReadStore({
                    url:"projectBuilder/getUnitTypes"
            });

            this.billSelect = new Select({
                name: "bill_type[type]",
                style: "width:180px;padding:2px!important;",
                options: this.BillTypeConstants,
                searchAttr: "value",
                onChange: dojo.hitch(this, "doChangeBillType")
            }).placeAt(this.billTypeInputDivNode);

            this.buildUpQuantityRoundingSelect = new Select({
                name: "bill_setting[build_up_quantity_rounding_type]",
                style: "width:180px;padding:2px!important;",
                options: this.RoundingTypeConstants,
                searchAttr: "name"
            }).placeAt(this.buildUpQuantityRoundingTypeInputDivNode);

            this.buildUpRateRoundingSelect = new Select({
                name: "bill_setting[build_up_rate_rounding_type]",
                style: "width:180px;padding:2px!important;",
                options: this.RoundingTypeConstants,
                searchAttr: "name"
            }).placeAt(this.buildUpRateRoundingTypeInputDivNode);

            this.unitTypeSelect = new Select({
                name: "bill_setting[unit_type]",
                style: "width:80px;padding:2px!important;",
                store: unitTypeSelectStore,
                searchAttr: "name"
            }).placeAt(this.unitTypeInputDivNode);
        },
        startup: function(){
            this.inherited(arguments);
            var self = this;

            if(this.billId > 0){
                this.toggleEditableInputElements(false);
            }else{
                this.toggleEditableInputElements(true);
            }
            //setting up form values before we start our form
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'projectBuilder/billForm',
                    handleAs: 'json',
                    content: { id: self.billId, parent_id: self.parentId },
                    load: function(data){
                        self.billForm.setFormValues(data);
                        self.billType = self.BillTypeConstantArray[data['bill_type[type]']];
                        html.set(self.unitTypeViewDivNode, (data.unitTypeText) ? data.unitTypeText : '');
                        html.set(self.billTypeViewDivNode, self.billType.title);
                        html.set(self.billTypeDescriptionViewDivNode, self.billType.description);
                        pb.hide();
                    },
                    error: function(error) {
                        //something is wrong somewhere
                        pb.hide();
                    }
                });
            });
        },
        toggleEditableInputElements: function(enable){
            if(enable){
                domStyle.set(this.unitTypeViewDivNode, 'display', 'none');
                domStyle.set(this.billTypeViewDivNode, 'display', 'none');

                domStyle.set(this.unitTypeInputDivNode, 'display', '');
                domStyle.set(this.billTypeInputDivNode, 'display', '');
            }else{
                domStyle.set(this.unitTypeViewDivNode, 'display', '');
                domStyle.set(this.billTypeViewDivNode, 'display', '');

                domStyle.set(this.unitTypeInputDivNode, 'display', 'none');
                domStyle.set(this.billTypeInputDivNode, 'display', 'none');
            }
        },
        doChangeBillType: function(){
            var billTypeId = this.billSelect.get('value');
            this.billType = this.BillTypeConstantArray[billTypeId];
            html.set(this.billTypeViewDivNode, this.billType.title);
            html.set(this.billTypeDescriptionViewDivNode, this.billType.description);
        },
        save: function(){

            if(this.billForm.validate()){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'...'
                });

                var self = this,
                    values = dojo.formToObject(self.billForm.id);

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'projectBuilder/billUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="error-bill_setting_"]').forEach(function(node){
                                node.innerHTML = '';
                            });
                            if(resp.success){
                                var grid = dijit.byId('bill_element_container_'+self.projectBreakdownGrid.rootProject.id+'-bill-'+resp.item.id);
                                self.projectBreakdownGrid.reload();
                                if(grid){
                                    grid.reconstructBillContainer();
                                }

                                if(self.dialogObj){
                                    self.dialogObj.hide();
                                }
                            }else{
                                for(var key in resp.errors){
                                    html.set(dom.byId("error-bill_setting_"+key), resp.errors[key]);
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
        close: function(){

        },
        onCancel: function(){

        }
    });

    return declare('buildspace.apps.ProjectBuilder.BillFormDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        billId: -1,
        parentId: null,
        projectBreakdownGrid: null,
        title: null,
        buildRendering: function(){
            var form = this.createForm();
            form.startup();
            this.content = form;

            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function(e){
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createForm: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:540px;height:320px;",
                gutters: false
            });

            var form = new BillFormWidget({
                dialogObj: this,
                billId: this.billId,
                parentId: this.parentId,
                projectBreakdownGrid: this.projectBreakdownGrid
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(form, 'save')
                })
            );
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );
            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });
});