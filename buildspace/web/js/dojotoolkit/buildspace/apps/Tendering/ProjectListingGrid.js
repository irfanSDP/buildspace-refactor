define('buildspace/apps/Tendering/ProjectListingGrid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    'dojo/_base/event',
    "dojo/dom-class",
    "dijit/focus",
    'dojo/keys',
    'dojo/aspect',
    'dojox/grid/EnhancedGrid',
    "dojox/grid/enhanced/plugins/Rearrange",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    './tenderImportDialog',
    'buildspace/widget/grid/Filter',
    'dojo/i18n!buildspace/nls/Tendering'
], function(declare, lang, array, evt, domClass, focusUtil, keys, aspect, EnhancedGrid, Rearranger, MenuPlugin, SelectorPlugin, TenderImportDialog, FilterToolbar, nls){

    var ProjectListingGrid = declare('buildspace.apps.Tendering.ProjectListingGrid', EnhancedGrid, {
        keepSelection: true,
        id: 'Tendering-project_listing_grid',
        style: "border:none;",
        rowSelector: '0px',
        deleteUrl: null,
        constructor:function(args){
            this.rearranger = Rearranger(this, {});
        },
        canSort: function(inSortInfo){
            return false;
        },
        canEdit: function(inCell, inRowIndex) {
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);

            this.on("RowContextMenu", function(e){
                this.selection.clear();
                var item = this.getItem(e.rowIndex);
                this.selection.setSelected(e.rowIndex, true);
                if(item.id > 0){
                    this.contextMenu(e);
                    this.disableToolbarButtons(false);
                }
            }, true);

            this.on('RowClick', function(e){
                var item = this.getItem(e.rowIndex);
                if(item && item.id > 0){
                    this.disableToolbarButtons(false);
                }else{
                    this.disableToolbarButtons(true);
                }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        contextMenu: function(e){
            var rowCtxMenu = this.rowCtxMenu = new dijit.Menu();
            this.contextMenuItems(e);
            var info = {target: e.target, coords: e.keyCode !== keys.F10 && "pageX" in e ? {x: e.pageX, y: e.pageY } : null};
            var item = this.getItem(e.rowIndex);
            if(rowCtxMenu && item && (this.selection.isSelected(e.rowIndex) || e.rowNode && domClass.contains(e.rowNode, 'dojoxGridRowbar'))){
                rowCtxMenu._openMyself(info);
                evt.stop(e);
                return;
            }
        },
        contextMenuItems: function(e){
            //
        },
        deleteRow: function(rowIndex){
            var self = this,
                item = self.getItem(rowIndex),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });

            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            var onYes = function(){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: self.deleteUrl,
                        content: { id: item.id, _csrf_token: item._csrf_token },
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
                            } else {
                                new buildspace.dialog.alert(nls.projectCannotBeDeleted, data.errorMsg, 60, 380);
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
                    });
                });
            };

            if (item.can_be_deleted[0]) {
                var content = '<div>'+nls.deleteProjectAndAllData+'</div>';
                buildspace.dialog.confirm(nls.confirmation,content,60,280, onYes);
            } else {
                new buildspace.dialog.alert(nls.projectCannotBeDeleted, nls.projectCannotBeDeletedMsg, 80, 300);
            }
        },
        reload: function(){
            this.store.close();
            this._refresh();
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable){
            if(isDisable && buttonsToEnable instanceof Array ){
                dojo.forEach(buttonsToEnable, function(label){
                    var btn = dijit.byId('ProjectListing_'+label+'Row-button');
                    if(btn)
                        btn._setDisabledAttr(false);
                });
            }
        }
    });

    return declare('buildspace.apps.Tendering.ProjectListing', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;outline:none;width:100%;height:100%;",
        gutters: false,
        title: null,
        gridOpts: {},
        postCreate: function(){
            this.inherited(arguments);
            lang.mixin(this.gridOpts, {
                region:"center",
                borderContainerWidget: this
            });

            var grid = this.grid = new ProjectListingGrid(this.gridOpts);
            var toolbar = new dijit.Toolbar({region: "top", style:"outline:none!important;padding:2px;border:none;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    id: 'ProjectListing_ImportTenderProject-button',
                    label: nls.importTenderProject,
                    iconClass:"icon-16-container icon-16-import",
                    onClick: function(e){
                        var tenderImportDialog = new TenderImportDialog({
                            uploadUrl: "tendering/uploadTenderProject",
                            importUrl: "tendering/importTenderProject",
                            importType: "tender",
                            title: nls.importTenderProject
                        });

                        tenderImportDialog.show();
                    }
                })
            );

            this.addChild(new FilterToolbar({
                region: 'top',
                grid: grid,
                editableGrid: false,
                filterFields: ['title', 'reference', 'status', 'country', 'state']
            }));

            this.addChild(toolbar);
            this.addChild(grid);
        }
    });
});