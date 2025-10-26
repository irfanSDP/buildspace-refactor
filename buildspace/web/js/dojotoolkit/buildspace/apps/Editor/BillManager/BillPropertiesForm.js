define('buildspace/apps/Editor/BillManager/BillPropertiesForm',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/dom",
    "dojo/dom-construct",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/dom-attr",
    "dojo/dom-geometry",
    "dojo/number",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/billPropertiesForm.html",
    "dojo/text!./templates/columnSettingsForm.html",
    'dojo/i18n!buildspace/nls/Tendering'
], function(declare, html, dom, domConstruct, keys, domStyle, domAttr, domGeo, number, _WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin, template, columnSettingsTemplate, nls){

    var ColumnSettingsForm = declare('buildspace.apps.Editor.BillManager.ColumnSettingsForm',[_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin],{
        count: 0,
        billId: -1,
        columnSettingName:'',
        columnSettingQuantity:'',
        columnId: -1,
        quantity: 1,
        templateString: columnSettingsTemplate,
        postCreate: function(){
            this.inherited(arguments);
            this.setValues();
        },
        setValues: function(){
            //set input element values
            html.set(this.nameViewDivNode, this.columnSettingName);
            html.set(this.quantityViewDivNode, ''+this.columnSettingQuantity+'');
        }
    });

    var BillPropertiesMainInfoForm = declare("buildspace.apps.Editor.BillManager.BillPropertiesMainInfoForm", [
        _WidgetBase,
        _TemplatedMixin,
        _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'top',
        billId: -1,
        projectBreakdownGrid: null,
        formData: null,
        unitTypeText: null,
        description: null,
        nls: nls,
        style: "padding:5px;overflow:auto;border:0px;height:140px;",
        constructor: function(){
            var BillTypeConstantArray = this.BillTypeConstantArray = [];
            BillTypeConstantArray[buildspace.apps.Editor.ProjectStructureConstants.BILL_TYPE_STANDARD] = buildspace.apps.Editor.ProjectStructureConstants.BILL_TYPE_STANDARD_TEXT;
            BillTypeConstantArray[buildspace.apps.Editor.ProjectStructureConstants.BILL_TYPE_PRELIMINARY] = buildspace.apps.Editor.ProjectStructureConstants.BILL_TYPE_PRELIMINARY_TEXT;
            BillTypeConstantArray[buildspace.apps.Editor.ProjectStructureConstants.BILL_TYPE_PROVISIONAL] = buildspace.apps.Editor.ProjectStructureConstants.BILL_TYPE_PROVISIONAL_TEXT;
            BillTypeConstantArray[buildspace.apps.Editor.ProjectStructureConstants.BILL_TYPE_PROVISIONAL] = buildspace.apps.Editor.ProjectStructureConstants.BILL_TYPE_PROVISIONAL_TEXT;
            BillTypeConstantArray[buildspace.apps.Editor.ProjectStructureConstants.BILL_TYPE_PRIMECOST] = buildspace.apps.Editor.ProjectStructureConstants.BILL_TYPE_PRIMECOST_TEXT;

            this.currencyAbbreviation = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;

            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);

            html.set(this.titleNode, this.formData['bill_setting[title]']);
            html.set(this.descriptionNode, this.formData['bill_setting[description]']);
            html.set(this.unitTypeTextNode, this.unitTypeText);
            html.set(this.billTypeTextNode, this.BillTypeConstantArray[this.formData.billType]);
        },
        startup: function(){
            this.inherited(arguments);
        }
    });

    return declare("buildspace.apps.Editor.BillManager.BillPropertiesForm", dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;overflow:auto;",
        gutters: false,
        region: 'center',
        billId: -1,
        projectBreakdownGrid: null,
        nls: nls,
        totalColumnSettings: 0,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'billProperties/'+self.billId,
                    handleAs: 'json',
                    load: function(data){
                        self.renderBillPropertiesInfoForm(data.bill_setting);
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
            var form = this.mainForm = new BillPropertiesMainInfoForm({
                billId: this.billId,
                projectBreakdownGrid: this.projectBreakdownGrid,
                unitTypeText: data.unit_type_text,
                formData: data
            });

            this.addChild(form);
        },
        renderBillColumnSettings: function(data){
            var container = this,
                billId = this.billId;

            declare("ColumnSettingTableWidget", [_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
                style: "padding:5px;overflow:auto;border:0px;overflow:auto;",
                region: 'bottom',
                baseClass: "buildspace-form",
                totalColumnSettings: 0,
                columnSettings: data,
                templateString: '<div><fieldset>' +
                    '<legend>'+nls.columnSettings+'</legend>' +
                    '<div style="height:2px;">&nbsp;</div>' +
                    '<table class="buildspace-table">' +
                    '<thead><tr class="gridHeader">' +
                    '<th class="gridCell" style="text-align:center;" rowspan="2">No.</th><th class="gridCell" rowspan="2">'+nls.name+'</th><th class="gridCell" style="text-align: center;" rowspan="2">'+nls.totalUnit+'</th>' +
                    '</tr>' +
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
                            columnId: columnSetting.id,
                            billId: billId
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
                }
            });
            this.columnSettingsTable = new ColumnSettingTableWidget();
            this.addChild(this.columnSettingsTable);
        },
        resize: function(){
            var height = domGeo.getContentBox(this.domNode).h - 125;

            if(this.columnSettingsTable){
                domStyle.set(this.columnSettingsTable.domNode, "height", height+"px");
            }

            this.inherited(arguments);
        }
    });
});
