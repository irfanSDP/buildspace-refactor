define('buildspace/apps/ProjectBuilderReport/BillManager/buildUpRateSummary',[
    'dojo/_base/declare',
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dijit/form/Button",
    "dijit/form/TextBox",
    "dijit/form/CheckBox",
    "dijit/form/Select",
    "dijit/InlineEditBox",
    "dojo/text!./templates/buildUpRateSummary.html",
    "dojo/currency",
    "dojo/i18n!buildspace/nls/BuildUpGrid"
], function(declare, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, Button, ValidateTextBox, Checkbox, Select, InlineEditBox, template, currency, nls){

    return declare("buildspace.apps.ProjectBuilderReport.BillManager.buildUpRateSummary", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        style: "outline:none;",
        itemId: -1,
        nls: nls,
        region: 'bottom',
        _csrf_token: null,
        container: null,
        billGridStore: null,
        applyConversionFactor: false,
        conversionFactorAmount: 0,
        conversionFactorOperator: buildspace.constants.ARITHMETIC_OPERATOR_MULTIPLICATION,
        totalCost: 0,
        totalCostAfterConversion: 0,
        markup: 0,
        finalCost: 0,
        roundingType: 1,
        currentBillLockedStatus: false,
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

            if (args.currentBillLockedStatus) {
                this.disableEditingMode = true;
            }

            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);
            var self = this,
                xhrArgs = {
                    url: 'billBuildUpRate/getBuildUpSummary',
                    content: { id: this.itemId },
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

                        var totalCost = self.totalCost = currency.format(resp.total_cost);
                        dojo.attr(self.totalCostNode, "innerHTML", totalCost);

                        var totalCostAfterConversion = self.totalCostAfterConversion = currency.format(resp.total_cost_after_conversion);
                        dojo.attr(self.totalCostAfterConversionNode, "innerHTML", totalCostAfterConversion);

                        var finalCost = self.finalCost = currency.format(resp.final_cost);
                        dojo.attr(self.finalCostNode, "innerHTML", finalCost);

                        var markup = self.markup = currency.format(resp.markup);
                        self.markupNode.set('value', markup);

                        // disable or enable inputs
                        self.applyConversionFactorNode.set('disabled', self.disableEditingMode);
                        self.roundingTypeNode.set('disabled', self.disableEditingMode);
                        self.conversionFactorAmountNode.set('disabled', self.disableEditingMode);
                        self.conversionFactorOperatorNode.set('disabled', self.disableEditingMode);
                        self.markupNode.set('disabled', self.disableEditingMode);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                }
            dojo.xhrGet(xhrArgs);
        },
        startup: function(){
            this.inherited(arguments);
            this.toggleConversionFactorRows(false);
        },
        submitMarkup: function(){
            var markup = this.markupNode.get('value');
            if(markup != this.markup){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                pb.show();
                var self = this,
                    xhrArgs = {
                        url: 'billBuildUpRate/buildUpSummaryMarkupUpdate',
                        content: { id: this.itemId, value: markup, _csrf_token: this._csrf_token },
                        handleAs: 'json',
                        load: function(resp) {
                            var finalCost = self.finalCost = currency.format(resp.final_cost);
                            dojo.attr(self.finalCostNode, "innerHTML", finalCost);
                            var markup = self.markup = currency.format(resp.markup);
                            self.markupNode.set('value', markup);

                            self.updateBillGridStore(self.itemId);
                            pb.hide();
                        },
                        error: function(error) {
                            console.log(error);
                            pb.hide();
                        }
                    }
                dojo.xhrPost(xhrArgs);
            }
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
                        url: 'billBuildUpRate/buildUpSummaryApplyConversionFactorUpdate',
                        content: { id: this.itemId, value: checked, _csrf_token: this._csrf_token },
                        handleAs: 'json',
                        load: function(resp) {
                            self.conversionFactorAmount = resp.conversion_factor_amount;
                            self.conversionFactorAmountNode.set('value', self.conversionFactorAmount);

                            self.conversionFactorOperator = resp.conversion_factor_operator;
                            self.conversionFactorOperatorNode.set('value', self.conversionFactorOperator);

                            var totalCostAfterConversion = self.totalCostAfterConversion = currency.format(resp.total_cost_after_conversion);
                            dojo.attr(self.totalCostAfterConversionNode, "innerHTML", totalCostAfterConversion);

                            var finalCost = self.finalCost = currency.format(resp.final_cost);
                            dojo.attr(self.finalCostNode, "innerHTML", finalCost);

                            self.updateBillGridStore(self.itemId);
                            pb.hide();
                        },
                        error: function(error) {
                            console.log(error);
                            pb.hide();
                        }
                    }
                dojo.xhrPost(xhrArgs);
            }

            this.applyConversionFactor = checked;
            this.toggleConversionFactorRows(checked) ;
        },
        toggleConversionFactorRows: function(show)
        {
            if(show){
                var self = this,
                    xhrArgs = {
                        url: 'billBuildUpRate/getConversionFactorUom',
                        content: { id: this.itemId },
                        handleAs: 'json',
                        load: function(resp) {
                            var uomSelect = new Select({
                                name: "bill_item_build_up_rate[conversion_factor_uom_id]",
                                style: "width:100%;",
                                options: resp.uomOptions,
                                onChange: function(){
                                    self.submitConversionFactorUOM(this.value)
                                }
                            }, self.conversionFactorUOMNode);
                            dojo.query("."+self.itemId+"-conversion_factor-row").style({display: ""});
                            self.container.resize();
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    }
                dojo.xhrPost(xhrArgs);
            }else{
                dojo.query("."+this.itemId+"-conversion_factor-row").style({display: "none"});
                this.container.resize();
            }
        },
        submitConversionFactorAmount: function(){
            var amount = this.conversionFactorAmountNode.get('value');
            if(amount != this.conversionFactorAmount){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
                pb.show();
                var self = this,
                    billGridStore = self.billGridStore,
                    xhrArgs = {
                        url: 'billBuildUpRate/buildUpSummaryConversionFactorUpdate',
                        content: { id: this.itemId, val: amount, type: 'amount', _csrf_token: this._csrf_token },
                        handleAs: 'json',
                        load: function(resp) {
                            self.conversionFactorAmount = resp.conversion_factor_amount;
                            self.conversionFactorAmountNode.set('value', self.conversionFactorAmount);

                            var totalCostAfterConversion = self.totalCostAfterConversion = currency.format(resp.total_cost_after_conversion);
                            dojo.attr(self.totalCostAfterConversionNode, "innerHTML", totalCostAfterConversion);

                            var finalCost = self.finalCost = currency.format(resp.final_cost);
                            dojo.attr(self.finalCostNode, "innerHTML", finalCost);

                            self.updateBillGridStore(self.itemId);
                            pb.hide();
                        },
                        error: function(error) {
                            console.log(error);
                            pb.hide();
                        }
                    }
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
                    billGridStore = self.billGridStore,
                    xhrArgs = {
                        url: 'billBuildUpRate/buildUpSummaryConversionFactorUpdate',
                        content: { id: this.itemId, val: operator, type: 'operator', _csrf_token: this._csrf_token },
                        handleAs: 'json',
                        load: function(resp) {
                            self.conversionFactorOperator = resp.conversion_factor_operator;
                            self.conversionFactorOperatorNode.set('value', self.conversionFactorOperator);

                            var totalCostAfterConversion = self.totalCostAfterConversion = currency.format(resp.total_cost_after_conversion);
                            dojo.attr(self.totalCostAfterConversionNode, "innerHTML", totalCostAfterConversion);

                            var finalCost = self.finalCost = currency.format(resp.final_cost);
                            dojo.attr(self.finalCostNode, "innerHTML", finalCost);

                            self.updateBillGridStore(self.itemId);
                            pb.hide();
                        },
                        error: function(error) {
                            console.log(error);
                            pb.hide();
                        }
                    }
                dojo.xhrPost(xhrArgs);
            }
        },
        submitConversionFactorUOM: function(val){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });
            pb.show();
            var self = this,
                xhrArgs = {
                    url: 'billBuildUpRate/buildUpSummaryConversionFactorUomUpdate',
                    content: { id: this.itemId, uom_id: val, _csrf_token: this._csrf_token },
                    handleAs: 'json',
                    load: function(resp) {
                        pb.hide();
                    },
                    error: function(error) {
                        console.log(error);
                        pb.hide();
                    }
                }
            dojo.xhrPost(xhrArgs);
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
                        url: 'billBuildUpRate/buildUpSummaryRoundingUpdate',
                        content: { rounding_type: roundingType, _csrf_token: this._csrf_token, id: this.itemId },
                        handleAs: 'json',
                        load: function(resp) {

                            var finalCost = self.finalCost = currency.format(resp.final_cost);
                            dojo.attr(self.finalCostNode, "innerHTML", finalCost);

                            self.roundingType = resp.rounding_type;

                            self.updateBillGridStore(self.itemId);
                            pb.hide();
                        },
                        error: function(error) {
                            console.log(error);
                            pb.hide();
                        }
                    }
                dojo.xhrPost(xhrArgs);
            }
        },
        refreshTotalCost: function(){
            var self = this,
                xhrArgs = {
                    url: 'billBuildUpRate/getBuildUpSummary',
                    content: { id: this.itemId },
                    handleAs: 'json',
                    load: function(resp) {
                        var totalCost = self.totalCost = currency.format(resp.total_cost);
                        dojo.attr(self.totalCostNode, "innerHTML", totalCost);

                        var totalCostAfterConversion = self.totalCostAfterConversion = currency.format(resp.total_cost_after_conversion);
                        dojo.attr(self.totalCostAfterConversionNode, "innerHTML", totalCostAfterConversion);

                        var finalCost = self.finalCost = currency.format(resp.final_cost);
                        dojo.attr(self.finalCostNode, "innerHTML", finalCost);
                    },
                    error: function(error) {
                        console.log(error);
                    }
                }
            dojo.xhrGet(xhrArgs);
        }
    });
});