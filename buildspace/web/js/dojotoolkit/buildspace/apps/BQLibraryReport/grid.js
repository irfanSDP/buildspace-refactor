define('buildspace/apps/BQLibraryReport/grid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/_base/array",
    "dojo/aspect",
    'dojo/request',
    "dojo/dom-attr",
    "dijit/TooltipDialog",
    "dijit/popup",
    "dojox/grid/enhanced/plugins/Menu",
    'dojo/number',
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    'dojox/grid/enhanced/plugins/IndirectSelection',
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/_base/html',
    'dojo/request/xhr',
    'dijit/PopupMenuItem',
    'buildspace/widget/grid/cells/Textarea',
    'buildspace/widget/grid/cells/FormulaTextBox',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    './PrintItemsDialog',
    'dojo/i18n!buildspace/nls/BQLibrary',
    'buildspace/widget/grid/Filter'
], function(declare, lang, connect, array, aspect, request, domAttr, TooltipDialog, popup, Menu, number, Selector, Rearrange, FormulatedColumn, IndirectSelection, evt, keys, focusUtil, html, xhr, PopupMenuItem, Textarea, FormulaTextBox, DropDownButton, DropDownMenu, MenuItem, PrintItemsDialog, nls, Filter ){

    var BQLibraryGrid = declare('buildspace.apps.BQLibraryReport.Grid', dojox.grid.EnhancedGrid, {
        type: null,
        libraryId: 0,
        elementId: 0,
        style: "border-top:none;",
        selectedItem: null,
        borderContainerWidget: null,
        keepSelection: true,
        gridContainer: null,
        constructor:function(args){
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this,{});

            this.plugins = {indirectSelection: {headerSelector:true, width:"40px", styles:"text-align: center;"}};

            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        },
        postCreate: function() {
            var self = this;
            var tooltipDialog = null;

            self.inherited(arguments);

            if ( self.type === 'tree' ) {
                aspect.after(self, "_onFetchComplete", function() {
                    self.gridContainer.markedCheckBoxObject(self, self.gridContainer.selectedItemStore[self.libraryId]);
                });

                this._connects.push(connect.connect(this, 'onCellMouseOver', function(e) {
                    var colField = e.cell.field,
                        rowIndex = e.rowIndex,
                        item = this.getItem(rowIndex);

                    var fieldConstantName = colField.replace("-value", "");

                    // will show tooltip for formula, if available
                    if (typeof item[fieldConstantName+'-has_formula'] === 'undefined' || ! item[fieldConstantName+'-has_formula'][0] ) {
                        return;
                    }

                    var formulaValue = item[fieldConstantName+'-value'][0];

                    // convert ITEM ID into ROW ID (if available)
                    formulaValue = this.formulatedColumn.convertItemIdToRowIndex(formulaValue, rowIndex);

                    if(tooltipDialog === null) {
                        tooltipDialog = new TooltipDialog({
                            content: formulaValue,
                            onMouseLeave: function() {
                                popup.close(tooltipDialog);
                            }
                        });

                        popup.open({
                            popup: tooltipDialog,
                            around: e.cellNode
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
            }
        },
        startup: function() {
            var self = this;
            self.inherited(arguments);

            this._connects.push(connect.connect(this, 'onCellClick', function(e) {
                if (e.cell.name !== "") {
                    return;
                }

                self.singleCheckBoxSelection(e);
            }));

            this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function(newValue) {
                self.toggleAllSelection(newValue);
            }));
        },
        destroy: function(){
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.BQLibraryReport.grid', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;border:0px;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: null,
        libraryId: 0,
        elementId: 0,
        rowSelector: null,
        gridOpts: {},
        type: null,
        postCreate: function(){
            var self = this,
                filterFields;
            this.inherited(arguments);
            lang.mixin(self.gridOpts, { libraryId: self.libraryId, elementId: self.elementId, type:self.type, region:"center", borderContainerWidget: self });

            var menu    = new DropDownMenu({ style: "display: none;"});
            var toolbar = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
            var grid    = this.grid = new BQLibraryGrid(self.gridOpts);

            if(self.type == 'tree'){
                filterFields = [
                    {'description': nls.description},
                    {'uom_symbol': nls.unit},
                    {'rate-final_value': nls.rate}
                ];
            }

            if(self.type != 'tree'){
                filterFields = [{'description': nls.description}];
            }

            var menus = ['selectedItems', 'selectedItemsWithRate', 'selectedItemsWithBuildUpRates'];

            dojo.forEach(menus, function(opt) {
                var printPreviewMethod;

                switch(opt) {
                    case 'selectedItems':
                        printPreviewMethod = 'openPrintSelectedItems';
                        break;

                    case 'selectedItemsWithRate':
                        printPreviewMethod = 'openPrintSelectedItemsWithRate';
                        break;

                    case 'selectedItemsWithBuildUpRates':
                        printPreviewMethod = 'openPrintSelectedItemsWithBuildUpRates';
                        break;
                }

                menu.addChild(new MenuItem({
                    label: nls[opt],
                    onClick: function() {
                        self[printPreviewMethod](opt);
                    }
                }));
            });

            toolbar.addChild(
                new DropDownButton({
                    label: nls.printPreview,
                    iconClass: "icon-16-container icon-16-print",
                    dropDown: menu
                })
            );

            self.addChild(
                new Filter({
                    region: 'top',
                    grid: grid,
                    filterFields: filterFields,
                    editableGrid: true
                })
            );

            self.addChild(toolbar);
            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('bqLibrary_report_'+self.libraryId+'-stackContainer');

            if(container){
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 60), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                lang.mixin(child, {grid: grid});
                container.selectChild(self.pageId);
            }
        },
        openPrintSelectedItems: function() {
            var self = this,
                selectedItemsStore = self.gridOpts.gridContainer.selectedItemStore[self.libraryId],
                items = [];

            selectedItemsStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('bqLibraryReporting/getPrintPreviewSelectedItems', {
                handleAs: 'json',
                data: {
                    libraryId: self.libraryId,
                    item_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items)),
                }
            }).then(function(data) {
                var dialog = new PrintItemsDialog({
                    libraryId: self.libraryId,
                    title: nls.selectedItems,
                    data: data,
                    selectedItems: items,
                    printURL: 'bqLibraryReporting/printingSelectedItems',
                    exportURL: 'bqLibraryExportExcelReporting/exportExcelSelectedItems',
                    type: 'withoutRates'
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintSelectedItemsWithRate: function() {
            var self = this,
                selectedItemsStore = self.gridOpts.gridContainer.selectedItemStore[self.libraryId],
                items = [];

            selectedItemsStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('bqLibraryReporting/getPrintPreviewSelectedItemsWithRate', {
                handleAs: 'json',
                data: {
                    libraryId: self.libraryId,
                    item_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items)),
                }
            }).then(function(data) {
                var dialog = new PrintItemsDialog({
                    libraryId: self.libraryId,
                    title: nls.selectedItemsWithRate,
                    data: data,
                    selectedItems: items,
                    printURL: 'bqLibraryReporting/printingSelectedItemsWithRate',
                    exportURL: 'bqLibraryExportExcelReporting/exportExcelSelectedItemsWithRate'
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintSelectedItemsWithBuildUpRates: function() {
            var self = this,
                selectedItemsStore = self.gridOpts.gridContainer.selectedItemStore[self.libraryId],
                items = [];

            selectedItemsStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('bqLibraryReporting/getPrintPreviewSelectedItemsWithBuildUpRates', {
                handleAs: 'json',
                data: {
                    libraryId: self.libraryId,
                    item_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items)),
                }
            }).then(function(data) {
                var dialog = new PrintItemsDialog({
                    libraryId: self.libraryId,
                    title: nls.selectedItemsWithBuildUpRates,
                    data: data,
                    selectedItems: items,
                    printURL: 'bqLibraryReporting/printingPreviewSelectedItemsWithBuildUpRates',
                    exportURL: 'bqLibraryExportExcelReporting/exportExcelSelectedItemsWithBuildUpRates'
                });

                dialog.show();
                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        }
    });
});