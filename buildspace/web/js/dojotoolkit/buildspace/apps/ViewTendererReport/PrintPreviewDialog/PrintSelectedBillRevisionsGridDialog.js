(function () {
    define("buildspace/apps/ViewTendererReport/PrintPreviewDialog/PrintSelectedBillRevisionsGridDialog", ["dojo/_base/declare", "dojo/aspect", "dojo/_base/lang", "dojo/_base/connect", "dojo/when", "dojo/html", "dojo/dom", "dojo/keys", "dojo/dom-style", "dojo/request", "dojo/json", "./PrintPreviewFormDialog", "buildspace/widget/grid/cells/Formatter", 'dojo/i18n!buildspace/nls/TenderingReport'], function (declare, aspect, lang, connect, when_, html, dom, keys, domStyle, request, JSON, PrintPreviewFormDialog, Formatter, nls) {
        var selectedBillGrid, selectedBillGridContainer;
        selectedBillGrid = declare("buildspace.apps.TenderingReport.SelectedBillRevisionsGrid", dojox.grid.EnhancedGrid, {
            style: "border-top:none;",
            region: "center",
            selectedBills: [],
            escapeHTMLInData: false,
            canSort: function () {
                return false;
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
        selectedBillGridContainer = declare("buildspace.apps.TenderingReport.SelectedBillRevisionsGridContainer", dijit.layout.BorderContainer, {
            style: "padding:0px;width:100%;height:100%;",
            region: "center",
            gutters: false,
            projectId: -1,
            dialog: null,
            store: null,
            structure: null,
            selectedTenderers: [],
            selectedBills: [],
            type: null,
            postCreate: function () {
                var grid, self, toolbar;
                self = this;
                self.inherited(arguments);
                grid = this.grid = new selectedBillGrid({
                    store: self.store,
                    structure: self.structure,
                    selectedBills: self.selectedBills
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
                        selectedRows: self.selectedBills,
                        type: self.type,
                        printURL: null,
                        exportURL: 'exportExcelReport/exportBillRevisions',
                        projectId: self.projectId,
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
            companyId: -1,
            companyName: null,
            data: null,
            projectId: -1,
            selectedTenderers: [],
            selectedBills: [],
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
                var borderContainer, companiesList, content, self, revisions;
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

                request("viewTendererReporting/getRevisions", {
                    sync: true,
                    handleAs: 'json',
                    query: {
                        id: self.project.id
                    }
                }).then(function (response) {
                    return revisions = response['revisions'];
                }, function (error) {
                    console.log(error);
                });

                content = new selectedBillGridContainer({
                    store: dojo.data.ItemFileWriteStore({
                        data: self.data
                    }),
                    dialog: self,
                    selectedTenderers: self.selectedTenderers,
                    projectId: self.projectId,
                    selectedBills: self.selectedBills,
                    structure: self.constructGridStructure(companiesList, revisions),
                    type: this.type
                });
                borderContainer.addChild(content);
                return borderContainer;
            },
            constructGridStructure: function (selectedCompanies, revisions, type, self) {
                var basicStructure, companyName, formatter, key, tendererEstimateColumn, textColor, revisionName, fixedCells;
                var parties = [];
                var scrollableCells = [];

                // Exclude the last revision because it is the current revision.
                var numberOfRevisions = (revisions.length - 1);

                if (selectedCompanies == null) {
                    selectedCompanies = [];
                }
                if (type == null) {
                    type = this.type;
                }
                if (self == null) {
                    self = this;
                }
                formatter = new Formatter();
                fixedCells = [
                    {
                        name: "No",
                        field: "id",
                        width: '30px',
                        styles: 'text-align: center;',
                        formatter: formatter.rowCountCellFormatter
                    }, {
                        name: nls.description,
                        field: 'title',
                        width: 'auto',
                        formatter: formatter.printPreviewTreeCellFormatter,
                        noresize: true
                    }
                ];

                // Add estimate revision columns
                for(var i = 0; i < numberOfRevisions; i++)
                {
                    revisionName = revisions[i]['revision'];
                    scrollableCells.push(
                        {
                            name: revisionName + ' ' + nls.grandTotal,
                            field: 'grand_total-revision-' + i,
                            styles: "text-align:right;color:blue;",
                            width: '100px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true
                        }
                    );
                }

                scrollableCells.push({
                    name: nls.grandTotal,
                    field: 'grand_total',
                    styles: "text-align:right;color:blue;",
                    width: '100px',
                    formatter: formatter.unEditableCurrencyCellFormatter,
                    noresize: true
                });

                parties.push({
                    name: nls.estimate,
                    styles: "text-align:center;",
                    colSpan: 1 + numberOfRevisions
                });

                for (key in selectedCompanies) {
                    textColor = selectedCompanies[key]['awarded'] ? 'blue' : 'black';
                    companyName = buildspace.truncateString(selectedCompanies[key]['name'], 28);

                    // Add tenderer revision columns
                    for(i = 0; i < numberOfRevisions; i++)
                    {
                        revisionName = revisions[i]['revision'];
                        scrollableCells.push(
                            {
                                name: revisionName + ' ' + nls.grandTotal,
                                field: selectedCompanies[key]['id'] + "-grand_total-revision-" + i,
                                width: '100px',
                                styles: "text-align:right;color:" + textColor + ";",
                                formatter: formatter.printPreviewTendererCurrencyCellFormatter,
                                noresize: true
                            }
                        );
                    }

                    tendererEstimateColumn = {
                        name: nls.grandTotal,
                        field: selectedCompanies[key]['id'] + "-grand_total",
                        styles: "text-align:right;color:" + textColor + ";",
                        width: '100px',
                        formatter: formatter.printPreviewTendererCurrencyCellFormatter,
                        noresize: true
                    };

                    scrollableCells.push(tendererEstimateColumn);

                    parties.push({
                        name: "<span style=\"color:" + textColor + "\">" + companyName + "</span>",
                        styles: "text-align:center;",
                        colSpan: 1 + numberOfRevisions
                    });
                }

                basicStructure = [
                    {
                        cells: [fixedCells],
                        width: '410px',
                        noscroll: true
                    }, {
                        cells: [scrollableCells, parties]
                    }
                ];

                return basicStructure;
            }
        });
    });

}).call(this);
