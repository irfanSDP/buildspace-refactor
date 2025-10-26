define('buildspace/apps/Tendering/PrintFinalBQDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/keys',
    "dojo/dom-style",
    "dijit/Menu",
    "dijit/Dialog",
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "dijit/PopupMenuItem",
    'dojo/i18n!buildspace/nls/PrintBillDialog'
], function(declare, lang, keys, domStyle, Menu, DijitDialog, DropDownButton, DropDownMenu, MenuItem, PopupMenuItem, nls){

    var PrintFinalBQGrid = declare('buildspace.apps.Tendering.PrintFinalBQGrid', dojox.grid.EnhancedGrid, {
        project: null,
        withPrice: true,
        region: 'center',
        style: "border-top:none;",
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this,
                project = this.project;

            this.inherited(arguments);

            this.on('RowClick', function(e){
                var item = self.getItem(e.rowIndex),
                    colField = e.cell.field;

                if(!isNaN(parseInt(String(item.id))) && colField == 'print'){
                    switch(parseInt(String(item.type))) {
                        case buildspace.constants.TYPE_BILL:
                            var val = self.withPrice ? '1' : '0';
                            window.open('finalBQPdf/' + String(item.id) + '/'+val+'/' + project._csrf_token, '_blank');
                            return window.focus();
                            break;
                        default:
                        // do nothing
                    }
                }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        }
    });

    var Formatter = declare("buildspace.apps.Tendering.PrintFinalBQGrid.CellFormatter", null, {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            return parseInt(rowIdx)+1;
        },
        printCellFormatter: function(cellValue, rowIdx){
            var item = this.grid.getItem(rowIdx);

            return (!isNaN(parseInt(String(item.id)))) ? '<a href="javascript:void(0);">'+nls.print+'</a>' : null;
        }
    });

    return declare('buildspace.apps.Tendering.PrintFinalBQDialog', DijitDialog, {
        style:"padding:0px;margin:0px;",
        title: nls.printFinalBQ,
        project: null,
        tenderAlternative: null,
        withPrice: true,
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
            var borderContainer = new dijit.layout.BorderContainer({
                    style:"padding:0px;width:800px;height:350px;",
                    gutters: false
                }),
                toolbar = new dijit.Toolbar({
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

            borderContainer.addChild(toolbar);

            var tenderAlternativeId = (this.tenderAlternative) ? parseInt(String(this.tenderAlternative.id)) : -1;
            var formatter = new Formatter(),
                store = dojo.data.ItemFileWriteStore({
                    url: "finalBQPrintList/"+this.project.id+"/"+tenderAlternativeId
                });

            borderContainer.addChild(PrintFinalBQGrid({
                project: this.project,
                withPrice: this.withPrice,
                store: store,
                structure: [
                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                    {name: nls.description, field: 'title', width:'auto' },
                    {name: nls.numberOfItems, field: 'item_count', width:'120px', styles:'text-align:center;' },
                    {name: '&nbsp;', field: 'print', width:'80px', styles:'text-align:center;', formatter: formatter.printCellFormatter }
                ]
            }));

            return borderContainer;
        }
    });
});
