define("buildspace/apps/ProjectAnalyzerReport/plugins/TotalPane", [
    "dojo/_base/kernel",
    "dojo/_base/declare",
    "dojo/_base/array",
    "dojo/_base/connect",
    "dojo/currency",
    "dojo/number",
    "dojo/_base/lang",
    "dojo/_base/html",
    "dojo/text!./templates/TotalPane.html",
    "dojox/grid/enhanced/_Plugin",
    "dojox/grid/EnhancedGrid",
    "dijit/_Widget",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojox/html/metrics",
    'dojo/i18n!buildspace/nls/ProjectAnalyzer'
], function(kernel, declare, array, connect, currency, number, lang, html,
            template, _Plugin, EnhancedGrid,
            _Widget, _TemplatedMixin, _WidgetsInTemplateMixin, metrics, nls){

    var _TotalPane = declare("buildspace.apps.ProjectAnalyzerReport.ResourceEnhancedGrid.plugins._TotalPane", [_Widget, _TemplatedMixin], {
        templateString: template,
        constructor: function(params){
            lang.mixin(this, params);
            this.grid = this.plugin.grid;
            this.sumTotalQty = 0;
            this.sumTotalCost = 0;
            this.currencyAbbreviation = buildspace.currencyAbbreviation;
        },
        postCreate: function(){
            this.inherited(arguments);
            var _this = this, g = this.grid;
            this.plugin.connect(g, "_resize", lang.hitch(this, "_resetGridHeight"));
            this._originalResize = g.resize;
            g.resize = function(changeSize, resultSize){
                _this._changeSize = changeSize;
                _this._resultSize = resultSize;
                _this._originalResize.apply(g, arguments);
            };
            this._placeSelf();
        },
        destroy: function(){
            this.inherited(arguments);
            this.grid.resize = this._originalResize;
        },
        _update: function(){
            this._updateTotalCost();
        },
        _placeSelf: function(){
            // summary:
            //		Place pagination bar to a position.
            //		There are two options, top of the grid, bottom of the grid.
            var g = this.grid,
                isTop = this.position == "top";
            this.placeAt(isTop ? g.viewsHeaderNode : g.viewsNode, isTop ? "before" : "after");
        },
        _resetGridHeight: function(changeSize, resultSize){
            // summary:
            //		Function of resize grid height to place this pagination bar.
            //		Since the grid would be able to add other element in its domNode, we have
            //		change the grid view size to place the pagination bar.
            //		This function will resize the grid viewsNode height, scorllboxNode height
            var g = this.grid;
            changeSize = changeSize || this._changeSize;
            resultSize = resultSize || this._resultSize;
            delete this._changeSize;
            delete this._resultSize;
            if(g._autoHeight){
                return;
            }
            var padBorder = g._getPadBorder().h;
            if(!this.plugin.gh){
                this.plugin.gh = (g.domNode.clientHeight || html.style(g.domNode, 'height')) + 2 * padBorder;
            }
            if(resultSize){
                changeSize = resultSize;
            }
            if(changeSize){
                this.plugin.gh = html.contentBox(g.domNode).h + 2 * padBorder;
            }
            var gh = this.plugin.gh,
                hh = g._getHeaderHeight(),
                ph = html.marginBox(this.domNode).h;
            if(typeof g.autoHeight === "number"){
                var cgh = gh + ph - padBorder;
                html.style(g.domNode, "height", cgh + "px");
                html.style(g.viewsNode, "height", (cgh - ph - hh) + "px");
            }else{
                var h = gh - ph - hh - padBorder;
                html.style(g.viewsNode, "height", h + "px");
                var hasHScroller = array.some(g.views.views, function(v){
                    return v.hasHScrollbar();
                });
                array.forEach(g.viewsNode.childNodes, function(c){
                    html.style(c, "height", h + "px");
                });
                array.forEach(g.views.views, function(v){
                    if(v.scrollboxNode){
                        if(!v.hasHScrollbar() && hasHScroller){
                            html.style(v.scrollboxNode, "height", (h - metrics.getScrollbar().h) + "px");
                        }else{
                            html.style(v.scrollboxNode, "height", h + "px");
                        }
                    }
                });
            }
        },
        _updateTotalCost: function(){
            if(this.totalCostNode){
                dojo.attr(this.totalQtyNode, "innerHTML", number.format(this.sumTotalQty, {places:2}));
                dojo.attr(this.totalCostNode, "innerHTML", currency.format(this.sumTotalCost));
            }
        }
    });

    var TotalPane = declare("buildspace.apps.ProjectAnalyzerReport.ResourceEnhancedGrid.plugins.TotalPane", _Plugin, {
        name: "totalPane",
        position: 'bottom',
        init: function(){
            var g = this.grid;
            // wrap store layer

            this._store = g.store;

            this._totalPane = this.option.position != "top" ?
                new _TotalPane(lang.mixin(this.option, {position: "bottom", plugin: this})) :
                new _TotalPane(lang.mixin(this.option, {position: "top", plugin: this}));
            var self = this;
            this.connect(g.store, "_getItemsFromLoadedData", function(data) {
                self._totalPane.sumTotalQty = data.hasOwnProperty('sum_total_qty') ? data.sum_total_qty : 0;
                self._totalPane.sumTotalCost = data.hasOwnProperty('sum_total_cost') ? data.sum_total_cost : 0;
                self._totalPane._update();
            });


        },
        destroy: function(){
            this.inherited(arguments);
            this._totalPane.destroy();
            this._totalPane = null;
            this._nls = null;
        },
        _onDelete: function(){
            this.grid.resize();
            this.grid._refresh();
        }
    });

    EnhancedGrid.registerPlugin(TotalPane/*name:'pagination'*/);

    return TotalPane;

});