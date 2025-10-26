define('buildspace/apps/ProjectBuilder/ScheduleOfRateBillForm',[
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
    "dojo/text!./templates/scheduleOfRateBillForm.html",
    'dojo/i18n!buildspace/nls/ProjectBuilder',
    "dojo/on"
], function(declare, html, dom, keys, domStyle, Form, ValidateTextBox, Textarea, Select, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls, on){

    var ScheduleOfRateBillFormWidget = declare("buildspace.apps.ProjectBuilder.ScheduleOfRateBillFormWidget", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        billId: -1,
        parentId: null,
        projectBreakdownGrid: null,
        nls: nls,
        style: "padding:5px;overflow:auto;",
        postCreate: function(){
            this.inherited(arguments);
            //get Unittypes
            var unitTypeSelectStore = new dojo.data.ItemFileReadStore({
                url:"projectBuilder/getUnitTypes"
            });

            this.unitTypeSelect = new Select({
                name: "schedule_of_rate_bill[unit_type]",
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
            pb.show();
            dojo.xhrGet({
                url: 'scheduleOfRateBill/billForm',
                handleAs: 'json',
                content: { id: this.billId, parent_id: this.parentId },
                load: function(data){
                    self.scheduleOfRateBillForm.setFormValues(data);
                    html.set(self.unitTypeViewDivNode, (data.unitTypeText) ? data.unitTypeText : '');
                    pb.hide();
                },
                error: function(error) {
                    //something is wrong somewhere
                    pb.hide();
                }
            });
        },
        toggleEditableInputElements: function(enable){
            if(enable){
                domStyle.set(this.unitTypeViewDivNode, 'display', 'none');
                domStyle.set(this.unitTypeInputDivNode, 'display', '');
            }else{
                domStyle.set(this.unitTypeViewDivNode, 'display', '');
                domStyle.set(this.unitTypeInputDivNode, 'display', 'none');
            }
        },
        save: function(){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'...'
            });

            var self = this,
                values = dojo.formToObject(self.scheduleOfRateBillForm.id);

            if(this.scheduleOfRateBillForm.validate()){
                pb.show();
                dojo.xhrPost({
                    url: 'scheduleOfRateBill/billUpdate',
                    content: values,
                    handleAs: 'json',
                    load: function(resp) {
                        dojo.query('[id^="error-schedule_of_rate_bill_"]').forEach(function(node){
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
                                html.set(dom.byId("error-schedule_of_rate_bill_"+key), resp.errors[key]);
                            }
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            }
        },
        close: function(){

        },
        onCancel: function(){

        }
    });

    return declare('buildspace.apps.ProjectBuilder.ScheduleOfRateBillFormDialog', dijit.Dialog, {
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
                style:"padding:0px;width:500px;height:240px;",
                gutters: false
            });

            var form = new ScheduleOfRateBillFormWidget({
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