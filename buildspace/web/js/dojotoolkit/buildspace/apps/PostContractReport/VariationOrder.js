define('buildspace/apps/PostContractReport/VariationOrder',[
    'dojo/_base/declare',
    'dojo/aspect',
    'dojo/currency',
    'dojo/number',
    'dojo/request',
    'dojo/store/Memory',
    "dijit/layout/ContentPane",
    "./VariationOrder/VariationOrderGrid",
    "./VariationOrder/VariationOrderItemContainer",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'],
    function(declare, aspect, currency, number, request, Memory, ContentPane, VariationOrderGrid, VariationOrderItemContainer, GridFormatter, nls) {
        var statusOptions = {
            options: [
                nls.pending,
                nls.approved
            ],
            values: [
                "false",
                "true"
            ]
        };

        var CustomFormatter = {
            statusCellFormatter: function(cellValue, rowIdx, cell){
                var item = this.grid.getItem(rowIdx);

                if(item.id == buildspace.constants.GRID_LAST_ROW){
                    cell.customClasses.push('disable-cell');
                    return "&nbsp;";
                }

                if(cellValue == "true"){
                    cell.customClasses.push('green-cell');
                    return nls.approved.toUpperCase();
                }else{
                    cell.customClasses.push('yellow-cell');
                    return nls.pending.toUpperCase();
                }
            },
            nettOmissionAdditionCellFormatter: function(cellValue, rowIdx, cell){
                cell.customClasses.push('disable-cell');
                var value = number.parse(cellValue);

                if(value < 0){
                    return '<span style="color:#FF0000">'+currency.format(value)+'</span>';
                }else{
                    return value == 0 ? "&nbsp;" : '<span style="color:#42b449;">'+currency.format(value)+'</span>';
                }
            }
        };

        var VariationOrderContainer = declare('buildspace.apps.PostContractReport.VariationOrderContainer', dijit.layout.BorderContainer, {
            style: "padding:0px;border:0px;width:100%;height:100%;",
            gutters: false,
            project: null,
            voSelectedStore: [],
            voItemSelectedStore: [],
            postCreate: function() {
                this.inherited(arguments);

                this.voSelectedStore     = new Memory({ idProperty: 'id' });
                this.voItemSelectedStore = new Memory({ idProperty: 'id' });

                var self = this,
                    formatter = new GridFormatter(),
                    store = dojo.data.ItemFileWriteStore({
                        url: "variationOrder/getVariationOrderList/pid/"+self.project.id,
                        clearOnClose: true
                    });

                var grid = new VariationOrderGrid({
                    id: 'variation_order-'+self.project.id,
                    stackContainerTitle: nls.scheduleOfRates,
                    pageId: 'variation_order-'+self.project.id,
                    project: self.project,
                    type: 'vo',
                    gridOpts: {
                        type: 'vo',
                        gridContainer: self,
                        store: store,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.description, field: 'description', width:'auto'},
                            {name: nls.omission, field: 'omission', width:'150px', styles:'text-align:right;color:#FF0000;', formatter: formatter.unEditableCurrencyCellFormatter},
                            {name: nls.addition, field: 'addition', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                            {name: nls.nettOmissionAddition, field: 'nett_omission_addition', width:'150px', styles:'text-align:right;', formatter: CustomFormatter.nettOmissionAdditionCellFormatter},
                            {name: nls.upToDateClaim, field: 'total_claim', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                            {name: nls.status, field: 'is_approved', width:'80px', styles:'text-align:center;', type: 'dojox.grid.cells.Select', options: statusOptions.options, values: statusOptions.values, formatter: CustomFormatter.statusCellFormatter },
                            {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
                        ],
                        onRowDblClick: function(e) {
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.description[0] !== null && _item.description[0].length > 0) {
                                new VariationOrderItemContainer({
                                    gridContainer: self,
                                    stackContainerTitle: _item.description,
                                    project: self.project,
                                    variationOrder: _item,
                                    id: 'variation_order_items-'+_item.id+'-'+self.project.id,
                                    pageId: 'variation_order_items-'+_item.id+'-'+self.project.id+'-page'
                                });
                            }
                        },
                        singleCheckBoxSelection: function(e) {
                            var self = this,
                                rowIndex = e.rowIndex,
                                checked = this.selection.selected[rowIndex],
                                item = this.getItem(rowIndex);

                            // used to store removeable selection
                            self.removedIds = [];

                            if ( checked ) {
                                self.gridContainer.voSelectedStore.put({ id: item.id[0] });

                                return self.getAffectedItemsByVO(item, 'add');
                            } else {
                                self.gridContainer.voSelectedStore.remove(item.id[0]);

                                self.removedIds.push(item.id[0]);

                                return self.getAffectedItemsByVO(item, 'remove');
                            }
                        },
                        toggleAllSelection: function(checked) {
                            var self = this, selection = this.selection, storeName;

                            // used to store removeable selection
                            self.removedIds = [];

                            if (checked) {
                                selection.selectRange(0, self.rowCount-1);
                                self.store.fetch({
                                    onComplete: function (items) {
                                        dojo.forEach(items, function (item, index) {
                                            if(item.id > 0) {
                                                self.gridContainer.voSelectedStore.put({ id: item.id[0] });
                                            }
                                        });
                                    }
                                });

                                return self.getAffectedItemsByVO(null , 'add');
                            } else {
                                selection.deselectAll();

                                self.store.fetch({
                                    onComplete: function (items) {
                                        dojo.forEach(items, function (item, index) {
                                            if(item.id > 0) {
                                                self.gridContainer.voSelectedStore.remove(item.id[0]);

                                                self.removedIds.push(item.id[0]);
                                            }
                                        });
                                    }
                                });

                                return self.getAffectedItemsByVO(null, 'remove');
                            }
                        },
                        getAffectedItemsByVO: function(item, type) {
                            var self = this,
                                vos = [];

                            var pb = buildspace.dialog.indeterminateProgressBar({
                                title: nls.pleaseWait+'...'
                            });

                            pb.show();

                            if (type === 'add') {
                                self.gridContainer.voSelectedStore.query().forEach(function(item) {
                                    vos.push(item.id);
                                });
                            } else {
                                for (var voKeyIndex in self.removedIds) {
                                    vos.push(self.removedIds[voKeyIndex]);
                                }
                            }

                            request.post('variationOrderReporting/getAffectedItems', {
                                handleAs: 'json',
                                data: {
                                    pid: self.project.id,
                                    vo_ids: JSON.stringify(self.gridContainer.arrayUnique(vos))
                                }
                            }).then(function(data) {
                                if ( type === 'add' ) {
                                    for (var voId in data) {
                                        for (var itemIdIndex in data[voId]) {
                                            self.gridContainer.voItemSelectedStore.put({ id: data[voId][itemIdIndex] });
                                        }
                                    }
                                } else {
                                    for (var voId in data) {
                                        for (var itemIdIndex in data[voId]) {
                                            self.gridContainer.voItemSelectedStore.remove(data[voId][itemIdIndex]);
                                        }

                                        // remove checked type selection if no item is selected in the current VO
                                        self.store.fetchItemByIdentity({
                                            identity: voId,
                                            onItem: function(node) {
                                                if ( ! node ) {
                                                    return;
                                                }

                                                self.gridContainer.voSelectedStore.remove(voId);

                                                return self.rowSelectCell.toggleRow(node._0, false);
                                            }
                                        });
                                    }
                                }

                                pb.hide();
                            }, function(error) {
                                pb.hide();
                                console.log(error);
                            });
                        }
                    }
                });

                this.addChild(this.makeGridContainer(grid, nls.variationOrders));
            },
            makeGridContainer: function(content, title){
                var id = this.project.id;
                var stackContainer = dijit.byId('variationOrder-'+id+'-stackContainer');
                if(stackContainer){
                    dijit.byId('variationOrder-'+id+'-stackContainer').destroyRecursive();
                }

                stackContainer = new dijit.layout.StackContainer({
                    style:'width:100%;height:100%;border:0px;',
                    region: "center",
                    id: 'variationOrder-'+id+'-stackContainer'
                });

                var stackPane = new dijit.layout.ContentPane({
                    title: title,
                    content: content,
                    grid: content.grid
                });

                stackContainer.addChild(stackPane);

                var controller = new dijit.layout.StackController({
                    region: "top",
                    containerId: 'variationOrder-'+id+'-stackContainer'
                });

                var controllerPane = new dijit.layout.ContentPane({
                    style: "padding:0px;overflow:hidden;",
                    class: 'breadCrumbTrail',
                    region: 'top',
                    content: controller
                });

                var borderContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:100%;height:100%;border:0px;",
                    gutters: false,
                    region: 'center'
                });

                borderContainer.addChild(stackContainer);
                borderContainer.addChild(controllerPane);

                dojo.subscribe('variationOrder-'+id+'-stackContainer-selectChild',"",function(page){
                    var widget = dijit.byId('variationOrder-'+id+'-stackContainer');
                    if(widget){
                        var children = widget.getChildren(),
                            index = dojo.indexOf(children, page);

                        while(children.length > index+1 ){
                            index = index + 1;
                            widget.removeChild(children[ index ]);
                            children[ index ].destroyRecursive();
                        }
                    }
                });

                return borderContainer;
            },
            markedCheckBoxObject: function(grid, selectedRowStore) {
                var store = grid.store;

                selectedRowStore.query().forEach(function(item) {
                    if (item.id == buildspace.constants.GRID_LAST_ROW) {
                        return;
                    }

                    store.fetchItemByIdentity({
                        identity: item.id,
                        onItem: function(node) {
                            if ( ! node ) {
                                return;
                            }

                            return grid.rowSelectCell.toggleRow(node._0, true);
                        }
                    });
                });
            },
            arrayUnique: function(array) {
                return array.reverse().filter(function (e, i, arr) {
                    return arr.indexOf(e, i+1) === -1;
                }).reverse();
            }
        });

    return declare('buildspace.apps.PostContractReport.VariationOrder', dijit.layout.BorderContainer, {
        region: "center",
        rootProject: null,
        style: "padding:0px;border:0px;margin:0px;width:100%;height:100%;",
        postCreate: function() {
            this.inherited(arguments);
            var variationOrderBC = VariationOrderContainer({
                project: this.rootProject,
                region: 'center'
            });

            this.addChild(variationOrderBC);
        }
    });
});