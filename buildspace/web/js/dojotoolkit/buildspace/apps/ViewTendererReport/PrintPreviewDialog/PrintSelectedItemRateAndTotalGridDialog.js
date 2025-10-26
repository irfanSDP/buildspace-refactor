(function () {
    define(
        "buildspace/apps/ViewTendererReport/PrintPreviewDialog/PrintSelectedItemRateAndTotalGridDialog",
        ["dojo/_base/declare", "dojo/aspect", "dojo/_base/lang", "dojo/_base/connect", "dojo/when", "dojo/html", "dojo/dom", "dojo/keys", "dojo/dom-style", "dojo/request", "dojo/json", "./PrintPreviewFormDialog", "buildspace/widget/grid/cells/Formatter", 'dojo/i18n!buildspace/nls/TenderingReport'],
        function (declare, aspect, lang, connect, when_, html, dom, keys, domStyle, request, JSON, PrintPreviewFormDialog, Formatter, nls) {
            var Dialog, selectedItemGrid, selectedItemGridContainer;
            selectedItemGrid = declare("buildspace.apps.TenderingReport.SelectedItemRateAndTotalGrid", dojox.grid.EnhancedGrid, {
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
            selectedItemGridContainer = declare("buildspace.apps.TenderingReport.SelectedItemRateAndTotalGridContainer", dijit.layout.BorderContainer, {
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
                    var tendererSelected = (self.type === null);
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
                            printURL: 'printReport/printItemRateAndTotal',
                            exportURL: 'exportExcelReport/exportItemRateAndTotal',
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
            return Dialog = declare("buildspace.apps.TenderingReport.GroupProjectAssignmentDialog", dijit.Dialog, {
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
                    var borderContainer, companiesList, content, self;
                    self = this;
                    companiesList = {};
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
                    content = new selectedItemGridContainer({
                        store: dojo.data.ItemFileWriteStore({
                            data: self.data
                        }),
                        dialog: self,
                        billId: self.billId,
                        elementId: self.elementId,
                        selectedTenderers: self.selectedTenderers,
                        selectedItems: self.selectedItems,
                        structure: self.constructGridStructure(companiesList),
                        type: this.type
                    });
                    borderContainer.addChild(content);
                    return borderContainer;
                },
                constructGridStructure: function (selectedCompanies, type, self) {
                    var basicStructure, companyName, formatter, key, structure, textColor;
                    var parties, tendererRateAndTotalColumns;

                    if (selectedCompanies == null) {
                        selectedCompanies = [];
                    }

                    formatter = new Formatter();

                    basicStructure = [
                        {
                            name: "No",
                            field: "id",
                            width: '30px',
                            rowSpan: 2,
                            styles: 'text-align: center;',
                            formatter: formatter.rowCountCellFormatter
                        }, {
                            name: nls.billReference,
                            field: 'bill_ref',
                            width: '80px',
                            rowSpan: 2,
                            styles: "text-align:center; color: red;",
                            formatter: formatter.billRefCellFormatter,
                            noresize: true
                        }, {
                            name: nls.description,
                            field: 'description',
                            width: selectedCompanies.length > 2 ? '380px' : 'auto',
                            rowSpan: 2,
                            formatter: formatter.printPreviewTreeCellFormatter,
                            noresize: true
                        }, {
                            name: nls.unit,
                            field: 'uom_id',
                            width: '70px',
                            styles: 'text-align:center;',
                            rowSpan: 2,
                            formatter: formatter.unitIdCellFormatter,
                            noresize: true
                        }, {
                            name: nls.quantity,
                            field: 'grand_total_quantity',
                            width: '70px',
                            rowSpan: 2,
                            styles: 'text-align:right;color:blue;',
                            formatter: formatter.unEditableNumberAndTextCellFormatter,
                            noresize: true
                        }, {
                            name: nls.rate,
                            field: 'rate_after_markup',
                            width: '100px',
                            styles: "text-align:right;color:blue;",
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true
                        }, {
                            name: nls.total,
                            field: 'grand_total',
                            width: '100px',
                            styles: "text-align:right;color:blue;",
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true
                        }
                    ];
                    // parties include project owner (for estimates) and tenderers
                    parties = [];

                    parties.push({
                        name: nls.estimate,
                        styles: "text-align:center;",
                        headerClasses: "staticHeader",
                        colSpan: 2
                    });
                    for (key in selectedCompanies) {

                        textColor = selectedCompanies[key]['awarded'] ? 'blue' : 'black';
                        companyName = buildspace.truncateString(selectedCompanies[key]['name'], 28);
                        var companiesColumns = {
                            name: "<span style=\"color:" + textColor + "\">" + companyName + "</span>",
                            styles: "text-align:center;color:" + textColor + ";",
                            headerClasses: "staticHeader",
                            colSpan: 2
                        };
                        parties.push(companiesColumns);

                        tendererRateAndTotalColumns = [
                            {
                                name: "<span style=\"color:" + textColor + "\">" + nls.rate + "</span>",
                                field: selectedCompanies[key]['id'] + "-rate-value",
                                styles: "text-align:right;color:" + textColor + ";",
                                width: '100px',
                                formatter: formatter.printPreviewTendererRateCellFormatter,
                                noresize: true
                            },
                            {
                                name: "<span style=\"color:" + textColor + "\">" + nls.total + "</span>",
                                field: selectedCompanies[key]['id'] + "-grand_total",
                                styles: "text-align:right;color:" + textColor + ";",
                                width: '100px',
                                formatter: formatter.printPreviewTendererCurrencyCellFormatter,
                                noresize: true
                            }
                        ];
                        for(key in tendererRateAndTotalColumns)
                        {
                            structure = tendererRateAndTotalColumns[key];
                            basicStructure.push(structure);
                        }
                    }

                    return [basicStructure, parties];
                }
            });
        });

}).call(this);
