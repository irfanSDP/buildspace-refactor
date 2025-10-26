define('buildspace/apps/ProjectBuilder/SupplyOfMaterial/BillPropertiesForm',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/dom",
    "dojo/dom-construct",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/dom-attr",
    "dojo/dom-geometry",
    "dijit/form/Form",
    "dijit/form/Select",
    "dijit/form/ValidationTextBox",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/billPropertiesForm.html",
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function(declare, html, dom, domConstruct, keys, domStyle, domAttr, domGeo, Form, Select, ValidateTextBox, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls){

    var BillPropertiesMainInfoForm = declare("buildspace.apps.ProjectBuilder.SupplyOfMaterial.BillPropertiesMainInfoForm", [Form,
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin,
        dojox.form.manager._Mixin,
        dojox.form.manager._NodeMixin,
        dojox.form.manager._ValueMixin,
        dojox.form.manager._DisplayMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'top',
        billId: -1,
        projectBreakdownGrid: null,
        tenderAlternativeBillGridId: null,
        billElementGrid: null,
        formData: null,
        unitTypeText: null,
        description: null,
        nls: nls,
        style: "padding:5px;overflow:auto;border:0px;height:140px;",
        postCreate: function(){
            this.inherited(arguments);
            html.set(this.unitTypeTextNode, this.unitTypeText);
        },
        disableForm: function(){
            this.titleNode.set('disabled', true);
            this.descriptionNode.set('disabled', true);
        },
        submit: function(){
            var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'...'
                }),
                grid = this.billElementGrid;

            var self = this,
                values = dojo.formToObject(this.id);

            if(this.validate()){
                pb.show();
                dojo.xhrPost({
                    url: 'supplyOfMaterial/billUpdate',
                    content: values,
                    handleAs: 'json',
                    load: function(resp) {
                        dojo.query('[id^="error-supply_of_material_"]').forEach(function(node){
                            node.innerHTML = '';
                        });
                        if(resp.success){

                            self.projectBreakdownGrid.store.fetchItemByIdentity({ 'identity' : resp.item.id,  onItem : function(item){
                                if(item){

                                    for(var property in resp.item){
                                        if(item.hasOwnProperty(property) && property != self.projectBreakdownGrid.store._getIdentifierAttribute()){
                                            self.projectBreakdownGrid.store.setValue(item, property, resp.item[property]);
                                        }
                                    }

                                    self.projectBreakdownGrid.store.save();

                                    var billManagerTab = dijit.byId(item.id+'-form_'+item.type),
                                        tabArea = billManagerTab.getParent(),
                                        tac = tabArea.getChildren();

                                    for(var i in tac){
                                        if(tac[i].id == item.id+'-form_'+item.type){
                                            tac[i].set('title', buildspace.truncateString(item.title, 35));
                                            tabArea.resize();
                                            break;
                                        }
                                    }

                                    self.projectBreakdownGrid.reload();

                                    if(self.tenderAlternativeBillGridId){
                                        tenderAlternativeGrid = dijit.byId(self.tenderAlternativeBillGridId);
                                        if(tenderAlternativeGrid){
                                            tenderAlternativeGrid.grid.reload();
                                        }
                                    }
                                }
                            }});
                        }else{
                            for(var key in resp.errors){
                                var msg = resp.errors[key];
                                html.set(dom.byId("error-supply_of_material_"+key), msg);
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
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formData);

            if(this.projectBreakdownGrid.rootProject.status_id != buildspace.constants.STATUS_PRETENDER)
                this.disableForm();
        }
    });

    return declare("buildspace.apps.ProjectBuilder.SupplyOfMaterial.BillPropertiesForm", dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;overflow:auto;",
        gutters: false,
        region: 'center',
        billId: -1,
        projectBreakdownGrid: null,
        tenderAlternativeBillGridId: null,
        billElementGrid: null,
        nls: nls,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            //setting up form values before we start our form
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            pb.show();

            dojo.xhrGet({
                url: 'supplyOfMaterial/billForm',
                handleAs: 'json',
                content: { id: this.billId },
                load: function(data){
                    self.renderBillPropertiesInfoForm(data);
                    pb.hide();
                },
                error: function(error) {
                    //something is wrong somewhere
                    pb.hide();
                }
            });
        },
        renderBillPropertiesInfoForm: function(data){
            var form = this.mainForm = new BillPropertiesMainInfoForm({
                billId: this.billId,
                projectBreakdownGrid: this.projectBreakdownGrid,
                tenderAlternativeBillGridId: this.tenderAlternativeBillGridId,
                unitTypeText: data.unitTypeText,
                billElementGrid: this.billElementGrid,
                formData: data
            });

            this.addChild(form);
        }
    });
});