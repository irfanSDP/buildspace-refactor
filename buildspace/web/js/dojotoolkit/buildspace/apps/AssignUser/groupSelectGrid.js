(function() {
    define(
        "buildspace/apps/AssignUser/groupSelectGrid",
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
                 Formatter,
                 IndirectSelection,
                 FilterToolbar,
                 nls) {

            var GroupSelectGrid = declare( 'buildspace.apps.groupSelectGrid', dojox.grid.EnhancedGrid, {
                region: "center",
                style: "border-top:none;",
                projectStatus: null,
                rootProject: null,
                parent: null,
                constructor: function(args) {
                    this.inherited( arguments );
                },
                postCreate: function() {
                    this.inherited( arguments );
                },
                onRowDblClick: function(e) {
                    var item = this.getItem( e.rowIndex );

                    if( item.id != buildspace.constants.GRID_LAST_ROW )
                    {
                        this.parent.hide();
                        this.parent.parent.addTab({ id: item.id[ 0 ], name: item.name[ 0 ] });
                    }
                }
            } );

            return declare( "buildspace.apps.GroupSelectDialog", dijit.Dialog, {
                style: "padding:0px;margin:0px;",
                title: nls.selectGroup,
                projectStatus: null,
                rootProject: null,
                sysName: null,
                _csrf_token: null,
                constructor: function(args) {
                    this.inherited( arguments );
                },
                buildRendering: function() {
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
                onHide: function() {
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
                            if( self.parent.tabContainer.getChildren().length < 1 ) {
                                self.parent.onHide();
                            }
                            self.hide();
                        }
                    } ) );

                    borderContainer.addChild( toolbar );

                    var formatter = new Formatter();

                    var grid = new GroupSelectGrid( {
                        projectStatus: self.projectStatus,
                        rootProject: self.rootProject,
                        sysName: self.sysName,
                        parent: self,
                        store: new dojo.data.ItemFileWriteStore( {
                            clearOnClose: true,
                            url: "projectUserPermission/getGroupsBySysName/sys/" + self.sysName
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
                        } ]
                    } );

                    borderContainer.addChild( new FilterToolbar( {
                        region: 'top',
                        grid: grid,
                        editableGrid: false,
                        filterFields: [ 'name' ]
                    } ) );

                    borderContainer.addChild( grid );

                    return borderContainer;
                }
            } );
        } );
}).call( this );
