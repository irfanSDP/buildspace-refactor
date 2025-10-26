define('buildspace/apps/SubPackage/BillPrintoutSetting/PrintBillDialog',[
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

    var PrintSubPackageBillGrid = declare('buildspace.apps.SubPackage.BillPrintoutSetting.PrintSubPackageBillGrid', dojox.grid.EnhancedGrid, {
        subPackageId: -1,
        subContractorId: -1,
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
                        window.open('SubPackageBQPdf/'+self.subPackageId+'/'+item.id+'/'+item._csrf_token+'/0', '_blank');
                        return window.focus();
                    } else if(colField == 'printWithoutPrice' && item.id == 0 ){
                        window.open('SubPackageBQSummary/'+self.subPackageId+'/'+item._csrf_token+'/0', '_blank');
                        return window.focus();
                    } else if(colField == 'printWithPrice' && item.id > 0 ){
                        if(this.subContractorId > 0)
                        {
                            window.open('SubPackageContractorBQPdf/'+self.subPackageId+'/'+item.id+'/'+this.subContractorId+'/'+item._csrf_token+'/1', '_blank');
                        }
                        else
                        {
                            window.open('SubPackageBQPdf/'+self.subPackageId+'/'+item.id+'/'+item._csrf_token+'/1', '_blank');
                        }
                        return window.focus();
                    } else if(colField == 'printWithPrice' && item.id == 0 ){

                        if(this.subContractorId > 0)
                        {
                            window.open('SubPackageContractorBQSummary/'+self.subPackageId+'/'+this.subContractorId+'/'+item._csrf_token+'/1', '_blank');
                        }
                        else
                        {
                           window.open('SubPackageBQSummary/'+self.subPackageId+'/'+item._csrf_token+'/1', '_blank');
                        }
                        
                        return window.focus();
                    }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        }
    });

    var Formatter = declare("buildspace.apps.SubPackage.BillPrintoutSetting.PrintBillGrid.CellFormatter", null, {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            return parseInt(rowIdx)+1;
        },
        descriptionCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            return item.id == 0 ? nls.summaryPage : cellValue;
        },
        printCellAddendumWithPriceFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);

            return item.id >= 0 ? '<a href="javascript:void(0);">'+nls.print+'</a>' : null;
        }
    });

    return declare('buildspace.apps.SubPackage.BillPrintoutSetting.PrintSubPackageBillGridDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.printBQ,
        subContractorId: -1,
        bqCSRFToken: null,
        subPackage: null,
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
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            var formatter = new Formatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "printSubPackage/getPrintList/id/"+self.subPackage.id
                }),
                content = PrintSubPackageBillGrid({
                    subPackageId: self.subPackage.id,
                    subContractorId: self.subContractorId,
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.descriptionCellFormatter },
                        {name: nls.action, field: 'printWithPrice', width:'120px', styles:'text-align:center;', formatter: formatter.printCellAddendumWithPriceFormatter }
                    ]
                });
            borderContainer.addChild(toolbar);
            borderContainer.addChild(content);

            return borderContainer;
        }
    });
});