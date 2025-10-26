(function() {
    define( "buildspace/apps/MasterCostData/LinkProjectParticularsDialog",
        [
            "dojo/_base/declare",
            "dojo/aspect",
            "dojo/_base/array",
            "dojo/_base/lang",
            "dojo/_base/connect",
            "dojo/when",
            "dojo/html",
            "dojo/dom",
            "dojo/keys",
            "dojo/dom-style",
            "buildspace/widget/grid/cells/Formatter",
            "dojox/grid/enhanced/plugins/IndirectSelection",
            'dojo/i18n!buildspace/nls/CostData'
        ],
        function(declare,
            aspect,
            array,
            lang,
            connect,
            when_,
            html,
            dom,
            keys,
            domStyle,
            Formatter,
            IndirectSelection,
            nls) {

            var LinkProjectParticularsGrid = declare( "buildspace.apps.MasterCostData.LinkProjectParticularsGrid", dojox.grid.EnhancedGrid, {
                style: "border-top:none;",
                region: "center",
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

            return declare( "buildspace.apps.MasterCostData.LinkProjectParticularsDialog", dijit.Dialog, {
                style: "padding:0px;margin:0px;",
                title: null,
                masterCostData: null,
                item: null,
                field: '',
                buildRendering: function() {
                    if( !this.title ) {
                        this.title = buildspace.truncateString( nls.projectParticulars, 80 );
                    }

                    var content = this.createContent();
                    content.startup();

                    this.content = content;
                    this.inherited( arguments );
                },
                postCreate: function() {
                    domStyle.set( this.containerNode, {
                        padding: "0px",
                        margin: "0px"
                    } );
                    this.closeButtonNode.style.display = "none";

                    this.inherited( arguments );
                },
                _onKey: function(e) {
                    var key = e.keyCode;
                    if( key === keys.ESCAPE ) {
                        return dojo.stopEvent( e );
                    }
                },
                onHide: function() {
                    return this.destroyRecursive();
                },
                createContent: function() {
                    var borderContainer, toolbar, self;
                    self = this;
                    borderContainer = self.borderContainer = new dijit.layout.BorderContainer( {
                        style: "padding:0px;width:750px;height:380px;",
                        gutters: false
                    } );

                    toolbar = new dijit.Toolbar( {
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

                    borderContainer.addChild( toolbar );

                    this.createGrid();

                    return borderContainer;
                },
                createGrid: function(){
                    var self = this;
                    self.gridFormatter = new Formatter();

                    dojo.xhrGet({
                        url: "masterCostData/getLinkedProjectParticulars",
                        content: {
                            id: self.item.id,
                            master_cost_data_id: self.masterCostData.id,
                            field: self.field,
                            _csrf_token: self.item._csrf_token
                        },
                        handleAs: "json",
                        load: function(data) {
                            if( data.success ) {
                                self.grid = new LinkProjectParticularsGrid({
                                    selectedItemIds: data.linked_project_particular_ids,
                                    deselectedItemIds: [],
                                    store: new dojo.data.ItemFileWriteStore( {
                                        clearOnClose: true,
                                        url:"masterCostData/getProjectParticularList/master_cost_data_id/"+self.masterCostData.id
                                    } ),
                                    structure: [ {
                                        name: "No",
                                        field: "id",
                                        width: '30px',
                                        styles: 'text-align: center;',
                                        formatter: self.gridFormatter.rowCountCellFormatter
                                    }, {
                                        name: nls.description,
                                        field: "description",
                                        width: 'auto'
                                    }, {
                                        name: nls.unit,
                                        field: "uom_id",
                                        width: '200px',
                                        formatter: self.gridFormatter.unitCellFormatter
                                    } ]
                                } );

                                self.borderContainer.addChild(self.grid);
                            }
                        },
                        error: function(error) {
                        }
                    });

                },
                save: function() {

                    var self = this;

                    var pb = buildspace.dialog.indeterminateProgressBar( {
                        title: nls.pleaseWait + '...'
                    } );

                    pb.show().then( function() {
                        dojo.xhrPost( {
                            url: "masterCostData/linkProjectParticulars",
                            handleAs: "json",
                            content: {
                                id: self.item.id,
                                field: self.field,
                                "selectedIds[]": self.grid.selectedItemIds,
                                "deselectedIds[]": self.grid.deselectedItemIds,
                                "_csrf_token": self.item._csrf_token
                            },
                            load: function(data) {
                                if( ! data.success ) {
                                    new buildspace.dialog.alert(nls.savingUnsuccessful, data.errorMsg, 80, 400);
                                }

                                self.grid.refreshGrid();
                                self.grid.selectedItemIds = data.linked_project_particular_ids;
                                self.grid.deselectedItemIds = [];
                                self.grid.markedCheckBoxObject(self.grid.selectedItemIds, true);

                                pb.hide();
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        } );
                    } );
                }
            } );
        } );
}).call( this );
