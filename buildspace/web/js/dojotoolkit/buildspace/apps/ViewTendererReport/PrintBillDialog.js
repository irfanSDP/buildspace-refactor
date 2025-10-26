define('buildspace/apps/ViewTendererReport/PrintBillDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    'dojo/i18n!buildspace/nls/PrintBillDialog'
], function(declare, lang, connect, when, html, dom, keys, domStyle, nls){

    var PrintBillGrid = declare('buildspace.apps.ViewTendererReport.PrintBillGrid', dojox.grid.EnhancedGrid, {
        projectId: 0,
        region: 'center',
        contractor: null,
        style: "border-top:none;",
        _csrf_token: null,
        printRationalizedRate: null,
        currentModuleName: null,
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this,
            contractor = self.contractor;

            self.inherited(arguments);
            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex),
                    colField = e.cell.field;

                    if(!self.contractor)
                    {
                        if (colField == 'printWithoutNotListedItem' && item.id > 0 ){
                            window.open('EstimationBQFinalPdf/'+self.projectId+'/'+item.id+'/'+self._csrf_token+'/0', '_blank');
                            return window.focus();
                        } else if(colField == 'printWithoutNotListedItem' && item.id == 0 ){
                            window.open('EstimationBQSummary/'+self.projectId+'/-1/'+self._csrf_token+'/0', '_blank');
                            return window.focus();
                        } else if(colField == 'printWithNotListedItem' && item.id > 0 ){
                            window.open('EstimationBQFinalPdf/'+self.projectId+'/'+item.id+'/'+self._csrf_token+'/1', '_blank');
                            return window.focus();
                        } else if(colField == 'printWithNotListedItem' && item.id == 0 ){
                            window.open('EstimationBQSummary/'+self.projectId+'/-1/'+self._csrf_token+'/1', '_blank');
                            return window.focus();
                        }
                    }
                    else
                    {
                        if (colField == 'printWithoutNotListedItem' && item.id > 0 ){
                            window.open('ContractorBQFinalPdf/'+self.projectId+'/'+item.id+'/'+contractor.tender_company_id+'/'+self._csrf_token+'/0', '_blank');
                            return window.focus();
                        } else if(colField == 'printWithoutNotListedItem' && item.id == 0 ){
                            window.open('ContractorBQSummary/'+self.projectId+'/'+contractor.tender_company_id+'/'+self._csrf_token+'/0', '_blank');
                            return window.focus();
                        } else if(colField == 'printWithNotListedItem' && item.id > 0 ){
                            window.open('ContractorBQFinalPdf/'+self.projectId+'/'+item.id+'/'+contractor.tender_company_id+'/'+self._csrf_token+'/1', '_blank');
                            return window.focus();
                        } else if(colField == 'printWithNotListedItem' && item.id == 0 ){
                            window.open('ContractorBQSummary/'+self.projectId+'/'+contractor.tender_company_id+'/'+self._csrf_token+'/1', '_blank');
                            return window.focus();
                        }
                    }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        }
    });

    var Formatter = declare("buildspace.apps.ViewTendererReport.PrintBillGrid.CellFormatter", null, {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            return parseInt(rowIdx)+1;
        },
        descriptionCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            return item.id == 0 ? nls.summaryPage : cellValue;
        },
        printCellAddendumWithoutNotListedItemFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);

            return item.id >= 0 ? '<a href="javascript:void(0);">'+nls.print+'</a>' : null;
        },
        printCellAddendumWithNotListedItemFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);

            return item.id >= 0 ? '<a href="javascript:void(0);">'+nls.print+'</a>' : null;
        }
    });

    var Dialog = declare('buildspace.apps.ViewTendererReport.PrintBillGridDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.printBQ,
        contractor: false,
        projectId: null,
        bqCSRFToken: null,
        printRationalizedRate: false,
        _csrf_token: null,
        currentModuleName: 'tendering',
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;

            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function(e){
            key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createContent: function(){
            var self = this;
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:800px;height:350px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border:0px;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            var formatter = new Formatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "printBQ/getProjectPrintList/id/"+self.projectId
                }),
                content = PrintBillGrid({
                    projectId: self.projectId,
                    contractor: self.contractor,
                    _csrf_token: self._csrf_token,
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'title', width:'auto', formatter: formatter.descriptionCellFormatter },
                        {name: nls.numberOfItems, field: 'item_count', width:'120px', styles:'text-align:center;' },
                        {name: nls.w_o + nls.notListedItem , field: 'printWithoutNotListedItem', width:'120px', styles:'text-align:center;', formatter: formatter.printCellAddendumWithoutNotListedItemFormatter },
                        {name: nls.w + nls.notListedItem , field: 'printWithNotListedItem', width:'120px', styles:'text-align:center;', formatter: formatter.printCellAddendumWithNotListedItemFormatter }
                    ],
                    currentModuleName: self.currentModuleName
                });
            borderContainer.addChild(toolbar);
            borderContainer.addChild(content);

            return borderContainer;
        }
    });

    return Dialog;
});