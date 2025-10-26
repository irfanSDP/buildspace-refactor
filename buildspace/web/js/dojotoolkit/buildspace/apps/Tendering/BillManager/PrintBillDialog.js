define('buildspace/apps/Tendering/BillManager/PrintBillDialog',[
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

    var PrintBillGrid = declare('buildspace.apps.Tendering.BillManager.PrintBillGrid', dojox.grid.EnhancedGrid, {
        billId: 0,
        statusId: null,
        region: 'center',
        style: "border-top:none;",
        currentModuleName: null,
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex),
                    colField = e.cell.field;

                    if (colField == 'printWithoutPrice' && item.id > 0 ){
                    	if(self.statusId == buildspace.constants.STATUS_IMPORT_SUB_PACKAGE || self.statusId == buildspace.constants.STATUS_POSTCONTRACT_SUB_PACKAGE) {
	                    	window.open('TenderSubPackagePdf/'+self.billId+'/'+item.id+'/'+item._csrf_token+'/0/'+self.currentModuleName, '_blank');
	                    } else {
	                    	window.open('BQPdf/'+self.billId+'/'+item.id+'/'+item._csrf_token+'/0/'+self.currentModuleName, '_blank');
	                    }
                        return window.focus();
                    } else if(colField == 'printWithoutPrice' && item.id == 0 ){
                    	if(self.statusId == buildspace.constants.STATUS_IMPORT_SUB_PACKAGE || self.statusId == buildspace.constants.STATUS_POSTCONTRACT_SUB_PACKAGE) {
	                    	window.open('TenderSubPackageSummary/'+self.billId+'/'+item._csrf_token+'/0/'+self.currentModuleName, '_blank');
	                    } else {
	                    	window.open('BQSummary/'+self.billId+'/'+item._csrf_token+'/0/'+self.currentModuleName, '_blank');
	                    }
                        return window.focus();
                    } else if(colField == 'printWithPrice' && item.id > 0 ){
                    	if(self.statusId == buildspace.constants.STATUS_IMPORT_SUB_PACKAGE || self.statusId == buildspace.constants.STATUS_POSTCONTRACT_SUB_PACKAGE) {
	                    	window.open('TenderSubPackagePdf/'+self.billId+'/'+item.id+'/'+item._csrf_token+'/1/'+self.currentModuleName, '_blank');
	                    } else {
	                    	window.open('BQPdf/'+self.billId+'/'+item.id+'/'+item._csrf_token+'/1/'+self.currentModuleName, '_blank');
	                    }
                        return window.focus();
                    } else if(colField == 'printWithPrice' && item.id == 0 ){
                    	if(self.statusId == buildspace.constants.STATUS_IMPORT_SUB_PACKAGE || self.statusId == buildspace.constants.STATUS_POSTCONTRACT_SUB_PACKAGE) {
	                    	window.open('TenderSubPackageSummary/'+self.billId+'/'+item._csrf_token+'/1/'+self.currentModuleName, '_blank');
	                    } else {
	                    	window.open('BQSummary/'+self.billId+'/'+item._csrf_token+'/1/'+self.currentModuleName, '_blank');
	                    }
                        return window.focus();
                    }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        }
    });

    var Formatter = declare("buildspace.apps.Tendering.BillManager.PrintBillGrid.CellFormatter", null, {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            return parseInt(rowIdx)+1;
        },
        descriptionCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            return item.id == 0 ? nls.summaryPage : cellValue;
        },
        printCellAddendumWithoutPriceFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);

            return item.id >= 0 ? '<a href="javascript:void(0);">'+nls.printWithoutPrice+'</a>' : null;
        },
        printCellAddendumWithPriceFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);

            return item.id >= 0 ? '<a href="javascript:void(0);">'+nls.printWithPrice+'</a>' : null;
        }
    });

    return declare('buildspace.apps.Tendering.BillManager.PrintBillGridDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.printBQ,
        billId: null,
        statusId: null,
        bqCSRFToken: null,
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
            var key = e.keyCode;
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
                    label: nls.printAllWithoutPrice,
                    iconClass: "icon-16-container icon-16-print",
                    style:"outline:none!important;",
                    onClick: function() {
                    	if(self.statusId == buildspace.constants.STATUS_IMPORT_SUB_PACKAGE || self.statusId == buildspace.constants.STATUS_POSTCONTRACT_SUB_PACKAGE) {
	                    	window.open('TenderSubPackagePrintAll/'+self.billId+'/'+self.bqCSRFToken+'/0/'+self.currentModuleName, '_blank');
	                    } else {
	                    	window.open('BQPrintAll/'+self.billId+'/'+self.bqCSRFToken+'/0/'+self.currentModuleName, '_blank');
	                    }
                        return window.focus();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.printAllWithPrice,
                    iconClass: "icon-16-container icon-16-print",
                    style:"outline:none!important;",
                    onClick: function() {
                    	if(self.statusId == buildspace.constants.STATUS_IMPORT_SUB_PACKAGE || self.statusId == buildspace.constants.STATUS_POSTCONTRACT_SUB_PACKAGE) {
	                    	window.open('TenderSubPackagePrintAll/'+self.billId+'/'+self.bqCSRFToken+'/1/'+self.currentModuleName, '_blank');
	                    } else {
	                    	window.open('BQPrintAll/'+self.billId+'/'+self.bqCSRFToken+'/1/'+self.currentModuleName, '_blank');
	                    }
                        return window.focus();
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

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
                    url: "printBQ/getPrintList/id/"+self.billId
                }),
                content = PrintBillGrid({
                    billId: self.billId,
                    statusId: self.statusId,
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.descriptionCellFormatter },
                        {name: nls.numberOfItems, field: 'item_count', width:'120px', styles:'text-align:center;' },
                        {name: nls.action, field: 'printWithoutPrice', width:'120px', styles:'text-align:center;', formatter: formatter.printCellAddendumWithoutPriceFormatter },
                        {name: nls.action, field: 'printWithPrice', width:'120px', styles:'text-align:center;', formatter: formatter.printCellAddendumWithPriceFormatter }
                    ],
                    currentModuleName: self.currentModuleName
                });
            borderContainer.addChild(toolbar);
            borderContainer.addChild(content);

            return borderContainer;
        }
    });
});