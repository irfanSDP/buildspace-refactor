define('buildspace/apps/ProjectBuilderReport/BillManager/BillPropertiesForm',[
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

    var MarkupSettingsForm = declare('buildspace.apps.ProjectBuilderReport.BillManager.MarkupSettingsForm',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin],{
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

    var ColumnSettingsForm = declare('buildspace.apps.ProjectBuilderReport.BillManager.ColumnSettingsForm',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin],{
        count: 0,
        billId: -1,
        columnSettingName:'',
        columnSettingQuantity:'',
        columnSettingRemeasurementQuantityEnabled: false,
        columnSettingUseOriginalQuantity: true,
        columnSettingTotalFloorAreaM2: 0,
        columnSettingTotalFloorAreaFT2: 0,
        columnSettingShowEstimatedTotalCost: false,
        columnSettingFloorAreaHasBuildUp: false,
        columnSettingFloorAreaUseMetric: false,
        columnSettingFloorAreaDisplayMetric: false,
        editMode: false,
        columnId: -1,
        quantity: 1,
        locked: false,
        templateString: columnSettingsTemplate,
        billElementObj: null,
        _csrf_token: 0,
        postCreate: function(){
            this.inherited(arguments);

            this.toggleEditableInputElements(false);
            this.setValues();
        },
        setValues: function() {
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


            if(this.columnSettingFloorAreaUseMetric) {
                html.set(this.totalFloorAreaM2ViewDivNode, this.createBuildUpFloorAreaLinks('M2'));
                html.set(this.totalFloorAreaFT2ViewDivNode, ''+this.columnSettingTotalFloorAreaFT2+'');

            }else {
                html.set(this.totalFloorAreaM2ViewDivNode, ''+this.columnSettingTotalFloorAreaM2+'');
                html.set(this.totalFloorAreaFT2ViewDivNode, this.createBuildUpFloorAreaLinks('FT2'));
            }

            if(this.columnSettingUseOriginalQuantity){
                this.originalQtyNode.set('checked', true);

                this.remeasurementQtyNode.set('disabled', true);

            }else{
                this.remeasurementQtyNode.set('checked', true);

                this.originalQtyNode.set('disabled', true);
            }

            if(this.columnSettingFloorAreaDisplayMetric){
                this.floorAreaDisplayMetricNode.set('checked', true);
            }else{
                this.floorAreaDisplayImperialNode.set('checked', true);
            }

            this.toggleCheckUseMetric();
        },
        toggleCheckUseMetric: function(){
            if(this.columnSettingFloorAreaUseMetric){
                this.floorAreaUseMetricNode.set('checked', true);
            }else{
                this.floorAreaUseImperialNode.set('checked', true);
            }

            this.floorAreaUseMetricNode.set('disabled', true);
            this.floorAreaUseImperialNode.set('disabled', true);
        },
        createBuildUpFloorAreaLinks: function(unit){
            var self = this;
            var displayValue = null;
            if(unit == 'M2'){
                displayValue = self.columnSettingTotalFloorAreaM2;
            }else{
                displayValue = self.columnSettingTotalFloorAreaFT2;
            }

            var totalFloorAreaLinks = domConstruct.create("a", {
                onclick: function(){
                    var gfaDialog = new BuildUpFloorAreaDialog({
                        billColumnSettingId: self.columnId,
                        billId: self.billId,
                        columnSettingForm: self,
                        unit: unit
                    });
                    gfaDialog.show();
                },
                class: 'buildUpLink',
                href: "#",
                title: "Open Build Up Floor Area",
                innerHTML: displayValue
            });

            return totalFloorAreaLinks;
        },
        toggleEditableInputElements: function(){
            domStyle.set(this.nameInputDivNode, 'display', 'none');
            domStyle.set(this.quantityInputDivNode, 'display', 'none');
            domStyle.set(this.remeasurementQuantityEnabledInputDivNode, 'display', 'none');
            domStyle.set(this.showEstimatedTotalCostInputDivNode, 'display', 'none');

            domStyle.set(this.nameViewDivNode, 'display', '');
            domStyle.set(this.quantityViewDivNode, 'display', '');
            domStyle.set(this.showEstimatedTotalCostViewDivNode, 'display', '');

            this.toggleFloorAreaInputDiv();

            var enableRemeasurementQty = this.columnSettingRemeasurementQuantityEnabled ? nls.yesCapital : nls.noCapital;
            html.set(this.remeasurementQuantityEnabledViewDivNode, enableRemeasurementQty);

            this.toggleUseOriginalQuantity();
            this.toggleFloorAreaDisplayMetric();
        },
        toggleUseOriginalQuantity: function(){
            domStyle.set(this.originalQtyInputDivNode, 'display', 'none');
            domStyle.set(this.remeasurementQtyInputDivNode, 'display', 'none');

            domStyle.set(this.originalQtyViewDivNode, 'display', 'block');
            domStyle.set(this.remeasurementQtyViewDivNode, 'display', 'block');

            var originalQtyIconClass = null;
            var remeasurementQtyIconClass = null;
            if(this.columnSettingRemeasurementQuantityEnabled){
                originalQtyIconClass =  this.columnSettingUseOriginalQuantity ? 'icon-16-container icon-16-checkmark2' : 'icon-16-container icon-16-cross';
                remeasurementQtyIconClass = this.columnSettingUseOriginalQuantity ? 'icon-16-container icon-16-cross' : 'icon-16-container icon-16-checkmark2';
            }

            html.set(this.originalQtyViewDivNode, '<span class="dijitReset dijitInline dijitIcon '+originalQtyIconClass+'"></span>');
            html.set(this.remeasurementQtyViewDivNode, '<span class="dijitReset dijitInline dijitIcon '+remeasurementQtyIconClass+'"></span>');
        },
        toggleFloorAreaInputDiv: function(){
            domStyle.set(this.totalFloorAreaM2InputDivNode, 'display', 'none');
            domStyle.set(this.totalFloorAreaM2ViewDivNode, 'display', '');
            domStyle.set(this.totalFloorAreaFT2InputDivNode, 'display', 'none');
            domStyle.set(this.totalFloorAreaFT2ViewDivNode, 'display', '');
        },
        toggleFloorAreaDisplayMetric: function(){
            domStyle.set(this.floorAreaDisplayMetricInputDivNode, 'display', 'none');
            domStyle.set(this.floorAreaDisplayImperialInputDivNode, 'display', 'none');

            domStyle.set(this.floorAreaDisplayMetricViewDivNode, 'display', '');
            domStyle.set(this.floorAreaDisplayImperialViewDivNode, 'display', '');

            var floorAreaDisplayMetricIconClass =  this.columnSettingFloorAreaDisplayMetric ? 'icon-16-container icon-16-checkmark2' : 'icon-16-container icon-16-cross';
            var floorAreaDisplayImperialIconClass = this.columnSettingFloorAreaDisplayMetric ? 'icon-16-container icon-16-cross' : 'icon-16-container icon-16-checkmark2';

            html.set(this.floorAreaDisplayMetricViewDivNode, '<span class="dijitReset dijitInline dijitIcon '+floorAreaDisplayMetricIconClass+'"></span>');
            html.set(this.floorAreaDisplayImperialViewDivNode, '<span class="dijitReset dijitInline dijitIcon '+floorAreaDisplayImperialIconClass+'"></span>');
        },
        checkManualFloorArea: function(){
            var totalFloorAreaM2 = number.format(this.totalFloorAreaM2Node.get('value'), {places: 2});
            var totalFloorAreaFT2 = number.format(this.totalFloorAreaFT2Node.get('value'), {places: 2});

            if(this.columnId > 0 && this.columnSettingFloorAreaHasBuildUp && ((totalFloorAreaM2 != this.columnSettingTotalFloorAreaM2) || (totalFloorAreaFT2 != this.columnSettingTotalFloorAreaFT2))){
                return true;
            }
        }
    });

    var Form = declare("buildspace.apps.ProjectBuilderReport.BillManager.BillPropertiesMainInfoForm", [Form,
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
        explorer: null,
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
            BillTypeConstantArray[buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.BILL_TYPE_STANDARD] = buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.BILL_TYPE_STANDARD_TEXT;
            BillTypeConstantArray[buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.BILL_TYPE_PRELIMINARY] = buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.BILL_TYPE_PRELIMINARY_TEXT;
            BillTypeConstantArray[buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.BILL_TYPE_PROVISIONAL] = buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.BILL_TYPE_PROVISIONAL_TEXT;
            BillTypeConstantArray[buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.BILL_TYPE_PROVISIONAL] = buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.BILL_TYPE_PROVISIONAL_TEXT;
            BillTypeConstantArray[buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.BILL_TYPE_PRIMECOST] = buildspace.apps.ProjectBuilderReport.ProjectStructureConstants.BILL_TYPE_PRIMECOST_TEXT;

            this.currencyAbbreviation = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);
            html.set(this.unitTypeTextNode, this.unitTypeText);
            html.set(this.billTypeTextNode, this.BillTypeConstantArray[this.formData.billType]);
        },
        disableForm: function(){
            var self = this;

            self.buildUpQuantityRoundingTypeNode.set('disabled', true);
            self.titleNode.set('disabled', true);
            self.descriptionNode.set('disabled', true);
        },
        startup: function(){
            this.inherited(arguments);
            this.setFormValues(this.formData);

            this.disableForm();
        }
    });

    var Container = declare("buildspace.apps.ProjectBuilderReport.BillManager.BillPropertiesForm", dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;overflow:auto;",
        gutters: false,
        region: 'center',
        billId: -1,
        explorer: null,
        billElementObj: null,
        nls: nls,
        totalColumnSettings: 0,
        locked: false,
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
            var form = this.mainForm = new Form({
                billId: this.billId,
                explorer: this.explorer,
                unitTypeText: data.unit_type_text,
                billElementObj: this.billElementObj,
                formData: data
            });

            this.addChild(form);
        },
        renderMarkupSettings: function(data){
            var _csrf_token = this.markupSettings_csrf_token;
            var form = new MarkupSettingsForm({
                billId: this.billId,
                billElementObj: this.billElementObj,
                markupSettings: data,
                _csrf_token: _csrf_token
            });
            this.addChild(form);
        },
        renderBillColumnSettings: function(data){
            var container = this,
                billId = this.billId,
                billElementObj = this.billElementObj,
                _csrf_token = this.columnSettings_csrf_token;

            var ColumnSettingTableWidget = declare("ColumnSettingTableWidget", [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
                style: "padding:5px;overflow:auto;border:0px;overflow:auto;",
                region: 'bottom',
                locked: container.locked,
                baseClass: "buildspace-form",
                totalColumnSettings: 0,
                columnSettings: data,
                templateString: '<div><fieldset>' +
                    '<legend>'+nls.columnSettings+'</legend>' +
                    '<div style="height:2px;">&nbsp;</div>' +
                    '<table class="buildspace-table">' +
                    '<thead><tr class="gridHeader">' +
                    '<th class="gridCell" style="text-align:center;" rowspan="2">No.</th><th class="gridCell" rowspan="2">'+nls.name+'</th><th class="gridCell" style="text-align: center;" rowspan="2">'+nls.totalUnit+'</th><th class="gridCell" style="text-align: center;" rowspan="2">'+nls.enable+' '+nls.qty+' (2)</th><th class="gridCell" style="text-align: center;" rowspan="2">'+nls.use+' '+nls.qty+'</th><th class="gridCell" style="text-align: center;" rowspan="2">'+nls.use+' '+nls.qty+' (2)</th><th class="gridCell" style="text-align: center;" colspan="7">'+nls.gross+' '+nls.floorArea+'</th>' +
                    '</tr><tr class="gridHeaderBottom"><th style="text-align:center;" colspan="2">'+nls.metreSquare+'</th><th style="text-align:center;" colspan="2">'+nls.squareFeet+'</th><th style="text-align:center;">'+nls.show +' '+nls.metreSquare+'</th><th style="text-align:center;">'+nls.show +' '+nls.squareFeet+'</th><th style="text-align:center;">'+nls.show +' '+nls.estimation+'</th></tr></thead>' +
                    '<tbody id="columnSettingTbody-'+billId+'" data-dojo-attach-point="columnSettingsTBody"></tbody>' +
                    '</table>' +
                    '</fieldset></div>',
                postCreate: function(){
                    this.inherited(arguments);
                    var count = 0;
                    dojo.forEach(this.columnSettings, function(columnSetting, i){
                        count = this.totalColumnSettings;
                        var columnSettingsForm = new ColumnSettingsForm({
                            count: count,
                            locked: container.locked,
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
                            billElementObj: billElementObj,
                            _csrf_token: _csrf_token
                        });
                        columnSettingsForm.placeAt(this.columnSettingsTBody);
                        dojo.parser.parse(columnSettingsForm.domNode);
                    },this);
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
        }
    });

    return Container;
});