define('buildspace/apps/PostContractReport/Preliminaries/buildUpQuantitySummary',[
    'dojo/_base/declare',
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/Button",
    "dijit/form/TextBox",
    "dijit/form/CheckBox",
    "dijit/form/Select",
    "dojo/text!./templates/buildUpQuantitySummary.html",
    "dojo/number",
    "dojo/i18n!buildspace/nls/BuildUpQuantityGrid"
], function(declare, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, Button, ValidateTextBox, Checkbox, Select, template, number, nls){

    return declare("buildspace.apps.PostContractReport.buildUpQuantitySummary", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        style: "outline:none;",
        itemId: -1,
        billColumnSettingId: -1,
        nls: nls,
        region: 'bottom',
        _csrf_token: null,
        container: null,
        billGridStore: null,
        applyConversionFactor: false,
        conversionFactorAmount: 0,
        conversionFactorOperator: buildspace.constants.ARITHMETIC_OPERATOR_ADDITION,
        totalQuantity: 0,
        totalQuantityAfterConversion: 0,
        roundingType: 1,
        finalQuantity: 0,
        editable: false,
        moduleName: null,
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
            var self = this,
                xhrArgs = {
                    url: self.moduleName+'/getBuildUpSummary',
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

                        var totalQuantity = self.totalQuantity = number.format(resp.total_quantity,{places:2});
                        dojo.attr(self.totalQuantityNode, "innerHTML", totalQuantity);

                        var finalQuantity = self.finalQuantity = number.format(resp.final_quantity,{places:2});
                        dojo.attr(self.finalQuantityNode, "innerHTML", finalQuantity);

                        // disable or enable inputs
                        self.applyConversionFactorNode.set('disabled', true);
                        self.roundingTypeNode.set('disabled', true);
                        self.conversionFactorAmountNode.set('disabled', true);
                        self.conversionFactorOperatorNode.set('disabled', true);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                };
            dojo.xhrGet(xhrArgs);
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
                var self = this,
                    xhrArgs = {
                        url: self.moduleName+'/buildUpSummaryApplyConversionFactorUpdate',
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
                            console.log(error);
                            pb.hide();
                        }
                    };
                dojo.xhrPost(xhrArgs);
            }

            this.applyConversionFactor = checked;
            this.toggleConversionFactorRows(checked) ;
        },
        toggleConversionFactorRows: function(show)
        {
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
                var self = this,
                    xhrArgs = {
                        url: self.moduleName+'/buildUpSummaryConversionFactorUpdate',
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
                            console.log(error);
                            pb.hide();
                        }
                    };
                dojo.xhrPost(xhrArgs);
            }
        },
        submitConversionFactorOperator: function(){
            var operator = this.conversionFactorOperatorNode.get('value');
            if(operator != this.conversionFactorOperator){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                pb.show();
                var self = this,
                    xhrArgs = {
                        url: self.moduleName+'/buildUpSummaryConversionFactorUpdate',
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
                            console.log(error);
                            pb.hide();
                        }
                    };
                dojo.xhrPost(xhrArgs);
            }
        },
        refreshTotalQuantity: function(){
            var self = this,
                xhrArgs = {
                    url: self.moduleName+'/getBuildUpSummary',
                    content: { id: this.itemId, bill_column_setting_id: this.billColumnSettingId, type: this.type  },
                    handleAs: 'json',
                    load: function(resp) {
                        var totalQuantity = self.totalQuantity = number.format(resp.total_quantity,{places:2});
                        dojo.attr(self.totalQuantityNode, "innerHTML", totalQuantity);

                        var finalQuantity = self.finalQuantity = number.format(resp.final_quantity,{places:2});
                        dojo.attr(self.finalQuantityNode, "innerHTML", finalQuantity);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                };
            dojo.xhrGet(xhrArgs);
        },
        submitRoundingType: function(){
            var roundingType = this.roundingTypeNode.get('value');

            if(roundingType != this.roundingType){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                pb.show();
                var self = this,
                    xhrArgs = {
                        url: self.moduleName+'/buildUpSummaryRoundingUpdate',
                        content: { id: this.itemId, rounding_type: roundingType, _csrf_token: this._csrf_token, bill_column_setting_id: this.billColumnSettingId, type: this.type },
                        handleAs: 'json',
                        load: function(resp) {

                            var finalQuantity = self.finalQuantity = number.format(resp.final_quantity,{places:2});
                            dojo.attr(self.finalQuantityNode, "innerHTML", finalQuantity);

                            self.roundingType = resp.rounding_type;

                            pb.hide();
                        },
                        error: function(error) {
                            console.log(error);
                            pb.hide();
                        }
                    };
                dojo.xhrPost(xhrArgs);
            }
        }
    });
});