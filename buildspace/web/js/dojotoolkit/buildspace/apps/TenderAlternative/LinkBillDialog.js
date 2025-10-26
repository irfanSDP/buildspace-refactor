define('buildspace/apps/TenderAlternative/LinkBillDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/on",
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    'buildspace/widget/grid/Filter',
    "dojox/grid/enhanced/plugins/IndirectSelection",
    'dojo/i18n!buildspace/nls/TenderAlternative'
], function(declare, lang, connect, keys, domStyle, on, EnhancedGrid, GridFormatter, Filter, IndirectSelection, nls){

    var BillGrid = declare('buildspace.apps.TenderAlternative.LinkBillGrid', EnhancedGrid, {
        style: "border-top:none;",
        containerDialog: null,
        tenderAlternative: null,
        tenderAlternativeGrid: null,
        region: 'center',
        constructor: function(args){
            this.plugins = {indirectSelection: {headerSelector:true, width:"20px", styles:"text-align:center;"}}
            this.inherited(arguments);
        },
        postCreate: function(){
            var self = this;
            this.inherited(arguments);
        },
        canSort: function(inSortInfo){
            return false;
        },
        save: function(){
            if(this.selection.getSelected().length > 0){
                var ids = [];
                var grid = this;

                dojo.forEach(grid.selection.getSelected(), function(item){
                    if(item && parseInt(String(item.id)) > 0 &&
                        parseInt(String(item.type)) == buildspace.constants.TYPE_BILL ||
                        parseInt(String(item.type)) == buildspace.constants.TYPE_SUPPLY_OF_MATERIAL_BILL ||
                        parseInt(String(item.type)) == buildspace.constants.TYPE_SCHEDULE_OF_RATE_BILL){
                            ids.push(parseInt(String(item.id)));
                    }
                });

                if(ids.length > 0){
                    var pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.savingData+'. '+nls.pleaseWait+'...'
                    });

                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: 'linkTenderAlternativeBills',
                            content: {
                                id: parseInt(String(grid.tenderAlternative.id)),
                                _csrf_token: grid.tenderAlternative._csrf_token,
                                bids: ids.toString()
                            },
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success && grid.tenderAlternativeGrid){
                                    grid.tenderAlternativeGrid.reload();
                                }
                                pb.hide();
                                grid.selection.clear();

                                grid.containerDialog.hide();
                            },
                            error: function(error) {
                                pb.hide();
                                grid.selection.clear();
                                grid.containerDialog.hide();
                            }
                        });
                    });

                }else{
                    grid.selection.clear();
                    grid.containerDialog.hide();
                }
            }else{
                buildspace.dialog.alert(nls.noBillAlert, nls.pleaseSelectBill+'.', 90, 320);
            }
        }
    });

    var BillGridContainer = declare('buildspace.apps.TenderAlternative.LinkBillGridContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        containerDialog: null,
        tenderAlternative: null,
        tenderAlternativeGrid: null,
        postCreate: function(){
            this.inherited(arguments);

            var formatter = new GridFormatter();
            var Formatter = {
                rowCountCellFormatter: function (cellValue, rowIdx) {
                    return cellValue > 0 ? cellValue : '';
                },
                treeCellFormatter: function (cellValue, rowIdx) {
                    var item = this.grid.getItem(rowIdx);
                    var level = parseInt(String(item.level)) * 16;
                    cellValue = cellValue == null ? '&nbsp' : cellValue;
                    if (parseInt(String(item.type)) < buildspace.constants.TYPE_BILL) {
                        cellValue = '<b>' + cellValue + '</b>';
                    }
                    cellValue = '<div class="treeNode" style="padding-left:' + level + 'px;"><div class="treeContent">' + cellValue + '&nbsp;</div></div>';
                    return cellValue;
                }
            };

            var grid = this.grid = new BillGrid({
                containerDialog: this.containerDialog,
                tenderAlternative: this.tenderAlternative,
                tenderAlternativeGrid: this.tenderAlternativeGrid,
                structure: [{
                    name: 'No.',
                    field: 'count',
                    width: '30px',
                    styles: 'text-align:center;',
                    formatter: Formatter.rowCountCellFormatter
                }, {
                    name: nls.description, field: 'title', width: 'auto', formatter: Formatter.treeCellFormatter
                }, {
                    name: nls.overallTotal,
                    field: 'overall_total_after_markup',
                    width: '150px',
                    styles: 'text-align: right;',
                    formatter: formatter.unEditableCurrencyCellFormatter
                }],
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose: true,
                    url: "getTenderAlternativeUnlinkBills/" + parseInt(String(this.tenderAlternative.id))
                })
            });

            this.addChild(grid);

            this.addChild(new Filter({
                region: 'top',
                editableGrid: false,
                grid: grid,
                filterFields: [
                    {'title':nls.description}
                ]
            }));
        },
        save: function(){
            this.grid.save();
        }
    });

    return declare('buildspace.apps.TenderAlternative.LinkBillDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        tenderAlternative: null,
        tenderAlternativeGrid: null,
        title: nls.tagBillsToTenderAlternatives,
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
                style:"padding:0px;width:980px;height:580px;",
                gutters: false
            });

            var gridContainer = new BillGridContainer({
                region: "center",
                containerDialog: this,
                tenderAlternative: this.tenderAlternative,
                tenderAlternativeGrid: this.tenderAlternativeGrid
            });
            
            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(gridContainer, 'save')
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

            borderContainer.addChild(toolbar);
            borderContainer.addChild(gridContainer);

            return borderContainer;
        }
    });
});