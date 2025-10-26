define('buildspace/apps/ProjectBuilderReport/BillManager/primeCostRateDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/form/Form",
    "dijit/form/CurrencyTextBox",
    "dijit/form/NumberTextBox",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/primeCostRateForm.html",
    'dojo/currency',
    'dojo/number',
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function(declare, lang, when, html, dom, keys, domStyle, Form, CurrencyTextBox, NumberTextBox, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, currency, number, nls){

    var Form = declare("buildspace.apps.ProjectBuilderReport.BillManager.PrimeCostRateForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        style: "outline:none;",
        itemObj: null,
        nls: nls,
        billGridStore: null,
        elementGridStore: null,
        region: 'center',
        disableEditingMode: false,
        constructor:function(args){
            this.inherited(arguments);
            this.currencyAbbreviation = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;
        },
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            dojo.connect(this.supplyRateNode, "onKeyUp", function() {
                self.updateTotal();
            });

            dojo.connect(this.wastagePercentageNode, "onKeyUp", function() {
                self.updateTotal();
            });

            dojo.connect(this.labourForInstallationNode, "onKeyUp", function() {
                self.updateTotal();
            });

            dojo.connect(this.otherCostNode, "onKeyUp", function() {
                self.updateTotal();
            });

            dojo.connect(this.profitPercentageNode, "onKeyUp", function() {
                self.updateTotal();
            });
        },
        startup: function(){
            this.inherited(arguments);
            var self = this;
            //setting up form values before we start our form
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            pb.show();
            var xhrArgs = {
                url: 'billManager/primeCostRateForm',
                handleAs: 'json',
                content: { id: this.itemObj.id },
                load: function(data){
                    self.primeCostRateForm.setFormValues(data);
                    pb.hide();
                },
                error: function(error) {
                    //something is wrong somewhere
                    pb.hide();
                }
            }
            dojo.xhrGet(xhrArgs);
        },
        updateTotal: function(){
            var supplyRate = isNaN(this.supplyRateNode.get('value')) ? 0 : this.supplyRateNode.get('value'),
                wastagePercentage = isNaN(this.wastagePercentageNode.get('value')) ? 0 : this.wastagePercentageNode.get('value'),
                wastageAmount = number.parse(supplyRate) * (number.parse(wastagePercentage) / 100),
                labourForInstallation = isNaN(this.labourForInstallationNode.get('value')) ? 0 : this.labourForInstallationNode.get('value'),
                otherCost = isNaN(this.otherCostNode.get('value')) ? 0 : this.otherCostNode.get('value'),
                profitPercentage = isNaN(this.profitPercentageNode.get('value')) ? 0 : this.profitPercentageNode.get('value'),
                profitAmount = (number.parse(supplyRate) + wastageAmount + number.parse(labourForInstallation) + number.parse(otherCost)) * (number.parse(profitPercentage) / 100);

            this.wastageAmountNode.set('value', wastageAmount);
            this.profitAmountNode.set('value', profitAmount);

            var total = (number.parse(supplyRate) + number.parse(wastageAmount) + number.parse(labourForInstallation) + number.parse(otherCost) + number.parse(profitAmount));

            this.totalNode.set('value', total);
        }
    });

    var Dialog = declare('buildspace.apps.ProjectBuilderReport.BillManager.PrimeCostRateDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.primeCostRate,
        itemObj: null,
        billGridStore: null,
        elementGridStore: null,
        currentBillLockedStatus: false,
        disableEditingMode: false,
        constructor:function(args){
            if (args.currentBillLockedStatus) {
                this.disableEditingMode = true;
            }
        },
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;

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
            key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createContent: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:450px;height:280px;",
                gutters: false
            });

            var form = new Form({
                itemObj: this.itemObj,
                billGridStore: this.billGridStore,
                elementGridStore: this.elementGridStore,
                disableEditingMode: this.disableEditingMode
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

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

    return Dialog;
});