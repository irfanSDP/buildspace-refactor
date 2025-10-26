define('buildspace/apps/ProjectBuilder/BillManager/BillPropertiesForm',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/dom",
    "dojo/dom-construct",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/dom-attr",
    "dojo/dom-geometry",
    "dijit/form/Form",
    "dijit/form/RadioButton",
    "dijit/form/Select",
    "dijit/form/ValidationTextBox",
    "dijit/form/NumberTextBox",
    "dijit/form/CurrencyTextBox",
    "dojo/number",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/billPropertiesForm.html",
    "dojo/text!./templates/columnSettingsForm.html",
    "dojo/text!./templates/markupSettingsForm.html",
    'dojo/i18n!buildspace/nls/ProjectBuilder',
    './buildUpFloorAreaDialog'
], function(declare, html, dom, domConstruct, keys, domStyle, domAttr, domGeo, Form, RadioButton, Select, ValidateTextBox, NumberTextBox, CurrencyTextBox, number, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, columnSettingsTemplate, markupSettingsTemplate, nls, BuildUpFloorAreaDialog, on){

    var MarkupSettingsForm = declare('buildspace.apps.ProjectBuilder.BillManager.MarkupSettingsForm',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin],{
        billId: -1,
        templateString: markupSettingsTemplate,
        baseClass: "buildspace-form",
        nls: nls,
        billElementObj: null,
        markupSettings: null,
        _csrf_token: 0,
        region: "center",
        style: "padding:5px;overflow:auto;border:0px;height:146px;",
        constructor: function(){
            this.ROUNDING_TYPE_DISABLED = buildspace.constants.ROUNDING_TYPE_DISABLED;
            this.ROUNDING_TYPE_UPWARD = buildspace.constants.ROUNDING_TYPE_UPWARD;
            this.ROUNDING_TYPE_DOWNWARD = buildspace.constants.ROUNDING_TYPE_DOWNWARD;
            this.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER = buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER;
            this.ROUNDING_TYPE_NEAREST_TENTH = buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH;
            this.ROUNDING_TYPE_DISABLED_TEXT = buildspace.constants.ROUNDING_TYPE_DISABLED_TEXT;
            this.ROUNDING_TYPE_UPWARD_TEXT = buildspace.constants.ROUNDING_TYPE_UPWARD_TEXT;
            this.ROUNDING_TYPE_DOWNWARD_TEXT = buildspace.constants.ROUNDING_TYPE_DOWNWARD_TEXT;
            this.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER_TEXT = buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER_TEXT;
            this.ROUNDING_TYPE_NEAREST_TENTH_TEXT = buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH_TEXT;

            this.currencyAbbreviation = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);
            this.setValues();
        },
        setValues: function(){
            this.elementMarkupEnabledNode.set('checked', this.markupSettings.element_markup_enabled);
            var elementMarkupEnabledStr = this.markupSettings.element_markup_enabled ? nls.yesCapital : nls.noCapital;
            html.set(this.elementMarkupEnabledViewDivNode, elementMarkupEnabledStr);

            this.itemMarkupEnabledNode.set('checked', this.markupSettings.item_markup_enabled);
            var itemMarkupEnabledStr = this.markupSettings.item_markup_enabled ? nls.yesCapital : nls.noCapital;
            html.set(this.itemMarkupEnabledViewDivNode, itemMarkupEnabledStr);

            this.roundingTypeNode.set('value', this.markupSettings.rounding_type);
            var roundingTypeText = this.getRoundingTypeText();
            html.set(this.roundingTypeViewDivNode, roundingTypeText);
        },
        editElementMarkup: function(){
            domStyle.set(this.editElementMarkupButton.domNode,'display','none');
            domStyle.set(this.saveElementMarkupButton.domNode,'display','');

            domStyle.set(this.elementMarkupEnabledViewDivNode, 'display', 'none');
            domStyle.set(this.elementMarkupEnabledInputDivNode, 'display', '');
        },
        saveElementMarkup: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'...'
                }),
                grid = this.billElementObj;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'billManager/billMarkupSettingUpdate',
                    content: {
                        'bill_id': self.billId,
                        'type': 'element',
                        'bill_markup_setting[element_markup_enabled]': self.elementMarkupEnabledNode.get('checked'),
                        'bill_markup_setting[_csrf_token]': self._csrf_token
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success == true){
                            domStyle.set(self.editElementMarkupButton.domNode,'display','');
                            domStyle.set(self.saveElementMarkupButton.domNode,'display','none');

                            domStyle.set(self.elementMarkupEnabledViewDivNode, 'display', '');
                            domStyle.set(self.elementMarkupEnabledInputDivNode, 'display', 'none');

                            self.markupSettings = resp.data;
                            self.setValues();

                            grid.reconstructBillContainer();
                        }else{
                            var errors = resp.errors;
                            for(var error in errors){
                                if(self['error-'+error]){
                                    html.set(self['error-'+error], errors[error]);
                                }
                            }
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        editItemMarkup: function(){
            domStyle.set(this.editItemMarkupButton.domNode,'display','none');
            domStyle.set(this.saveItemMarkupButton.domNode,'display','');

            domStyle.set(this.itemMarkupEnabledViewDivNode, 'display', 'none');
            domStyle.set(this.itemMarkupEnabledInputDivNode, 'display', '');
        },
        saveItemMarkup: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'...'
                }),
                grid = this.billElementObj;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'billManager/billMarkupSettingUpdate',
                    content: {
                        'bill_id': self.billId,
                        'type': 'item',
                        'bill_markup_setting[item_markup_enabled]': self.itemMarkupEnabledNode.get('checked'),
                        'bill_markup_setting[_csrf_token]': self._csrf_token
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success == true){
                            domStyle.set(self.editItemMarkupButton.domNode,'display','');
                            domStyle.set(self.saveItemMarkupButton.domNode,'display','none');

                            domStyle.set(self.itemMarkupEnabledViewDivNode, 'display', '');
                            domStyle.set(self.itemMarkupEnabledInputDivNode, 'display', 'none');

                            self.markupSettings = resp.data;
                            self.setValues();

                            grid.reconstructBillContainer();
                        }else{
                            var errors = resp.errors;
                            for(var error in errors){
                                if(self['error-'+error]){
                                    html.set(self['error-'+error], errors[error]);
                                }
                            }
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        editRoundingType: function(){
            domStyle.set(this.editRoundingTypeButton.domNode,'display','none');
            domStyle.set(this.saveRoundingTypeButton.domNode,'display','');

            domStyle.set(this.roundingTypeViewDivNode, 'display', 'none');
            domStyle.set(this.roundingTypeInputDivNode, 'display', '');
        },
        saveRoundingType: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'...'
                }),
                grid = this.billElementObj;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'billManager/billMarkupSettingUpdate',
                    content: {
                        'bill_id': self.billId,
                        'type': 'rounding',
                        'bill_markup_setting[rounding_type]': self.roundingTypeNode.get('value'),
                        'bill_markup_setting[_csrf_token]': self._csrf_token
                    },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success == true){
                            domStyle.set(self.editRoundingTypeButton.domNode,'display','');
                            domStyle.set(self.saveRoundingTypeButton.domNode,'display','none');

                            domStyle.set(self.editRoundingTypeButton.domNode,'display','');
                            domStyle.set(self.saveRoundingTypeButton.domNode,'display','none');

                            domStyle.set(self.roundingTypeViewDivNode, 'display', '');
                            domStyle.set(self.roundingTypeInputDivNode, 'display', 'none');

                            self.markupSettings = resp.data;
                            self.setValues();

                            grid.reconstructBillContainer();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        getRoundingTypeText: function(){
            switch(this.markupSettings.rounding_type.toString()){
                case buildspace.constants.ROUNDING_TYPE_DISABLED:
                    return buildspace.constants.ROUNDING_TYPE_DISABLED_TEXT;
                case buildspace.constants.ROUNDING_TYPE_UPWARD:
                    return buildspace.constants.ROUNDING_TYPE_UPWARD_TEXT;
                case buildspace.constants.ROUNDING_TYPE_DOWNWARD:
                    return buildspace.constants.ROUNDING_TYPE_DOWNWARD_TEXT;
                case buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER:
                    return buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER_TEXT;
                case buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH:
                    return buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH_TEXT;
                default:
                    return null;
            }
        }
    });

    var ColumnSettingsForm = declare('buildspace.apps.ProjectBuilder.BillManager.ColumnSettingsForm',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin],{
        count: 0,
        billId: -1,
        columnSettingName:'',
        columnSettingQuantity:1,
        columnSettingRemeasurementQuantityEnabled: false,
        columnSettingUseOriginalQuantity: true,
        columnSettingTotalFloorAreaM2: 0,
        columnSettingTotalFloorAreaFT2: 0,
        columnSettingShowEstimatedTotalCost: false,
        columnSettingFloorAreaHasBuildUp: false,
        columnSettingFloorAreaUseMetric: false,
        columnSettingFloorAreaDisplayMetric: false,
        editMode: false,
        locked: false,
        columnId: -1,
        quantity: 1,
        templateString: columnSettingsTemplate,
        billElementObj: null,
        _csrf_token: 0,
        postCreate: function(){
            this.inherited(arguments);
            this.toggleEditableInputElements(this.editMode);

            this.setValues();
        },
        setValues: function(){
            //set input element values
            this.nameNode.set('value', this.columnSettingName);
            this.quantityNode.set('value', this.columnSettingQuantity);
            this.totalFloorAreaM2Node.set('value', this.columnSettingTotalFloorAreaM2);
            this.totalFloorAreaFT2Node.set('value', this.columnSettingTotalFloorAreaFT2);
            this.remeasurementQuantityEnabledNode.set('checked', this.columnSettingRemeasurementQuantityEnabled);
            this.showEstimatedTotalCostNode.set('checked', this.columnSettingShowEstimatedTotalCost);

            var showEstimatedTotalCostStr = this.columnSettingShowEstimatedTotalCost ? nls.yesCapital : nls.noCapital;

            html.set(this.nameViewDivNode, this.columnSettingName);
            html.set(this.showEstimatedTotalCostViewDivNode, showEstimatedTotalCostStr);
            html.set(this.quantityViewDivNode, ''+this.columnSettingQuantity+'');


            if(this.columnSettingFloorAreaUseMetric){
                html.set(this.totalFloorAreaM2ViewDivNode, this.createBuildUpFloorAreaLinks('M2'));
                html.set(this.totalFloorAreaFT2ViewDivNode, ''+this.columnSettingTotalFloorAreaFT2+'');

            }else{
                html.set(this.totalFloorAreaM2ViewDivNode, ''+this.columnSettingTotalFloorAreaM2+'');
                html.set(this.totalFloorAreaFT2ViewDivNode, this.createBuildUpFloorAreaLinks('FT2'));
            }

            if(this.columnSettingUseOriginalQuantity){
                this.originalQtyNode.set('checked', true);
            }else{
                this.remeasurementQtyNode.set('checked', true);
            }

            if(this.columnSettingFloorAreaDisplayMetric){
                this.floorAreaDisplayMetricNode.set('checked', true);
            }else{
                this.floorAreaDisplayImperialNode.set('checked', true);
            }

            this.toggleCheckUseMetric();

            if(!this.editMode){
                domStyle.set(this.saveButton.domNode,'display','none');
                domStyle.set(this.editButton.domNode,'display','');
            }else{
                domStyle.set(this.editButton.domNode,'display','none');
                domStyle.set(this.saveButton.domNode,'display','');
            }

            if(this.locked){
                this.lockColumnSetting();
            }
        },
        toggleCheckUseMetric: function(){
            if(this.columnSettingFloorAreaUseMetric){
                this.floorAreaUseMetricNode.set('checked', true);
            }else{
                this.floorAreaUseImperialNode.set('checked', true);
            }
        },
        lockColumnSetting: function(){
            this.removeColumnButton.set('disabled', true);
        },
        createBuildUpFloorAreaLinks: function(unit){
            var self = this;
            var displayValue = null;
            if(unit == 'M2'){
                displayValue = this.columnSettingTotalFloorAreaM2;
            }else{
                displayValue = this.columnSettingTotalFloorAreaFT2;
            }

            return domConstruct.create("a", {
                onclick: function(){
                    new BuildUpFloorAreaDialog({
                        billColumnSettingId: self.columnId,
                        billId: self.billId,
                        columnSettingForm: self
                    }).show();

                    return false;
                },
                class: 'buildUpLink',
                href: "#",
                title: "Open Build Up Floor Area",
                innerHTML: displayValue
            });
        },
        toggleDeleteButton: function(){
            var billId = this.billId;
            dojo.xhrGet({
                url: 'billManager/getColumnSettingCount',
                handleAs: 'json',
                content: { id: billId },
                load: function(resp){
                    var rows = dojo.query("#columnSettingTbody-"+billId+" > tr");
                    if(rows.hasOwnProperty('0')){
                        var firstRow = dijit.byId(domAttr.get(rows[0],'id'));
                        if(parseInt(resp.c) > 1){
                            firstRow.removeColumnButton.set('disabled', false);
                        }else{
                            firstRow.removeColumnButton.set('disabled', true);
                        }
                    }
                },
                error: function(error) {
                    //fucked!
                }
            });
        },
        toggleEditableInputElements: function(enable){
            var self = this;
            var toggleInputDivNode = function(show){
                if(show){

                    domStyle.set(self.remeasurementQuantityEnabledViewDivNode, 'display', 'none');
                    domStyle.set(self.showEstimatedTotalCostViewDivNode, 'display', 'none');

                    if(self.locked){
                        domStyle.set(self.nameViewDivNode, 'display', '');
                        domStyle.set(self.quantityViewDivNode, 'display', '');

                        domStyle.set(self.nameInputDivNode, 'display', 'none');
                        domStyle.set(self.quantityInputDivNode, 'display', 'none');
                    }else{
                        domStyle.set(self.nameViewDivNode, 'display', 'none');
                        domStyle.set(self.quantityViewDivNode, 'display', 'none');

                        domStyle.set(self.nameInputDivNode, 'display', '');
                        domStyle.set(self.quantityInputDivNode, 'display', '');
                    }

                    domStyle.set(self.remeasurementQuantityEnabledInputDivNode, 'display', '');
                    domStyle.set(self.showEstimatedTotalCostInputDivNode, 'display', '');
                }else{
                    domStyle.set(self.nameInputDivNode, 'display', 'none');
                    domStyle.set(self.quantityInputDivNode, 'display', 'none');
                    domStyle.set(self.remeasurementQuantityEnabledInputDivNode, 'display', 'none');
                    domStyle.set(self.showEstimatedTotalCostInputDivNode, 'display', 'none');

                    domStyle.set(self.nameViewDivNode, 'display', '');
                    domStyle.set(self.quantityViewDivNode, 'display', '');
                    domStyle.set(self.remeasurementQuantityEnabledViewDivNode, 'display', '');
                    domStyle.set(self.showEstimatedTotalCostViewDivNode, 'display', '');
                }

                self.toggleFloorAreaInputDiv(show);
            };

            if(enable){
                toggleInputDivNode(true);
                this.toggleFloorAreaDisplayMetric(true);

                if(this.columnSettingRemeasurementQuantityEnabled){
                    this.toggleUseOriginalQuantity(true);
                }else{
                    this.toggleUseOriginalQuantity(false);
                }
            }else{
                toggleInputDivNode(false);

                var enableRemeasurementQty = this.columnSettingRemeasurementQuantityEnabled ? nls.yesCapital : nls.noCapital;
                html.set(this.remeasurementQuantityEnabledViewDivNode, enableRemeasurementQty);

                this.toggleUseOriginalQuantity(false);
                this.toggleFloorAreaDisplayMetric(false);
            }
        },
        toggleUseOriginalQuantity: function(enableEdit){
            var self = this;
            var toggleInputDivNode = function(show){
                if(show){
                    domStyle.set(self.originalQtyViewDivNode, 'display', 'none');
                    domStyle.set(self.remeasurementQtyViewDivNode, 'display', 'none');

                    domStyle.set(self.originalQtyInputDivNode, 'display', '');
                    domStyle.set(self.remeasurementQtyInputDivNode, 'display', '');
                }else{
                    domStyle.set(self.originalQtyInputDivNode, 'display', 'none');
                    domStyle.set(self.remeasurementQtyInputDivNode, 'display', 'none');

                    domStyle.set(self.originalQtyViewDivNode, 'display', '');
                    domStyle.set(self.remeasurementQtyViewDivNode, 'display', '');
                }
            };

            if(enableEdit){
                toggleInputDivNode(true);
            }else{
                toggleInputDivNode(false);

                var originalQtyIconClass = null;
                var remeasurementQtyIconClass = null;
                if(this.columnSettingRemeasurementQuantityEnabled){
                    originalQtyIconClass =  this.columnSettingUseOriginalQuantity ? 'icon-16-container icon-16-checkmark2' : 'icon-16-container icon-16-cross';
                    remeasurementQtyIconClass = this.columnSettingUseOriginalQuantity ? 'icon-16-container icon-16-cross' : 'icon-16-container icon-16-checkmark2';
                }

                html.set(this.originalQtyViewDivNode, '<span class="dijitReset dijitInline dijitIcon '+originalQtyIconClass+'"></span>');
                html.set(this.remeasurementQtyViewDivNode, '<span class="dijitReset dijitInline dijitIcon '+remeasurementQtyIconClass+'"></span>');
            }
        },
        toggleFloorAreaInputDiv: function(show){
            var self = this;
            //reset input element on save
            if(show){
                if(self.columnSettingFloorAreaUseMetric){
                    domStyle.set(self.totalFloorAreaM2ViewDivNode, 'display', 'none');
                    domStyle.set(self.totalFloorAreaM2InputDivNode, 'display', '');

                    domStyle.set(self.totalFloorAreaFT2ViewDivNode, 'display', '');
                    domStyle.set(self.totalFloorAreaFT2InputDivNode, 'display', 'none');
                    self.totalFloorAreaFT2Node.set('value', self.columnSettingTotalFloorAreaFT2);
                    html.set(self['error-total_floor_area_ft2'], '');
                }else{
                    domStyle.set(self.totalFloorAreaFT2ViewDivNode, 'display', 'none');
                    domStyle.set(self.totalFloorAreaFT2InputDivNode, 'display', '');

                    domStyle.set(self.totalFloorAreaM2ViewDivNode, 'display', '');
                    domStyle.set(self.totalFloorAreaM2InputDivNode, 'display', 'none');
                    self.totalFloorAreaM2Node.set('value', self.columnSettingTotalFloorAreaM2);
                    html.set(self['error-total_floor_area_m2'], '');
                }
            }else{
                    domStyle.set(self.totalFloorAreaM2InputDivNode, 'display', 'none');
                    domStyle.set(self.totalFloorAreaM2ViewDivNode, 'display', '');
                    domStyle.set(self.totalFloorAreaFT2InputDivNode, 'display', 'none');
                    domStyle.set(self.totalFloorAreaFT2ViewDivNode, 'display', '');
            }
        },
        toggleFloorAreaDisplayMetric: function(enableEdit){
            var self = this;
            var toggleInputDivNode = function(show){
                if(show){
                    domStyle.set(self.floorAreaDisplayMetricViewDivNode, 'display', 'none');
                    domStyle.set(self.floorAreaDisplayImperialViewDivNode, 'display', 'none');

                    domStyle.set(self.floorAreaDisplayMetricInputDivNode, 'display', '');
                    domStyle.set(self.floorAreaDisplayImperialInputDivNode, 'display', '');
                }else{
                    domStyle.set(self.floorAreaDisplayMetricInputDivNode, 'display', 'none');
                    domStyle.set(self.floorAreaDisplayImperialInputDivNode, 'display', 'none');

                    domStyle.set(self.floorAreaDisplayMetricViewDivNode, 'display', '');
                    domStyle.set(self.floorAreaDisplayImperialViewDivNode, 'display', '');
                }
            };

            if(enableEdit){
                toggleInputDivNode(true);
            }else{
                toggleInputDivNode(false);

                var floorAreaDisplayMetricIconClass =  this.columnSettingFloorAreaDisplayMetric ? 'icon-16-container icon-16-checkmark2' : 'icon-16-container icon-16-cross';
                var floorAreaDisplayImperialIconClass = this.columnSettingFloorAreaDisplayMetric ? 'icon-16-container icon-16-cross' : 'icon-16-container icon-16-checkmark2';

                html.set(this.floorAreaDisplayMetricViewDivNode, '<span class="dijitReset dijitInline dijitIcon '+floorAreaDisplayMetricIconClass+'"></span>');
                html.set(this.floorAreaDisplayImperialViewDivNode, '<span class="dijitReset dijitInline dijitIcon '+floorAreaDisplayImperialIconClass+'"></span>');
            }
        },
        doEnableRemeasurementQuantity: function(){
            var self = this, checked = this.remeasurementQuantityEnabledNode.get('checked');
            if(!checked){
                if(this.columnSettingRemeasurementQuantityEnabled){
                    var onNo = function(){
                        self.remeasurementQuantityEnabledNode.set('checked', true);
                        self.toggleUseOriginalQuantity(true);
                    };
                    var content = '<div>'+nls.detachAllQty2BuildUpAndLink+'</div>';
                    buildspace.dialog.confirm(nls.confirmation,content,60,280, function(){
                        self.columnSettingRemeasurementQuantityEnabled = false;
                        self.toggleUseOriginalQuantity(false);},
                    onNo);
                }else{
                    self.toggleUseOriginalQuantity(false);
                }
            }else{
                self.toggleUseOriginalQuantity(true);
            }
        },
        edit: function(){
            domStyle.set(this.editButton.domNode,'display','none');
            domStyle.set(this.saveButton.domNode,'display','');
            this.editMode = true;
            this.toggleEditableInputElements(true);
        },
        checkManualFloorArea: function(){
            var totalFloorAreaM2 = number.format(this.totalFloorAreaM2Node.get('value'), {places: 2});
            var totalFloorAreaFT2 = number.format(this.totalFloorAreaFT2Node.get('value'), {places: 2});

            if(this.columnId > 0 && this.columnSettingFloorAreaHasBuildUp && ((totalFloorAreaM2 != this.columnSettingTotalFloorAreaM2) || (totalFloorAreaFT2 != this.columnSettingTotalFloorAreaFT2))){
                return true;
            }
        },
        save: function(){
            var onYes = function(){
                // flush buildUp
                // change Setting for use Metric
                this.columnSettingFloorAreaHasBuildUp = false;
                this.doSave();
            };

            var onCancel = function(){
                //revert value of ft2 & m2 to original
                this.totalFloorAreaM2Node.set('value', this.columnSettingTotalFloorAreaM2);
                this.totalFloorAreaFT2Node.set('value', this.columnSettingTotalFloorAreaFT2);
            };

            if(this.checkManualFloorArea()){
                var content = '<div>'+nls.detachAllBuildUpAndLink+'</div>';
                buildspace.dialog.confirm(nls.confirmation, content,60,280, onYes, onCancel);
            }else{
                this.doSave();
            }
        },
        doSave: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'...'
                }),
                grid = this.billElementObj;

            var useOriginalQty = this.originalQtyNode.get('checked') ? true : false;
            var floorAreaDisplayMetric = this.floorAreaDisplayMetricNode.get('checked') ? true : false;
            var floorAreaUseMetric = this.floorAreaUseMetricNode.get('checked') ? true : false;
            var values = {
                'id': this.columnId,
                'bill_column_setting[project_structure_id]': this.billId,
                'bill_column_setting[name]': this.nameNode.get('value'),
                'bill_column_setting[quantity]': this.quantityNode.get('value'),
                'bill_column_setting[total_floor_area_m2]': this.totalFloorAreaM2Node.get('value'),
                'bill_column_setting[total_floor_area_ft2]': this.totalFloorAreaFT2Node.get('value'),
                'bill_column_setting[floor_area_display_metric]': floorAreaDisplayMetric,
                'bill_column_setting[floor_area_use_metric]': floorAreaUseMetric,
                'bill_column_setting[floor_area_has_build_up]': this.columnSettingFloorAreaHasBuildUp,
                'bill_column_setting[remeasurement_quantity_enabled]': this.remeasurementQuantityEnabledNode.get('checked'),
                'bill_column_setting[show_estimated_total_cost]': this.showEstimatedTotalCostNode.get('checked'),
                'bill_column_setting[use_original_quantity]': useOriginalQty,
                'bill_column_setting[_csrf_token]': this._csrf_token
            };

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'billManager/billColumnSettingUpdate',
                    content: values,
                    handleAs: 'json',
                    load: function(resp) {
                        html.set(self['error-name'], '');
                        html.set(self['error-quantity'], '');
                        html.set(self['error-total_floor_area_m2'], '');
                        html.set(self['error-total_floor_area_ft2'], '');

                        if(resp.success == true){
                            domStyle.set(self.saveButton.domNode,'display','none');
                            domStyle.set(self.editButton.domNode,'display','');

                            self.editMode = false;
                            self.columnId = resp.item.id;
                            self.columnSettingName = resp.item.name;
                            self.columnSettingQuantity = resp.item.quantity;
                            self.columnSettingRemeasurementQuantityEnabled = resp.item.remeasurement_quantity_enabled;
                            self.columnSettingUseOriginalQuantity = resp.item.use_original_quantity;
                            self.columnSettingTotalFloorAreaFT2 = number.format(resp.item.total_floor_area_ft2, {places: 2});
                            self.columnSettingTotalFloorAreaM2 = number.format(resp.item.total_floor_area_m2, {places: 2});
                            self.columnSettingFloorAreaHasBuildUp = resp.item.floor_area_has_build_up;
                            self.columnSettingFloorAreaUseMetric = resp.item.floor_area_use_metric;
                            self.columnSettingFloorAreaDisplayMetric = resp.item.floor_area_display_metric;
                            self.columnSettingShowEstimatedTotalCost = resp.item.show_estimated_total_cost;

                            self.setValues();
                            self.toggleEditableInputElements(false);
                            self.toggleDeleteButton();

                            grid.reconstructBillContainer();
                        }else{
                            var errors = resp.errors;
                            for(var error in errors){
                                if(self['error-'+error]){
                                    html.set(self['error-'+error], errors[error]);
                                }
                            }
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        refreshColumn: function(){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'...'
                }),
                grid = this.billElementObj;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: 'billManager/getBillColumnSetting',
                    content: {
                        'id': self.columnId
                    },
                    handleAs: 'json',
                    load: function(resp) {

                        domStyle.set(self.saveButton.domNode,'display','none');
                        domStyle.set(self.editButton.domNode,'display','');

                        self.editMode = false;
                        self.columnId = resp.item.id;
                        self.columnSettingName = resp.item.name;
                        self.columnSettingQuantity = resp.item.quantity;
                        self.columnSettingRemeasurementQuantityEnabled = resp.item.remeasurement_quantity_enabled;
                        self.columnSettingUseOriginalQuantity = resp.item.use_original_quantity;
                        self.columnSettingTotalFloorAreaFT2 = number.format(resp.item.total_floor_area_ft2, {places: 2});
                        self.columnSettingTotalFloorAreaM2 = number.format(resp.item.total_floor_area_m2, {places: 2});
                        self.columnSettingFloorAreaHasBuildUp = resp.item.floor_area_has_build_up;
                        self.columnSettingFloorAreaUseMetric = resp.item.floor_area_use_metric;
                        self.columnSettingFloorAreaDisplayMetric = resp.item.floor_area_display_metric;
                        self.columnSettingShowEstimatedTotalCost = resp.item.show_estimated_total_cost;

                        self.setValues();
                        self.toggleEditableInputElements(false);

                        grid.reconstructBillContainer();

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        doChangeFloorAreaUseMetric: function(isChecked){

            if(!isChecked || (this.floorAreaUseMetricNode.get('checked') == this.columnSettingFloorAreaUseMetric)){
                return;
            }

            var self = this;

            var onCancel = function(){
                self.toggleCheckUseMetric();
            };

            var changeUseMetricSetting = function(){
                self.columnSettingFloorAreaUseMetric = (self.floorAreaUseMetricNode.get('checked')) ? true : false;
            };

            var onYes = function(){
                // flush buildUp
                // change Setting for use Metric
                self.columnSettingFloorAreaHasBuildUp = false;
                changeUseMetricSetting();
                if(!this.editMode){
                    self.doSave();
                }else{
                    //toggle input
                    self.toggleFloorAreaInputDiv(true);
                }
            };

            if(self.columnSettingFloorAreaHasBuildUp){
                var content = '<div>'+nls.detachAllBuildUpAndLink+'</div>';
                buildspace.dialog.confirm(nls.confirmation, content,60,280, onYes, onCancel);
            }else{
                if(!this.editMode){
                    self.doSave();
                }else{
                    //toggle input
                    changeUseMetricSetting();
                    self.toggleFloorAreaInputDiv(true);
                }
            }
        },
        removeColumn: function(){
            var self = this,
            pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.deleting+'. '+nls.pleaseWait+'...'
            });

            if(this.columnId !== null && this.columnId > 0){
                var values = {
                        id: this.columnId
                    },
                    grid = this.billElementObj;

                new buildspace.dialog.confirm(nls.deleteBillTypeDialogBoxTitle, nls.deleteBillTypeDialogBoxMsg, 80, 320, function() {

                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: 'billManager/billColumnSettingDelete',
                            content: values,
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success === true) {
                                    self.destroy();
                                    self.onDelete();
                                    self.toggleDeleteButton();

                                    grid.reconstructBillContainer();
                                }

                                pb.hide();
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        });
                    });

                }, function() {
                });
            }else{
                this.destroy();
                this.onDelete();
            }
        },
        onDelete: function(){
            return;
        }
    });

    var BillPropertiesMainInfoForm = declare("buildspace.apps.ProjectBuilder.BillManager.BillPropertiesMainInfoForm", [Form,
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
        formData: null,
        buildUpRateRoundingType: null,
        buildUpQuantityRoundingType: null,
        unitTypeText: null,
        description: null,
        nls: nls,
        style: "padding:5px;overflow:auto;border:0px;height:140px;",
        constructor: function(){
            var BillTypeConstantArray = this.BillTypeConstantArray = [];
            this.ROUNDING_TYPE_DISABLED = buildspace.constants.ROUNDING_TYPE_DISABLED;
            this.ROUNDING_TYPE_UPWARD = buildspace.constants.ROUNDING_TYPE_UPWARD;
            this.ROUNDING_TYPE_DOWNWARD = buildspace.constants.ROUNDING_TYPE_DOWNWARD;
            this.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER = buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER;
            this.ROUNDING_TYPE_NEAREST_TENTH = buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH;
            this.ROUNDING_TYPE_DISABLED_TEXT = buildspace.constants.ROUNDING_TYPE_DISABLED_TEXT;
            this.ROUNDING_TYPE_UPWARD_TEXT = buildspace.constants.ROUNDING_TYPE_UPWARD_TEXT;
            this.ROUNDING_TYPE_DOWNWARD_TEXT = buildspace.constants.ROUNDING_TYPE_DOWNWARD_TEXT;
            this.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER_TEXT = buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER_TEXT;
            this.ROUNDING_TYPE_NEAREST_TENTH_TEXT = buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH_TEXT;
            BillTypeConstantArray[buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_STANDARD] = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_STANDARD_TEXT;
            BillTypeConstantArray[buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRELIMINARY] = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRELIMINARY_TEXT;
            BillTypeConstantArray[buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PROVISIONAL] = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PROVISIONAL_TEXT;
            BillTypeConstantArray[buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRIMECOST] = buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_TYPE_PRIMECOST_TEXT;

            this.currencyAbbreviation = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);
            html.set(this.unitTypeTextNode, this.unitTypeText);
            html.set(this.billTypeTextNode, this.BillTypeConstantArray[this.formData.billType]);
        },
        disableForm: function(){
            this.buildUpQuantityRoundingTypeNode.set('disabled', true);
            this.titleNode.set('disabled', true);
            this.descriptionNode.set('disabled', true);
        },
        submit: function(){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'...'
            }),
            grid = this.billElementObj;

            if(this.validate()){
                var self = this,
                    values = dojo.formToObject(this.id);

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'billManager/billPropertiesUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="error-bill_setting_"]').forEach(function(node){
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
                                                tac[i].set('title', buildspace.truncateString(item.title, 35)+ ' :: '+buildspace.apps.ProjectBuilder.getBillTypeText(item.bill_type));
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

                                        grid.reconstructBillContainer();
                                    }
                                }});
                            }else{
                                for(var key in resp.errors){
                                    var msg = resp.errors[key];
                                    html.set(dom.byId("error-bill_setting_"+key), msg);
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
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formData);

            if(this.projectBreakdownGrid.rootProject.status_id != buildspace.constants.STATUS_PRETENDER)
                this.disableForm();
        }
    });

    return declare("buildspace.apps.ProjectBuilder.BillManager.BillPropertiesForm", dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;overflow:auto;",
        gutters: false,
        region: 'center',
        billId: -1,
        projectBreakdownGrid: null,
        tenderAlternativeBillGridId: null,
        billElementObj: null,
        nls: nls,
        locked: false,
        totalColumnSettings: 0,
        columnSettings_csrf_token: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            //setting up form values before we start our form
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'billManager/billPropertiesForm',
                    handleAs: 'json',
                    content: { id: self.billId },
                    load: function(data){
                        self.markupSettings_csrf_token = data.markup_settings_csrf_token;
                        self.columnSettings_csrf_token = data.column_settings_csrf_token;
                        self.renderBillPropertiesInfoForm(data.bill_setting);
                        self.renderMarkupSettings(data.markup_settings);
                        self.renderBillColumnSettings(data.column_settings);
                        self.resize();
                        self.toggleDeleteButtonAfterStartup();
                        pb.hide();
                    },
                    error: function(error) {
                        //something is wrong somewhere
                        pb.hide();
                    }
                });
            });
        },
        renderBillPropertiesInfoForm: function(data){
            var form = this.mainForm = new BillPropertiesMainInfoForm({
                billId: this.billId,
                projectBreakdownGrid: this.projectBreakdownGrid,
                tenderAlternativeBillGridId: this.tenderAlternativeBillGridId,
                unitTypeText: data.unit_type_text,
                billElementObj: this.billElementObj,
                formData: data
            });

            this.addChild(form);
        },
        renderMarkupSettings: function(data){
            this.addChild(new MarkupSettingsForm({
                billId: this.billId,
                billElementObj: this.billElementObj,
                markupSettings: data,
                _csrf_token: this.markupSettings_csrf_token
            }));
        },
        renderBillColumnSettings: function(data){
            var container = this,
                billId = this.billId,
                billElementObj = this.billElementObj,
                _csrf_token = this.columnSettings_csrf_token;

            declare("ColumnSettingTableWidget", [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
                style: "padding:5px;overflow:auto;border:0px;overflow:auto;",
                region: 'bottom',
                locked: container.locked,
                baseClass: "buildspace-form",
                totalColumnSettings: 0,
                columnSettings: data,
                templateString: '<div><fieldset>' +
                    '<legend>'+nls.columnSettings+'</legend>' +
                    '<div data-dojo-type="dijit/form/Button" id="addColumnButton-'+billId+'" data-dojo-attach-event="onClick:addColumn" data-dojo-attach-point="addColumnButton" data-dojo-props="iconClass:\'icon-16-container icon-16-add\'">'+nls.addType+'</div>' +
                    '<div style="height:2px;">&nbsp;</div>' +
                    '<table class="buildspace-table">' +
                    '<thead><tr class="gridHeader">' +
                    '<th class="gridCell" style="text-align:center;" rowspan="2">No.</th><th class="gridCell" rowspan="2">'+nls.name+'</th><th class="gridCell" style="text-align: center;" rowspan="2">'+nls.totalUnit+'</th><th class="gridCell" style="text-align: center;" rowspan="2">'+nls.enable+' '+nls.qty+' (2)</th><th class="gridCell" style="text-align: center;" rowspan="2">'+nls.use+' '+nls.qty+'</th><th class="gridCell" style="text-align: center;" rowspan="2">'+nls.use+' '+nls.qty+' (2)</th><th class="gridCell" style="text-align: center;" colspan="7">'+nls.gross+' '+nls.floorArea+'</th><th class="gridCell" style="text-align: center;" rowspan="2">'+nls.action+'</th><th class="gridCell" style="text-align: center;" rowspan="2">'+nls.delete+'</th>' +
                    '</tr><tr class="gridHeaderBottom"><th style="text-align:center;" colspan="2">'+nls.metreSquare+'</th><th style="text-align:center;" colspan="2">'+nls.squareFeet+'</th><th style="text-align:center;">'+nls.show +' '+nls.metreSquare+'</th><th style="text-align:center;">'+nls.show +' '+nls.squareFeet+'</th><th style="text-align:center;">'+nls.show +' '+nls.estimation+'</th></tr></thead>' +
                    '<tbody id="columnSettingTbody-'+billId+'" data-dojo-attach-point="columnSettingsTBody"></tbody>' +
                    '</table>' +
                    '</fieldset></div>',
                postCreate: function(){
                    this.inherited(arguments);
                    var self = this, count = 0;
                    dojo.forEach(this.columnSettings, function(columnSetting, i){
                        self.updateTotalColumnSettings(true);
                        count = self.totalColumnSettings;
                        var columnSettingsForm = new ColumnSettingsForm({
                            count: count,
                            columnSettingName: columnSetting.name,
                            columnSettingQuantity: columnSetting.quantity,
                            columnSettingRemeasurementQuantityEnabled: columnSetting.remeasurement_quantity_enabled,
                            columnSettingUseOriginalQuantity: columnSetting.use_original_quantity,
                            columnSettingTotalFloorAreaM2: number.format(columnSetting.total_floor_area_m2, {places: 2}),
                            columnSettingTotalFloorAreaFT2: number.format(columnSetting.total_floor_area_ft2, {places: 2}),
                            columnSettingFloorAreaHasBuildUp: columnSetting.floor_area_has_build_up,
                            columnSettingFloorAreaUseMetric: columnSetting.floor_area_use_metric,
                            columnSettingFloorAreaDisplayMetric: columnSetting.floor_area_display_metric,
                            columnSettingShowEstimatedTotalCost: columnSetting.show_estimated_total_cost,
                            columnId: columnSetting.id,
                            billId: billId,
                            locked: self.locked,
                            billElementObj: billElementObj,
                            _csrf_token: _csrf_token,
                            onDelete: function(){
                                self.updateTotalColumnSettings(false);
                                self.addColumnButton.set('disabled', false);
                                container.resize();
                            }
                        });
                        columnSettingsForm.placeAt(self.columnSettingsTBody);
                        dojo.parser.parse(columnSettingsForm.domNode);
                    },self);
                },
                updateTotalColumnSettings: function(add){
                    if(add){
                        this.totalColumnSettings = this.totalColumnSettings + 1
                    }else{
                        this.totalColumnSettings = this.totalColumnSettings - 1;
                    }

                    if(this.totalColumnSettings >= 5 || this.locked){
                        this.addColumnButton.set('disabled', true);
                    }else{
                        this.addColumnButton.set('disabled', false);
                    }
                },
                addColumn: function(){
                    this.updateTotalColumnSettings(true);
                    var self = this;
                    var columnSettingsForm = new ColumnSettingsForm({
                        billId: billId,
                        billElementObj: billElementObj,
                        locked: this.locked,
                        _csrf_token: _csrf_token,
                        count: this.totalColumnSettings,
                        editMode: true,
                        onDelete: dojo.hitch(self, 'updateTotalColumnSettings', false)
                    });
                    columnSettingsForm.placeAt(this.columnSettingsTBody);
                    dojo.parser.parse(columnSettingsForm.domNode);
                    columnSettingsForm.toggleDeleteButton();
                    container.resize();
                }
            });
            this.columnSettingsTable = new ColumnSettingTableWidget();
            this.addChild(this.columnSettingsTable);
        },
        resize: function(){
            var height = domGeo.getContentBox(this.domNode).h - 265;

            if(this.columnSettingsTable){
                domStyle.set(this.columnSettingsTable.domNode, "height", height+"px");
            }

            this.inherited(arguments);
        },
        toggleDeleteButtonAfterStartup: function(){
            var billId = this.billId;
            dojo.xhrGet({
                url: 'billManager/getColumnSettingCount',
                handleAs: 'json',
                content: { id: billId },
                load: function(resp){
                    var rows = dojo.query("#columnSettingTbody-"+billId+" > tr");
                    if(rows.hasOwnProperty('0')){
                        var firstRow = dijit.byId(domAttr.get(rows[0],'id'));
                        if(parseInt(resp.c) > 1){
                            firstRow.removeColumnButton.set('disabled', false);
                        }else{
                            firstRow.removeColumnButton.set('disabled', true);
                        }
                    }
                },
                error: function(error) {
                    //fucked!
                }
            });
        }
    });
});