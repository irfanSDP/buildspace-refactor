define('buildspace/apps/Tendering/ScheduleOfRateBill/BillPropertiesForm',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/dom",
    "dojo/dom-construct",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/dom-attr",
    "dojo/dom-geometry",
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/billPropertiesForm.html",
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function(declare, html, dom, domConstruct, keys, domStyle, domAttr, domGeo, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls){

    var BillPropertiesMainInfoForm = declare("buildspace.apps.Tendering.ScheduleOfRateBill.BillPropertiesMainInfoForm", [Form,
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
        formData: null,
        unitTypeText: null,
        billDescription: "",
        billTitle: "",
        nls: nls,
        style: "padding:5px;overflow:auto;border:0px;height:140px;",
        postCreate: function(){
            this.inherited(arguments);
            html.set(this.unitTypeTextNode, this.unitTypeText);
            html.set(this.titleNode, this.billTitle);
            html.set(this.descriptionNode, this.billDescription);
        }
    });

    return declare("buildspace.apps.Tendering.ScheduleOfRateBill.BillPropertiesForm", dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;overflow:auto;",
        gutters: false,
        region: 'center',
        billId: -1,
        projectBreakdownGrid: null,
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
                url: 'scheduleOfRateBill/billForm',
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
                unitTypeText: data.unitTypeText,
                billTitle: data['schedule_of_rate_bill[title]'],
                billDescription: data['schedule_of_rate_bill[description]']
            });

            this.addChild(form);
        }
    });
});