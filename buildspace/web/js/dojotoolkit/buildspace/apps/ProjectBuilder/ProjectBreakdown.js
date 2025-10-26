define('buildspace/apps/ProjectBuilder/ProjectBreakdown', [
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojox/grid/EnhancedGrid',
    'dijit/PopupMenuItem',
    "dijit/MenuSeparator",
    'dojo/_base/event',
    "dojo/html",
    'dojo/keys',
    'dojo/_base/connect',
    "dijit/TooltipDialog",
    "dijit/popup",
    'buildspace/widget/grid/cells/Formatter',
    './RecalculateDialog',
    './exportItemDialog',
    './exportSupplyOfMaterialItemDialog',
    './ExportScheduleOfRateBillDialog',
    './fileImportDialog',
    './SupplyOfMaterialFileImportDialog',
    './ScheduleOfRateBillFileImportDialog',
    './importBackupDialog',
    './ExportProjectBackupDialog',
    './EmptyGrandTotalQtyDialog',
    './LevelForm',
    './BillForm',
    './SupplyOfMaterialForm',
    './ScheduleOfRateBillForm',
    'buildspace/apps/TenderAlternative/TenderAlternativeFormDialog',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    'buildspace/apps/PageGenerator/GeneratorDialog',
    'dojo/i18n!buildspace/nls/ProjectBuilder'
], function (declare, lang, EnhancedGrid, PopupMenuItem, MenuSeparator, evt, html, keys, connect, TooltipDialog, popup, GridFormatter, RecalculateDialog, ExportItemDialog, ExportSupplyOfMaterialItemDialog, ExportScheduleOfRateBillDialog, FileImportDialog, SupplyOfMaterialFileImportDialog, ScheduleOfRateBillFileImportDialog, ImportBackupDialog, ExportProjectBackupDialog, EmptyGrandTotalQtyDialog, LevelForm, BillForm, SupplyOfMaterialForm, ScheduleOfRateBillForm, TenderAlternativeFormDialog, DropDownButton, DropDownMenu, MenuItem, GeneratorDialog, nls) {

    var Grid = declare('buildspace.apps.ProjectBuilder.ProjectBreakdownGrid', EnhancedGrid, {
        rootProject: null,
        workArea: null,
        style: "border:none;",
        region: 'center',
        keepSelection: true,
        rowSelector: '0px',
        currencySetting: buildspace.currencyAbbreviation,
        aspectHandles: [],
        canSort: function () {
            return false;
        },
        postCreate: function () {
            var self = this;
            this.inherited(arguments);
            var tooltipDialog = null;

            var defaultButtonsToEnable = ['AddBill', 'AddLevel'];
            var projectButtonsToEnable = ['AddBill', 'AddLevel', 'BackupDropDown', 'ImportBackup', 'ImportDropDown'];
            var billButtonsToEnable = ['AddBill', 'AddLevel', 'BackupDropDown', 'ImportBackup', 'ExportBackup', 'Delete', 'Indent', 'Outdent', 'ExportItemFromBill', 'ImportDropDown', 'ImportBill', 'ImportBuildsoft', 'ImportExcel'];
            var levelButtonsToEnable = ['AddBill', 'AddLevel', 'BackupDropDown', 'ImportBackup', 'Delete', 'Indent', 'Outdent', 'ImportDropDown'];
            var supplyOfMaterialBillButtonsToEnable = ['AddBill', 'AddLevel', 'Delete', 'Indent', 'Outdent', 'ImportDropDown', 'ImportBill', 'ImportExcel', 'ExportItemFromBill'];
            var scheduleOfRateBillButtonsToEnable = ['AddBill', 'AddLevel', 'Delete', 'Indent', 'Outdent', 'ImportDropDown', 'ImportBill', 'ImportExcel', 'ExportItemFromBill'];

            this.on("RowContextMenu", function (e) {
                self.selection.clear();
                self.selection.setSelected(e.rowIndex, true);

                var _item = this.getItem(e.rowIndex);

                if (self.rootProject.status_id == buildspace.constants.STATUS_PRETENDER) {
                    var buttonsToEnable = defaultButtonsToEnable.slice();

                    if (_item && !isNaN(parseInt(String(_item.id)))) {
                        switch (parseInt(String(_item.type))) {
                            case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_ROOT:
                                buttonsToEnable = projectButtonsToEnable.slice();
                                break;
                            case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL:
                                buttonsToEnable = billButtonsToEnable.slice();
                                break;
                            case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                                buttonsToEnable = supplyOfMaterialBillButtonsToEnable.slice();
                                break;
                            case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                                buttonsToEnable = scheduleOfRateBillButtonsToEnable.slice();
                                break;
                            case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_LEVEL:
                                buttonsToEnable = levelButtonsToEnable.slice();
                                break;
                            default:
                                break;
                        }
                    }
                    this.disableToolbarButtons(true, buttonsToEnable);
                    self.contextMenu(e);
                } else {
                    if (_item && !isNaN(parseInt(String(_item.id))) && (parseInt(String(_item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL || _item.type[0] == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL || _item.type[0] == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL)) {
                        this.disableButtonsAfterPublish();
                    } else {
                        this.disableToolbarButtons(true, []);
                    }
                }

            }, true);

            this.on('RowClick', function (e) {
                if (e.cell) {

                    var colField = e.cell.field,
                        _item = this.getItem(e.rowIndex);

                    if (self.rootProject.status_id == buildspace.constants.STATUS_PRETENDER) {
                        var buttonsToEnable = defaultButtonsToEnable.slice();
                        if (_item && !isNaN(parseInt(String(_item.id)))) {
                            switch (parseInt(String(_item.type))) {
                                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_ROOT:
                                    buttonsToEnable = projectButtonsToEnable.slice();
                                    break;
                                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL:
                                    buttonsToEnable = billButtonsToEnable.slice();
                                    break;
                                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                                    buttonsToEnable = supplyOfMaterialBillButtonsToEnable.slice();
                                    break;
                                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                                    buttonsToEnable = scheduleOfRateBillButtonsToEnable.slice();
                                    break;
                                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_LEVEL:
                                    buttonsToEnable = levelButtonsToEnable.slice();
                                    break;
                                default:
                                    break;
                            }
                        }

                        if (_item && !isNaN(parseInt(String(_item.id))) && parseInt(String(_item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL &&
                            ( buildspace.apps.ProjectBuilder.isRecalculateBillStatus(parseInt(String(_item['bill_status']))) )) {

                            if(colField == 'recalculate') {
                                new RecalculateDialog( {
                                    title: nls.recalculate + ' ' + buildspace.truncateString( _item.title, 65 ),
                                    rootProject: self.rootProject,
                                    bill: _item,
                                    projectBreakDownGrid: self
                                } ).show();
                            }

                            // Remove export buttons.
                            var buttonsToRemove = ['BackupDropDown', 'ExportItemFromBill'];
                            buttonsToRemove.forEach( function(element) {
                                var index = buttonsToEnable.indexOf( element );
                                if( index > -1 ) buttonsToEnable.splice( index, 1 );
                            } );
                        }

                        this.disableToolbarButtons(true, buttonsToEnable);
                    } else {

                        var itemTypes = [
                            buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL,
                            buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL,
                            buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL
                        ];

                        if ( _item && !isNaN(parseInt(String(_item.id))) && (itemTypes.indexOf( parseInt(String(_item.type)) ) >= 0)) {
                            this.disableButtonsAfterPublish();
                        } else {
                            this.disableToolbarButtons( true, [] );
                        }
                    }
                }
            });

            this._connects.push(connect.connect(this, 'onCellMouseOver', function(e) {
                var colField = e.cell.field,
                    rowIndex = e.rowIndex,
                    item = this.getItem(rowIndex);
                
                // will show tooltip for formula, if available
                if (!item || (!item.hasOwnProperty('tender_alternative_count')) || colField != 'tender_alternative_count' || typeof item['tender_alternative_count'] === 'undefined' || ! parseInt(String(item.tender_alternative_count))) {
                    return;
                }

                if(tooltipDialog === null) {
                    // Call the asynchronous xhrGet
                    var deferred = dojo.xhrGet({
                        url: "getTenderAlternativeInfoByBill/"+String(item.id),
                        handleAs: "json",
                        sync:true,
                        preventCache: true
                    });
                    
                    // Now add the callbacks
                    deferred.then(function(data){
                        if(data.length){
                            var content = '<table class="buildspace-table"><thead><tr>'
                            + '<th class="gridCell" style="text-align:center;padding:2px;">'+nls.title+'</th>'
                            + '<th class="gridCell" style="text-align:center;padding:2px;">'+nls.revision+'</th>'
                            + '</tr><tbody>';
                            for (var i = 0; i < data.length; i++){
                                content += '<tr><td class="gridCell" style="text-align:center;">'+data[i].title + '</td><td class="gridCell" style="text-align:center;padding-left:4px;padding-right:4px;">'+ data[i].revision +'</td></tr>';
                            }
                            content +='</tbody></table>';
                            tooltipDialog = new TooltipDialog({
                                content: content,
                                onMouseLeave: function() {
                                    popup.close(tooltipDialog);
                                }
                            });
                            popup.open({
                                popup: tooltipDialog,
                                around: e.cellNode
                            });
                        }
                    });
                }
            }));

            this._connects.push(connect.connect(this, 'onCellMouseOut', function() {
                if(tooltipDialog !== null){
                    popup.close(tooltipDialog);
                    tooltipDialog = null;
                }
            }));

            this._connects.push(connect.connect(this, 'onStartEdit', function() {
                if(tooltipDialog !== null){
                    popup.close(tooltipDialog);
                    tooltipDialog = null;
                }
            }));
        },
        dodblclick: function (e) {
            this.onRowDblClick(e);
        },
        onRowDblClick: function (e) {
            this.inherited(arguments);
            var item = this.getItem(e.rowIndex);
            switch (parseInt(String(item.type))) {
                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL:
                    if (parseInt(String(item['bill_status'])) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_STATUS_OPEN) {
                        this.workArea.initTab(item, {
                            billId: parseInt(String(item.id)),
                            billType: parseInt(String(item.bill_type)),
                            billLayoutSettingId: item.billLayoutSettingId,
                            projectBreakdownGrid: this,
                            rootProject: this.rootProject
                        });
                    }
                    break;
                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                    this.workArea.initTab(item, {
                        billId: parseInt(String(item.id)),
                        somBillLayoutSettingId: item.somBillLayoutSettingId,
                        projectBreakdownGrid: this,
                        rootProject: this.rootProject
                    });
                    break;
                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                    this.workArea.initTab(item, {
                        billId: parseInt(String(item.id)),
                        sorBillLayoutSettingId: item.sorBillLayoutSettingId,
                        projectBreakdownGrid: this,
                        rootProject: this.rootProject
                    });
                    break;
                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_LEVEL:
                    if (parseInt(String(this.rootProject.status_id)) == buildspace.constants.STATUS_PRETENDER) {
                        this.openLevelForm(e.rowIndex, 'edit');
                    }
                    break;
                default:
                    break;
            }
        },
        contextMenu: function (e) {
            var rowCtxMenu = this.rowCtxMenu = new dijit.Menu();
            this.contextMenuItems(e);
            var info = {
                target: e.target,
                coords: e.keyCode !== keys.F10 && "pageX" in e ? {x: e.pageX, y: e.pageY} : null
            };
            var item = this.getItem(e.rowIndex);

            if (rowCtxMenu && item && (this.selection.isSelected(e.rowIndex) || e.rowNode && html.hasClass(e.rowNode, 'dojoxGridRowbar'))) {
                rowCtxMenu._openMyself(info);
                evt.stop(e);
                return;
            }
        },
        contextMenuItems: function (e) {
            var self = this, item = this.getItem(e.rowIndex);

            var menuItem = new dijit.PopupMenuItem({
                label: nls.addBill,
                iconClass: "icon-16-container icon-16-add",
                popup: new dijit.Menu()
            });

            menuItem.popup.addChild(new dijit.MenuItem({
                label: nls.normalBill,
                onClick: dojo.hitch(this, 'openBillForm', e.rowIndex)
            }));
            menuItem.popup.addChild(new dijit.MenuItem({
                label: nls.supplyOfMaterialBill,
                onClick: dojo.hitch(this, 'openSupplyOfMaterialForm', e.rowIndex)
            }));
            menuItem.popup.addChild(new dijit.MenuItem({
                label: nls.scheduleOfRateBill,
                onClick: dojo.hitch(this, 'openScheduleOfRateBillForm', e.rowIndex)
            }));

            this.rowCtxMenu.addChild(menuItem);

            this.rowCtxMenu.addChild(new dijit.MenuItem({
                label: nls.addLevel,
                iconClass: "icon-16-container icon-16-add",
                onClick: dojo.hitch(this, 'openLevelForm', e.rowIndex, 'add')
            }));

            if (item && !isNaN(parseInt(String(item.id))) && (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_LEVEL)) {
                if (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL) {
                    this.rowCtxMenu.addChild(new dijit.MenuItem({
                        label: nls.editBill,
                        iconClass: "icon-16-container icon-16-edit",
                        disabled: parseInt(String(item['bill_status'])) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_STATUS_OPEN ? false : true,
                        onClick: function (e) {
                            if (parseInt(String(item['bill_status'])) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_STATUS_OPEN) {
                                self.workArea.initTab(item, {
                                    billId: parseInt(String(item.id)),
                                    billType: parseInt(String(item.bill_type)),
                                    billLayoutSettingId: item.billLayoutSettingId,
                                    projectBreakdownGrid: self,
                                    rootProject: self.rootProject
                                });
                            }
                        }
                    }));
                } else if (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_LEVEL) {
                    this.rowCtxMenu.addChild(new dijit.MenuItem({
                        label: nls.editLevel,
                        iconClass: "icon-16-container icon-16-edit",
                        onClick: dojo.hitch(this, 'openLevelForm', e.rowIndex, 'edit')
                    }));
                } else if (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL) {
                    this.rowCtxMenu.addChild(new dijit.MenuItem({
                        label: nls.editBill,
                        iconClass: "icon-16-container icon-16-edit",
                        onClick: function (e) {
                            self.workArea.initTab(item, {
                                billId: parseInt(String(item.id)),
                                somBillLayoutSettingId: item.somBillLayoutSettingId,
                                projectBreakdownGrid: self,
                                rootProject: self.rootProject
                            });
                        }
                    }));
                } else if (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL) {
                    this.rowCtxMenu.addChild(new dijit.MenuItem({
                        label: nls.editBill,
                        iconClass: "icon-16-container icon-16-edit",
                        onClick: function (e) {
                            self.workArea.initTab(item, {
                                billId: parseInt(String(item.id)),
                                projectBreakdownGrid: self,
                                rootProject: self.rootProject
                            });
                        }
                    }));
                }

                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.indent,
                    iconClass: "icon-16-container icon-16-indent",
                    onClick: dojo.hitch(self, 'indentOutdent', e.rowIndex, 'Indent')
                }));
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.outdent,
                    iconClass: "icon-16-container icon-16-outdent",
                    onClick: dojo.hitch(this, 'indentOutdent', e.rowIndex, 'Outdent')
                }));
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.cut,
                    iconClass: "icon-16-container icon-16-cut",
                    onClick: dojo.hitch(this, 'cutItem')
                }));
            }

            this.rowCtxMenu.addChild(new dijit.MenuItem({
                label: nls.paste,
                iconClass: "icon-16-container icon-16-paste",
                onClick: dojo.hitch(this, 'pasteItem', e.rowIndex),
                disabled: this.selectedItem ? false : true
            }));

            if (parseInt(String(item.id)) > 0 && (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_LEVEL)) {
                this.rowCtxMenu.addChild(new dijit.MenuItem({
                    label: nls.delete,
                    iconClass: "icon-16-container icon-16-delete",
                    onClick: dojo.hitch(this, 'deleteRow', item)
                }));
            }

        },
        openLevelForm: function (rowIndex, type) {
            var item = this.getItem(rowIndex);

            if (String(item.id) == buildspace.constants.GRID_LAST_ROW) {
                item = this.getItem(rowIndex - 1);
            }

            var opts = {
                title: type == 'edit' ? nls.editLevel : nls.addLevel,
                projectBreakdownGrid: this
            };

            if (type == 'edit') {
                lang.mixin(opts, {levelId: item.id});
            } else {
                lang.mixin(opts, {parentId: item.id});
            }

            var f = LevelForm(opts);
            f.startup();
            f.show();
        },
        openBillForm: function (rowIndex) {
            var item = this.getItem(rowIndex);

            if (String(item.id) == buildspace.constants.GRID_LAST_ROW) {
                item = this.getItem(rowIndex - 1);
            }

            var f = BillForm({
                title: nls.addBill,
                projectBreakdownGrid: this,
                parentId: parseInt(String(item.id))
            });
            f.startup();
            f.show();
        },
        openSupplyOfMaterialForm: function (rowIndex) {
            var item = this.getItem(rowIndex);

            if (String(item.id) == buildspace.constants.GRID_LAST_ROW) {
                item = this.getItem(rowIndex - 1);
            }

            var f = SupplyOfMaterialForm({
                title: nls.addSupplyOfMaterialBill,
                projectBreakdownGrid: this,
                parentId: parseInt(String(item.id))
            });
            f.startup();
            f.show();
        },
        openScheduleOfRateBillForm: function (rowIndex) {
            var item = this.getItem(rowIndex);

            if (String(item.id) == buildspace.constants.GRID_LAST_ROW) {
                item = this.getItem(rowIndex - 1);
            }

            var f = ScheduleOfRateBillForm({
                title: nls.addScheduleOfRateBill,
                projectBreakdownGrid: this,
                parentId: parseInt(String(item.id))
            });
            f.startup();
            f.show();
        },
        cutItem: function () {
            this.selectedItem = this.selection.getFirstSelected();
        },
        pasteItem: function (rowIndex) {
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.pleaseWait + '...'
                }),
                targetItem = this.selection.getFirstSelected();
            var prevItemId = (String(targetItem.id) == buildspace.constants.GRID_LAST_ROW && rowIndex > 0) ? this.getItem(rowIndex - 1).id : 0;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: "projectBuilder/itemPaste",
                    content: {
                        tid: targetItem.id,
                        pid: prevItemId,
                        id: self.selectedItem.id,
                        _csrf_token: self.selectedItem._csrf_token
                    },
                    handleAs: 'json',
                    load: function (resp) {
                        if (resp.success) {
                            self.reload();
                        }
                        self.selection.clear();
                        self.disableToolbarButtons(true);
                        self.selectedItem = null;
                        pb.hide();
                    },
                    error: function (error) {
                        self.selection.clear();
                        self.disableToolbarButtons(true);
                        self.selectedItem = null;
                        pb.hide();
                    }
                });
            });
        },
        deleteRow: function (item) {
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.deleting + '. ' + nls.pleaseWait + '...'
                });

            var onYes = function () {
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'projectBuilder/projectStructureDelete',
                        content: {id: item.id, _csrf_token: item._csrf_token},
                        handleAs: 'json',
                        load: function (resp) {
                            if (resp.success) {
                                var items = resp.items;
                                for (var i = 0, len = items.length; i < len; ++i) {
                                    self.store.fetchItemByIdentity({
                                        'identity': items[i].id, onItem: function (itm) {
                                            var tac = self.workArea.getChildren();
                                            for (var i in tac) {
                                                if (typeof tac[i].pane_info != "object") continue;
                                                if (tac[i].pane_info.id == parseInt(String(itm.id)) + '-form_' + parseInt(String(itm.type))) {
                                                    self.workArea.removeChild(tac[i]);
                                                    break;
                                                }
                                            }
                                        }
                                    });
                                }
                                items.length = 0;
                                self.reload();
                            }
                            pb.hide();
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                        },
                        error: function (error) {
                            pb.hide();
                            self.selection.clear();
                            self.disableToolbarButtons(true);
                        }
                    });
                });
            };

            var content;

            if (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL) {
                content = '<div>' + nls.deleteBillAndAllData + '</div>';
            } else if (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_LEVEL) {
                content = '<div>' + nls.deleteLevelAndAllData + '</div>';
            }

            buildspace.dialog.confirm(nls.confirmation, content, 80, 300, onYes);
        },
        indentOutdent: function (rowIndex, type) {
            if (rowIndex > 0) {
                var item = this.getItem(rowIndex);
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title: nls.recalculateRows + '. ' + nls.pleaseWait + '...'
                });

                if (item && !isNaN(parseInt(String(item.id)))) {
                    var self = this,
                        store = self.store;

                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: 'projectBuilder/item' + type,
                            content: {id: item.id, _csrf_token: item._csrf_token},
                            handleAs: 'json',
                            load: function (data) {
                                if (data.success) {
                                    var nextItems = data.c;
                                    for (var property in data.item) {
                                        if (data.item.hasOwnProperty(property) && property != store._getIdentifierAttribute()) {
                                            store.setValue(item, property, data.item[property]);
                                        }
                                    }
                                    for (var x = 0, len = nextItems.length; x < len; ++x) {
                                        store.fetchItemByIdentity({
                                            'identity': nextItems[x].id, onItem: function (nextItem) {
                                                for (var property in nextItems[x]) {
                                                    if (nextItem.hasOwnProperty(property) && property != store._getIdentifierAttribute()) {
                                                        store.setValue(nextItem, property, nextItems[x][property]);
                                                    }
                                                }
                                            }
                                        });
                                    }
                                    store.save();
                                }
                                pb.hide();
                            },
                            error: function (error) {
                                self.selection.clear();
                                self.disableToolbarButtons(true);
                                pb.hide();
                            }
                        });
                    });
                }
            }
        },
        disableToolbarButtons: function (isDisable, buttonsToEnable) {
            var exportItemsBtn = dijit.byId(this.rootProject.id + 'ExportItemFromBillRow-button'),
                importDropDownBtn = dijit.byId(this.rootProject.id + 'ImportDropDownRow-button'),
                importBillBtn = dijit.byId(this.rootProject.id + 'ImportBillRow-button'),
                importBuildsoftBtn = dijit.byId(this.rootProject.id + 'ImportBuildsoftRow-button'),
                importExcelBtn = dijit.byId(this.rootProject.id + 'ImportExcelRow-button'),
                backupDropDownBtn = dijit.byId(this.rootProject.id + 'BackupDropDownRow-button'),
                addBillBtn = dijit.byId(this.rootProject.id + 'AddBillRow-button'),
                addLevelBtn = dijit.byId(this.rootProject.id + 'AddLevelRow-button'),
                importBackupBtn = dijit.byId(this.rootProject.id + 'ImportBackupRow-button'),
                exportBackupBtn = dijit.byId(this.rootProject.id + 'ExportBackupRow-button'),
                deleteBtn = dijit.byId(this.rootProject.id + 'DeleteRow-button'),
                indentBtn = dijit.byId(this.rootProject.id + 'IndentRow-button'),
                outdentBtn = dijit.byId(this.rootProject.id + 'OutdentRow-button');

            if (exportItemsBtn)
                exportItemsBtn._setDisabledAttr(isDisable);

            if (importDropDownBtn)
                importDropDownBtn._setDisabledAttr(isDisable);

            if (importBillBtn)
                importBillBtn._setDisabledAttr(isDisable);

            if (importBuildsoftBtn)
                importBuildsoftBtn._setDisabledAttr(isDisable);

            if (importExcelBtn)
                importExcelBtn._setDisabledAttr(isDisable);

            if (backupDropDownBtn)
                backupDropDownBtn._setDisabledAttr(isDisable);

            if (addBillBtn)
                addBillBtn._setDisabledAttr(isDisable);

            if (addLevelBtn)
                addLevelBtn._setDisabledAttr(isDisable);

            if (exportBackupBtn)
                exportBackupBtn._setDisabledAttr(isDisable);

            if (importBackupBtn)
                importBackupBtn._setDisabledAttr(isDisable);

            if (deleteBtn)
                deleteBtn._setDisabledAttr(isDisable);

            if (indentBtn)
                indentBtn._setDisabledAttr(isDisable);

            if (outdentBtn)
                outdentBtn._setDisabledAttr(isDisable);

            if (isDisable && buttonsToEnable instanceof Array) {
                var _this = this;

                dojo.forEach(buttonsToEnable, function (label) {
                    var btn = dijit.byId(_this.rootProject.id + label + 'Row-button');
                    if (btn)
                        btn._setDisabledAttr(false);
                });
            }

            if (parseInt(String(this.rootProject.status_id)) == buildspace.constants.STATUS_TENDERING) {
                this.disableButtonsAfterPublish();
            }

            if (parseInt(String(this.rootProject.status_id)) == buildspace.constants.STATUS_PRETENDER) {
                this.checkPublishButton();
            }
        },
        checkPublishButton: function () {
            var publishToTenderBtn = dijit.byId(this.rootProject.id + 'PublishToTenderRow-button');

            this.store.fetch({
                query: {type: buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL},
                onComplete: function (items, request) {

                    if (items.length <= 0) {
                        if (publishToTenderBtn)
                            publishToTenderBtn._setDisabledAttr(true);
                    } else {
                        if (publishToTenderBtn)
                            publishToTenderBtn._setDisabledAttr(false);
                    }

                }
            });
        },
        disableButtonsAfterPublish: function () {
            var publishToTenderBtn = dijit.byId(this.rootProject.id + 'PublishToTenderRow-button'),
                importDropDownBtn = dijit.byId(this.rootProject.id + 'ImportDropDownRow-button'),
                addBillBtn = dijit.byId(this.rootProject.id + 'AddBillRow-button'),
                addLevelBtn = dijit.byId(this.rootProject.id + 'AddLevelRow-button'),
                deleteBtn = dijit.byId(this.rootProject.id + 'DeleteRow-button'),
                indentBtn = dijit.byId(this.rootProject.id + 'IndentRow-button'),
                outdentBtn = dijit.byId(this.rootProject.id + 'OutdentRow-button'),
                importBackupBtn = dijit.byId(this.rootProject.id + 'ImportBackupRow-button'),
                addTenderAlternativeBtn = dijit.byId(this.rootProject.id + 'AddTenderAlternative-button'),
                scheduleOfQuantityButton = dijit.byId('soq-' + this.rootProject.id + '-mainButton');

            if (publishToTenderBtn)
                publishToTenderBtn._setDisabledAttr(true);

            if (importDropDownBtn)
                importDropDownBtn._setDisabledAttr(true);

            if (importBackupBtn)
                importBackupBtn._setDisabledAttr(true);

            if (addBillBtn)
                addBillBtn._setDisabledAttr(true);

            if (addLevelBtn)
                addLevelBtn._setDisabledAttr(true);

            if (deleteBtn)
                deleteBtn._setDisabledAttr(true);

            if (indentBtn)
                indentBtn._setDisabledAttr(true);

            if (outdentBtn)
                outdentBtn._setDisabledAttr(true);
            
            if (addTenderAlternativeBtn)
                addTenderAlternativeBtn._setDisabledAttr(true);
            
            if (scheduleOfQuantityButton)
                scheduleOfQuantityButton._setDisabledAttr(true);

            var tab = dijit.byId(String(this.rootProject.id)+'-form_1337');
            if(tab){
                this.workArea.removeChild(tab);
                tab.destroy();

                var item = {
                    id: String(this.rootProject.id),
                    type: 1337
                };
    
                this.workArea.initTab(item, {
                    projectBreakdownGrid: this,
                    project: this.rootProject,
                    editable: false
                }, false);

                var projectBreakdown = dijit.byId('main-project_breakdown');
                if(projectBreakdown){
                    this.workArea.selectChild(projectBreakdown);
                }
            }
        },
        reload: function () {
            var self = this,
                validateEmptyGrandTotalQtyXhr = dojo.xhrGet({
                    url: "projectBuilder/validateEmptyGrandTotalQty/",
                    content: { pid: this.rootProject.id },
                    handleAs: "json"
                });
            validateEmptyGrandTotalQtyXhr.then(function(r){
                if(r.has_error){
                    new EmptyGrandTotalQtyDialog({
                        id: 'EmptyGrandTotalQtyDialog-' + self.rootProject.id,
                        project: self.rootProject,
                        data: r
                    }).show();
                }
            });
            this.store.close();
            
            this._refresh();
            
            this.checkPublishButton();
        },
        destroy: function(){
            this.inherited(arguments);

            this.aspectHandles.forEach(function(handle){
                handle.remove();
            });
            this.aspectHandles = [];

            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    var Formatter = {
        rowCountCellFormatter: function (cellValue, rowIdx) {
            return cellValue > 0 ? cellValue : '';
        },
        treeCellFormatter: function (cellValue, rowIdx) {
            var item = this.grid.getItem(rowIdx);
            var level = item.level * 16;
            cellValue = cellValue == null ? '&nbsp' : cellValue;
            if (item && parseInt(String(item.type)) < buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL) {
                cellValue = '<b>' + cellValue + '</b>';
            }
            cellValue = '<div class="treeNode" style="padding-left:' + level + 'px;"><div class="treeContent">' + cellValue + '&nbsp;</div></div>';
            return cellValue;
        }
    };

    return declare('buildspace.apps.ProjectBuilder.ProjectBreakdown', dijit.layout.BorderContainer, {
        region: "center",
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        rootProject: null,
        workArea: null,
        grid: null,
        postCreate: function () {
            this.inherited(arguments);
            var self = this;
            var formatter = new GridFormatter();
            var currencySetting = (buildspace.billCurrencyAbbreviation) ? buildspace.billCurrencyAbbreviation : buildspace.currencyAbbreviation;
            
            var structure = [{
                name: 'No.',
                field: 'count',
                width: '30px',
                styles: 'text-align:center;',
                formatter: Formatter.rowCountCellFormatter
            },{
                    name: nls.description, field: 'title', width: 'auto', formatter: Formatter.treeCellFormatter
            }];

            if(parseInt(String(this.rootProject.has_tender_alternative))){
                structure.push({name: nls.tenderAlternative, field: 'tender_alternative_count', styles:'text-align: center;', width:'68px', formatter: formatter.tenderAlternativeInfoCellFormatter, noresize: true});
            }

            var otherColumns = [{
                name: nls.originalAmount,
                field: 'original_total',
                width: '150px',
                styles: 'text-align: right;',
                formatter: formatter.unEditableCurrencyCellFormatter
            },{
                name: nls.total + ' ' + nls.markup + ' (%)',
                field: 'original_total',
                width: '100px',
                styles: 'text-align: right;',
                formatter: formatter.elementTotalMarkupPercentageCellFormatter
            },{
                name: nls.total + ' ' + nls.markup + ' (' + currencySetting + ')',
                field: 'original_total',
                width: '120px',
                styles: 'text-align: right;',
                formatter: formatter.elementTotalMarkupAmountCellFormatter
            },{
                name: nls.overallTotal,
                field: 'overall_total_after_markup',
                width: '150px',
                styles: 'text-align: right;',
                formatter: formatter.unEditableCurrencyCellFormatter
            },{
                name: '% ' + nls.project,
                field: 'overall_total_after_markup',
                width: '80px',
                styles: 'text-align: center;',
                formatter: formatter.projectBreakdownJobPercentageCellFormatter
            },{
                name: nls.recalculate,
                field: 'recalculate',
                width: '80px',
                styles: 'text-align: center;',
                formatter: formatter.recalculateBillCellFormatter
            }];

            dojo.forEach(otherColumns,function(column){
                structure.push(column);
            });

            var grid = this.grid = Grid({
                    id: "projectBuilder-projectBreakdownGrid",
                    rootProject: this.rootProject,
                    workArea: this.workArea,
                    currencySetting: currencySetting,
                    structure: structure,
                    store: new dojo.data.ItemFileWriteStore({
                        clearOnClose: true,
                        url: "projectBuilder/getProjectBreakdown/id/" + this.rootProject.id
                    })
                }),
                toolbar = new dijit.Toolbar({
                    region: "top",
                    style: "outline:none!important;border:none;padding:2px;width:100%;"
                });

            var addBillDropDownMenu = new DropDownMenu({style: "display: none;"});

            addBillDropDownMenu.addChild(new MenuItem({
                label: nls.normalBill,
                onClick: function (e) {
                    if (grid.selection.selectedIndex > -1) {
                        grid.openBillForm(grid.selection.selectedIndex);
                    }
                }
            }));

            addBillDropDownMenu.addChild(new MenuItem({
                label: nls.supplyOfMaterialBill,
                onClick: function (e) {
                    if (grid.selection.selectedIndex > -1) {
                        grid.openSupplyOfMaterialForm(grid.selection.selectedIndex);
                    }
                }
            }));

            addBillDropDownMenu.addChild(new MenuItem({
                label: nls.scheduleOfRateBill,
                onClick: function (e) {
                    if (grid.selection.selectedIndex > -1) {
                        grid.openScheduleOfRateBillForm(grid.selection.selectedIndex);
                    }
                }
            }));

            toolbar.addChild(new DropDownButton({
                id: self.rootProject.id + 'AddBillRow-button',
                label: nls.addBill,
                iconClass: "icon-16-container icon-16-add",
                dropDown: addBillDropDownMenu,
                disabled: true,
                style: "outline:none!important;"
            }));

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id + 'AddLevelRow-button',
                    label: nls.addLevel,
                    iconClass: "icon-16-container icon-16-add",
                    disabled: true,
                    style: "outline:none!important;",
                    onClick: function () {
                        if (grid.selection.selectedIndex > -1) {
                            grid.openLevelForm(grid.selection.selectedIndex, "add");
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id + 'AddTenderAlternative-button',
                    label: nls.addTenderAlternative,
                    iconClass: "icon-16-container icon-16-add",
                    style: "outline:none!important;",
                    onClick: function () {
                        var d = new TenderAlternativeFormDialog({
                            tenderAlternativeId: -1,
                            project: self.rootProject,
                            projectBreakdownGrid: grid
                        });

                        d.show();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id + 'IndentRow-button',
                    label: nls.indent,
                    iconClass: "icon-16-container icon-16-indent",
                    disabled: true,
                    style: "outline:none!important;",
                    onClick: function () {
                        if (grid.selection.selectedIndex > -1) {
                            var item = grid.getItem(grid.selection.selectedIndex);
                            if (item && parseInt(String(item.id)) > 0 && (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_LEVEL)) {
                                grid.indentOutdent(grid.selection.selectedIndex, 'Indent');
                            }
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id + 'OutdentRow-button',
                    label: nls.outdent,
                    iconClass: "icon-16-container icon-16-outdent",
                    disabled: true,
                    style: "outline:none!important;",
                    onClick: function () {
                        if (grid.selection.selectedIndex > -1) {
                            var item = grid.getItem(grid.selection.selectedIndex);
                            if (item && parseInt(String(item.id)) > 0 && (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_LEVEL)) {
                                grid.indentOutdent(grid.selection.selectedIndex, 'Outdent');
                            }
                        }
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id + 'DeleteRow-button',
                    label: nls.delete,
                    iconClass: "icon-16-container icon-16-delete",
                    disabled: true,
                    style: "outline:none!important;",
                    onClick: function () {
                        if (grid.selection.selectedIndex > -1) {
                            var item = grid.getItem(grid.selection.selectedIndex);
                            if (item && parseInt(String(item.id)) > 0 && (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL || parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_LEVEL)) {
                                grid.deleteRow(item);
                            }
                        }
                    }
                })
            );

            var importDropDownMenu = new DropDownMenu({style: "display: none;"});

            importDropDownMenu.addChild(new MenuItem({
                id: self.rootProject.id + 'ImportBillRow-button',
                label: nls.importBill,
                disabled: true,
                onClick: function (e) {
                    if (grid.selection.selectedIndex > -1) {
                        var item = grid.getItem(grid.selection.selectedIndex);

                        // will determine which url to post to once detected which
                        // type of bill selected
                        if (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL) {
                            new SupplyOfMaterialFileImportDialog({
                                bill: item,
                                importType: buildspace.constants.FILE_IMPORT_TYPE_BUILDSPACE,
                                uploadUrl: "supplyOfMaterialImportFile/importBuildSpaceExcel",
                                title: nls.importSupplyOfMaterialBill
                            }).show();
                        } else if (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL) {
                            new ScheduleOfRateBillFileImportDialog({
                                bill: item,
                                importType: buildspace.constants.FILE_IMPORT_TYPE_BUILDSPACE,
                                uploadUrl: "scheduleOfRateBill/importBuildSpaceExcel",
                                title: nls.importScheduleOfRateBill
                            }).show();
                        } else {
                            new FileImportDialog({
                                bill: item,
                                importType: buildspace.constants.FILE_IMPORT_TYPE_BUILDSPACE,
                                uploadUrl: "billManagerImportFile/importBuildspaceExcel",
                                title: nls.importBill
                            }).show();
                        }
                    }
                }
            }));

            importDropDownMenu.addChild(new MenuItem({
                id: self.rootProject.id + 'ImportBuildsoftRow-button',
                label: nls.importFromBuildsoft,
                disabled: true,
                onClick: function (e) {
                    if (grid.selection.selectedIndex > -1) {
                        var item = grid.getItem(grid.selection.selectedIndex);
                        new FileImportDialog({
                            bill: item,
                            importType: buildspace.constants.FILE_IMPORT_TYPE_BUILDSOFT,
                            uploadUrl: "billManagerImportFile/importBuildsoftExcel",
                            title: nls.importFromBuildsoft
                        }).show();
                    }
                }
            }));
            
            importDropDownMenu.addChild(new MenuItem({
                label: nls.importFromExcel,
                id: self.rootProject.id + 'ImportExcelRow-button',
                disabled: true,
                onClick: function (e) {
                    if (grid.selection.selectedIndex > -1) {
                        var item = grid.getItem(grid.selection.selectedIndex);

                        // will determine which url to post to once detected which
                        // type of bill selected
                        if (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL) {
                            new SupplyOfMaterialFileImportDialog({
                                bill: item,
                                importType: buildspace.constants.FILE_IMPORT_TYPE_EXCEL,
                                uploadUrl: "supplyOfMaterialImportFile/previewImportedFile/bid/" + parseInt(String(item.id)),
                                title: nls.importFromExcel
                            }).show();
                        } else if (parseInt(String(item.type)) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL) {
                            new ScheduleOfRateBillFileImportDialog({
                                bill: item,
                                importType: buildspace.constants.FILE_IMPORT_TYPE_EXCEL,
                                uploadUrl: "scheduleOfRateBill/previewImportedFile/bid/" + parseInt(String(item.id)),
                                title: nls.importFromExcel
                            }).show();
                        } else {
                            new FileImportDialog({
                                bill: item,
                                importType: buildspace.constants.FILE_IMPORT_TYPE_EXCEL,
                                uploadUrl: "billManagerImportFile/previewImportedFile/bid/" + parseInt(String(item.id)),
                                title: nls.importFromExcel
                            }).show();
                        }
                    }
                }
            }));

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(new DropDownButton({
                id: self.rootProject.id + 'ImportDropDownRow-button',
                label: nls.importFromFiles,
                iconClass: "icon-16-container icon-16-import",
                dropDown: importDropDownMenu,
                disabled: grid.selection.selectedIndex > -1 ? false : true
            }));

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id + 'ExportItemFromBillRow-button',
                    label: nls.exportBill,
                    iconClass: "icon-16-container icon-16-export",
                    disabled: grid.selection.selectedIndex > -1 ? false : true,
                    onClick: function (e) {
                        if (grid.selection.selectedIndex > -1) {
                            var item = grid.getItem(grid.selection.selectedIndex);
                            var d = new GeneratorDialog({
                                project: self.rootProject,
                                bill: item,
                                onSuccess: dojo.hitch(self, "_openExportExcelDialog", item),
                                onClickErrorNode: function(bill, evt){
                                    switch (parseInt(String(bill.type))) {
                                        case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL:
                                            if (parseInt(String(bill['bill_status'])) == buildspace.apps.ProjectBuilder.ProjectStructureConstants.BILL_STATUS_OPEN) {
                                                self.workArea.initTab(bill, {
                                                    billId: parseInt(String(bill.id)),
                                                    billType: parseInt(String(bill.bill_type)),
                                                    billLayoutSettingId: bill.billLayoutSettingId,
                                                    projectBreakdownGrid: grid,
                                                    rootProject: self.rootProject
                                                });
                                            }
                                            break;
                                        case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                                            self.workArea.initTab(bill, {
                                                billId: parseInt(String(bill.id)),
                                                somBillLayoutSettingId: bill.somBillLayoutSettingId,
                                                projectBreakdownGrid: grid,
                                                rootProject: self.rootProject
                                            });
                                            break;
                                        case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                                            self.workArea.initTab(bill, {
                                                billId: parseInt(String(bill.id)),
                                                sorBillLayoutSettingId: bill.sorBillLayoutSettingId,
                                                projectBreakdownGrid: grid,
                                                rootProject: self.rootProject
                                            });
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            });

                            d.show();
                        }
                    }
                })
            );

            var backupDropdownMenu = new DropDownMenu({style: "display: none;"});

            backupDropdownMenu.addChild(new MenuItem({
                id: self.rootProject.id + 'ExportBackupRow-button',
                label: nls.exportBackup,
                iconClass: "icon-16-container icon-16-export",
                onClick: function (e) {
                    if (grid.selection.selectedIndex > -1) {
                        var _item = grid.getItem(grid.selection.selectedIndex);
                        new ExportProjectBackupDialog({
                            bill: _item,
                            project: self.rootProject,
                            exportUrl: 'projectBackup/index'
                        }).show();
                    }
                }
            }));

            backupDropdownMenu.addChild(new MenuItem({
                id: self.rootProject.id + 'ImportBackupRow-button',
                label: nls.importBackup,
                iconClass: "icon-16-container icon-16-import",
                onClick: function (e) {
                    if (grid.selection.selectedIndex > -1) {
                        var _item = self.grid.getItem(grid.selection.selectedIndex);

                        new ImportBackupDialog({
                            item: _item,
                            rootProject: self.rootProject,
                            projectBreakdownGrid: self.grid,
                            title: nls.importBackup
                        }).show();
                    }
                }
            }));

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(new DropDownButton({
                id: self.rootProject.id + 'BackupDropDownRow-button',
                label: nls.backup,
                iconClass: "icon-16-container icon-16-cabinet",
                dropDown: backupDropdownMenu,
                disabled: grid.selection.selectedIndex > -1 ? false : true
            }));

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(new dijit.form.Button({
                id: self.rootProject.id + 'LocationCodeContainerRow-button',
                label: nls.defineLocations,
                iconClass: "icon-16-container icon-16-location",
                onClick: dojo.hitch(this.workArea, "initLocationCodeContainerTab")
            }));

            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    id: self.rootProject.id + 'ReloadGridRow-button',
                    label: nls.reload,
                    iconClass: "icon-16-container icon-16-reload",
                    onClick: dojo.hitch(grid, "reload")
                })
            );

            grid.checkPublishButton();

            this.addChild(toolbar);
            this.addChild(grid);
        },
        _openExportExcelDialog: function(bill){
            // will be determine by bill type
            switch(parseInt(String(bill.type))){
                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                    new ExportSupplyOfMaterialItemDialog({
                        bill: bill
                    }).show();
                    break;
                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                    new ExportScheduleOfRateBillDialog({
                        bill: bill
                    }).show();
                    break;
                default:
                    new ExportItemDialog({
                        bill: bill
                    }).show();
            }
        }
    });
});