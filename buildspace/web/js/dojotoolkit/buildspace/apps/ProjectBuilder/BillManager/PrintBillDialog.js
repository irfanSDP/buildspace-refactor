define('buildspace/apps/ProjectBuilder/BillManager/PrintBillDialog',[
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

    var PrintBillGrid = declare('buildspace.apps.ProjectBuilder.BillManager.PrintBillGrid', dojox.grid.EnhancedGrid, {
        billId: 0,
        region: 'center',
        style: "border-top:none;",
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex),
                    colField = e.cell.field;
                    if(colField == 'print' && item.id > 0 ){
                        window.open('BQPdf/'+self.billId+'/'+item.id+'/'+item._csrf_token, '_blank');
                        return window.focus();
                    }else if(colField == 'print' && item.id == 0 ){
                        window.open('BQSummary/'+self.billId+'/'+item._csrf_token, '_blank');
                        return window.focus();
                    }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        }
    });

    var Formatter = declare("buildspace.apps.ProjectBuilder.BillManager.PrintBillGrid.CellFormatter", null, {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            return parseInt(rowIdx)+1;
        },
        descriptionCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);
            return item.id == 0 ? nls.summaryPage : cellValue;
        },
        printCellFormatter: function(cellValue, rowIdx){
            return cellValue ? '<a href="#" onclick="return false;">'+nls.print+'</a>' : null;
        }
    });

    return declare('buildspace.apps.ProjectBuilder.BillManager.ImportResourceDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.printBQ,
        billId: null,
        bqCSRFToken: null,
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
                style:"padding:0px;width:600px;height:350px;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border:0px;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.printAll,
                    iconClass: "icon-16-container icon-16-print",
                    style:"outline:none!important;",
                    onClick: function() {
                        window.open('BQPrintAll/'+self.billId+'/'+self.bqCSRFToken, '_blank');
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
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.description, field: 'description', width:'auto', formatter: formatter.descriptionCellFormatter },
                        {name: nls.numberOfItems, field: 'item_count', width:'120px', styles:'text-align:center;' },
                        {name: nls.action, field: 'print', width:'80px', styles:'text-align:center;', formatter: formatter.printCellFormatter }
                    ]
                });
            borderContainer.addChild(toolbar);
            borderContainer.addChild(content);

            return borderContainer;
        }
    });
});