define('buildspace/apps/Location/ProjectLocationManagement/BillSettings/BillSettingsContainer',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/dom-construct",
    "dijit/layout/TabContainer",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/billSettingsForm.html",
    'dojo/i18n!buildspace/nls/Location'
], function(declare, lang, domConstruct, TabContainer, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls){

    var BillSettingsForm = declare('buildspace.apps.Location.ProjectLocationManagement.BillSettings.BillSettingsForm',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin],{
        templateString: template,
        baseClass: "buildspace-form",
        nls: nls,
        billColumnSettings: null,
        billId: null,
        baseApp: null,
        _csrf_token: null,
        postCreate: function(){
            this.inherited(arguments);

            var self = this;
            var remeasurementQtyEnabled = false;

            dojo.forEach(this.billColumnSettings, function(billColumnSetting){

                var checkedOriginalQty, checkedRemeasurementQty;
                if(billColumnSetting.location_bill_settings.use_original_qty){
                    checkedOriginalQty = "checked";
                    checkedRemeasurementQty = null;
                }else{
                    checkedOriginalQty = null;
                    checkedRemeasurementQty = "checked";
                }

                var row = '<tr>' +
                    '<td class="gridCell counter" style="width:30px;text-align: center;vertical-align: middle;"><h1></h1></td>\n' +
                    '<td class="gridCell" style="vertical-align:middle;padding-right:5px;">'+billColumnSetting.name+'</td>\n' +
                    '<td class="gridCell" style="width:60px;text-align: center;vertical-align: middle;">'+billColumnSetting.quantity+'</td>\n' +
                    '<td class="gridCell" style="width:80px;text-align:center;vertical-align: middle;">\n' +
                    '<input name="bill_column_setting-'+billColumnSetting.id+'"  '+checkedOriginalQty+' value="1" type="radio" data-dojo-type="dijit/form/RadioButton" />\n' +
                    '</td>';

                if(billColumnSetting.remeasurement_quantity_enabled){
                    remeasurementQtyEnabled = true;
                    row += '<td class="gridCell" style="width:80px;text-align: center;vertical-align:middle;">' +
                        '<input name="bill_column_setting-'+billColumnSetting.id+'" '+checkedRemeasurementQty+' value="0" type="radio" data-dojo-type="dijit/form/RadioButton" />' +
                        '</td>';
                }

                if(!billColumnSetting.remeasurement_quantity_enabled && remeasurementQtyEnabled){
                    row += '<td class="gridCell" style="width:80px;text-align: center;vertical-align:middle;"></td>';
                }

                row += '</tr>';

                domConstruct.place(domConstruct.toDom(row), self.LocationBillSettingsTableBody);
            });

            if(remeasurementQtyEnabled){
                var row = domConstruct.toDom('<th class="gridCell" style="text-align: center;" rowspan="2">'+nls.useQty2+'</th>');

                domConstruct.place(row, this.LocationBillSettingsTableHeader);
            }
        },
        save: function(){
            var values = dojo.formToObject(this.LocationBillSettingsForm.id);

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'...'
            });

            lang.mixin(values, { _csrf_token: this._csrf_token, bid: this.billId});

            var baseApp = this.baseApp;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'location/locationBillSettingUpdate',
                    content: values,
                    handleAs: 'json',
                    load: function(resp) {
                        if(baseApp){
                            baseApp.resetBQLocationTab();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        }
    });

    return declare('buildspace.apps.Location.ProjectLocationManagement.BillSettings.BillSettingsContainer', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        project: null,
        baseApp: null,
        postCreate: function(){
            this.inherited(arguments);

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            var self = this;

            pb.show().then(function(){
                var aContainer = new TabContainer({
                    region: "center",
                    style:"padding:0;margin:0;width:100%;height:100%;border:none;outline:none;"
                });

                dojo.xhrGet({
                    url: "location/getBillProperties",
                    handleAs: "json",
                    preventCache: true,
                    content: {
                        pid: self.project.id
                    },
                    load: function(data){

                        if(data.length > 0){
                            dojo.forEach(data, function(obj){
                                self.createBillForm(obj, aContainer);
                            });
                        }

                        self.addChild(aContainer);
                        pb.hide();
                    },
                    error: function(error){
                        pb.hide();
                    }
                });
            });
        },
        createBillForm: function(data, aContainer){
            var container = new dijit.layout.BorderContainer({
                id: 'accPane-LocationBillSettings_'+data.bill_id,
                title: data.bill_title,
                style:"padding:0;margin:0;width:100%;height:100%;",
                gutters: false
            });

            var form = new BillSettingsForm({
                billColumnSettings: data.bill_column_settings,
                billId: data.bill_id,
                _csrf_token: data._csrf_token,
                region: "center",
                baseApp: this.baseApp
            });

            var toolbar = new dijit.Toolbar({region: "top", style:"border-top:none;border-left:none;border-right:none;padding:2px;width:100%;"});

            toolbar.addChild(new dijit.form.Button({
                label: nls.save,
                iconClass: "icon-16-container icon-16-save",
                style:"outline:none!important;",
                onClick: dojo.hitch(form, 'save')
            }));

            container.addChild(toolbar);
            container.addChild(form);

            aContainer.addChild(container);
        }
    });
});
