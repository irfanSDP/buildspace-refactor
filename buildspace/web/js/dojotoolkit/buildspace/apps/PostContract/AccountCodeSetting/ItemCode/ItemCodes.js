define('buildspace/apps/PostContract/AccountCodeSetting/ItemCode/ItemCodes',[
    'dojo/_base/declare',
    "dojo/html",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    'dojo/text!../../templates/itemCodeSettingsBreakdownColumHeaderCell.html',
    'dojo/text!../../templates/itemCodeSettingsBreakdownForm.html',
    'dojo/text!../../templates/itemCodeSettingsBreakdownRowContainer.html',
    'dojo/text!../../templates/itemCodeSettingsBreakdownInputCell.html',
    'dojo/text!../../templates/itemCodeSettingsBreakdownCell.html',
    'dojo/i18n!buildspace/nls/ItemCodeSettings',
],
function(declare, html, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, breakdownColumnHeaderCellTemplate, breakdownFormTemplate, breakdownRowContainerTemplate, breakdownInputCellTemplate, breakdownCellTemplate, nls) {
    var ItemCodeSettingsBreakdownForm = declare('buildspace.apps.PostContract.AccountCodeSetting.ItemCode.ItemCodesettingsBreakdownForm', [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: breakdownFormTemplate,
        baseClass: 'buildspace-form',
        nls: nls,
        project: null,
        claimCertificate: null,
        data: null,
        constructor: function() {
            this.inherited(arguments);
        },
        postCreate: function() {
            this.inherited(arguments);
            this.renderItemCodeSettingBreakdownColumHeaderCells();
            this.renderItemCodeSettingBreakdownRows();
        },
        startup: function() {
            this.inherited(arguments);
            this.itemCodeSettingObjectIdsHiddenInput.set('value', this.data.objectIds.join(','));
            this.itemCodeSettingIdsHiddenInput.set('value', this.data.itemCodeSettingIds.join(','));
        },
        renderItemCodeSettingBreakdownColumHeaderCells: function() {
            var self = this;

            for(var i = 0; i < self.data.itemCodeSettings.length; i++) {
                var breakdownColumnHeaderCell = new ItemCodeSettingsBreakdownColumnHeaderCell({
                    header: self.data.itemCodeSettings[i].description,
                });

                breakdownColumnHeaderCell.placeAt(self.itemCodeSettingBreakdownColumnHeader);
            }
        },
        renderItemCodeSettingBreakdownRows: function() {
            var self = this;

            for(var i = 0; i < this.data['itemCodeSettingObjects'].length; i++)
            {
                var breakdownRowContainer = new ItemCodeSettingsBreakdownRowContainer({
                    objectId: this.data['itemCodeSettingObjects'][i].object_id,
                    rowData: self.data['itemCodeSettingObjects'][i],
                });

                breakdownRowContainer.placeAt(self.itemCodeSettingsBreakdownBody);
            }
        },
        save: function() {
            var self = this;
            if(this.itemCodeSettingBreakdownForm.validate()) {
                html.set(self.item_code_settings_error.id, '');

                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

                var formData = dojo.formToObject(self.itemCodeSettingBreakdownForm.id);
                pb.show().then(function() {
                    dojo.xhrPost({
                        url: 'AccountCodeSetting/saveItemCodeSettingsBreakdown',
                        handleAs: 'json',
                        content: formData,
                        load: function(data){
                            if(data.success) {
                                pb.hide();
                                buildspace.dialog.alert('Success', nls.itemCodeSettingSaved, 50, 300);
                            } else {
                                pb.hide();
                                html.set(self.item_code_settings_error.id, data.errors.global_errors[0]);
                            }
                        },
                        error: function(error) {
                            console.log(error);
                            pb.hide();
                        }
                    });
                });
            }
        },
    });

    var ItemCodeSettingsBreakdownColumnHeaderCell = declare('buildspace.apps.PostContract.AccountCodeSettings.ItemCode.ItemCodeSettingsBreakdownColumnHeaderCell', [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: breakdownColumnHeaderCellTemplate,
        baseClass: 'buildspace-form',
        nls: nls,
        header: null,
    });

    var ItemCodeSettingsBreakdownRowContainer = declare('buildspace.apps.PostContract.AccountCodeSettings.ItemCode.ItemCodeSettingsBreakdownRowContainer', [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: breakdownRowContainerTemplate,
        baseClass: 'buildspace-form',
        nls: nls,
        prefix: null,
        objectId: null,
        rowData: null,
        formHeader: null,
        constructor: function() {
            this.inherited(arguments);
        },
        postCreate: function() {
            this.inherited(arguments);
            var self = this;
            
            var descriptionCell = new ItemCodeSettingsBreakdownCell({
                name: '',
                value: self.rowData.description,
                textAlign: 'left',
            });

            descriptionCell.placeAt(self.itemCodeSettingsBreakdownRowContainer);

            var currentClaimCell = new ItemCodeSettingsBreakdownCell({
                name: '',
                value: self.rowData.currentClaim,
                textAlign: 'right',
            });
            
            currentClaimCell.placeAt(self.itemCodeSettingsBreakdownRowContainer);

            for(var i = 0; i < self.rowData.breakdowns.length; i++) {
                var fieldName = 'item_code_setting_object[item_code_setting_object_id_' + self.rowData.object_id + '_item_code_setting_id_' + self.rowData.breakdowns[i].item_code_setting_id + ']';
                var cellData = {
                    name: fieldName,
                    value: self.rowData.breakdowns[i].amount,
                    textAlign: 'right',
                };

                var breakdownCell = new ItemCodeSettingsBreakdownInputCell(cellData);
                breakdownCell.placeAt(self.itemCodeSettingsBreakdownRowContainer);
            }
        },
        startup: function() {
            this.inherited(arguments);
            this.currentClaimHiddenInput.set('value', this.rowData.currentClaim);
            this.objectTypeIdHiddenInput.set('value', this.rowData.objectType);
        },
    });

    var ItemCodeSettingsBreakdownInputCell = declare('buildspace.apps.PostContract.AccountCodeSettings.ItemCode.ItemCodeSettingsBreakdownInputCell', [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: breakdownInputCellTemplate,
        baseClass: 'buildspace-form',
        nls: nls,
        name: null,
        value: null,
        textAlign: null,
        constructor: function() {
            this.inherited(arguments);
        },
        postCreate: function() {
            this.inherited(arguments);
            this.breakdownCell.set('value', this.value);
        },
    });

    var ItemCodeSettingsBreakdownCell = declare('buildspace.apps.PostContract.AccountCodeSettings.ItemCode.ItemCodeSettingsBreakdownCell', [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: breakdownCellTemplate,
        baseClass: 'buildspace-form',
        nls: nls,
        name: null,
        value: null,
        textAlign: null,
        constructor: function() {
            this.inherited(arguments);
        },
        postCreate: function() {
            this.inherited(arguments);
        },
    });

    return declare('buildspace.apps.PostContract.AccountCodeSetting.ItemCode.ItemCodes', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        project: null,
        claimCertificate: null,
        postCreate:function() {
            this.inherited(arguments);
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.loading+'. '+nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'AccountCodeSetting/getItemCodeSettingBreakdowns',
                    content: { 
                        projectStructureId: self.project.id,
                        claimCertificateId: self.claimCertificate.id,
                     },
                    handleAs: 'json',
                    load: function(response) {
                        if(response.success){
                            var itemCodeSettingsBreakdownForm = new ItemCodeSettingsBreakdownForm({
                                region: 'center',
                                id: 'item-code-settingsForm-' + self.project.id,
                                style: "padding:0;width:100%;margin:5px;border:none;height:100%;",
                                gutters: false,
                                project: self.project,
                                claimCertificate: self.claimCertificate,
                                data: response.data,
                            });

                            var toolbar = new dijit.Toolbar({
                                id: 'itemCodeSettingsToolbar',
                                region:"top", 
                                style:"outline:none!important;padding:2px;width:100%;",
                            });

                            toolbar.addChild(
                                new dijit.form.Button({
                                    label: nls.save,
                                    iconClass: "icon-16-container icon-16-save",
                                    onClick: dojo.hitch(itemCodeSettingsBreakdownForm, 'save')
                                })
                            );

                            self.addChild(toolbar);
                            self.addChild(itemCodeSettingsBreakdownForm);
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        console.log(error);
                        pb.hide();
                    }
                });
            });
        },
    });
});