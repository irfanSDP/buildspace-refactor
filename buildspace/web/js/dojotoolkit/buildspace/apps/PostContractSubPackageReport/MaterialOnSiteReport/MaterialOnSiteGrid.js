define('buildspace/apps/PostContractSubPackageReport/MaterialOnSiteReport/MaterialOnSiteGrid', [
    'dojo/_base/declare',
    "dojo/_base/connect",
    'dojo/_base/lang',
    'dojo/_base/html',
    "dojo/dom-style",
    "dojo/number",
    "dojo/request",
    "dojo/aspect",
    "dijit/focus",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/TooltipDialog",
    "dijit/popup",
    'dojox/grid/EnhancedGrid',
    "dijit/form/DropDownButton",
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/plugins/FormulatedColumn",
    "buildspace/widget/grid/cells/Formatter",
    "dojox/grid/enhanced/plugins/IndirectSelection",
    './PrintSelectedMOSItemsDialog',
    './PrintSelectedMOSItemsByAmountOnlyDialog',
    'dojo/i18n!buildspace/nls/PostContract'
], function (declare, connect, lang, html, domStyle, number, request, aspect, focusUtil, evt, keys, TooltipDialog, popup, EnhancedGrid, DropDownButton, DropDownMenu, MenuItem, Rearrange, FormulatedColumn, GridFormatter, IndirectSelection, PrintSelectedMOSItemsDialog, PrintSelectedMOSItemsByAmountOnlyDialog, nls) {

    var MaterialOnSiteGrid = declare('buildspace.apps.PostContractSubPackageReport.MaterialOnSiteReport.MaterialOnSiteReportEnhancedGrid', EnhancedGrid, {
        type: null,
        style: "border-top:none;",
        rowSelector: '0px',
        selectedItem: null,
        region: 'center',
        subPackage: null,
        locked: false,
        keepSelection: true,
        gridContainer: null,
        constructor: function () {
            this.connects = [];
            this.rearranger = Rearrange(this, {});
            this.formulatedColumn = FormulatedColumn(this, {});

            this.plugins = {indirectSelection: {headerSelector: true, width: "40px", styles: "text-align: center;"}};
        },
        postCreate: function () {
            var self = this, store;
            self.inherited(arguments);

            if (self.type === 'vo') {
                store = self.gridContainer.mosSelectedStore;
            } else {
                store = self.gridContainer.mosItemSelectedStore;
            }

            aspect.after(self, "_onFetchComplete", function () {
                self.gridContainer.markedCheckBoxObject(self, store);
            });
        },
        startup: function () {
            var self = this;
            self.inherited(arguments);

            this._connects.push(connect.connect(this, 'onCellClick', function (e) {
                if (e.cell.name !== "") {
                    return;
                }

                self.singleCheckBoxSelection(e);
            }));

            this._connects.push(connect.connect(this.rowSelectCell, 'toggleAllSelection', function (newValue) {
                self.toggleAllSelection(newValue);
            }));
        },
        canSort: function () {
            return false;
        },
        destroy: function () {
            this.inherited(arguments);
            dojo.forEach(this._connects, connect.disconnect);
            delete this._connects;
        }
    });

    return declare('buildspace.apps.PostContractSubPackageReport.MaterialOnSiteReport.MaterialOnSiteReportGrid', dijit.layout.BorderContainer, {
        stackContainerTitle: '',
        style: "padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        subPackage: null,
        materialOnSite: null,
        gridOpts: {},
        type: null,
        pageId: 0,
        postCreate: function () {
            var self = this;

            var id, stackContainerId;
            this.inherited(arguments);

            lang.mixin(this.gridOpts, {
                type: this.type,
                subPackage: this.subPackage,
                materialOnSite: this.materialOnSite,
                locked: true
            });

            var grid = this.grid = new MaterialOnSiteGrid(this.gridOpts);

            switch (this.type) {
                case 'vo':
                    id = 'materialOnSiteReport-' + this.subPackage.id;
                    stackContainerId = 'materialOnSiteReport-' + this.subPackage.id;
                    break;
                case 'vo-items':
                    id = 'materialOnSiteReport-' + this.subPackage.id + '_' + this.materialOnSite.id + '-items';
                    stackContainerId = 'materialOnSiteItemsReport-' + this.subPackage.id + '_' + this.materialOnSite.id;
                    break;
                default:
                    throw new Error("type must be set!");
                    break;
            }

            var menu = new DropDownMenu({style: "display: none;"});
            var toolbar = new dijit.Toolbar({region: "top", style: "padding:2px;border-bottom:none;width:100%;"});

            var sortOptions = ['selectedMOSItems', 'selectedMOSItemsByAmount'];

            dojo.forEach(sortOptions, function (opt) {
                var printPreviewMethod;

                switch (opt) {
                    case 'selectedMOSItems':
                        printPreviewMethod = 'openPrintSelectedMOSItems';
                        break;

                    case 'selectedMOSItemsByAmount':
                        printPreviewMethod = 'openPrintSelectedMOSItemsByAmounts';
                        break;
                }

                menu.addChild(new MenuItem({
                    label: nls[opt],
                    onClick: function () {
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

            this.addChild(toolbar);
            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId(stackContainerId + '-stackContainer');

            if (container) {
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane({
                    title: buildspace.truncateString(this.stackContainerTitle, 60),
                    content: this,
                    id: this.pageId,
                    executeScripts: true
                }, node);
                container.addChild(child);
                lang.mixin(child, {grid: grid});
                container.selectChild(this.pageId);
            }
        },
        openPrintSelectedMOSItems: function () {
            var self = this,
                selectedItemStore = self.gridOpts.gridContainer.mosItemSelectedStore,
                items = [];

            selectedItemStore.query().forEach(function (item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show();

            request.post('subPackageMaterialOnSiteReporting/getPrintPreviewSelectedItems', {
                handleAs: 'json',
                data: {
                    subPackageId: self.subPackage.id[0],
                    item_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items))
                }
            }).then(function (data) {
                var dialog = new PrintSelectedMOSItemsDialog({
                    subPackageId: self.subPackage.id,
                    title: nls.selectedMOSItems,
                    data: data,
                    selectedItems: items
                });

                dialog.show();
                pb.hide();
            }, function (error) {
                console.log(error);
                pb.hide();
            });
        },
        openPrintSelectedMOSItemsByAmounts: function () {
            var self = this,
                selectedItemStore = self.gridOpts.gridContainer.mosItemSelectedStore,
                items = [];

            selectedItemStore.query().forEach(function (item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show();

            request.post('subPackageMaterialOnSiteReporting/getPrintPreviewSelectedItems', {
                handleAs: 'json',
                data: {
                    subPackageId: self.subPackage.id[0],
                    item_ids: JSON.stringify(self.gridOpts.gridContainer.arrayUnique(items))
                }
            }).then(function (data) {
                var dialog = new PrintSelectedMOSItemsByAmountOnlyDialog({
                    subPackageId: self.subPackage.id,
                    title: nls.selectedMOSItemsByAmount,
                    data: data,
                    selectedItems: items
                });

                dialog.show();
                pb.hide();
            }, function (error) {
                console.log(error);
                pb.hide();
            });
        }
    });
});