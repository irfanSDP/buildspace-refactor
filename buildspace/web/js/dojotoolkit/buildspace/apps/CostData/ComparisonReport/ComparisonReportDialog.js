define('buildspace/apps/CostData/ComparisonReport/ComparisonReportDialog',[
    'dojo/_base/declare',
    "dojo/aspect",
    "dojo/_base/array",
    "dojo/_base/connect",
    "dojo/dom-style",
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'buildspace/widget/grid/Filter',
    "./ComparisonReportPreviewDialog",
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, aspect, array, connect, domStyle, EnhancedGrid, IndirectSelection, FilterToolbar, PreviewDialog, nls){

    var Grid = declare('buildspace.apps.CostData.ComparisonReport.Grid', dojox.grid.EnhancedGrid, {
        region: 'center',
        style: "border-top:none;",
        selectedItemIds: [],
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

            if( selected ) {
                if( selectedItemIdx === -1 ) {
                    this.selectedItemIds.push( item.id[ 0 ] );
                }
            } else {
                if( selectedItemIdx !== -1 ) {
                    this.selectedItemIds.splice( selectedItemIdx, 1 );
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

    return declare('buildspace.apps.CostData.ComparisonReport.Dialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.comparisonReport,
        exportUrl: null,
        gridUrl: null,
        costData: null,
        selectedItemIds: [],
        level: null,
        type: null,
        parentItemId: null,
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
                label: nls.export,
                iconClass: "icon-16-container icon-16-print",
                onClick: function() {
                    self.export();
                }
            } ) );

            toolbar.addChild( new dijit.ToolbarSeparator() );

            toolbar.addChild( new dijit.form.Button( {
                label: nls.preview,
                iconClass: "icon-16-container icon-16-print",
                onClick: function() {
                    self.createPreviewDialog();
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
                name: nls.name,
                field: "name",
                width: '350px'
            },
            {name: nls.type, field: 'type', width:'100px', styles:'text-align: center;'},
            {name: nls.country, field: 'country', width:'100px', styles:'text-align: center;'},
            {name: nls.state, field: 'state', width:'120px', styles:'text-align: center;'},
            {name: nls.tender_year, field: 'tender_year', width:'120px', styles:'text-align: center;'},
            {name: nls.award_year, field: 'award_year', width:'120px', styles:'text-align: center;'}];
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
            borderContainer.addChild(new FilterToolbar({
                region: 'top',
                grid: grid,
                editableGrid: false,
                filterFields: ['name', 'type', 'country', 'state', 'tender_year', 'award_year']
            }));
            borderContainer.addChild(this.createToolbar());
            borderContainer.addChild(grid);

            return borderContainer;
        },
        export: function(){
            form = document.createElement("form");
            form.setAttribute("method", "post");
            form.setAttribute("action", this.exportUrl);
            form.setAttribute("target", "_self");

            costDataId = document.createElement("select");
            costDataId.setAttribute("name", "selected_ids[]");
            costDataId.setAttribute('multiple', '');
            costDataId.setAttribute('type', 'hidden');
            form.appendChild(costDataId);

            var option;
            for(var i in this.grid.selectedItemIds)
            {
                option = document.createElement("option");
                option.setAttribute("value", this.grid.selectedItemIds[i]);
                option.setAttribute("selected", '');
                costDataId.appendChild(option);
            }

            document.body.appendChild(form);

            return form.submit();
        },
        createPreviewDialog: function(){
            var selectedCostDataInfo = {};
            selectedCostDataInfo[this.costData.id] = {
                name: this.costData.name,
                awarded_date: this.costData.awarded_date,
                approved_date: this.costData.approved_date,
                adjusted_date: this.costData.adjusted_date,
            };

            for(var i in this.grid.selectedItemIds){
                this.grid.store.fetchItemByIdentity( {
                    identity: this.grid.selectedItemIds[i],
                    onItem: function(node) {
                        if( !node ) {
                            return;
                        }

                        selectedCostDataInfo[node.id[0]] = {
                            name: node.name[0],
                            awarded_date: node.awarded_date[0],
                            approved_date: node.approved_date[0],
                            adjusted_date: node.adjusted_date[0],
                        };
                    }
                } );
            }

            var previewDialog = new PreviewDialog({
                title: nls.comparisonReport,
                costData: this.costData,
                selectedCostDataInfo: selectedCostDataInfo,
                selectedItemIds: this.grid.selectedItemIds,
                parent: this,
                parentItemId: this.parentItemId,
                level: this.level,
                type: this.type
            });

            previewDialog.show();
        }
    });
});
