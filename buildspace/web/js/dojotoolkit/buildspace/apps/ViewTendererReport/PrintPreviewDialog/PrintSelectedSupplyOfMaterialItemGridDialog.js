(function () {
    define("buildspace/apps/ViewTendererReport/PrintPreviewDialog/PrintSelectedSupplyOfMaterialItemGridDialog", ["dojo/_base/declare", "dojo/aspect", "dojo/_base/lang", "dojo/_base/connect", "dojo/when", "dojo/html", "dojo/dom", "dojo/keys", "dojo/dom-style", "dojo/request", "dojo/json", "./PrintPreviewFormDialog", "buildspace/widget/grid/cells/Formatter", 'dojo/i18n!buildspace/nls/TenderingReport'], function (declare, aspect, lang, connect, when_, html, dom, keys, domStyle, request, JSON, PrintPreviewFormDialog, Formatter, nls) {
        var Dialog, selectedItemGrid, selectedItemGridContainer;
        selectedItemGrid = declare("buildspace.apps.TenderingReport.SelectedSupplyOfMaterialItemGrid", dojox.grid.EnhancedGrid, {
            style: "border-top:none;",
            region: "center",
            selectedItems: [],
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
            },
            destroy: function() {
                this.inherited(arguments);
                dojo.forEach(this._connects, connect.disconnect);
                delete this._connects;
            }
        });
        selectedItemGridContainer = declare("buildspace.apps.TenderingReport.SelectedSupplyOfMaterialItemGridContainer", dijit.layout.BorderContainer, {
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
                var dialog = new PrintPreviewFormDialog({
                    title: this.dialog.title,
                    selectedTenderers: this.selectedTenderers,
                    selectedRows: this.selectedItems,
                    exportURL: 'exportExcelReport/exportSupplyOfMaterialItem',
                    billId: this.billId,
                });

                return dialog.show();
            }
        });
        return Dialog = declare("buildspace.apps.TenderingReport.PrintSelectedSupplyOfMaterialItemDialog", dijit.Dialog, {
            style: "padding:0px;margin:0px;",
            project: null,
            companyId: -1,
            billId: -1,
            elementId: -1,
            data: null,
            companiesList: [],
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
                var borderContainer, content, self;
                self = this;
                borderContainer = new dijit.layout.BorderContainer({
                    style: "padding:0px;width:1280px;height:600px;",
                    gutters: false
                });
                this.companiesList = [];
                for(var i in this.companies){
                    if(this.selectedTenderers.includes(this.companies[i]['id'])){
                        this.companiesList.push(this.companies[i]);
                    }
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
                    structure: self.getGridStructure(),
                });
                borderContainer.addChild(content);
                return borderContainer;
            },
            getGridStructure: function(){
                var formatter = this.formatter = new Formatter();

                var descriptionWidth = 'auto';
                var fixedColumns, fixedColumnsAfterTypeColumns, columnToDisplay;

                if(this.selectedTenderers.length > 0){
                    descriptionWidth = '500px';
                }

                fixedColumns = this.fixedColumns = {
                    noscroll: false,
                    width: 57.8,
                    cells: [
                        [{
                            name: 'No',
                            field: 'id',
                            styles: "text-align:center;",
                            width: '30px',
                            formatter: formatter.rowCountCellFormatter,
                            noresize: true,
                            showInCtxMenu: true,
                            rowSpan:2
                        },{
                            name: nls.description,
                            field: 'description',
                            width: descriptionWidth,
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan:2
                        },{
                            name: nls.unit,
                            field: 'uom_id',
                            width: '50px',
                            editable: false,
                            styles: 'text-align:center;',
                            formatter: formatter.unitIdCellFormatter,
                            noresize: true,
                            rowSpan:2
                        },{
                            name: nls.supplyRate,
                            field: 'supply_rate',
                            styles: "text-align:right;",
                            width: '100px',
                            formatter: formatter.unEditableCurrencyCellFormatter,
                            noresize: true,
                            rowSpan:2
                        }]
                    ]
                };

                fixedColumnsAfterTypeColumns = [];

                columnToDisplay = this.generateContractorRateColumn(fixedColumns);

                dojo.forEach(fixedColumnsAfterTypeColumns,function(column){
                    columnToDisplay.cells[0].push(column);
                });

                return columnToDisplay;
            },
            generateContractorRateColumn: function(fixedColumns){
                var companies = this.companiesList,
                    formatter = this.formatter,
                    parentCells = [];
                var colCount = 0;
                var companyColumnChildren = [
                    {name: nls.estimatedQty+"<br/>("+nls.includeWastage+")<br />(A)", field_name: 'estimated_qty', width: '85px', cellType:'buildspace.widget.grid.cells.TextBox', styles: "text-align:right;", editable: true, formatter: formatter.currencyCellFormatter },
                    {name: "% "+nls.ofWastageAllowed, field_name: 'percentage_of_wastage', width: '65px', cellType:'buildspace.widget.grid.cells.TextBox', styles: "text-align:right;", editable: true, formatter: formatter.currencyCellFormatter},
                    {name: nls.contractorRate+"<br />(X)", field_name: 'contractor_supply_rate', width: '85px', cellType:'buildspace.widget.grid.cells.TextBox', styles: "text-align:right;", editable: true, formatter: formatter.currencyCellFormatter },
                    {name: nls.difference+"<br />(B)=(Y)-(X)", field_name: 'difference', width: '85px', styles: "text-align:right;", formatter: formatter.unEditableCurrencyCellFormatter },
                    {name: nls.amount+ " ("+this.currencySetting+")<br />(C)=(A)x(B)", field_name: 'amount', width: '85px', styles: "text-align:right;", formatter: formatter.unEditableCurrencyCellFormatter }
                ];

                dojo.forEach(companies, function(company){
                    var colspan = companyColumnChildren.length;

                    colCount++;

                    var companyName = null;

                    if(company.awarded){
                        companyName = '<span style="color:blue;">'+buildspace.truncateString(company.name, 28)+'</span>';
                    }else{
                        companyName = buildspace.truncateString(company.name, 28);
                    }

                    parentCells.push({
                        name: companyName,
                        styles:'text-align:center;',
                        headerClasses: "staticHeader typeHeader"+colCount,
                        colSpan: colspan,
                        headerId: company.id,
                        hidden: false
                    });

                    var field = null;

                    for(i=0;i<companyColumnChildren.length;i++){
                        field = company.id+'-'+companyColumnChildren[i].field_name;

                        var cellStructure = {
                            field: field,
                            columnType: "contractorColumn",
                            companyId: company.id,
                            headerClasses: "typeHeader"+colCount
                        };

                        lang.mixin(cellStructure, companyColumnChildren[i]);

                        fixedColumns.cells[0].push(cellStructure);
                    }
                });

                fixedColumns.cells.push(parentCells);

                return fixedColumns;
            }
        });
    });

}).call(this);
