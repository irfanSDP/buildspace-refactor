define('buildspace/apps/PostContractSubPackageReport/Preliminaries/lumpSumPercentDialog',[
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
    "dojo/text!./templates/lumpSumPercentForm.html",
    'dojo/currency',
    'dojo/number',
    'dojo/i18n!buildspace/nls/PostContractSubPackage'
], function(declare, lang, when, html, dom, keys, domStyle, Form, CurrencyTextBox, NumberTextBox, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, currency, number, nls) {

    var Form = declare("buildspace.apps.PostContractSubPackageReport.Preliminaries.LumpSumPercentForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
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

            dojo.connect(this.rateNode, "onKeyUp", function() {
                self.updateTotal();
            });

            dojo.connect(this.percentageNode, "onKeyUp", function() {
                self.updateTotal();
            });

            this.percentageNode.set('readOnly', true);
            this.rateNode.set('readOnly', this.disableEditingMode);
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
                url: 'postContractPreliminaries/lumpSumPercentageForm',
                handleAs: 'json',
                content: { id: this.itemObj.id },
                load: function(data){
                    self.lumpSumPercentageForm.setFormValues(data);
                    pb.hide();
                },
                error: function(error) {
                    //something is wrong somewhere
                    pb.hide();
                }
            }
            dojo.xhrGet(xhrArgs);
        },
        submit: function(dialog){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'...'
            });
            var self = this, billGridStore = this.billGridStore,
                values = dojo.formToObject(self.lumpSumPercentageForm.id),
                xhrArgs = {
                    url: 'postContractPreliminaries/lumpSumPercentageUpdate',
                    content: {id : values.id, rate: values['bill_item_lump_sum_percentage[amount]'], _csrf_token: values['bill_item_lump_sum_percentage[_csrf_token]']},
                    handleAs: 'json',
                    load: function(resp) {
                        pb.hide();
                        var data = resp.data;
                        if(resp.success){
                            billGridStore.fetchItemByIdentity({ 'identity' : self.itemObj.id,  onItem : function(item){
                                for(var property in data){
                                    if(item.hasOwnProperty(property) && property != billGridStore._getIdentifierAttribute()){
                                        billGridStore.setValue(item, property, data[property]);
                                    }
                                }
                                billGridStore.save();
                            }});
                            dialog.hide();
                        }
                    },
                    error: function(error) {
                        pb.hide();
                        dialog.hide();
                    }
                };

            if(this.lumpSumPercentageForm.validate()){
                pb.show();
                dojo.xhrPost(xhrArgs);
            }
        },
        updateTotal: function(){
            var rate = isNaN(this.rateNode.get('value')) ? 0 : this.rateNode.get('value'),
                percentage = isNaN(this.percentageNode.get('value')) ? 0 : this.percentageNode.get('value'),
                amount = number.parse(rate) * (number.parse(percentage) / 100);
            this.amountNode.set('value', amount);
        }
    });

    var Dialog = declare('buildspace.apps.PostContractSubPackageReport.Preliminaries.LumpSumPercentDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.lumpSumPercentage,
        itemObj: null,
        billGridStore: null,
        elementGridStore: null,
        disableEditingMode: false,
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
                style:"padding:0px;width:400px;height:150px;",
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
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style:"outline:none!important;",
                    disabled: this.disableEditingMode,
                    onClick: dojo.hitch(form, 'submit', this)
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

    return Dialog;
});