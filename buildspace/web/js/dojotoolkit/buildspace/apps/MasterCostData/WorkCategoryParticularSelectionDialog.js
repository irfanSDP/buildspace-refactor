define('buildspace/apps/MasterCostData/WorkCategoryParticularSelectionDialog',[
    'dojo/_base/declare',
    "dojo/aspect",
    "dojo/_base/array",
    "dojo/_base/connect",
    "dojo/dom-style",
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, aspect, array, connect, domStyle, EnhancedGrid, IndirectSelection, nls){

    var Grid = declare('buildspace.apps.MasterCostData.WorkCategoryParticularSelectionDialog.Grid', dojox.grid.EnhancedGrid, {
        region: 'center',
        style: "border-top:none;",
        selectedItemIds: [],
        deselectedItemIds: [],
        canSort: function(inSortInfo){
            return false;
        },
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
        postCreate: function() {
            this.inherited( arguments );

            var self = this;

            aspect.after( this, "_onFetchComplete", function() {
                self.markedCheckBoxObject( self.selectedItemIds, true );
            } );

            this._connects.push( connect.connect( this, 'onSelected', function(rowIndex) {
                var item = self.getItem( rowIndex );
                if( item && ((item.id[ 0 ] > 0) || item.id[ 0 ] == 'provisional_sum') ) {
                    self.pushItemIdIntoGridArray( item, true );
                }
            } ) );

            this._connects.push( connect.connect( this, 'onDeselected', function(rowIndex) {
                var item = self.getItem( rowIndex );
                if( item && ((item.id[ 0 ] > 0) || item.id[ 0 ] == 'provisional_sum') ) {
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
    });

    var CustomFormatter = {
        rowCountCellFormatter: function(cellValue, rowIdx){
            return parseInt(rowIdx)+1;
        }
    };

    return declare('buildspace.apps.MasterCostData.WorkCategoryParticularSelectionDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.showHideColumns,
        gridUrl: null,
        masterCostData: null,
        projectCostingItem: null,
        selectedItemIds: [],
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;

            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createToolbar: function(){
            var self = this;

            var toolbar = new dijit.Toolbar( {
                region: "top",
                style: "outline:none!important;padding:2px;width:100%;"
            } );

            toolbar.addChild( new dijit.form.Button( {
                label: nls.close,
                iconClass: "icon-16-container icon-16-close",
                onClick: function() {
                    self.hide();
                }
            } ) );

            toolbar.addChild( new dijit.ToolbarSeparator() );

            toolbar.addChild( new dijit.form.Button( {
                label: nls.save,
                iconClass: "icon-16-container icon-16-save",
                onClick: function() {
                    self.save();
                }
            } ) );

            return toolbar;
        },
        getGridStructure: function(){
            return [{
                name: "No",
                field: "id",
                width: '30px',
                styles: 'text-align: center;',
                formatter: CustomFormatter.rowCountCellFormatter
            },{
                name: nls.description,
                field: "description",
                width: 'auto'
            }];
        },
        createContent: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:800px;height:350px;",
                gutters: false
            });

            var grid = this.grid = Grid({
                selectedItemIds: this.selectedItemIds,
                structure: this.getGridStructure(),
                store: dojo.data.ItemFileWriteStore({
                    url: this.gridUrl,
                    clearOnClose: true
                })
            });
            borderContainer.addChild(this.createToolbar());
            borderContainer.addChild(grid);

            return borderContainer;
        },
        save: function(){
            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'. '+nls.pleaseWait+'...'
            });

            var params = {
                "selected_ids[]": this.grid.selectedItemIds,
                "deselected_ids[]": this.grid.deselectedItemIds,
                _csrf_token: this.projectCostingItem._csrf_token ? this.projectCostingItem._csrf_token : null
            };

            var xhrArgs = {
                url: "masterCostData/updateWorkCategorySelectedParticulars/id/"+this.projectCostingItem.id,
                content: params,
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        self.hide();
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
    });
});
