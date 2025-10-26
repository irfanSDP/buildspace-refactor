define('buildspace/apps/ViewTenderer/fileBrowserDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/FileBrowser',
    'dijit/form/ToggleButton',
    'dijit/form/RadioButton',
    'dojox/grid/enhanced/plugins/Pagination',
    'dojox/grid/enhanced/plugins/Filter'
], function(declare, lang, connect, when, html, dom, keys, domStyle, GridFormatter, IndirectSelection, nls, ToggleButton, RadioButton){

    var FileBrowserGrid = declare('buildspace.apps.ViewTenderer.FileBrowserGrid', dojox.grid.EnhancedGrid, {
        type: null,
        selectedItem: null,
        projectId: 0,
        dialogWidget: null,
        _csrf_token: null,
        billGrid: null,
        escapeHTMLInData: false,
        style: "border-top:none;",
        plugins: {
            pagination: {
              pageSizes: ["20", "40", "80", "All"],
              description: true,
              sizeSwitch: true,
              pageStepper: true,
              gotoButton: true,
              defaultPageSize: 20,
                      /*page step to be displayed*/
              maxPageStep: 4,
                      /*position of the pagination bar*/
              position: "bottom"
            },
            filter: {
                // Show the closeFilterbarButton at the filter bar
                closeFilterbarButton: false,
                // Set the maximum rule count to 5
                ruleCount: 5,
                // Set the name of the items
                itemsName: "files"
            }
        },
        constructor: function(args){
            this.itemIds = [];
            this.connects = [];
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex);

                if(item && item.id > 0){
                    self.disableToolbarButtons(false);
                }else{
                    self.disableToolbarButtons(true);
                }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        disableToolbarButtons: function(isDisable){
            var deleteBtn = dijit.byId('FileBrowserGrid-'+this.projectId+'deleteFile-button');

            if(deleteBtn)
                deleteBtn._setDisabledAttr(isDisable);
        },
        deleteRow: function(rowIndex){
            var self = this, item = self.getItem(rowIndex),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });

            pb.show();
            var xhrArgs = {
                url: "tenderingExportFile/deleteFile",
                content: { id: item.id },
                handleAs: 'json',
                load: function(data) {
                    if(data.success){
                        var items = data.items;
                        var store = self.store;

                        for(var i=0, len=items.length; i<len; ++i){
                            store.fetchItemByIdentity({ 'identity' : items[i].id,  onItem : function(itm){
                                store.deleteItem(itm);
                                store.save();
                            }});
                        }
                        items.length = 0;
                    }
                    pb.hide();
                    self.selection.clear();
                    window.setTimeout(function() {
                        self.focus.setFocusIndex(rowIndex, 0);
                    }, 10);
                    self.disableToolbarButtons(true);
                    self.selectedItem = null;
                },
                error: function(error) {
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                    self.selectedItem = null;
                    pb.hide();
                }
            }
            dojo.xhrPost(xhrArgs);
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var FileBrowserGridContainer = declare('buildspace.apps.ViewTenderer.FileBrowserGridContainer', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        projectId: 0,
        gridOpts: {},
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { type: self.type, projectId: self.projectId, region:"center" });
            var grid = this.grid = new FileBrowserGrid(self.gridOpts);
            if(self.type != 'tree'){
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.close,
                        iconClass: "icon-16-container icon-16-close",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(grid.dialogWidget, 'hide')
                    })
                );

                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'FileBrowserGrid-'+self.projectId+'deleteFile-button',
                        label: nls.delete,
                        iconClass: "icon-16-container icon-16-trash",
                        disabled: grid.selection.selectedIndex > -1 ? false : true,
                        onClick: function(){
                            if(grid.selection.selectedIndex > -1){
                                grid.deleteRow(grid.selection.selectedIndex);
                            }
                        }
                    })
                );
                self.addChild(toolbar);
            }
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('ViewTenderer-file_browser_'+this.projectId+'_'+this.billElementId+'-stackContainer');
            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                container.selectChild(self.pageId);
            }
        }
    });

    var Dialog = declare('buildspace.apps.ViewTenderer.FileBrowserDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.fileBrowser,
        selectedItem: null,
        projectId: 0,
        billGrid: null,
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
        _onKey: function(e){
            key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createContent: function(){
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:800px;height:400px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            var formatter = new GridFormatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "tenderingExportFile/getFileByProject/id/"+self.projectId
                }),
                content = FileBrowserGridContainer({
                    stackContainerTitle: nls.files,
                    pageId: 'fileBrowser-page_library-'+this.projectId,
                    projectId: this.projectId,
                    gridOpts: {
                        store: store,
                        dialogWidget: self,
                        structure: [
                            {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                            {name: nls.filename, field: 'filename',  width:'auto' },
                            {name: nls.filetype, field: 'file_type', styles:'text-align:center;', width:'80px' },
                            {name: nls.updated_at, field: 'updated_at', styles:'text-align:center;', width:'150px' },
                            {name: nls.download, field: 'downloadPath', styles:'text-align:center;', width:'80px' }
                        ]
                    }
                });
            var gridContainer = this.makeGridContainer(content,nls.files);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        },
        makeGridContainer: function(content, title){
            var id = this.projectId;
            var stackContainer = dijit.byId('ViewTenderer-file_browser_'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('ViewTenderer-file_browser_'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:0px;',
                region: "center",
                id: 'ViewTenderer-file_browser_'+id+'-stackContainer'
            });

            var stackPane = new dijit.layout.ContentPane({
                title: title,
                content: content
            });

            stackContainer.addChild(stackPane);

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'ViewTenderer-file_browser_'+id+'-stackContainer'
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

            return borderContainer;
        }
    });

    return Dialog;
});