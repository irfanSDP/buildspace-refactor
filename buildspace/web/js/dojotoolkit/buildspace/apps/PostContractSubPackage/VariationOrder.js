define('buildspace/apps/PostContractSubPackage/VariationOrder',[
    'dojo/_base/declare',
    'dojo/aspect',
    'dojo/currency',
    'dojo/number',
    "dijit/layout/ContentPane",
    "./VariationOrder/VariationOrderGrid",
    "./VariationOrder/VariationOrderItemContainer",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'],
    function(declare, aspect, currency, number, ContentPane, VariationOrderGrid, VariationOrderItemContainer, GridFormatter, nls) {
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

    return declare('buildspace.apps.PostContractSubPackage.VariationOrder', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;border:none;width:100%;height:100%;",
        gutters: false,
        region: "center",
        subPackage: null,
        postCreate: function() {
            this.inherited(arguments);

            var self = this,
                formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "variationOrder/getSpVariationOrderList/spid/"+self.subPackage.id,
                    clearOnClose: true
                }),
                grid = new VariationOrderGrid({
                    id: 'variation_order-'+self.subPackage.id,
                    stackContainerTitle: nls.scheduleOfRates,
                    pageId: 'variation_order-'+self.subPackage.id,
                    subPackage: self.subPackage,
                    type: 'vo',
                    gridOpts: {
                        store: store,
                        addUrl: 'variationOrder/spVariationOrderAdd',
                        updateUrl: 'variationOrder/spVariationOrderUpdate',
                        deleteUrl: 'variationOrder/spVariationOrderDelete',
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.description, field: 'description', width:'auto', editable:true, cellType:'buildspace.widget.grid.cells.Textarea' },
                            {name: nls.omission, field: 'omission', width:'150px', styles:'text-align:right;color:#FF0000;', formatter: formatter.unEditableCurrencyCellFormatter},
                            {name: nls.addition, field: 'addition', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                            {name: nls.nettOmissionAddition, field: 'nett_omission_addition', width:'150px', styles:'text-align:right;', formatter: CustomFormatter.nettOmissionAdditionCellFormatter},
                            {name: nls.upToDateClaim, field: 'total_claim', width:'150px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                            {name: nls.status, field: 'is_approved', width:'80px', styles:'text-align:center;', editable: true, type: 'dojox.grid.cells.Select', options: statusOptions.options, values: statusOptions.values, formatter: CustomFormatter.statusCellFormatter },
                            {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
                        ],
                        onRowDblClick: function(e){
                            var _this = this, _item = _this.getItem(e.rowIndex);
                            if(_item.id > 0 && _item.description[0] !== null && _item.description[0].length > 0){
                               new VariationOrderItemContainer({
                                    stackContainerTitle: _item.description,
                                    subPackage: self.subPackage,
                                    variationOrder: _item,
                                    id: 'SP_variation_order_items-'+_item.id+'-'+self.subPackage.id,
                                    pageId: 'SP_variation_order_items-'+_item.id+'-'+self.subPackage.id+'-page'
                               });
                            }
                        }
                    }
                });

            var gridContainer = this.makeGridContainer(grid, nls.variationOrders);
            this.addChild(gridContainer);
        },
        makeGridContainer: function(content, title){
            var id = this.subPackage.id;
            var stackContainer = dijit.byId('SP_variationOrder-'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('SP_variationOrder-'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:none;',
                region: "center",
                id: 'SP_variationOrder-'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'SP_variationOrder-'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;margin:0;width:100%;height:100%;border:none;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('SP_variationOrder-'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('SP_variationOrder-'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){

                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();
                            index = index + 1;
                        }

                        var selectedIndex = page.grid.selection.selectedIndex;

                        page.grid.store.save();
                        page.grid.store.close();

                        var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                            handle.remove();

                            if(selectedIndex > -1){
                                this.scrollToRow(selectedIndex, true);
                                this.selection.setSelected(selectedIndex, true);
                            }
                        });

                        page.grid.sort();
                    }
                }
            });

            return borderContainer;
        }
    });
});