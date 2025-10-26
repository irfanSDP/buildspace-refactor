define("buildspace/widget/grid/plugins/UpdateAllColumn", [
    "dojo/_base/declare",
    "dojo/_base/lang",
    "dojo/_base/array",
    "dojo/query",
    "dojo/dom-attr",
    "dojo/dom-construct",
    "dojo/on",
    "dojo/_base/connect",
    "dojox/grid/enhanced/_Plugin",
    "dojox/grid/EnhancedGrid",
    "dijit/form/NumberTextBox",
    "dijit/form/Button",
    'dojo/i18n!buildspace/nls/Common'
], function(declare, lang, array, query, domAttr, domConstruct, on, connect, _Plugin, EnhancedGrid, NumberTextBox, Button, nls){

    var UpdateAllColumn = declare("buildspace.widget.grid.plugins.UpdateAllColumn", _Plugin, {
        // name: String
        //		plugin name
        name: "buildspaceUpdateAllColumn",

        constructor: function(grid, args){
            // summary:
            //		See constructor of dojox.grid.enhanced._Plugin.
            this.grid = grid;
            this.nls = nls;
            this._connects = [];

            args = lang.isObject(args) ? args : {};

            this.columns = args.columns;
            this.updateUrl = args.updateUrl;
            this._csrf_token = args['_csrf_token'];

            if(args.hasOwnProperty('onSuccess')){
                this.onSuccess = args.onSuccess;
            }

            this._widgets = [];
            this._buttons = [];

            this._connects.push(connect.connect(this.grid.views, 'render', this, '_addWidgetsToHeaders'));
            this._connects.push(connect.connect(this.grid, '_onFetchComplete', this, '_addWidgetsToHeaders'));
        },
        _addWidgetsToHeaders: function(){
            var columnHeaderNodes = query(
                '.dojoxGridHeader table th',
                this.grid.viewsHeaderNode);

            if(this.columns){

                var columns = this.columns;
                var grid = this.grid;
                var self = this;

                dojo.forEach(columnHeaderNodes, function(node){

                    dojo.forEach(columns, function(column){
                        if(parseInt(domAttr.get(node, "idx")) == parseInt(column.idx)){
                            var tgt;
                            if(node.firstChild && node.firstChild.nodeType != 3)
                                tgt = node.firstChild;
                            else tgt = node;

                            var cell = grid.layout.cells[column.idx];

                            var widget = dijit.byId(grid.id+'-'+cell.index+'-'+cell.field+'_update_all-txtbox');
                            var btn = dijit.byId(grid.id+'-'+cell.index+'-'+cell.field+'_update_all-btn');

                            if(!widget){
                                widget = new NumberTextBox({
                                    'id': grid.id+'-'+cell.index+'-'+cell.field+'_update_all-txtbox',
                                    style: 'width:80px;padding:3px;font-weight:normal!important;',
                                    selectOnClick: true,
                                    intermediateChanges: true,
                                    cell: cell
                                });

                                self._widgets.push(widget);
                            }

                            if(!btn){
                                btn = new Button({
                                    'id': grid.id+'-'+cell.index+'-'+cell.field+'_update_all-btn',
                                    iconClass: 'icon-16-container icon-16-save',
                                    showLabel: false,
                                    onClick: lang.hitch(self, "_doApplyUpdate", widget)
                                });

                                self._buttons.push(btn);
                            }

                            widget.placeAt(tgt, "after");
                            btn.placeAt(widget, "after");
                        }
                    });
                });
            }
        },
        _doApplyUpdate: function(widget){
            if(widget.isValid() && !isNaN(widget.get("value"))){
                var items = this.grid.store._arrayOfAllItems;
                var ids = [];
                if(items.length){
                    ids = new Array();
                    dojo.forEach(items, function(item, idx){
                        if(String(item.id) != buildspace.constants.GRID_LAST_ROW)
                            ids[idx] = String(item.id);
                    });
                }

                if(ids.length){
                    var _this = this,
                        url = this.updateUrl,
                        params = {
                            'ids': ids.join(),
                            field: widget.cell.field,
                            val: widget.get("value"),
                            _csrf_token: this._csrf_token
                        },
                        pb = buildspace.dialog.indeterminateProgressBar({
                            title:nls.pleaseWait+'...'
                        });

                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: url,
                            content: params,
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success){
                                    _this.onSuccess(resp.items, widget);
                                    pb.hide();
                                }
                                _this.resetSelectRow(widget.cell.index);
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        });

                        widget.set("value", null);
                    });
                }
            }
        },
        onSuccess: function(items, widget){
        },
        resetSelectRow: function(cellIdx){
            this.deselectAll();
            this.focusCell(cellIdx);
        },
        deselectAll: function(){
            if(!this.hasIndirectSelection){
                this.grid.selection.deselectAll();
            }
        },
        focusCell: function(cellIdx){
            this.grid.focus.setFocusIndex(0, cellIdx);
        },
        destroy: function(){
            array.forEach(this._connects, connect.disconnect);
            delete this._connects;

            dojo.forEach(this._widgets, function(widget) {
                widget.destroyRecursive();
            });

            dojo.forEach(this._buttons, function(button) {
                button.destroyRecursive();
            });

            delete this._widgets;
            delete this._buttons;
        }
    });

    EnhancedGrid.registerPlugin(UpdateAllColumn/*name:'buildspaceUpdateAllColumn'*/);

    return UpdateAllColumn;

});
