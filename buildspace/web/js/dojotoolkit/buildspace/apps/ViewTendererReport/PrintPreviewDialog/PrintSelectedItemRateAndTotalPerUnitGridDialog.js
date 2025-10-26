(function () {
    define(
        "buildspace/apps/ViewTendererReport/PrintPreviewDialog/PrintSelectedItemRateAndTotalPerUnitGridDialog",
        ["dojo/_base/declare", "dojo/aspect", "dojo/_base/lang", "dojo/_base/connect", "dojo/when", "dojo/html", "dojo/dom", "dojo/keys", "dojo/dom-style", "dojo/request", "dojo/json", "./PrintPreviewFormDialog", "buildspace/widget/grid/cells/Formatter", 'dojo/i18n!buildspace/nls/TenderingReport'],
        function (declare, aspect, lang, connect, when_, html, dom, keys, domStyle, request, JSON, PrintPreviewFormDialog, Formatter, nls) {
            var selectedItemGrid, selectedItemGridContainer;
            selectedItemGrid = declare("buildspace.apps.TenderingReport.SelectedItemRateAndTotalPerUnitGrid", dojox.grid.EnhancedGrid, {
                style: "border-top:none;",
                region: "center",
                selectedItems: [],
                escapeHTMLInData: false,
                canSort: function () {
                    return false;
                },
                onHeaderCellMouseOver: function(e) {
                    if (!dojo.hasClass(e.cell.id, "staticHeader")) {
                        dojo.addClass(e.cellNode, this.cellOverClass);
                    }
                },
                onStyleRow: function(e) {
                    this.inherited(arguments);

                    if(e.node.children[0]){
                        if(e.node.children[0].children[0].rows.length >= 2){
                            var elemToHide = e.node.children[0].children[0].rows[1],
                                childElement = e.node.children[0].children[0].rows[0].children;

                            elemToHide.parentNode.removeChild(elemToHide);

                            dojo.forEach(childElement, function(child, i){
                                var rowSpan = dojo.attr(child, 'rowSpan');

                                if(!rowSpan || rowSpan < 2)
                                    dojo.attr(child, 'rowSpan', 2);
                            });
                        }
                    }
                }
            });
            selectedItemGridContainer = declare("buildspace.apps.TenderingReport.SelectedItemRateAndTotalPerUnitGridContainer", dijit.layout.BorderContainer, {
                style: "padding:0px;width:100%;height:100%;",
                region: "center",
                gutters: false,
                billId: -1,
                elementId: -1,
                dialog: null,
                store: null,
                structure: null,
                selectedTenderers: [],
                selectedItems: [],
                type: null,
                postCreate: function () {
                    var grid, self, toolbar;
                    self = this;
                    self.inherited(arguments);
                    grid = this.grid = new selectedItemGrid({
                        store: self.store,
                        structure: self.structure,
                        selectedItems: self.selectedItems
                    });
                    toolbar = new dijit.Toolbar({
                        region: "top",
                        style: "padding:2px;border-bottom:none;width:100%;"
                    });
                    toolbar.addChild(new dijit.form.Button({
                        label: nls.print,
                        iconClass: "icon-16-container icon-16-print",
                        onClick: function (e) {
                            return self.openPrintingDialog();
                        }
                    }));
                    toolbar.addChild(new dijit.ToolbarSeparator());
                    toolbar.addChild(new dijit.form.Button({
                        label: nls.close,
                        iconClass: "icon-16-container icon-16-close",
                        onClick: function () {
                            return self.dialog.hide();
                        }
                    }));
                    self.addChild(toolbar);
                    return self.addChild(new dijit.layout.ContentPane({
                        style: 'width:100%',
                        content: grid,
                        region: 'center'
                    }));
                },
                openPrintingDialog: function (self) {
                    var pb;
                    if (self == null) {
                        self = this;
                    }
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title: nls.pleaseWait + "..."
                    });
                    pb.show();
                    return request.get("viewTendererReporting/getPrintingInformation", {
                        handleAs: 'json'
                    }).then(function (response) {
                        var dialog;
                        dialog = new PrintPreviewFormDialog({
                            title: self.dialog.title,
                            selectedTenderers: self.selectedTenderers,
                            selectedRows: self.selectedItems,
                            type: self.type,
                            printURL: 'printReport/printItemRateAndTotalPerUnit',
                            exportURL: 'exportExcelReport/exportItemRateAndTotalPerUnit',
                            billId: self.billId,
                            elementId: self.elementId,
                            _csrf_token: response._csrf_token
                        });
                        pb.hide();
                        return dialog.show();
                    }, function (error) {
                        return pb.hide();
                    });
                }
            });
            return declare("buildspace.apps.TenderingReport.GroupProjectAssignmentDialog", dijit.Dialog, {
                style: "padding:0px;margin:0px;",
                project: null,
                companyId: -1,
                companyName: null,
                billId: -1,
                elementId: -1,
                data: null,
                type: null,
                selectedTenderers: [],
                selectedItems: [],
                buildRendering: function () {
                    var content;
                    content = this.createContent();
                    content.startup();
                    this.content = content;
                    return this.inherited(arguments);
                },
                postCreate: function () {
                    domStyle.set(this.containerNode, {
                        padding: "0px",
                        margin: "0px"
                    });
                    this.closeButtonNode.style.display = "none";
                    return this.inherited(arguments);
                },
                _onKey: function (e) {
                    var key;
                    key = e.keyCode;
                    if (key === keys.ESCAPE) {
                        return dojo.stopEvent(e);
                    }
                },
                onHide: function () {
                    return this.destroyRecursive();
                },
                createContent: function () {
                    var borderContainer, companiesList, content, self, columnSettings;
                    self = this;
                    companiesList = {};
                    columnSettings = [];

                    borderContainer = new dijit.layout.BorderContainer({
                        style: "padding:0px;width:1280px;height:600px;",
                        gutters: false
                    });
                    if (this.selectedTenderers.length > 0 && this.type) {
                        request("viewTendererReporting/getContractors", {
                            sync: true,
                            handleAs: 'json',
                            query: {
                                id: self.project.id,
                                type: self.type,
                                contractorIds: JSON.stringify(self.selectedTenderers)
                            }
                        }).then(function (response) {
                            return companiesList = response;
                        }, function (error) {
                            return console.log(error);
                        });
                    }

                    request("billManager/getBillInfo", {
                        sync: true,
                        handleAs: 'json',
                        query: {
                            id: self.billId
                        }
                    }).then(function (response) {
                        return columnSettings = response['column_settings'];
                    }, function (error) {
                        return console.log(error);
                    });

                    content = new selectedItemGridContainer({
                        store: dojo.data.ItemFileWriteStore({
                            data: self.data
                        }),
                        dialog: self,
                        billId: self.billId,
                        elementId: self.elementId,
                        selectedTenderers: self.selectedTenderers,
                        selectedItems: self.selectedItems,
                        structure: self.constructGridStructure(companiesList, columnSettings),
                        type: this.type
                    });
                    borderContainer.addChild(content);
                    return borderContainer;
                },
                constructGridStructure: function (selectedCompanies, columnSettings, type, self) {
                    var companyName, formatter, key, textColor, parties;
                    var fixedCells, scrollableCells = [];

                    // Estimate
                    var numberOfParties = 1;

                    if (selectedCompanies == null) {
                        selectedCompanies = [];
                    }

                    for(key in selectedCompanies)
                    {
                        numberOfParties++;
                    }

                    formatter = new Formatter();

                    fixedCells = [{
                        name: nls.no,
                        field: "id",
                        width: '30px',
                        styles: 'text-align: center;',
                        formatter: formatter.rowCountCellFormatter
                    }, {
                        name: nls.billReference,
                        field: 'bill_ref',
                        width: '80px',
                        styles: "text-align:center; color: red;",
                        formatter: formatter.billRefCellFormatter,
                        noresize: true
                    }, {
                        name: nls.description,
                        field: 'description',
                        width: 'auto',
                        formatter: formatter.printPreviewTreeCellFormatter,
                        noresize: true
                    }, {
                        name: nls.unit,
                        field: 'uom_id',
                        width: '70px',
                        styles: 'text-align:center;',
                        formatter: formatter.unitIdCellFormatter,
                        noresize: true
                    }];

                    // parties include project owner (for estimates) and tenderers
                    parties = [];

                    var units = [];

                    var companyKey;
                    for(var columnSettingsKey in columnSettings)
                    {
                        parties.push({
                            name: nls.estimate,
                            styles: "text-align:center;",
                            headerClasses: "staticHeader",
                            colSpan: 2
                        });
                        units.push({
                            name: columnSettings[columnSettingsKey]['name'] + '<br/>(' + nls.totalUnits + ': ' + columnSettings[columnSettingsKey]['quantity'] + ')',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader",
                            colSpan: (1 + (numberOfParties * 2))
                        });
                        scrollableCells.push({
                            name: nls.singleUnitQuantity,
                            field: columnSettings[columnSettingsKey]['id'] + '-quantity_per_unit-final_value',
                            width: '70px',
                            rowSpan: 2,
                            styles: 'text-align:right;color:blue;',
                            formatter: formatter.unEditableNumberAndTextCellFormatter,
                            noresize: true
                        });
                        scrollableCells.push({
                            name: nls.rate,
                            field: 'single_unit_quantity',
                            width: '100px',
                            styles: "text-align:right;color:blue;",
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true
                        });
                        scrollableCells.push({
                            name: nls.singleUnitTotal,
                            field: columnSettings[columnSettingsKey]['id'] + '-total_per_unit',
                            width: '100px',
                            styles: "text-align:right;color:blue;",
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true
                        });

                        for(companyKey in selectedCompanies)
                        {
                            textColor = selectedCompanies[companyKey]['awarded'] ? 'blue' : 'black';
                            companyName = buildspace.truncateString(selectedCompanies[companyKey]['name'], 28);

                            scrollableCells.push({
                                name: "<span style=\"color:" + textColor + "\">" + nls.rate + "</span>",
                                field: selectedCompanies[companyKey]['id'] + '-rate-value',
                                styles: "text-align:right;color:" + textColor + ";",
                                width: '100px',
                                formatter: formatter.printPreviewTendererRateCellFormatter,
                                noresize: true
                            });
                            scrollableCells.push({
                                name: "<span style=\"color:" + textColor + "\">" + nls.singleUnitTotal + "</span>",
                                field: selectedCompanies[companyKey]['id'] + "-" + columnSettings[columnSettingsKey]['id'] + '-total_per_unit',
                                styles: "text-align:right;color:" + textColor + ";",
                                width: '100px',
                                formatter: formatter.printPreviewTendererCurrencyCellFormatter,
                                noresize: true
                            });

                            parties.push({
                                name: "<span style=\"color:" + textColor + "\">" + companyName + "</span>",
                                styles: "text-align:center;color:" + textColor + ";",
                                headerClasses: "staticHeader",
                                colSpan: 2
                            });
                        }
                    }

                    return [
                        {
                            cells: [fixedCells],
                            width: '560px',
                            noscroll: true
                        }, {
                            cells: [scrollableCells, parties, units]
                        }
                    ];
                }
            });
        });

}).call(this);
