(function() {
    define( "buildspace/apps/AssignUser/assignGroupProjectGrid",
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
            './groupSelectGrid',
            "buildspace/widget/grid/cells/Formatter",
            "dojox/grid/enhanced/plugins/IndirectSelection",
            'buildspace/widget/grid/Filter',
            'dojo/i18n!buildspace/nls/AssignUser'
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
                 GroupSelectGrid,
                 Formatter,
                 IndirectSelection,
                 FilterToolbar,
                 nls) {
            var AssignGroupProjectGridContainer, Dialog, AssignGroupProjectGrid;
            AssignGroupProjectGrid = declare( "buildspace.apps.AssignUser.AssignGroupProjectGrid", dojox.grid.EnhancedGrid, {
                style: "border-top:none;",
                region: "center",
                dialog: null,
                rootProject: null,
                userGroupId: -1,
                projectStatus: null,
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

                    this.on( "RowClick", function(e) {
                        var colField = e.cell.field,
                            rowIndex = e.rowIndex,
                            _item = this.getItem( rowIndex );
                        if( colField == 'is_admin' && _item.id > 0 ) {
                            self.dialog.isAdminUpdate( _item );
                        }
                    }, true );

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

            AssignGroupProjectGridContainer = declare( "buildspace.apps.AssignUser.AssignGroupProjectGridContainer", dijit.layout.BorderContainer, {
                style: "padding:0px;margin:0px;border:0px;width:100%;height:100%;overflow:hidden;",
                region: "center",
                gutters: false,
                filterToolbar: {},
                userGroupId: -1,
                dialog: null,
                store: null,
                rootProject: null,
                selectedItemIds: [],
                deselectedItemIds: [],
                gridId: null,
                postCreate: function() {
                    var grid, formatter;
                    this.inherited( arguments );
                    formatter = new Formatter();
                    grid = this.grid = new AssignGroupProjectGrid( {
                        id: this.gridId,
                        dialog: this.dialog,
                        rootProject: this.rootProject,
                        projectStatus: this.projectStatus,
                        userGroupId: this.userGroupId,
                        selectedItemIds: this.selectedItemIds,
                        deselectedItemIds: this.deselectedItemIds,
                        store: new dojo.data.ItemFileWriteStore( {
                            clearOnClose: true,
                            url: "projectUserPermission/getUsersByGroup/id/" + this.rootProject.id + "/st/" + this.projectStatus + "/gid/" + this.userGroupId
                        } ),
                        structure: [ {
                            name: "No",
                            field: "id",
                            width: '30px',
                            styles: 'text-align: center;',
                            formatter: formatter.rowCountCellFormatter
                        }, {
                            name: nls.name,
                            field: "name",
                            width: 'auto'
                        }, {
                            name: nls.email,
                            field: "email",
                            width: '200px'
                        }, {
                            name: nls.admin,
                            field: "is_admin",
                            width: '60px',
                            styles: 'text-align: center;',
                            formatter: formatter.setAdminButtonCellFormatter
                        } ]
                    } );

                    this.addChild( this.filterToolbar = new FilterToolbar( {
                        region: 'top',
                        grid: grid,
                        editableGrid: false,
                        filterFields: [ 'name', 'email' ]
                    } ) );

                    this.addChild( grid );
                }
            } );

            Dialog = declare( "buildspace.apps.AssignUser.GroupProjectAssignmentDialog", dijit.Dialog, {
                style: "padding:0px;margin:0px;",
                title: null,
                rootProject: null,
                sysName: null,
                userGroups: [],
                projectStatus: null,
                assignedUserIds: [],
                selectedItemIds: [],
                deselectedItemIds: [],
                _csrf_token: null,
                buildRendering: function() {
                    if( !this.title ) {
                        // Default dialog title.
                        this.title = nls.assignUsersToProject + " (" + buildspace.truncateString( this.rootProject.title, 80 ) + ")";
                    }

                    var content;

                    this.selectedItemIds = [];
                    this.deselectedItemIds = [];

                    content = this.createContent();
                    content.startup();
                    this.content = content;
                    this.inherited( arguments );
                },
                postCreate: function() {
                    var self = this;

                    dojo.xhrGet( {
                        url: 'projectUserPermission/getGroupsBySysName',
                        content: {sys: self.sysName},
                        handleAs: 'json'
                    } ).then( function(data) {
                        self._csrf_token = data._csrf_token;
                    } );

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
                    this.userGroups.length = 0;
                    return this.destroyRecursive();
                },
                createContent: function() {
                    var borderContainer, toolbar, self;
                    self = this;
                    borderContainer = new dijit.layout.BorderContainer( {
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

                    toolbar.addChild( self.saveButton = new dijit.form.Button( {
                        label: nls.save,
                        iconClass: "icon-16-container icon-16-save",
                        disabled: (self.userGroups.length < 1),
                        onClick: function() {
                            self.save();
                        }
                    } ) );

                    toolbar.addChild( new dijit.ToolbarSeparator() );

                    toolbar.addChild( new dijit.form.Button( {
                        label: 'Select Group',
                        iconClass: "icon-16-container icon-16-home",
                        onClick: function() {
                            self.selectGroup();
                        }
                    } ) );

                    borderContainer.addChild( toolbar );

                    var tabContainer = self.tabContainer = new dijit.layout.TabContainer( {
                        region: "center",
                        style: "padding:0px;margin:0px;border:0px;width:100%;height:100%;"
                    } );

                    borderContainer.addChild( tabContainer );
                    return borderContainer;
                },
                addTab: function(group) {
                    var self = this;

                    // Check if the group tab already exists.
                    var gridContainerId = 'assignUserPermissionGridContainer-' + self.rootProject.id + '-' + group.id;
                    var assignGroupProjectGridContainer = dijit.byId( gridContainerId );

                    if( !assignGroupProjectGridContainer ) {

                        self.userGroups.push( group );

                        dojo.xhrGet( {
                            url: "projectUserPermission/getAssignedUsersByGroup",
                            content: {
                                id: self.rootProject.id,
                                st: self.projectStatus,
                                group: group.id
                            },
                            handleAs: 'json'
                        } ).then( function(resp) {

                            array.forEach( resp, function(item) {
                                self.pushUserIdIntoGridArray( item.id, true );
                            } );

                            // Add a tab for the group.
                            assignGroupProjectGridContainer = new AssignGroupProjectGridContainer( {
                                gridId: 'assignUserPermissionGrid-' + self.rootProject.id + '-' + group.id,
                                id: gridContainerId,
                                title: buildspace.truncateString( group.name, 35 ),
                                closable: true,
                                onClose: function()
                                {
                                    self.userGroups = self.userGroups.filter( function(obj) {
                                        return (obj.id != group.id)
                                    } );

                                    self.updateSaveButtonDisabledProperty();

                                    return true;
                                },
                                dialog: self,
                                userGroupId: group.id,
                                projectStatus: self.projectStatus,
                                selectedItemIds: self.selectedItemIds,
                                deselectedItemIds: self.deselectedItemIds,
                                rootProject: self.rootProject
                            } );

                            self.tabContainer.addChild( assignGroupProjectGridContainer );
                            self.tabContainer.selectChild( assignGroupProjectGridContainer );

                            aspect.after( assignGroupProjectGridContainer.filterToolbar, "refreshGrid", function() {
                                dojo.xhrGet( {
                                    url: "projectUserPermission/getAssignedUsersByGroup",
                                    content: {
                                        id: self.rootProject.id,
                                        st: self.projectStatus,
                                        group: group.id
                                    },
                                    handleAs: 'json'
                                } ).then( function(resp) {
                                    var assignedUserIds = [];
                                    array.forEach( resp, function(item) {
                                        assignedUserIds.push( item.id );
                                    } );
                                    assignGroupProjectGridContainer.grid.markedCheckBoxObject( assignedUserIds, true );
                                } );
                            } );
                        } );
                    }

                    if( assignGroupProjectGridContainer ) {
                        self.tabContainer.selectChild( assignGroupProjectGridContainer );
                    }

                    self.updateSaveButtonDisabledProperty();
                },
                selectGroup: function() {
                    var self = this;

                    var pb = buildspace.dialog.indeterminateProgressBar( {
                        title: nls.pleaseWait + '...'
                    } );

                    pb.show().then( function() {
                        var groupSelectGrid = new GroupSelectGrid( {
                            rootProject: self.rootProject,
                            sysName: self.sysName,
                            parent: self,
                            projectStatus: self.projectStatus
                        } );

                        groupSelectGrid.show();

                        pb.hide();
                    } );
                },
                updateSaveButtonDisabledProperty: function() {
                    this.saveButton.set( 'disabled', (this.userGroups.length < 1) );
                },
                pushUserIdIntoGridArray: function(itemId, selected) {
                    var selectedItemIdx = dojo.indexOf( this.selectedItemIds, itemId );
                    var deselectedItemIdx = dojo.indexOf( this.deselectedItemIds, itemId );

                    if( selected ) {
                        if( selectedItemIdx === -1 ) {
                            this.selectedItemIds.push( itemId );
                        }
                        if( deselectedItemIdx !== -1 ) {
                            this.deselectedItemIds.splice( deselectedItemIdx, 1 );
                        }
                    } else {
                        if( selectedItemIdx !== -1 ) {
                            this.selectedItemIds.splice( selectedItemIdx, 1 );
                        }
                        if( deselectedItemIdx === -1 ) {
                            this.deselectedItemIds.push( itemId );
                        }
                    }
                },
                save: function() {
                    var self = this;

                    var pb = buildspace.dialog.indeterminateProgressBar( {
                        title: nls.pleaseWait + '...'
                    } );

                    pb.show().then( function() {
                        dojo.xhrPost( {
                            url: "projectUserPermission/updateUserPermission",
                            handleAs: "json",
                            content: {
                                id: self.rootProject.id,
                                st: self.projectStatus,
                                "uid1[]": self.selectedItemIds,
                                "uid2[]": self.deselectedItemIds,
                                "_csrf_token": self._csrf_token
                            },
                            load: function(data) {
                                if( data.success ) {
                                    self.selectedItemIds = [];
                                    self.deselectedItemIds = [];

                                    self.reloadAllGrids( data.ids );
                                }

                                pb.hide();
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        } );
                    } );
                },
                isAdminUpdate: function(item) {
                    var dialog = this;

                    var pb = buildspace.dialog.indeterminateProgressBar( {
                        title: nls.pleaseWait + '...'
                    } );

                    pb.show().then( function() {
                        dojo.xhrPost( {
                            url: "projectUserPermission/isAdminUpdate",
                            handleAs: "json",
                            content: {
                                id: item.id,
                                pid: dialog.rootProject.id,
                                st: dialog.projectStatus,
                                "_csrf_token": item._csrf_token
                            },
                            load: function(data) {
                                if( data.success ) {
                                    dialog.selectedItemIds = [];
                                    dialog.deselectedItemIds = [];

                                    dialog.reloadAllGrids( data.ids );
                                }

                                pb.hide();
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        } );
                    } );
                },
                reloadAllGrids: function(ids) {
                    var self = this;

                    array.forEach( ids, function(item) {
                        self.pushUserIdIntoGridArray( item.id, true );
                    } );

                    array.forEach( self.userGroups, function(group) {
                        var grid = dijit.byId( 'assignUserPermissionGrid-' + self.rootProject.id + '-' + group.id );

                        if( grid ) {

                            grid.selection.deselectAll();

                            grid.selectedItemIds = self.selectedItemIds;
                            grid.deselectedItemIds = self.deselectedItemIds;

                            grid.refreshGrid();
                        }
                    } );
                }
            } );
            return Dialog;
        } );
}).call( this );
