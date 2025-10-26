define('buildspace/apps/PostContract/BillManager/buildUpQuantitySummary',[
    'dojo/_base/declare',
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/Button",
    "dijit/form/TextBox",
    "dijit/form/CheckBox",
    "dijit/form/Select",
    'dijit/InlineEditBox',
    "dojo/text!./templates/buildUpQuantitySummary.html",
    "dojo/number",
    "dojo/i18n!buildspace/nls/BuildUpQuantityGrid"
], function(declare, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, Button, ValidateTextBox, Checkbox, Select, InlineEditBox, template, number, nls){

    return declare("buildspace.apps.PostContract.BillManager.buildUpQuantitySummary", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        style: "outline:none;",
        itemId: -1,
        billColumnSettingId: -1,
        nls: nls,
        region: 'bottom',
        _csrf_token: null,
        container: null,
        applyConversionFactor: false,
        conversionFactorAmount: 0,
        conversionFactorOperator: buildspace.constants.ARITHMETIC_OPERATOR_ADDITION,
        hasLinkedQty: false,
        linkedTotalQuantity: 0,
        totalQuantity: 0,
        totalQuantityAfterConversion: 0,
        roundingType: 1,
        finalQuantity: 0,
        disableEditingMode: false,
        constructor:function(args){
            this.ROUNDING_TYPE_DISABLED                  = buildspace.constants.ROUNDING_TYPE_DISABLED;
            this.ROUNDING_TYPE_UPWARD                    = buildspace.constants.ROUNDING_TYPE_UPWARD;
            this.ROUNDING_TYPE_DOWNWARD                  = buildspace.constants.ROUNDING_TYPE_DOWNWARD;
            this.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER      = buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER;
            this.ROUNDING_TYPE_NEAREST_TENTH             = buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH;
            this.ROUNDING_TYPE_DISABLED_TEXT             = buildspace.constants.ROUNDING_TYPE_DISABLED_TEXT;
            this.ROUNDING_TYPE_UPWARD_TEXT               = buildspace.constants.ROUNDING_TYPE_UPWARD_TEXT;
            this.ROUNDING_TYPE_DOWNWARD_TEXT             = buildspace.constants.ROUNDING_TYPE_DOWNWARD_TEXT;
            this.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER_TEXT = buildspace.constants.ROUNDING_TYPE_NEAREST_WHOLE_NUMBER_TEXT;
            this.ROUNDING_TYPE_NEAREST_TENTH_TEXT        = buildspace.constants.ROUNDING_TYPE_NEAREST_TENTH_TEXT;

            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);

            var self = this;

            if(this.hasLinkedQty){
                domStyle.set(this.linkedTotalQuantityRow, 'display', '');
            }

            dojo.xhrGet({
                url: 'billBuildUpQuantity/getBuildUpSummary',
                content: { id: this.itemId, bill_column_setting_id: this.billColumnSettingId, type: this.type },
                handleAs: 'json',
                load: function(resp) {
                    self.applyConversionFactor = resp.apply_conversion_factor;
                    self.applyConversionFactorNode.set('checked', self.applyConversionFactor);
                    self.doApplyConversionFactor();

                    self.roundingType = resp.rounding_type;
                    self.roundingTypeNode.set('value', self.roundingType);

                    self.conversionFactorAmount = resp.conversion_factor_amount;
                    self.conversionFactorAmountNode.set('value', self.conversionFactorAmount);

                    self.conversionFactorOperator = resp.conversion_factor_operator;
                    self.conversionFactorOperatorNode.set('value', self.conversionFactorOperator);

                    var linkedTotalQuantity = self.linkedTotalQuantity = number.format(resp.linked_total_quantity,{places:2});
                    dojo.attr(self.linkedTotalQuantityNode, "innerHTML", linkedTotalQuantity);

                    var totalQuantity = self.totalQuantity = number.format(resp.total_quantity,{places:2});
                    dojo.attr(self.totalQuantityNode, "innerHTML", totalQuantity);

                    var finalQuantity = self.finalQuantity = number.format(resp.final_quantity,{places:2});
                    dojo.attr(self.finalQuantityNode, "innerHTML", finalQuantity);

                    // disable or enable inputs
                    self.applyConversionFactorNode.set('disabled', self.disableEditingMode);
                    self.roundingTypeNode.set('disabled', self.disableEditingMode);
                    self.conversionFactorAmountNode.set('disabled', self.disableEditingMode);
                    self.conversionFactorOperatorNode.set('disabled', self.disableEditingMode);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        },
        startup: function(){
            this.inherited(arguments);
            this.toggleConversionFactorRows(false);
        },
        doApplyConversionFactor: function(){
            var checked = this.applyConversionFactorNode.get('checked');

            if(checked != this.applyConversionFactor){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                pb.show();

                var self = this;

                dojo.xhrPost({
                    url: 'billBuildUpQuantity/buildUpSummaryApplyConversionFactorUpdate',
                    content: { id: this.itemId, value: checked, _csrf_token: this._csrf_token, bill_column_setting_id: this.billColumnSettingId, type: this.type },
                    handleAs: 'json',
                    load: function(resp) {
                        self.conversionFactorAmount = resp.conversion_factor_amount;
                        self.conversionFactorAmountNode.set('value', self.conversionFactorAmount);

                        self.conversionFactorOperator = resp.conversion_factor_operator;
                        self.conversionFactorOperatorNode.set('value', self.conversionFactorOperator);

                        var finalQuantity = self.finalQuantity = number.format(resp.final_quantity,{places:2});
                        dojo.attr(self.finalQuantityNode, "innerHTML", finalQuantity);

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            }

            this.applyConversionFactor = checked;
            this.toggleConversionFactorRows(checked) ;
        },
        toggleConversionFactorRows: function(show){
            if(show){
                dojo.query("."+this.itemId+"-conversion_factor-row").style({display: ""});
            }else{
                dojo.query("."+this.itemId+"-conversion_factor-row").style({display: "none"});
            }
            this.container.resize();
        },
        submitConversionFactorAmount: function(){
            var amount = this.conversionFactorAmountNode.get('value');
            if(amount != this.conversionFactorAmount){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                pb.show();
                var self = this;
                dojo.xhrPost({
                    url: 'billBuildUpQuantity/buildUpSummaryConversionFactorUpdate',
                    content: { id: this.itemId, val: amount, token: 'amount', _csrf_token: this._csrf_token, bill_column_setting_id: this.billColumnSettingId, type: this.type },
                    handleAs: 'json',
                    load: function(resp) {
                        self.conversionFactorAmount = resp.conversion_factor_amount;
                        self.conversionFactorAmountNode.set('value', self.conversionFactorAmount);

                        var finalQuantity = self.finalQuantity = number.format(resp.final_quantity,{places:2});
                        dojo.attr(self.finalQuantityNode, "innerHTML", finalQuantity);

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            }
        },
        submitConversionFactorOperator: function(){
            var operator = this.conversionFactorOperatorNode.get('value');
            if(operator != this.conversionFactorOperator){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                pb.show();
                var self = this;
                dojo.xhrPost({
                    url: 'billBuildUpQuantity/buildUpSummaryConversionFactorUpdate',
                    content: { id: this.itemId, val: operator, token: 'operator', _csrf_token: this._csrf_token, bill_column_setting_id: this.billColumnSettingId, type: this.type },
                    handleAs: 'json',
                    load: function(resp) {
                        self.conversionFactorOperator = resp.conversion_factor_operator;
                        self.conversionFactorOperatorNode.set('value', self.conversionFactorOperator);

                        var finalQuantity = self.finalQuantity = number.format(resp.final_quantity,{places:2});
                        dojo.attr(self.finalQuantityNode, "innerHTML", finalQuantity);

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            }
        },
        refreshTotalQuantity: function(){
            var self = this;

            dojo.xhrGet({
                url: 'billBuildUpQuantity/getBuildUpSummary',
                content: { id: this.itemId, bill_column_setting_id: this.billColumnSettingId, type: this.type  },
                handleAs: 'json',
                load: function(resp) {
                    var linkedTotalQuantity = self.linkedTotalQuantity = number.format(resp.linked_total_quantity,{places:2});
                    dojo.attr(self.linkedTotalQuantityNode, "innerHTML", linkedTotalQuantity);

                    var totalQuantity = self.totalQuantity = number.format(resp.total_quantity,{places:2});
                    dojo.attr(self.totalQuantityNode, "innerHTML", totalQuantity);

                    var finalQuantity = self.finalQuantity = number.format(resp.final_quantity,{places:2});
                    dojo.attr(self.finalQuantityNode, "innerHTML", finalQuantity);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        },
        submitRoundingType: function(){
            var roundingType = this.roundingTypeNode.get('value');

            if(roundingType != this.roundingType){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                pb.show();
                var self = this;
                dojo.xhrPost({
                    url: 'billBuildUpQuantity/buildUpSummaryRoundingUpdate',
                    content: { id: this.itemId, rounding_type: roundingType, _csrf_token: this._csrf_token, bill_column_setting_id: this.billColumnSettingId, type: this.type },
                    handleAs: 'json',
                    load: function(resp) {

                        var finalQuantity = self.finalQuantity = number.format(resp.final_quantity,{places:2});
                        dojo.attr(self.finalQuantityNode, "innerHTML", finalQuantity);

                        self.roundingType = resp.rounding_type;

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            }
        }
    });
});