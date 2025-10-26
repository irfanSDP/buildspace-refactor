define('buildspace/apps/SubPackage/grid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/dom-attr",
    "dojox/grid/enhanced/plugins/Menu",
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    "dojo/when",
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    'buildspace/widget/grid/cells/Textarea',
    "./ImportResourceItemDialog",
    "./ImportScheduleOfRateItemDialog",
    "./ImportBillItemDialog",
    "./AssignContractorDialog",
    "./BillPrintoutSetting/printSettingDialog",
    "./BillPrintoutSetting/PrintBillDialog",
    "./ResourceFilterDialog",
    "./PushToPostContractDialog",
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dojo/i18n!buildspace/nls/SubPackages"
], function(declare, lang, array, domAttr, Menu, Selector, Rearrange, evt, keys, focusUtil, when, xhr, PopupMenuItem, Textarea, ImportResourceItemDialog, ImportScheduleOfRateItemDialog, ImportBillItemDialog, AssignContractorDialog, PrintSettingDialog, PrintBillDialog, ResourceFilterDialog, PushToPostContractDialog, DropDownButton, DropDownMenu, MenuItem, nls) {

    var SubPackagesGrid = declare('buildspace.apps.SubPackage.Grid', dojox.grid.EnhancedGrid, {
        type: null,
        subPackage: null,
        contId: 0,
        rootProject: null,
        style: "border-top:none;",
        selectedItem: null,
        keepSelection: true,
        rowSelector: '0px',
        addUrl: null,
        updateUrl: null,
        deleteUrl: null,
        pasteUrl: null,
        region: 'center',
        constructor:function(args) {
            this.rearranger = new Rearrange(this, {});
            this.structure  = args.structure;

            if ( args.type === 'sub_packages-bill_item_list' ) {
                this.createHeaderCtxMenu();
            }
        },
        canSort: function(inSortInfo) {
            return false;
        },
        postCreate: function() {
            var self = this;
            self.inherited(arguments);
            if(this.type == 'sub_packages-list') {
                this.on("RowContextMenu", function(e) {
                    self.selection.clear();
                    var item = self.getItem(e.rowIndex);
                    self.selection.setSelected(e.rowIndex, true);
                    self.contextMenu(e);
                    if(item.id > 0) {
                        self.disableToolbarButtons(false);
                    }else{
                        if(self.disableEditing) {
                            self.disableToolbarButtons(true);
                        }else{
                            self.disableToolbarButtons(true, ['Add']);
                        }
                    }
                }, true);

                this.on('RowClick', function(e) {
                    var item = self.getItem(e.rowIndex);
                    if(item && parseInt(String(item.id)) > 0) {
                        self.disableToolbarButtons(false);

                        if(self.type == 'sub_packages-list'){
                            if(item.locked[0]) {
                                self.disableToolbarButtons(true, ['Add']);
                            }else{
                                if(item.selected_company_id[0] == null) {
                                    self.disableToolbarButtons(true, ['Add', 'Delete', 'ImportResourceItem', 'ImportScheduleOfRateItem', 'ImportBillItem', 'ImportDropDown', 'AssignSubContractors', 'PrintSetting', 'PrintBQ', 'ExportSubPackage']);
                                }
                            }

                            if (e.cell) {
                                var colField = e.cell.field;
                                if(colField == 'filter'){
                                    new ResourceFilterDialog({
                                        subPackage: item
                                    }).show();
                                }
                            }
                        }

                    }else{
                        if(self.disableEditing) {
                            self.disableToolbarButtons(true);
                        }else{
                            self.disableToolbarButtons(true, ['Add']);
                        }
                    }
                });
            }
        },
        canEdit: function(inCell, inRowIndex) {
            var self = this;

            if(this.type=='sub_packages-list') {
                if(inCell != undefined) {
                    var item = this.getItem(inRowIndex);
                    if(item.locked[0]) {
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }
                }
            }

            if(this.type=='sub_packages-bill_item_list') {
                if(inCell != undefined) {
                    var item = this.getItem(inRowIndex);
                    if(item.id[0] <= 0 || (item.hasOwnProperty('type') && (item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type[0] == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type[0] < 1)) ) {
                        window.setTimeout(function() {
                            self.edit.cancel();
                            self.focus.setFocusIndex(inRowIndex, inCell.index);
                        }, 10);
                        return;
                    }
                }
            }
            return this._canEdit;
        },
        doApplyCellEdit: function(val, rowIdx, inAttrName) {
            var self = this,
                item = self.getItem(rowIdx),
                store = self.store,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
            var attrNameParsed = inAttrName.replace("rate-value-","");//for any sub cons rates

            if(val !== item[inAttrName][0]) {
                var params = {
                    id: item.id,
                    attr_name: attrNameParsed,
                    val: val,
                    _csrf_token: item._csrf_token ? item._csrf_token : null
                }, url = this.updateUrl;

                if(this.type == 'sub_packages-bill_item_list') {
                    lang.mixin(params, {
                        sid: this.subPackage.id
                    });
                }

                if(item.id==buildspace.constants.GRID_LAST_ROW) {
                    var prevItem = rowIdx > 0 ? self.getItem(rowIdx-1):false;
                    lang.mixin(params, {
                        prev_item_id: prevItem ? prevItem.id : 0,
                        relation_id: item.relation_id
                    });
                    url = this.addUrl;
                }

                var updateCell = function(data, store) {
                    for(var property in data) {
                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()) {
                            store.setValue(item, property, data[property]);
                        }
                    }
                    store.save();
                };

                pb.show().then(function(){
                    dojo.xhrPost({
                        url: url,
                        content: params,
                        handleAs: 'json',
                        load: function(resp) {
                            if(resp.success) {
                                if(item.id > 0) {
                                    updateCell(resp.data, store);
                                }else{
                                    store.deleteItem(item);
                                    store.save();
                                    dojo.forEach(resp.items, function(item) {
                                        store.newItem(item);
                                    });
                                    store.save();
                                    self.disableToolbarButtons(true);
                                }
                                var cell = self.getCellByField(inAttrName);
                                window.setTimeout(function() {
                                    self.focus.setFocusIndex(rowIdx, cell.index);
                                }, 10);
                                pb.hide();
                            }
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }
            this.inherited(arguments);
        },
        dodblclick: function(e) {
            this.onRowDblClick(e);
        },
        onStyleRow: function(e) {
            this.inherited(arguments);

            if(e.node.children[0]) {
                if(e.node.children[0].children[0].rows.length >= 2) {
                    var elemToHide = e.node.children[0].children[0].rows[1],
                        childElement = e.node.children[0].children[0].rows[0].children;

                    elemToHide.parentNode.removeChild(elemToHide);

                    dojo.forEach(childElement, function(child, i) {
                        var rowSpan = dojo.attr(child, 'rowSpan');
                        if(!rowSpan || rowSpan < 2)
                            dojo.attr(child, 'rowSpan', 2);
                    });
                }
            }
        },
        contextMenu: function(e) {
            var rowCtxMenu = this.rowCtxMenu = new dijit.Menu();
            this.contextMenuItems(e);
            var info = {target: e.target, coords: e.keyCode !== keys.F10 && "pageX" in e ? {x: e.pageX, y: e.pageY } : null};
            var item = this.getItem(e.rowIndex);
            if(rowCtxMenu && item && (this.selection.isSelected(e.rowIndex) || e.rowNode && html.hasClass(e.rowNode, 'dojoxGridRowbar'))) {
                rowCtxMenu._openMyself(info);
                evt.stop(e);
                return;
            }
        },
        contextMenuItems: function(e) {
            var self = this, item = this.getItem(e.rowIndex);
            self.rowCtxMenu.addChild(new dijit.MenuItem({
                label: nls.addRow,
                iconClass:"icon-16-container icon-16-add",
                disabled: (self.disableEditing) ? true : false,
                onClick: dojo.hitch(self,'addRow', e.rowIndex, 'lala')
            }));
            if(item.id > 0) {
                self.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.deleteRow,
                    iconClass:"icon-16-container icon-16-delete",
                    disabled: (self.disableEditing) ? true : false,
                    onClick: dojo.hitch(self,'deleteRow', e.rowIndex)
                }));
            }
        },
        addRow: function(rowIndex) {
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            var self = this,
                content,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.addingRow+'. '+nls.pleaseWait+'...'
                }),
                store = self.store,
                itemBefore = self.getItem(rowIndex);
            if(itemBefore.id > 0) {
                content = { before_id: itemBefore.id, _csrf_token:itemBefore._csrf_token };
            }else{
                var prevItemId = (rowIndex > 0) ? self.getItem(rowIndex-1).id : 0;
                content = { id: itemBefore.id, prev_item_id: prevItemId, relation_id: itemBefore.relation_id, _csrf_token:itemBefore._csrf_token }
            }

            pb.show().then(function(){
                dojo.xhrPost({
                    url: self.addUrl,
                    content: content,
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success) {
                            dojo.forEach(resp.items,function(data) {
                                if(data.id > 0) {
                                    var item = store.newItem(data);
                                    store.save();
                                    var itemIdx = self.getItemIndex(item);
                                    self.rearranger.moveRows([itemIdx], rowIndex);
                                    self.selection.clear();
                                }
                            });
                        }
                        window.setTimeout(function() {
                            self.selection.setSelected(rowIndex, true);
                            self.focus.setFocusIndex(rowIndex, 1);
                        }, 30);
                        pb.hide();
                    },
                    error: function(error) {
                        self.selection.clear();
                        self.disableToolbarButtons(true);
                        pb.hide();
                    }
                });
            });
        },
        deleteRow: function(rowIndex) {
            var self = this, item = self.getItem(rowIndex),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.deleting+'. '+nls.pleaseWait+'...'
                });
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            new buildspace.dialog.confirm(nls.deleteSubPackageTitle, nls.msgConfirmDeleteSubPackage, 80, 320, function() {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: self.deleteUrl,
                        content: { id: item.id, _csrf_token: item._csrf_token },
                        handleAs: 'json',
                        load: function(data) {
                            if(data.success) {
                                var items = data.items;
                                var store = self.store;
    
                                if(data.affected_nodes != undefined) {
                                    var affectedNodesList = data.affected_nodes;
                                    for(var i=0, len=affectedNodesList.length; i<len; ++i) {
                                        dojo.forEach(affectedNodesList[i], function(node) {
                                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(affectedItem) {
                                                for(var property in node) {
                                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()) {
                                                        store.setValue(affectedItem, property, node[property]);
                                                    }
                                                }
                                            }});
                                        });
                                    }
                                }
    
                                for(var i=0, len=items.length; i<len; ++i) {
                                    store.fetchItemByIdentity({ 'identity' : items[i].id,  onItem : function(itm) {
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
                            self.pasteOp = null;
                        },
                        error: function(error) {
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                            self.selectedItem = null;
                            self.pasteOp = null;
                            pb.hide();
                        }
                    });
                });
            }, function() {
                //on cancel
            });
        },
        disableToolbarButtons: function(isDisable, buttonsToEnable) {
            var addRowBtn = dijit.byId('SubPackages-'+this.contId+'-AddRow-button'),
                deleteRowBtn = dijit.byId('SubPackages-'+this.contId+'-DeleteRow-button'),
                importResourceItemBtn = dijit.byId('SubPackages-'+this.contId+'-ImportResourceItemRow-button'),
                importScheduleOfRateItemBtn = dijit.byId('SubPackages-'+this.contId+'-ImportScheduleOfRateItemRow-button'),
                importImportBillItemBtn = dijit.byId('SubPackages-'+this.contId+'-ImportBillItemRow-button'),
                importDropDownBtn = dijit.byId('SubPackages-'+this.contId+'-ImportDropDownRow-button'),
                assignSubContractorBtn = dijit.byId('SubPackages-'+this.contId+'-AssignSubContractorsRow-button');
                applyToUnitBtn = dijit.byId('SubPackages-'+this.rootProject.id+'-ApplyRow-button');
                pushToPostContractBtn = dijit.byId('SubPackages-'+this.rootProject.id+'-PushToPostContractRow-button');
                printSettingBtn = dijit.byId('SubPackages-'+this.contId+'-PrintSettingRow-button');
                exportSubPackageBtn = dijit.byId('SubPackages-'+this.contId+'-ExportSubPackageRow-button');
                printBQBtn = dijit.byId('SubPackages-'+this.contId+'-PrintBQRow-button');

            if(this.disableEditing) {
                if(addRowBtn)
                    addRowBtn._setDisabledAttr(true);
                if(deleteRowBtn)
                    deleteRowBtn._setDisabledAttr(true);
                if(importResourceItemBtn)
                    importResourceItemBtn._setDisabledAttr(true);
                if(importScheduleOfRateItemBtn)
                    importScheduleOfRateItemBtn._setDisabledAttr(true);
                if(importImportBillItemBtn)
                    importImportBillItemBtn._setDisabledAttr(true);
                if(importDropDownBtn)
                    importDropDownBtn._setDisabledAttr(true);
                if(pushToPostContractBtn)
                    pushToPostContractBtn._setDisabledAttr(true);
                if(applyToUnitBtn)
                    applyToUnitBtn._setDisabledAttr(isDisable);
                if(printSettingBtn)
                    printSettingBtn._setDisabledAttr(isDisable);
                if(printBQBtn)
                    printBQBtn._setDisabledAttr(isDisable);
                if(exportSubPackageBtn)
                    exportSubPackageBtn._setDisabledAttr(isDisable);
            }else{
                if(addRowBtn)
                    addRowBtn._setDisabledAttr(isDisable);
                if(deleteRowBtn)
                    deleteRowBtn._setDisabledAttr(isDisable);
                if(importResourceItemBtn)
                    importResourceItemBtn._setDisabledAttr(isDisable);
                if(importScheduleOfRateItemBtn)
                    importScheduleOfRateItemBtn._setDisabledAttr(isDisable);
                if(importImportBillItemBtn)
                    importImportBillItemBtn._setDisabledAttr(isDisable);
                if(importDropDownBtn)
                    importDropDownBtn._setDisabledAttr(isDisable);
                if(applyToUnitBtn)
                    applyToUnitBtn._setDisabledAttr(isDisable);
                if(printSettingBtn)
                    printSettingBtn._setDisabledAttr(isDisable);
                if(printBQBtn)
                    printBQBtn._setDisabledAttr(isDisable);
                if(exportSubPackageBtn)
                    exportSubPackageBtn._setDisabledAttr(isDisable);

                if(this.rootProject.status_id[0] != buildspace.constants.STATUS_POSTCONTRACT) {
                    if(pushToPostContractBtn)
                        pushToPostContractBtn._setDisabledAttr(true);

                }else{
                    if(pushToPostContractBtn)
                        pushToPostContractBtn._setDisabledAttr(isDisable);
                }
            }

            if(assignSubContractorBtn)
                assignSubContractorBtn._setDisabledAttr(isDisable);

            if(isDisable && buttonsToEnable instanceof Array ) {
                var _this = this;
                dojo.forEach(buttonsToEnable, function(label) {
                    var btn = dijit.byId('SubPackages-'+_this.contId+'-'+label+'Row-button');

                    if(btn)
                        btn._setDisabledAttr(false);

                })
            }
        },
        createHeaderCtxMenu: function() {
            var columnGroup = this.structure.cells[0],
                self = this,
                menusObject = {
                    headerMenu: new dijit.Menu()
                };

            dojo.forEach(columnGroup, function(data, index){
                if(data.showInCtxMenu){
                    menusObject.headerMenu.addChild(new dijit.CheckedMenuItem({
                        label: data.name,
                        checked: (typeof data.hidden === 'undefined' || data.hidden === false) ? true : false,
                        onChange: function(val) {

                            var show = false;

                            if (val) show = true;

                            self.showHideMergedColumn(show, index);
                        }
                    }));
                }
            });

            this.plugins = {menus: menusObject};
        },
        showHideMergedColumn: function(show, index) {
            this.beginUpdate();
            this.layout.setColumnVisibility(index, show);
            this.endUpdate();
        }
    });

    return declare('buildspace.apps.SubPackage.GridContainer', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;border:0px;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: null,
        rootProject: null,
        subPackage: null,
        disableEditing: false,
        gridOpts: {},
        type: null,
        postCreate: function() {
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { rootProject: self.rootProject, disableEditing: self.disableEditing, contId: self.id, type:self.type, region:"center"});
            var grid = this.grid = new SubPackagesGrid(self.gridOpts);

            if(self.type != 'sub_packages-bill_list' && self.type != 'sub_packages-resource_item_list' && self.type != 'sub_packages-bill_element_list' && self.type != 'sub_packages-bill_item_list') {
                var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});

                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'SubPackages-'+self.id+'-AddRow-button',
                        label: nls.addRow,
                        iconClass: "icon-16-container icon-16-add",
                        disabled: (grid.selection.selectedIndex > -1 && !self.disableEditing) ? false : true,
                        onClick: function() {
                            if(grid.selection.selectedIndex > -1) {
                                grid.addRow(grid.selection.selectedIndex);
                            }
                        }
                    })
                );
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(
                    new dijit.form.Button({
                        id: 'SubPackages-'+self.id+'-DeleteRow-button',
                        label: nls.deleteRow,
                        iconClass: "icon-16-container icon-16-delete",
                        disabled: (grid.selection.selectedIndex > -1 && !self.disableEditing) ? false : true,
                        onClick: function() {
                            if(grid.selection.selectedIndex > -1) {
                                grid.deleteRow(grid.selection.selectedIndex);
                            }
                        }
                    })
                );

                if(self.type == 'sub_packages-list') {
                    toolbar.addChild(new dijit.ToolbarSeparator());
                    
                    var importDropDownMenu = new DropDownMenu({ style: "display: none;"});
                    
                    importDropDownMenu.addChild(new MenuItem({
		            	id: 'SubPackages-'+self.id+'-ImportResourceItemRow-button',
		                label: nls.resourceAnalysis,
		                onClick: lang.hitch(self, "openImportResourceDialog", grid),
		                disabled: (grid.selection.selectedIndex > -1 && !self.disableEditing) ? false : true
		            }));
                    importDropDownMenu.addChild(new MenuItem({
                    	id: 'SubPackages-'+self.id+'-ImportScheduleOfRateItemRow-button',
		                label: nls.scheduleOfRateAnalysis,
		                onClick: lang.hitch(self, "openImportScheduleOfRateDialog", grid),
		                disabled: (grid.selection.selectedIndex > -1 && !self.disableEditing) ? false : true
		            }));
                    importDropDownMenu.addChild(new MenuItem({
                        id: 'SubPackages-'+self.id+'-ImportBillItemRow-button',
                        label: nls.bills,
                        onClick: lang.hitch(self, "openImportBillItemDialog", grid),
                        disabled: (grid.selection.selectedIndex > -1 && !self.disableEditing) ? false : true
                    }));
		            
                    toolbar.addChild(new DropDownButton({
		                id: 'SubPackages-'+self.id+'-ImportDropDownRow-button',
		                label: nls.extractBillItemFrom,
		                iconClass: "icon-16-container icon-16-import",
		                dropDown: importDropDownMenu,
		                disabled: (grid.selection.selectedIndex > -1 && !self.disableEditing) ? false : true
		            }));
		            
                    toolbar.addChild(new dijit.ToolbarSeparator());
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: 'SubPackages-'+self.id+'-AssignSubContractorsRow-button',
                            label: nls.assignSubContractors,
                            iconClass: "icon-16-container icon-16-messenger",
                            disabled: grid.selection.selectedIndex > -1 ? false : true,
                            onClick: function(e) {
                                var _item = grid.getItem(grid.selection.selectedIndex),
                                    companyFormXhr = dojo.xhrGet({
                                        url: "subPackage/subPackageCompanyForm/id/"+_item.id,
                                        handleAs: "json"
                                    }),
                                    pb = buildspace.dialog.indeterminateProgressBar({
                                        title:nls.pleaseWait+'...'
                                    });

                                pb.show();

                                when(companyFormXhr, function(values) {
                                    pb.hide();
                                    AssignContractorDialog({
                                        subPackage: _item,
                                        rootProject: self.rootProject,
                                        subPackageGrid: grid,
                                        disableEditing: self.disableEditing,
                                        formValues: values
                                    }).show();
                                });
                            }
                        })
                    );
                    toolbar.addChild(new dijit.ToolbarSeparator());
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: 'SubPackages-'+self.id+'-PrintSettingRow-button',
                            label: nls.printSettings,
                            iconClass: "icon-16-container icon-16-print",
                            disabled: grid.selection.selectedIndex > -1 ? false : true,
                            onClick: function(e) {

                                var _item = grid.getItem(grid.selection.selectedIndex);

                                new PrintSettingDialog({
                                    subPackage: _item,
                                    rootProject: self.rootProject,
                                    subPackageGrid: grid,
                                    disableEditing: self.disableEditing
                                }).show();
                            }
                        })
                    );

                    toolbar.addChild(new dijit.ToolbarSeparator());
                    toolbar.addChild(
                        new dijit.form.Button({
                            id: 'SubPackages-'+self.id+'-PrintBQRow-button',
                            label: nls.printBQ,
                            iconClass: "icon-16-container icon-16-print",
                            disabled: grid.selection.selectedIndex > -1 ? false : true,
                            onClick: function(e) {
                                var _item = grid.getItem(grid.selection.selectedIndex);

                                new PrintBillDialog({
                                    subPackage: _item
                                }).show();
                            }
                        })
                    );

                    if(this.rootProject.status_id[0] == buildspace.constants.STATUS_POSTCONTRACT) {
                        toolbar.addChild(new dijit.ToolbarSeparator());
                        toolbar.addChild(
                            new dijit.form.Button({
                                id: 'SubPackages-'+self.rootProject.id+'-PushToPostContractRow-button',
                                label: nls.pushToPostContract,
                                iconClass: "icon-16-container icon-16-indent",
                                disabled: true,
                                onClick: function(e) {
                                    var _item = grid.getItem(grid.selection.selectedIndex),
                                        pushToPostContractInfoXhr = dojo.xhrGet({
                                            url: "subPackage/getSelectedSubConInfo/id/"+_item.id,
                                            handleAs: "json"
                                        });
                                    var pb = buildspace.dialog.indeterminateProgressBar({
                                        title: nls.pleaseWait+'...'
                                    });
                                    pb.show();
                                    when(pushToPostContractInfoXhr, function(info) {
                                        pb.hide();
                                        new PushToPostContractDialog({
                                            companyInfo: info,
                                            rootProject : self.rootProject,
                                            subPackageGrid: grid,
                                            subPackage: _item,
                                            url: 'subpackage/applyToUnit'
                                        }).show();
                                    });
                                }
                            })
                        );
                    }

                }

                self.addChild(toolbar);
            }

            self.addChild(grid);

            var container = dijit.byId('SubPackages-'+self.rootProject.id+'-stackContainer');
            if(container) {
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 30), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                lang.mixin(child, {grid: grid});
                container.selectChild(self.pageId);
            }
        },
        openImportResourceDialog: function(SubPackageGrid) {
            if(SubPackageGrid.selection.selectedIndex > -1) {
                var _item = SubPackageGrid.getItem(SubPackageGrid.selection.selectedIndex);
                var dialog = new ImportResourceItemDialog({
                    subPackage: _item,
                    subPackageGridStore: SubPackageGrid.store
                });

                dialog.show();
            }
        },
        openImportScheduleOfRateDialog: function(SubPackageGrid) {
            if(SubPackageGrid.selection.selectedIndex > -1) {
                var _item = SubPackageGrid.getItem(SubPackageGrid.selection.selectedIndex);
                var dialog = new ImportScheduleOfRateItemDialog({
                    subPackage: _item,
                    subPackageGridStore: SubPackageGrid.store
                });

                dialog.show();
            }
        },
        openImportBillItemDialog: function(SubPackageGrid) {
            if(SubPackageGrid.selection.selectedIndex > -1) {
                var _item = SubPackageGrid.getItem(SubPackageGrid.selection.selectedIndex);
                var dialog = new ImportBillItemDialog({
                    subPackage: _item,
                    subPackageGridStore: SubPackageGrid.store,
                    rootProject: this.rootProject
                });

                dialog.show();
            }
        }
    });
});