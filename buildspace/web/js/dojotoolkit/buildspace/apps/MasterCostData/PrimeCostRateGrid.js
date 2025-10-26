define('buildspace/apps/MasterCostData/PrimeCostRateGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, lang, EnhancedGrid, GridFormatter, nls){

    var Grid = declare('buildspace.apps.MasterCostData.PrimeCostRateGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        masterCostData: null,
        parentItem: null,
        addUrl: 'masterCostData/addNewPrimeCostRate',
        updateUrl: 'masterCostData/updatePrimeCostRate',
        constructor:function(args){
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);

            this.on('RowClick', function (e) {
                if (e.cell) {
                    var item = this.getItem(e.rowIndex);
                    var enable = false;
                    if(item.id[0] > 0) {
                        enable = true;
                    }

                    self.enableToolbarButtons(enable);
                }
            });
        },
        enableToolbarButtons: function(enable){
            var deleteButton = dijit.byId(this.parentItem.id + 'prime-cost-rate-delete-button');
            deleteButton._setDisabledAttr(!enable);
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName){
            var self = this, item = self.getItem(rowIdx), store = self.store;

            if(val !== item[inAttrName][0]){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'. '+nls.pleaseWait+'...'
                });
                var params = {
                    id: item.id,
                    attr_name: inAttrName,
                    val: val,
                    master_cost_data_id: self.masterCostData.id,
                    parent_id: self.parentItem.id,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                if(item.id==buildspace.constants.GRID_LAST_ROW){
                    var prevItem = rowIdx > 0 ? self.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0
                    });
                    url = this.addUrl;
                }

                var updateCell = function(data, store){
                    for(var property in data){
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                            store.setValue(item, property, data[property]);
                        }
                    }
                    store.save();
                };

                var xhrArgs = {
                    url: url,
                    content: params,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            if(item.id > 0){
                                updateCell(resp.data, store);
                            }else{
                                store.deleteItem(item);
                                store.save();
                                dojo.forEach(resp.items, function(item){
                                    store.newItem(item);
                                });
                                store.save();
                            }
                            var cell = self.getCellByField(inAttrName);
                            window.setTimeout(function() {
                                self.focus.setFocusIndex(rowIdx, cell.index);
                            }, 10);
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                };

                pb.show();
                dojo.xhrPost(xhrArgs);
            }
            self.inherited(arguments);
        },
        canEdit: function(inCell, inRowIndex){
            return true;
        },
        reload: function(){
            this.store.close();
            this._refresh();
        }
    });

    var Formatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return parseInt(rowIdx)+1;
        }
    };

    return declare('buildspace.apps.MasterCostData.PrimeCostRateContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        masterCostData: null,
        parentItem: null,
        grid: null,
        stackContainer: null,
        level: null,
        gridCreator: null,
        unitsOfMeasurement: [],
        postCreate: function(){
            this.inherited(arguments);

            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show();
            dojo.xhrPost({
                url: 'default/getAllUnits',
                handleAs: 'json',
                load: function(resp) {
                    self.unitsOfMeasurement.values = resp.values;
                    self.unitsOfMeasurement.options = resp.options;
                },
                error: function(error) {
                }
            }).then(function(){
                var grid = self.createBreakdownGrid();

                var gridContainer = new dijit.layout.ContentPane( {
                    style:"padding:0px;border:none;width:100%;height:100%;",
                    region: 'center',
                    content: grid,
                    grid: grid
                });

                self.addChild(self.createToolbar());
                self.addChild(gridContainer);

                pb.hide();
            });
        },
        createToolbar: function(){
            var self = this;
            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    id: this.parentItem.id + 'prime-cost-rate-delete-button',
                    label: nls.delete,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: true,
                    style: "outline:none!important;",
                    onClick: function () {
                        if (self.grid.selection.selectedIndex > -1) {
                            var item = self.grid.getItem(self.grid.selection.selectedIndex);
                            if (item.id[0] > 0) {
                                self.deleteRow(item);
                            }
                        }
                    }
                })
            );

            return toolbar;
        },
        createBreakdownGrid: function(){
            var self = this;
            this.gridFormatter = new GridFormatter();
            self.grid = Grid({
                masterCostData: self.masterCostData,
                parentItem: self.parentItem,
                structure:[
                    {
                        name: 'No.',
                        field: 'count',
                        width:'30px',
                        styles:'text-align:center;',
                        formatter: Formatter.rowCountCellFormatter
                    },{
                        name: nls.description,
                        field: 'description',
                        width:'auto',
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        editable: true
                    },{
                        name: nls.unit,
                        field: 'uom_id',
                        width: '70px',
                        editable: true,
                        styles: 'text-align:center;',
                        type: 'dojox.grid.cells.Select',
                        options: self.unitsOfMeasurement.options,
                        values: self.unitsOfMeasurement.values,
                        hidden: self.level != 3,
                        formatter: this.gridFormatter.unitCellFormatter
                    }
                ],
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"masterCostData/getPrimeCostRateBreakdown/masterCostData/"+this.masterCostData.id+"/parent_id/"+this.parentItem.id
                }),
                onRowDblClick: function(e) {
                    var me = this,
                        item = me.getItem(e.rowIndex);
                    if(self.level < 3 && item.id[0] > 0){
                        self.createPrimeCostRateGrid(item);
                    }
                }
            });

            return self.grid;
        },
        createPrimeCostRateGrid: function(item){
            this.gridCreator.createPrimeCostRateGrid(item, this.level+1);
        },
        deleteRow: function (item) {
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.deleting + '. ' + nls.pleaseWait + '...'
                });

            var onYes = function () {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'masterCostData/deletePrimeCostRate',
                        content: {id: item.id, _csrf_token: item._csrf_token},
                        handleAs: 'json',
                        load: function (resp) {
                            if (resp.success) {
                                self.grid.reload();
                            }
                            else{
                                buildspace.dialog.alert(nls.cannotBeDeleted, resp.errorMsg, 80, 300);
                            }
                            pb.hide();
                            self.grid.selection.clear();
                            self.grid.enableToolbarButtons(false);
                        },
                        error: function (error) {
                            pb.hide();
                            self.grid.selection.clear();
                            self.grid.enableToolbarButtons(false);
                        }
                    });
                });
            };

            buildspace.dialog.confirm(nls.confirmation, nls.confirmationMessage + '<br/>' + nls.cannotBeUndone, 80, 300, onYes);
        }
    });
});