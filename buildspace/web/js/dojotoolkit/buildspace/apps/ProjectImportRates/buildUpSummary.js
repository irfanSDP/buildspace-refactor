define('buildspace/apps/ProjectImportRates/buildUpSummary',[
    'dojo/_base/declare',
    'dojo/on',
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dojo/text!./templates/buildUpSummary.html",
    "dojo/currency",
    "dojo/i18n!buildspace/nls/ScheduleOfRateLibrary"
], function(declare, on, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, template, currency, nls){
    return declare("buildspace.apps.ScheduleOfRateLibrary.buildUpSummary", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin], {
        templateString: template,
        style: "outline:none;",
        itemId: -1,
        nls: nls,
        region: 'bottom',
        container: null,
        applyConversionFactor: false,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;
            dojo.xhrGet({
                url: 'billManagerImportRate/getScheduleOfRateBuildUpSummary',
                content: { id: this.itemId },
                handleAs: 'json',
                load: function(resp) {
                    self.applyConversionFactor = resp.apply_conversion_factor;

                    dojo.attr(self.conversionFactorAmountNode, "innerHTML", currency.format(resp.conversion_factor_amount));

                    dojo.attr(self.conversionFactorOperatorNode, "innerHTML", resp.conversion_factor_operator);

                    dojo.attr(self.conversionFactorUOMNode, "innerHTML", resp.conversion_factor_uom);

                    dojo.attr(self.totalCostNode, "innerHTML", currency.format(resp.total_cost));

                    dojo.attr(self.totalCostAfterConversionNode, "innerHTML", currency.format(resp.total_cost_after_conversion));

                    dojo.attr(self.finalCostNode, "innerHTML", currency.format(resp.final_cost));

                    dojo.attr(self.markupNode, "innerHTML", currency.format(resp.markup));

                    self.toggleConversionFactorRows(self.applyConversionFactor);
                },
                error: function(error) {
                    console.log(error);
                }
            });
        },
        startup: function(){
            this.inherited(arguments);
            var self = this;
            on(this.toggleSummaryNode, 'click', function(e){
                e.preventDefault();
                var display = domStyle.get(self.tbodyNode, 'display');
                if(display == 'none'){
                    domStyle.set(self.tbodyNode, 'display', '');
                    dojo.attr(self.toggleSummaryNode, "innerHTML", nls.hide);
                }else{
                    domStyle.set(self.tbodyNode, 'display', 'none');
                    dojo.attr(self.toggleSummaryNode, "innerHTML", nls.show);
                }
                self.container.resize();
            });
        },
        toggleConversionFactorRows: function(show){
            if(show){
                dojo.query("."+this.itemId+"-conversion_factor-row").style({display: ""});
                this.container.resize();
            }else{
                dojo.query("."+this.itemId+"-conversion_factor-row").style({display: "none"});
                this.container.resize();
            }
        },
        toggleSummary: function(){
            return;
        }
    });
});