define('buildspace/apps/BQLibrary/AddResourceCategoryDialog',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dojo/currency",
    "./buildUpGrid",
    "./buildUpSummary",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/AddResourceCategoryDialog'
], function(declare, lang, connect, when, html, dom, keys, domStyle, currency, BuildUpGrid, BuildUpRateSummary, GridFormatter, nls){

    var AddResourceCategoryGrid = declare('buildspace.apps.BQLibrary.AddResourceCategoryGrid', dojox.grid.EnhancedGrid, {
        libraryId: 0,
        BQItem: null,
        currencyAbbr: null,
        BQItemGridStore: null,
        baseContainer: null,
        region: 'center',
        style: "border-top:none;",
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);
            this.on('RowClick', function(e){
                var resourceItem = self.getItem(e.rowIndex),
                    colField = e.cell.field,
                    store = self.store;

                if(colField == 'resource_library_exists' && resourceItem.id > 0 ){
                    var pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.pleaseWait+'...'
                    });

                    if(!resourceItem.resource_library_exists[0]){
                        pb.show();
                        var xhrArgs = {
                            url: 'bqLibrary/resourceCategoryAdd',
                            content: { bqid: self.BQItem.id, rid: resourceItem.id, _csrf_token: resourceItem._csrf_token },
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success){
                                    store.setValue(resourceItem, 'resource_library_exists', resp.resource_library_exists);
                                    store.save();

                                    var buildUpRateSummaryWidget = dijit.byId('buildUpRateSummary-'+self.BQItem.id);
                                    if(!buildUpRateSummaryWidget){
                                        buildUpRateSummaryWidget = new BuildUpRateSummary({
                                            id: 'buildUpRateSummary-'+self.BQItem.id,
                                            itemId: self.BQItem.id,
                                            container: self.baseContainer,
                                            bqItemGridStore: self.BQItemGridStore,
                                            _csrf_token: resourceItem._csrf_token
                                        });

                                        self.baseContainer.addChild(buildUpRateSummaryWidget);
                                    }

                                   var accContainer = dijit.byId('accordian_'+self.libraryId+'_'+self.BQItem.id+'-container'),
                                        resource = resp.resource,
                                        uom = resp.uom,
                                        formatter = new GridFormatter(),
                                        buildUpStore = new dojo.data.ItemFileWriteStore({
                                            url:"bqLibrary/getBuildUpRateItemList/item_id/"+self.BQItem.id+"/resource_id/"+resource.id,
                                            clearOnClose: true
                                        }),
                                        grid = BuildUpGrid({
                                            resource: resource,
                                            bqItemId: self.BQItem.id,
                                            gridOpts: {
                                                itemId: self.BQItem.id,
                                                addUrl:'bqLibrary/buildUpRateItemAdd',
                                                updateUrl:'bqLibrary/buildUpRateItemUpdate',
                                                deleteUrl:'bqLibrary/buildUpRateItemDelete',
                                                pasteUrl:'bqLibrary/buildUpRateItemPaste',
                                                store: buildUpStore,
                                                buildUpSummaryWidget: buildUpRateSummaryWidget,
                                                structure: [
                                                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                                    {name: nls.description, field: 'description', width:'auto', editable:true, cellType:'buildspace.widget.grid.cells.Textarea', formatter: formatter.linkedCellFormatter },
                                                    {name: nls.number, field: 'number-value', width:'100px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                                    {name: nls.constant, field: 'constant-value', width:'100px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                                    {name: nls.qty, field: 'quantity-value', width:'70px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaNumberCellFormatter},
                                                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', editable:true, cellType:'dojox.grid.cells.Select', options: uom.options, values:uom.values, formatter: formatter.linkedUnitIdCellFormatter},
                                                    {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                                    {name: nls.total, field: 'total', width:'120px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter},
                                                    {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', editable:true, cellType:'buildspace.widget.grid.cells.FormulaTextBox', formatter: formatter.formulaCurrencyCellFormatter},
                                                    {name: nls.lineTotal, field: 'line_total', width:'120px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter}
                                                ]
                                            }
                                        });

                                    var accContentPane = new dijit.layout.ContentPane({
                                        title: resource.name+'<span style="color:blue;float:right;">'+self.currencyAbbr+'&nbsp;'+currency.format(0)+'</span>',
                                        style: "padding:0px;border:0px;",
                                        doLayout: false,
                                        id: 'accPane-'+resource.id+'-'+self.BQItem.id,
                                        content: grid
                                    });

                                    accContainer.addChild(accContentPane);
                                    accContainer.selectChild(accContentPane);

                                    var defaultEmptyPane = dijit.byId('accPane-empty_resource-'+self.BQItem.id);

                                    if(defaultEmptyPane){
                                        accContainer.removeChild(defaultEmptyPane);
                                        defaultEmptyPane.destroyRecursive();
                                    }
                                }
                                pb.hide();
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        }
                        dojo.xhrPost(xhrArgs);
                    }
                    else
                    {
                        var xhrArgs = {
                            url: 'bqLibrary/resourceCategoryDelete',
                            content: { bqid: self.BQItem.id, rid: resourceItem.id, _csrf_token: resourceItem._csrf_token },
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success){
                                    store.setValue(resourceItem, 'resource_library_exists', false);
                                    store.save();

                                    var accPane = dijit.byId('accPane-'+resp.rid+'-'+self.BQItem.id),
                                        accContainer = dijit.byId('accordian_'+self.libraryId+'_'+self.BQItem.id+'-container'),
                                        buildUpRateSummaryWidget = dijit.byId('buildUpRateSummary-'+self.BQItem.id);

                                    if(accContainer && accPane){
                                        accContainer.removeChild(accPane);
                                        accPane.destroyRecursive();
                                    }

                                    if(buildUpRateSummaryWidget){
                                        if(resp.is_last_resource){
                                            self.baseContainer.removeChild(buildUpRateSummaryWidget);
                                            buildUpRateSummaryWidget.destroyRecursive();

                                            accContainer.addChild(new dijit.layout.ContentPane({
                                                title: nls.emptyResourceCategoryTitle,
                                                style: "padding:0px;border:0px;",
                                                doLayout: false,
                                                id: 'accPane-empty_resource-'+self.BQItem.id,
                                                content: '<div style="text-align:center;"><p><h1>'+nls.emptyResourceCategory+'</h1></p></div> '
                                            }));

                                            var BQItemGridStore = self.BQItemGridStore;

                                            var xhrUpdateBuildUpSum = {
                                                url: 'bqLibrary/getBuildUpSummary',
                                                content: { id: self.BQItem.id },
                                                handleAs: 'json',
                                                load: function(resp) {
                                                    // set the SoR's item's rate to follow build up's final value
                                                    BQItemGridStore.setValue(self.BQItem, 'rate-final_value', resp.final_cost);
                                                    BQItemGridStore.setValue(self.BQItem, 'updated_at', resp.updated_at);
                                                    BQItemGridStore.save();
                                                },
                                                error: function(error) {
                                                }
                                            }
                                            dojo.xhrGet(xhrUpdateBuildUpSum);
                                        }else{
                                            buildUpRateSummaryWidget.refreshTotalCost();
                                        }
                                    }
                                }
                                pb.hide();
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        }

                        var onYes = function(){
                            pb.show();
                            dojo.xhrPost(xhrArgs);
                        };

                        var content = '<div>'+nls.areYouSureDeleteResourceCategory+'</div>';
                        buildspace.dialog.confirm(nls.confirmation,content,60,280, onYes);
                    }
                }
            });
        },
        dodblclick: function(e){
            this.onRowDblClick(e);
        }
    });

    var Formatter = declare("buildspace.apps.BQLibrary.AddResourceCategoryGrid.CellFormatter", null, {
        rowCountCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            return item.id > 0 ? parseInt(rowIdx)+1 : '&nbsp;';
        },
        actionCellFormatter: function(cellValue, rowIdx){
            var txt       = cellValue ? nls.remove : nls.add;
            var item      = this.grid.getItem(rowIdx);
            var timeStamp = Math.round(new Date().getTime() / 1000);
            return item.id > 0 ? '<a href="#'+timeStamp+'" onclick="return false;">'+txt+'</a>' : null;
        }
    });

    var Dialog = declare('buildspace.apps.BQLibrary.AddResourceCategoryDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.addResourceCategory,
        libraryId: null,
        BQItem: null,
        currencyAbbr: null,
        BQItemGridStore: null,
        baseContainer: null,
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
                style:"padding:0px;width:500px;height:250px;",
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
                    url: "bqLibrary/getResourceList/item_id/"+self.BQItem.id
                }),
                content = AddResourceCategoryGrid({
                    libraryId: self.libraryId,
                    BQItem: self.BQItem,
                    currencyAbbr: self.currencyAbbr,
                    BQItemGridStore: self.BQItemGridStore,
                    baseContainer: self.baseContainer,
                    store: store,
                    structure: [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.name, field: 'name', width:'auto' },
                        {name: nls.action, field: 'resource_library_exists', width:'80px', styles:'text-align:center;', formatter: formatter.actionCellFormatter }
                    ]
                });
            borderContainer.addChild(toolbar);
            borderContainer.addChild(content);

            return borderContainer;
        }
    });

    return Dialog;
});