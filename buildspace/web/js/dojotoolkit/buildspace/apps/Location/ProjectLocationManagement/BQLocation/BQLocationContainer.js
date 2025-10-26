define('buildspace/apps/Location/ProjectLocationManagement/BQLocation/BQLocationContainer',[
    'dojo/_base/declare',
    "dojo/dom-style",
    'dojo/_base/lang',
    'dojo/keys',
    'dojo/query',
    'dojo/on',
    "dojo/dom",
    'dojo/number',
    "dijit/Menu",
    "dijit/CheckedMenuItem",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    "buildspace/widget/grid/plugins/LocationFilter",
    "buildspace/widget/grid/plugins/UpdateAllColumn",
    "dojox/grid/enhanced/plugins/Menu",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/Location'
], function(declare, domStyle, lang, keys, query, on, dom, number, Menu, CheckedMenuItem, IndirectSelection, LocationFilterPlugin, UpdateAllColumnPlugin, MenuPlugin, GridFormatter, nls){

    var Grid = declare('buildspace.apps.ProjectLocationManagement.BQLocation.Grid', dojox.grid.EnhancedGrid, {
        style: "border-top:none;",
        project: null,
        region: 'center',
        postCreate: function(){
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = this.getItem(rowIdx), store = this.store;

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    attr_name: inAttrName,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = "location/billItemQtyByLocationUpdate";

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }

                    store.save();
                };

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: url,
                        content: params,
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success){
                                if(!isNaN(parseInt(item.id[0]))){
                                    updateCell(resp.data, store);
                                }

                                var cell = self.getCellByField(inAttrName);
                                window.setTimeout(function() {
                                    self.focus.setFocusIndex(rowIdx, cell.index);
                                }, 10);
                                pb.hide();
                            }
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }

            this.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            if(inCell != undefined){
                var item = this.getItem(inRowIndex);

                if(item && isNaN(parseInt(item.id[0])) && inCell.editable){
                    var self = this;
                    window.setTimeout(function() {
                        self.edit.cancel();
                        self.focus.setFocusIndex(inRowIndex, inCell.index);
                    }, 10);
                    return false;
                }
            }

            return this._canEdit;
        },
        doCancelEdit: function(inRowIndex){
            this.inherited(arguments);
            this.views.renormalizeRow(inRowIndex);
            this.scroller.rowHeightChanged(inRowIndex, true);
        },
        showHideColumn: function(show, index) {
            this.beginUpdate();
            this.layout.setColumnVisibility(index, show);
            this.endUpdate();
        }
    });

    return declare('buildspace.apps.Location.ProjectLocationManagement.BQLocation.BQLocationContainer', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        project: null,
        baseApp: null,
        region: "center",
        postCreate: function(){
            this.inherited(arguments);

            this._selectedIds = [];

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            var self = this;
            pb.show().then(function(){
                dojo.xhrGet({
                    url: "location/getLocationCodeLevels",
                    handleAs: "json",
                    preventCache: true,
                    content: {
                        pid: self.project.id
                    },
                    load: function(data){
                        self.renderContent(data);
                        pb.hide();
                    },
                    error: function(error){
                        pb.hide();
                    }
                });
            });
        },
        renderContent: function(data){
            var mainContent = dijit.byId(this.project.id+"-BQLocation-main_grid_content");
            if(mainContent){
                mainContent.destroyRecursive();
            }

            var formatter = new GridFormatter();

            var customFormatter = {
                percentageCellFormatter: function(cellValue, rowIdx, cell){
                    var value = number.parse(cellValue);

                    if(isNaN(value) || value == 0 || value == null){
                        cellValue = "&nbsp;";
                    }else{
                        var formattedValue = number.format(value, {places:2})+"%";
                        cellValue = value >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
                    }

                    return cellValue;
                },
                locationCellFormatter:  function(cellValue, rowIdx, cell){
                    if(cellValue && cellValue.length > 0){
                        return cellValue;
                    } else {
                        cell.customClasses.push('disable-cell');
                        return "&nbsp;";
                    }
                }
            };
            var gridStructure = [
                {name: 'No.', field: 'count', width: '30px', styles: 'text-align:center;', formatter: formatter.rowCountCellFormatter},
                {name: nls.bill, field: 'bill_title', width:'280px', hidden: true, showInCtxMenu: true},
                {name: nls.columnType, field: 'column_name', width:'120px', styles:'text-align:center;', hidden: true, showInCtxMenu: true},
                {name: nls.columnUnit, field: 'column_unit', width:'120px', styles:'text-align:center;', hidden: true, showInCtxMenu: true}
            ];

            var filterPluginColumns = [];

            filterPluginColumns.push({
                'idx':3,
                'widgetName':'dropdown',
                'type': buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_TYPE,
                'data': data.column_types
            });
            filterPluginColumns.push({
                'idx':4,
                'widgetName':'dropdown',
                'type': buildspace.constants.LOCATION_SEQUENCE_TYPE_COLUMN_UNIT,
                'data': data.column_units
            });

            var updateAllColumnPluginColumns = [];

            var cellIdx = 4;//plus indirectselection checkbox column

            for(var i = 0; i < parseInt(data.predefined_location_codes.length); i++){
                var title;
                switch(i) {
                    case buildspace.constants.PREDEFINED_LOCATION_CODE_TRADE_LEVEL:
                        title = nls.trade;
                        break;
                    case buildspace.constants.PREDEFINED_LOCATION_CODE_ELEMENT_LEVEL:
                        title = nls.element;
                        break;
                    default:
                        title = i > buildspace.constants.PREDEFINED_LOCATION_CODE_SUB_ELEMENT_LEVEL ? nls.subElement+' ('+(i - 1)+')' : nls.subElement;
                }

                gridStructure.push({
                    name: title,
                    field: i+'-predefined_location_code',
                    width:'180px',
                    formatter: customFormatter.locationCellFormatter,
                    styles:'text-align:center;'
                });

                cellIdx++;

                filterPluginColumns.push({
                    'idx':cellIdx,
                    'widgetName':'dropdown',
                    'type': buildspace.constants.LOCATION_SEQUENCE_TYPE_TRADE,
                    'data': data.predefined_location_codes[i],
                    'level': i
                });
            }

            for(var l = 0; l < parseInt(data.project_structure_location_codes.length); l++){
                gridStructure.push({
                    name: nls.location + ' '+ (l + 1),
                    field: l+'-project_structure_location_code',
                    width:'180px',
                    formatter: customFormatter.locationCellFormatter,
                    styles:'text-align:center;'
                });

                cellIdx++;

                filterPluginColumns.push({
                    'idx':cellIdx,
                    'widgetName':'dropdown',
                    'type': buildspace.constants.LOCATION_SEQUENCE_TYPE_LOCATION,
                    'data': data.project_structure_location_codes[l],
                    'level': l
                });
            }

            gridStructure.push({
                name: nls.billItem,
                field: 'description',
                width: (parseInt(data.project_structure_location_codes.length) > 0 && parseInt(data.predefined_location_codes.length) > 0) ? '640px' : 'auto'
            });

            cellIdx +=1;

            filterPluginColumns.push({
                'idx':cellIdx,
                'widgetName':'textbox'
            });

            gridStructure.push({
                name: nls.unit,
                field: 'uom',
                width:'70px',
                styles:'text-align:center;',
                noresize: true
            });

            gridStructure.push({
                name: nls.prorated+" %",
                field: 'percentage',
                width:'120px',
                styles:'text-align:right;',
                editable: true,
                cellType: 'buildspace.widget.grid.cells.Textarea',
                formatter: customFormatter.percentageCellFormatter,
                noresize: true
            });

            cellIdx +=2;

            updateAllColumnPluginColumns.push({
                'idx':cellIdx
            });

            gridStructure.push({
                name: nls.qty,
                field: 'qty',
                width:'70px',
                styles:'text-align:right;',
                formatter: formatter.numberCellFormatter,
                noresize: true
            });

            gridStructure.push({
                name: nls.prorated+" "+nls.qty,
                field: 'prorated_qty',
                width:'120px',
                styles:'text-align:right;',
                editable: true,
                cellType: 'buildspace.widget.grid.cells.Textarea',
                formatter: formatter.numberCellFormatter,
                noresize: true
            });

            updateAllColumnPluginColumns.push({
                'idx':(cellIdx+2)
            });

            var headerMenu = new Menu();

            var self = this;
            dojo.forEach(gridStructure, function(cell, index){
                if(cell.hasOwnProperty('showInCtxMenu') && cell.showInCtxMenu){
                    headerMenu.addChild(new CheckedMenuItem({
                        label: cell.name,
                        checked: (!cell.hasOwnProperty('hidden') || cell.hidden === false),
                        onChange: lang.hitch(self, "showHideHeaderColumn", (index+1))//plus 1 because of indirectselection column
                    }));
                }
            });

            this.grid = new Grid({
                id: this.project.id+"-BQLocation-main_grid_content",
                structure: gridStructure,
                plugins: {
                    indirectSelection: {
                        headerSelector:true, width:"20px", styles:"text-align:center;"
                    },
                    menus: {
                        headerMenu: headerMenu
                    },
                    buildspaceLocationFilter: {
                        columns: filterPluginColumns,
                        gridStoreUrl: "location/getBillByLocations/pid/"+this.project.id,
                        dropDownFilterUrl: "location/getLocationCodeLevels/pid/"+this.project.id,
                        manualFilter: true
                    },
                    buildspaceUpdateAllColumn: {
                        columns: updateAllColumnPluginColumns,
                        updateUrl: "location/qtyBulkUpdate",
                        '_csrf_token': String(this.project._csrf_token),
                        onSuccess: lang.hitch(this, "updateAllCallback")
                    }
                },
                store: new dojo.data.ItemFileWriteStore({
                    url: "location/getBillByLocations/pid/"+this.project.id,
                    clearOnClose: true
                })
            });

            var toolbar = dijit.byId(this.project.id+"-BQLocation-main_toolbar");
            if(!toolbar){
                toolbar = new dijit.Toolbar({
                    id: this.project.id+"-BQLocation-main_toolbar",
                    region: "top",
                    style: "border-bottom:none;outline:none!important;padding:2px;overflow:hidden;"
                });

                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.removeFromLocations,
                        iconClass: "icon-16-container icon-16-delete",
                        style:"outline:none!important;",
                        onClick: lang.hitch(this, "removeFromLocations")
                    })
                );
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.filterLocations,
                        iconClass: "icon-16-container icon-16-zoom",
                        style:"outline:none!important;",
                        onClick: lang.hitch(this, "filterLocations")
                    })
                );

                this.addChild(toolbar);
            }

            this.addChild(this.grid);
        },
        showHideHeaderColumn: function(index, show){
            this.grid.showHideColumn(show, index)
        },
        updateAllCallback: function(items, widget){
            var _this = this,
                grid = this.grid,
                store = grid.store;

            dojo.forEach(items, function(item){
                store.fetchItemByIdentity({ 'identity' : item.id,  onItem : function(affectedItem){
                    for(var property in item){
                        if(affectedItem.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(affectedItem, property, item[property]);
                        }
                    }
                }});
            });

            store.save({
                onComplete: function () {
                    grid.focus.setFocusIndex(0, widget.cell.index);

                    _this.reloadLocationAssignmentItemGrid();
                }
            });
        },
        filterLocations: function(){
            this.grid.manualFilter();
        },
        removeFromLocations: function(){
            this._selectedIds = [];
            if(this.grid.selection.selected.length > 0){
                for (var rowIndex in this.grid.selection.selected){
                    if(this.grid.store._arrayOfAllItems.hasOwnProperty(rowIndex) && String(this.grid.store._arrayOfAllItems[rowIndex].id) != buildspace.constants.GRID_LAST_ROW){
                        var item = this.grid.store._arrayOfAllItems[rowIndex];
                        var parts = String(item.id).split("-");
                        if(this._selectedIds.indexOf(parseInt(parts[0])) === -1){
                            this._selectedIds.push(parseInt(parts[0]));
                        }
                    }
                }

                if(this._selectedIds.length > 0){
                    var _this = this,
                        selectedIds = this._selectedIds,
                        baseApp = this.baseApp,
                        project = this.project,
                        pb = buildspace.dialog.indeterminateProgressBar({
                            title:nls.deleting+'. '+nls.pleaseWait+'...'
                        });

                    new buildspace.dialog.confirm(nls.removeFromLocations, nls.bulkRemoveLocationsMsg, 90, 320, function() {
                        pb.show().then(function(){
                            dojo.xhrPost({
                                url: "location/removeAssignedLocations",
                                content: { ids: selectedIds.join(), _csrf_token: project._csrf_token },
                                handleAs: 'json',
                                load: function(data) {
                                    pb.hide();
                                    if(data.success){
                                        baseApp.resetBQLocationTab(true);
                                        _this.reloadLocationAssignmentItemGrid();
                                    }
                                },
                                error: function(error) {
                                    pb.hide();
                                }
                            });
                        });
                    });
                }
            }else{
                buildspace.dialog.alert(nls.noLocationSelected, nls.pleaseSelectLocationToBeRemoved+'.', 90, 320);
            }
        },
        reloadLocationAssignmentItemGrid: function(){
            var itemGrid = dijit.byId('location_assignment-project_item_grid-'+this.project.id);
            if(itemGrid){
                itemGrid.store.save();
                itemGrid.store.close();
                itemGrid._refresh();
            }
        }
    });
});
