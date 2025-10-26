define(['dojo/_base/declare',
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/Form",
    "dijit/form/Button",
    "dijit/form/ValidationTextBox",
    "dijit/form/TextBox",
    "dijit/form/DateTextBox",
    "dijit/Toolbar",
    "dijit/form/FilteringSelect",
    "dijit/form/Select",
    "dojo/text!./templates/billAdminSettingUpdateForm.html",
    'dojo/i18n!buildspace/nls/BillAdminSettingMaintenance',
    "dojo/html", "dojo/dom", "dojo/on",
    "dojo/dom-construct", "dojo/domReady!",
    "dojox/layout/TableContainer",
    'dojox/form/Manager',
    "dijit/InlineEditBox",
    "buildspace/widget/forms/InlineEditBox"
    ], function(declare, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, Form, Button, ValidateTextBox, TextBox, DateTextBox, Toolbar, FilteringSelect, Select, billAdminSettingUpdateFormTemplate, nls, html, dom, on, domConstruct){

    var BillAdminSettingUpdateForm = declare('buildspace.apps.BillAdminSettingMaintenance.BillAdminSettingUpdateForm',[_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin],{
        billAdminSettingId: -1,
        billAdminSettingData: null,
        baseClass: "buildspace-form",
        templateString: billAdminSettingUpdateFormTemplate,
        constructor:function(args){
            this.RoundingTypeConstants = RoundingTypeConstants = [{
                label: buildspace.constants.ROUNDING_TYPE_DISABLED_TEXT,
                value: buildspace.constants.ROUNDING_TYPE_DISABLED
            }, {
                label: buildspace.constants.ROUNDING_TYPE_UPWARD_TEXT,
                value: buildspace.constants.ROUNDING_TYPE_UPWARD
            }, {
                label: buildspace.constants.ROUNDING_TYPE_DOWNWARD_TEXT,
                value: buildspace.constants.ROUNDING_TYPE_DOWNWARD
            }, {
                label: buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER_TEXT,
                value: buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER
            }, {
                label: buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH_TEXT,
                value: buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH
            }];

            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);
            var unitTypeSelectStore = new dojo.data.ItemFileReadStore({
                url:"projectBuilder/getUnitTypes"
            });

            this.unitTypeSelect = new Select({
                name: "unitType",
                store: unitTypeSelectStore,
                searchAttr: "name",
                style: "width:180px;padding:2px!important;"
            }).placeAt(this.unitTypeInputDivNode);

            this.buildUpQuantityRoundingSelect = new Select({
                name: "buildUpQuantityRoundingType",
                options: this.RoundingTypeConstants,
                searchAttr: "name",
                style: "width:240px;padding:2px!important;"
            }).placeAt(this.buildUpQuantityRoundingInputDivNode);

            this.buildUpRateRoundingSelect = new Select({
                name: "buildUpRateRoundingType",
                options: this.RoundingTypeConstants,
                searchAttr: "name",
                style: "width:240px;padding:2px!important;"
            }).placeAt(this.buildUpRateRoundingInputDivNode);
        },
        startup: function(){
            this.inherited(arguments);

            this.billAdminSettingUpdateForm.setFormValues(this.billAdminSettingData);
        },
        submit: function(){
            var self = this;
            var values = dojo.formToObject(self.billAdminSettingUpdateForm.id);
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'default/billAdminSettingUpdate',
                    content: values,
                    handleAs: 'json',
                    load: function(resp) {
                        var data = [];
                        data.push(resp);
                        self.billAdminSettingId = resp.id;
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        }
    });

    return declare('buildspace.apps.BillAdminSettingMaintenance.BillAdminSettingMaintenanceFormContainer', dijit.layout.BorderContainer, {
        style:"padding:0px;width:100%;height:100%;border:0px;",
        gutters: false,
        resource: null,
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            dojo.xhrPost({
                url: 'default/getBillAdminSettingDetail',
                handleAs: 'json',
                load: function(data){

                    var billAdminSettingUpdateForm = self.billAdminSettingUpdateForm = new BillAdminSettingUpdateForm({
                        region: "center",
                        billAdminSettingData: data,
                        billAdminSettingId: data.id
                    });

                    var toolbar = new Toolbar({
                        region: "top",
                        style: "outline:none!important;padding:2px;overflow:hidden;"
                    });

                    toolbar.addChild(new Button({
                        label: nls.save,
                        iconClass: 'icon-16-container icon-16-save',
                        style: 'outline:none!important;',
                        onClick: dojo.hitch(self.billAdminSettingUpdateForm, "submit")
                    }));

                    self.addChild(toolbar);
                    self.addChild(billAdminSettingUpdateForm);
                },
                error: function(error) {
                    //something is wrong somewhere
                }
            });
        }
    });
});