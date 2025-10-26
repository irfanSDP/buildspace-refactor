define("buildspace/widget/grid/plugins/LocationFilter", [
    "dojo/_base/declare",
    "dojo/_base/lang",
    "dojo/_base/array",
    "dojo/_base/connect",
    'dojo/on',
    "dojo/aspect",
    "dojo/dom-attr",
    "dojo/query",
    "dijit/registry",
    "dijit/form/TextBox",
    "buildspace/widget/forms/MultiSelectDropDown",
    "dojox/grid/enhanced/_Plugin",
    "dojox/grid/EnhancedGrid",
    'dojo/i18n!buildspace/nls/Common'
], function(declare, lang, array, connect, on, aspect, domAttr, query, registry, TextBox, MultiSelectDropDown, _Plugin, EnhancedGrid, nls){

    var Filter = declare("buildspace.widget.grid.plugins.LocationFilter", _Plugin, {
        // name: String
        //		plugin name
        name: "buildspaceLocationFilter",

        constructor: function(grid, args){
            // summary:
            //		See constructor of dojox.grid.enhanced._Plugin.
            this.grid = grid;
            this.nls = nls;
            this._connects = [];

            args = lang.isObject(args) ? args : {};

            this.columns = args.columns;
            this.gridStoreUrl = args.gridStoreUrl;
            this.dropDownFilterUrl = args.dropDownFilterUrl;
            this.manualFilter = args.manualFilter;

            grid.manualFilter = lang.hitch(this, "doManualFilter");

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
                var dropDownStoreData = this.dropdownStoreData;
                var self = this;

                this.filterWidgets = [];

                dojo.forEach(columnHeaderNodes, function(node){

                    dojo.forEach(columns, function(column){
                        if(parseInt(domAttr.get(node, "idx")) == parseInt(column.idx)){
                            var tgt;
                            if(node.firstChild && node.firstChild.nodeType != 3)
                                tgt = node.firstChild;
                            else tgt = node;

                            var cell = grid.layout.cells[column.idx];

                            var widgetId,
                                storeSort,
                                storeParams;

                            if(column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_LOCATION || column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_TRADE){
                                widgetId = grid.id+'-'+column.level+'-'+column.type+'-location_filter';
                                storeParams = {
                                    'data': {
                                        identifier: 'id',
                                        label: 'name',
                                        items: column.data
                                    },
                                    clearOnClose: true
                                };
                                storeSort = [{
                                    attribute: "priority", descending: false
                                },{
                                    attribute: "lft", descending: false
                                },{
                                    attribute: "level", descending: false
                                }];
                            }else if(column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_TYPE || column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_UNIT){
                                widgetId = grid.id+'-'+column.type+'-column_filter';
                                storeParams = {
                                    'data': {
                                        identifier: 'id',
                                        label: 'name',
                                        items: column.data
                                    },
                                    clearOnClose: true
                                };
                                storeSort = [];
                            }else{
                                widgetId = grid.id+'-'+cell.index+'-'+cell.field+'-filter';

                                if(dropDownStoreData){
                                    storeSort = [];

                                    for (var i = 0; i < dropDownStoreData.length; i++) {
                                        if(dropDownStoreData[i]['idx'] == column.idx){
                                            storeParams = {
                                                'data': {
                                                    identifier: 'id',
                                                    label: 'name',
                                                    items: dropDownStoreData[i]['data']
                                                },
                                                clearOnClose: true
                                            };

                                            break;
                                        }
                                    }
                                }
                            }

                            var widget = dijit.byId(widgetId);
                            if(!widget){
                                switch(column.widgetName){
                                    case "dropdown":
                                        widget = new MultiSelectDropDown({
                                            id:  widgetId,
                                            cell: cell,
                                            column: column,
                                            name: cell.index+'-'+cell.field+'-filter',
                                            valueField: "id",
                                            textField: "name",
                                            style: "font-weight:normal!important;padding:2px;width:auto;display:block;",
                                            dropDownWidth: "240px",
                                            storeSort: storeSort,
                                            dataStore: new dojo.data.ItemFileReadStore(storeParams)
                                        });

                                        on(widget, "click", lang.hitch(self, "doDropDownFilter", widget));
                                        break;
                                    case "textbox":
                                        widget = new TextBox({
                                            id: widgetId,
                                            cell: cell,
                                            column: column,
                                            name: cell.index+'-'+cell.field+'-filter',
                                            style:"font-weight:normal!important;padding:2px;width:98%;display:block;",
                                            onKeyUp: dojo.hitch(self, "doTextBoxFilter", cell.index)
                                        });
                                        break;
                                    default:
                                }
                            }

                            if(widget){
                                self.filterWidgets.push(widget);

                                widget.placeAt(tgt, "before");
                            }
                        }
                    });
                });
            }
        },
        doDropDownFilter: function(widget) {
            if(!widget.dropDown.grid.store._arrayOfAllItems.length)
                return;

            var _this = this;
            var handle = aspect.after(this.grid, "_refresh", function() {
                handle.remove();

                _this.resetSelectRow(widget.cell.index);
            });

            var params = {};

            dojo.forEach(this.filterWidgets, function(filterWidget) {
                if(filterWidget.column.hasOwnProperty("type") && filterWidget.getSelectedIds().length > 0){
                    var type;
                    if((filterWidget.column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_LOCATION || filterWidget.column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_TRADE))
                    {
                        switch(filterWidget.column.type){
                            case buildspace.constants.LOCATION_SEQUENCE_TYPE_LOCATION:
                                type = "l-";
                                break;
                            default:
                                type = "t-";
                        }
                        params[type+filterWidget['column']['level']] = filterWidget._hidden.value;
                    }
                    if(filterWidget.column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_UNIT || filterWidget.column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_TYPE)
                    {
                        switch(filterWidget.column.type){
                            case buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_TYPE:
                                type = "column_type";
                                break;
                            default:
                                type = "column_unit";
                        }
                        params[type] = filterWidget._hidden.value;
                    }
                }
            });

            var grid = this.grid,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
            
            if(_this.dropDownFilterUrl){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: _this.dropDownFilterUrl,
                        handleAs: "json",
                        preventCache: true,
                        content: params,
                        load: function(resp){
                            var keyName;
                            var widgetChild;
    
                            switch(widget.column.type){
                                case buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_TYPE:
                                    keyName = 'column_types';
                                    break;
                                case buildspace.constants.LOCATION_SEQUENCE_TYPE_LOCATION:
                                    keyName = "project_structure_location_codes";
                                    break;
                                default:
                                    keyName = "predefined_location_codes";
                            }
    
                            if(resp.hasOwnProperty(keyName)){
                                if((keyName == "project_structure_location_codes") || (keyName == "predefined_location_codes")){
                                    for(var level in resp[keyName]){
                                        widgetChild = dijit.byId(grid.id+'-'+level+'-' + widget.column.type+'-location_filter');
                                        if(widgetChild){
                                            _this._updateDependentDropDownStore(widgetChild, resp[keyName][level]);
                                        }
                                    }
    
                                    if(resp[keyName].length == 0){
                                        level = widget.column.level;
                                    }
    
                                    var domNodes = dojo.query('span[widgetid$="-' + widget.column.type+'-location_filter"]');
    
                                    //unselect other locations that is not belong to the selected location and set the store with empty data
                                    if(domNodes.length > parseInt(level)){
                                        dojo.forEach(domNodes, function(domNode) {
    
                                            var s = domAttr.get(domNode, "widgetid").split("-");
    
                                            if(parseInt(s[1]) > parseInt(level)){
                                                var widgetNode = registry.getEnclosingWidget(domNode);
                                                widgetNode.resetDataStore(new dojo.data.ItemFileReadStore({
                                                    data: {
                                                        identifier: 'id',
                                                        label: 'name',
                                                        items: []
                                                    },
                                                    clearOnClose: true
                                                }));
                                            }
                                        });
                                    }
                                }
                                else if(keyName == "column_types") {
                                    widgetChild = dijit.byId(grid.id+'-'+buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_UNIT+'-column_filter');
                                    if(widgetChild){
                                        _this._updateDependentDropDownStore(widgetChild, resp['column_units']);
                                    }
                                }
                            }
    
                            pb.hide();

                            if(!_this.manualFilter){
                                _this.doFilter();
                            }
                        },
                        error: function(error){
                            pb.hide();
                        }
                    });
                });
            }
        },
        _updateDependentDropDownStore: function(widget, data){
            if(widget.column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_TRADE || widget.column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_LOCATION || widget.column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_UNIT){
                widget.resetDataStore(new dojo.data.ItemFileReadStore({
                    data: {
                        identifier: 'id',
                        label: 'name',
                        items: data
                    },
                    clearOnClose: true
                }));
            }
        },
        doTextBoxFilter: function(cellIndex){
            var _this = this;
            var handle = aspect.after(this.grid, "_refresh", function() {
                handle.remove();

                _this.resetSelectRow(cellIndex);
            });

            if(!this.manualFilter){
                this.doFilter();
            }
        },
        doManualFilter: function(){
            this.doFilter();
        },
        doFilter: function(){
            var params = {};
            dojo.forEach(this.filterWidgets, function(filterWidget) {
                if(filterWidget.column.hasOwnProperty("type") && filterWidget.column.widgetName.toLowerCase() == "dropdown" &&
                    filterWidget.getSelectedIds().length > 0){
                    var type;
                    if(filterWidget.column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_LOCATION || filterWidget.column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_TRADE) {
                        switch(filterWidget.column.type){
                            case buildspace.constants.LOCATION_SEQUENCE_TYPE_LOCATION:
                                type = "l-";
                                break;
                            default:
                                type = "t-";
                        }
                        params[type+filterWidget['column']['level']] = filterWidget._hidden.value;
                    }
                    else if(filterWidget.column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_UNIT || filterWidget.column.type == buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_TYPE) {
                        switch(filterWidget.column.type){
                            case buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_TYPE:
                                type = "column_type";
                                break;
                            default:
                                type = "column_unit";
                        }
                        params[type] = filterWidget._hidden.value;
                    }

                }else if(filterWidget.column.widgetName.toLowerCase() == "textbox"){
                    params[filterWidget.cell.field] = filterWidget.get("value");
                }
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

           var _this = this;
            _this.grid.filterParams = params;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: _this.gridStoreUrl,
                    content: params,
                    handleAs: 'json',
                    preventCache: true,
                    load: function(data) {
                        _this.grid.setStore(new dojo.data.ItemFileWriteStore({
                            data: data,
                            clearOnClose: true
                        }));

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
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
            dojo.forEach(this.filterWidgets, function(widget) {
                widget.destroyRecursive();
            });

            delete this.filterWidgets;
        }
    });

    EnhancedGrid.registerPlugin(Filter/*name:'buildspaceLocationFilter'*/);

    return Filter;

});
