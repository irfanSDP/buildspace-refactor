define('buildspace/apps/CostData/LinkVariationOrderItems/LinkVariationOrderItemsGrid',[
    'dojo/_base/declare',
    "dojo/aspect",
    'dojo/number',
    'dojo/currency',
    "dojo/_base/array",
    "dojo/_base/connect",
    'dojox/grid/EnhancedGrid',
    'buildspace/widget/grid/cells/Formatter',
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dijit/form/TextBox',
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, aspect, number, currency, array, connect, EnhancedGrid, GridFormatter, IndirectSelection, TextBox, nls){

    var Grid = declare('buildspace.apps.CostData.LinkVariationOrderItems.LinkVariationOrderItemsGrid', EnhancedGrid, {
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        escapeHTMLInData: false,
        selectedItemIds: [],
        deselectedItemIds: [],
        constructor: function(args) {
            this.plugins = {
                indirectSelection: {
                    headerSelector: true,
                    width: "20px",
                    styles: "text-align:center;"
                }
            };
            this.inherited( arguments );
        },
        canSort: function() {
            return false;
        },
        postCreate: function() {
            var self;
            self = this;
            this.inherited( arguments );

            aspect.after( this, "_onFetchComplete", function() {
                self.markedCheckBoxObject( self.selectedItemIds, true );
            } );

            this._connects.push( connect.connect( this, 'onSelected', function(rowIndex) {
                var item = self.getItem( rowIndex );
                if( item && item.id[ 0 ] > 0 ) {
                    self.pushItemIdIntoGridArray( item, true );
                }
            } ) );

            this._connects.push( connect.connect( this, 'onDeselected', function(rowIndex) {
                var item = self.getItem( rowIndex );
                if( item && item.id[ 0 ] > 0 ) {
                    self.pushItemIdIntoGridArray( item, false );
                }
            } ) );

            this._connects.push( connect.connect( this.rowSelectCell, 'toggleAllSelection', function(newValue) {
                self.toggleAllSelection( newValue );
            } ) );
        },
        markedCheckBoxObject: function(items, selected) {
            var self = this, store = this.store;

            array.forEach( items, function(id) {
                store.fetchItemByIdentity( {
                    identity: id,
                    onItem: function(node) {
                        if( !node ) {
                            return;
                        }
                        self.pushItemIdIntoGridArray( node, selected );
                        self.rowSelectCell.toggleRow( node._0, selected );
                    }
                } );
            } );
        },
        pushItemIdIntoGridArray: function(item, selected) {
            var selectedItemIdx = dojo.indexOf( this.selectedItemIds, item.id[ 0 ] );
            var deselectedItemIdx = dojo.indexOf( this.deselectedItemIds, item.id[ 0 ] );

            if( selected ) {
                if( selectedItemIdx === -1 ) {
                    this.selectedItemIds.push( item.id[ 0 ] );
                }
                if( deselectedItemIdx !== -1 ) {
                    this.deselectedItemIds.splice( deselectedItemIdx, 1 );
                }
            } else {
                if( selectedItemIdx !== -1 ) {
                    this.selectedItemIds.splice( selectedItemIdx, 1 );
                }
                if( deselectedItemIdx === -1 ) {
                    this.deselectedItemIds.push( item.id[ 0 ] );
                }
            }
        },
        toggleAllSelection: function(checked) {
            var grid, selection;
            grid = this;
            selection = grid.selection;

            if( checked ) {
                selection.selectRange( 0, grid.rowCount - 1 );
            } else {
                selection.deselectAll();
            }

            return grid.store.fetch( {
                onComplete: function(items) {
                    dojo.forEach( items, function(item, index) {
                        if( item.id > 0 ) {
                            grid.pushItemIdIntoGridArray( item, checked );
                        }
                    } );
                }
            } );
        },
        refreshGrid: function() {
            this.selection.deselectAll();
            this.beginUpdate();

            this.store.close();

            this._refresh();

            this.endUpdate();
        },
        destroy: function() {
            this.inherited( arguments );
            array.forEach( this._connects, connect.disconnect );
            return delete this._connects;
        }
    } );

    var CustomFormatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return parseInt(rowIdx)+1;
        },
        unEditableAmountCellFormatter: function(cellValue, rowIdx, cell){
            var value = number.parse(cellValue);

            cell.customClasses.push('disable-cell');

            if(isNaN(value) || value == 0 || value == null){
                cellValue = "&nbsp;";
            }else{
                cellValue = currency.format(cellValue);
            }

            return cellValue;
        }
    };

    return declare('buildspace.apps.CostData.LinkVariationOrderItems.LinkVariationOrderItemsContainer', dijit.layout.BorderContainer, {
        region: "center",
        title: '',
        style:"padding:0px;border:none;width:100%;height:100%;",
        gutters: false,
        costData: null,
        masterItem: null,
        item: null,
        grid: null,
        linkedIds: [],
        moduleContainer: null,
        postCreate: function(){
            var self = this;

            this.inherited(arguments);

            this.addToolbar();
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.pleaseWait+'...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "variationOrder/getLinkedItems/cost_data_id/"+self.costData.id+"/id/"+self.masterItem.id+"/id_type/variation_order_item_id",
                    handleAs: "json",
                    load: function(data) {
                        if( data.success ) {
                            self.linkedIds = data.ids;
                            self.addBreakdownGrid();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                    }
                });
            });
        },
        getGridStructure: function(){
            this.gridFormatter = new GridFormatter;

            return [ {
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: CustomFormatter.rowCountCellFormatter
            },{
                name: nls.description,
                field: "description",
                width: 'auto'
            },{
                name: nls.unit,
                field: "uom_id",
                width: '70px',
                styles: 'text-align: center;',
                formatter: this.gridFormatter.unitCellFormatter
            },{
                name: nls.nettOmissionAddition,
                field: "nett_omission_addition",
                width: '140px',
                styles: 'text-align: right;',
                formatter: CustomFormatter.unEditableAmountCellFormatter
            } ];
        },
        addToolbar: function(){
            var self = this;

            var toolbar = new dijit.Toolbar( {
                region: "top",
                style: "outline:none!important;padding:2px;width:100%;"
            } );

            toolbar.addChild( new dijit.form.Button( {
                label: nls.save,
                iconClass: "icon-16-container icon-16-save",
                onClick: function() {
                    self.save();
                }
            } ) );

            self.addChild(toolbar);
        },
        addBreakdownGrid: function(){
            var self = this;

            var grid = self.grid = Grid({
                structure: self.getGridStructure(),
                selectedItemIds: self.linkedIds,
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"variationOrder/getVariationOrderItemList/costData/"+self.costData.id+'/void/'+self.item.id
                })
            });

            var gridContainer = new dijit.layout.ContentPane( {
                style:"padding:0px;border:none;width:100%;height:100%;",
                region: 'center',
                content: grid,
                grid: grid
            });

            self.addChild(gridContainer);
        },
        save: function() {
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar( {
                title: nls.pleaseWait + '...'
            } );

            pb.show().then( function() {
                dojo.xhrPost( {
                    url: "variationOrder/linkItems/id/"+self.masterItem.id+"/_csrf_token/"+self.item._csrf_token,
                    handleAs: "json",
                    content: {
                        cost_data_id: self.costData.id,
                        "selectedIds": self.grid.selectedItemIds.join(),
                        "deselectedIds": self.grid.deselectedItemIds.join(),
                        "_csrf_token": self._csrf_token
                    },
                    load: function(data) {
                        self.grid.refreshGrid();
                        self.grid.selectedItemIds = data.item_ids;
                        self.grid.deselectedItemIds = [];
                        self.grid.markedCheckBoxObject(self.grid.selectedItemIds, true);

                        if(self.moduleContainer.onSave) self.moduleContainer.onSave();

                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                } );
            } );
        }
    });
});