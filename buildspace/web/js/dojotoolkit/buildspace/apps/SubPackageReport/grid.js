define('buildspace/apps/SubPackageReport/grid',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/array",
    "dojo/_base/connect",
    "dojo/dom-attr",
    'dijit/Menu',
    "dojox/grid/enhanced/plugins/Selector",
    "dojox/grid/enhanced/plugins/Rearrange",
    'dojo/_base/event',
    'dojo/keys',
    "dijit/focus",
    'dojo/aspect',
    "dojo/when",
    'dojo/request',
    'dijit/PopupMenuItem',
    'buildspace/widget/grid/cells/Textarea',
    "dijit/DropDownMenu",
    "dijit/form/DropDownButton",
    "dijit/MenuItem",
    "./PrintPreviewDialog/PrintSelectedBillGridDialog",
    "./PrintPreviewDialog/PrintSelectedItemRateAndTotalGridDialog",
    "dojo/i18n!buildspace/nls/SubPackages"
], function(declare, lang, array, connect, domAttr, Menu, Selector, Rearrange, evt, keys, focusUtil, aspect, when, request, PopupMenuItem, Textarea, DropDownMenu, DropDownButton, MenuItem, PrintSelectedBillGridDialog, PrintSelectedItemRateAndTotalGridDialog, nls) {

    var SubPackagesGrid = declare('buildspace.apps.SubPackageReport.Grid', dojox.grid.EnhancedGrid, {
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
        gridContainer: null,
        constructor:function(args) {
            this.rearranger    = new Rearrange(this, {});
            this.structure     = args.structure;
            this.gridContainer = args.gridContainer;
            this.type          = args.type;

            if ( args.type !== 'sub_packages-list' ) {
                this.plugins = {indirectSelection: {headerSelector:true, width:"40px", styles:"text-align: center;"}};
            }
        },
        postCreate: function() {
            var self = this, storeName;
            self.inherited(arguments);

            if ( self.type === 'sub_packages-bill_element_list' ) {
                aspect.after(self, "_onFetchComplete", function() {
                    self.gridContainer.markedCheckBoxObject(self, self.gridContainer.selectedElementStore);
                });
            } else if ( self.type === 'sub_packages-bill_item_list' ) {
                aspect.after(self, "_onFetchComplete", function() {
                    self.gridContainer.markedCheckBoxObject(self, self.gridContainer.selectedItemStore);
                });
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
        canSort: function(inSortInfo) {
            return false;
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
        }
    });

    return declare('buildspace.apps.SubPackageReport.GridContainer', dijit.layout.BorderContainer, {
        pageId: 'page-00',
        style: "padding:0px;border:0px;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: null,
        rootProject: null,
        subPackage: null,
        gridOpts: {},
        type: null,
        gridContainer: null,
        menu: null,
        postCreate: function() {
            var self = this;
            self.inherited(arguments);
            lang.mixin(self.gridOpts, { rootProject: self.rootProject, contId: self.id, type:self.type, region:"center", gridContainer: self.gridContainer, subPackage: self.subPackage });
            var grid = this.grid = new SubPackagesGrid(self.gridOpts);

            if ( self.type === 'sub_packages-bill_list' || self.type === 'sub_packages-bill_element_list' || self.type === 'sub_packages-bill_item_list' ) {
                var toolbar   = new dijit.Toolbar({region: "top", style:"padding:2px;border-bottom:none;width:100%;"});
                var labelName = null;
                self.menu     = new DropDownMenu({ style: "display: none;"});

                // disable print by selected tenderer if there is no tenderer selected
                var disabledPrintSelectedTendererReport = false;

                if ( self.gridOpts.subContractors.length === 0 ) {
                    disabledPrintSelectedTendererReport = true;
                }

                if ( self.gridOpts.subContractors.length > 0 && self.gridOpts.subContractors[0].selected === false ) {
                    disabledPrintSelectedTendererReport = true;
                }

                if ( self.type === 'sub_packages-bill_list' ) {
                    labelName = nls.summary;

                    self.createSummaryPrintPreviewButtons(disabledPrintSelectedTendererReport);
                }

                if ( self.type === 'sub_packages-bill_element_list' || self.type === 'sub_packages-bill_item_list' ) {
                    labelName = nls.itemRateAndTotal;

                    self.createItemRatePrintPreviewButtons(disabledPrintSelectedTendererReport);
                }

                toolbar.addChild(
                    new DropDownButton({
                        label: labelName,
                        iconClass: "icon-16-container icon-16-print",
                        dropDown: self.menu
                    })
                );

                self.addChild(toolbar);
            }

            self.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));

            var container = dijit.byId('SubPackagesTenderingReport-'+self.rootProject.id+'-stackContainer');
            if(container) {
                var node = document.createElement("div");
                var child = new dojox.layout.ContentPane( {title: buildspace.truncateString(self.stackContainerTitle, 30), id: self.pageId, executeScripts: true },node );
                container.addChild(child);
                child.set('content', self);
                lang.mixin(child, {grid: grid});
                container.selectChild(self.pageId);
            }
        },
        createSummaryPrintPreviewButtons: function(disabledPrintSelectedTendererReport) {
            var self = this;

            var billSummarySelectedTendererPrintPreview = new MenuItem({
                label: nls.billSummarySelectedTendererPrintPreview,
                iconClass: "icon-16-container icon-16-print",
                disabled: disabledPrintSelectedTendererReport,
                onClick: function() {
                    self.openBillSummarySelectedTendererPrintPreview();
                }
            });
            self.menu.addChild(billSummarySelectedTendererPrintPreview);

            var sortOptions = ['printPreviewLowestToHighest', 'printPreviewHighestToLowest'], pSubMenu = new Menu();

            dojo.forEach(sortOptions, function(opt) {
                var menuItem = new MenuItem({
                    label: nls[opt],
                    onClick: function() {
                        self.openBillSummaryAllTendererPrintPreview(opt);
                    }
                });
                pSubMenu.addChild(menuItem);
            });

            var mainMenuItem = new PopupMenuItem({
                label: nls.billSummaryAllTendererPrintPreview,
                iconClass: "icon-16-container icon-16-print",
                popup: pSubMenu
            });
            self.menu.addChild(mainMenuItem);
        },
        createItemRatePrintPreviewButtons: function(disabledPrintSelectedTendererReport) {
            var self = this;

            var sortOptions = ['printPreviewLowestToHighest', 'printPreviewHighestToLowest'], pSubMenu = new Menu();

            dojo.forEach(sortOptions, function(opt) {
                var menuItem = new MenuItem({
                    label: nls[opt],
                    onClick: function() {
                        self.openItemRateAndTotalAllTendererPrintPreview(opt);
                    }
                });
                pSubMenu.addChild(menuItem);
            });

            var mainMenuItem = new PopupMenuItem({
                label: nls.itemRateAndTotalAllTendererPrintPreview,
                iconClass: "icon-16-container icon-16-print",
                popup: pSubMenu
            });
            self.menu.addChild(mainMenuItem);
        },
        openBillSummarySelectedTendererPrintPreview: function() {
            var self = this,
                companies = self.gridOpts.subContractors,
                billStore = self.gridContainer.selectedBillStore,
                bills = [];

            billStore.query().forEach(function(item) {
                bills.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('subPackageReporting/getPrintingSelectedBillsSummaryForSelectedTenderer', {
                handleAs: 'json',
                data: {
                    sid: self.subPackage.id,
                    bill_ids: JSON.stringify(self.gridContainer.arrayUnique(bills))
                }
            }).then(function(data) {
                var companyId, companyName;

                dojo.forEach(companies, function(company) {
                    if (company.selected)
                    {
                        companyId   = company.id;
                        companyName = buildspace.truncateString(company.name, 28);
                    }
                });

                var dialog = new PrintSelectedBillGridDialog({
                    title: nls.billSummarySelectedTendererPrintPreview,
                    companyId: companyId,
                    companyName: companyName,
                    data: data,
                    subPackage: self.subPackage,
                    selectedBills: self.gridContainer.arrayUnique(bills),
                    printURL: 'subPackageReporting/printBillSummarySelectedTenderer',
                    exportURL: 'subPackageExportExcelReporting/exportExcelBillSummarySelectedTenderer'
                });

                dialog.show();

                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openBillSummaryAllTendererPrintPreview: function(type) {
            var self = this,
                billStore = self.gridContainer.selectedBillStore,
                bills = [];

            billStore.query().forEach(function(item) {
                bills.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('subPackageReporting/getPrintingSelectedBillsSummaryForAllTenderers', {
                handleAs: 'json',
                data: {
                    sid: self.subPackage.id,
                    bill_ids: JSON.stringify(self.gridContainer.arrayUnique(bills))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedBillGridDialog({
                    title: nls.billSummaryAllTendererPrintPreview + ' (' + nls[type] + ')',
                    data: data,
                    subPackage: self.subPackage,
                    selectedBills: self.gridContainer.arrayUnique(bills),
                    type: type,
                    printURL: 'subPackageReporting/printBillSummaryForAllTenderer',
                    exportURL: 'subPackageExportExcelReporting/exportExcelBillSummaryForAllTenderer'
                });

                dialog.show();

                pb.hide();
            }, function(error) {
                console.log(error);
                pb.hide();
            });
        },
        openItemRateAndTotalAllTendererPrintPreview: function(type) {
            var self = this,
                itemStore = self.gridContainer.selectedItemStore,
                items = [];

            itemStore.query().forEach(function(item) {
                items.push(item.id);
            });

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait+'...'
            });

            pb.show();

            request.post('subPackageReporting/getPrintingSelectedItemsRateSummaryForAllTenderer', {
                handleAs: 'json',
                data: {
                    sid: self.subPackage.id,
                    bid: self.gridOpts.bill.id,
                    item_ids: JSON.stringify(self.gridContainer.arrayUnique(items))
                }
            }).then(function(data) {
                var dialog = new PrintSelectedItemRateAndTotalGridDialog({
                    title: nls.itemRateAndTotalAllTendererPrintPreview + ' (' + nls[type] + ')',
                    data: data,
                    subPackage: self.subPackage,
                    bill: self.gridOpts.bill,
                    selectedItems: self.gridContainer.arrayUnique(items),
                    type: type,
                    printURL: 'subPackageReporting/printItemRateAndTotalForAllTenderer',
                    exportURL: 'subPackageExportExcelReporting/exportExcelItemRateAndTotalForAllTenderer'
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