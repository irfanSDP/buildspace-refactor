define('buildspace/apps/Tendering/BillManager/AddResourceCategoryDialog',[
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
    "./buildUpRateSummary",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/AddResourceCategoryDialog'
], function(declare, lang, connect, when, html, dom, keys, domStyle, currency, BuildUpGrid, BuildUpRateSummary, GridFormatter, nls){

    var AddResourceCategoryGrid = declare('buildspace.apps.Tendering.BillManager.AddResourceCategoryGrid', dojox.grid.EnhancedGrid, {
        billId: 0,
        billItem: null,
        currencyAbbr: null,
        billGridStore: null,
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
                            url: 'billBuildUpRate/resourceCategoryAdd',
                            content: { bid: self.billItem.id, rid: resourceItem.id, _csrf_token: resourceItem._csrf_token },
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success){
                                    store.setValue(resourceItem, 'resource_library_exists', resp.resource_library_exists);
                                    store.save();

                                    var buildUpRateSummaryWidget = dijit.byId('buildUpRateSummary-'+self.billItem.id);
                                    if(!buildUpRateSummaryWidget){
                                        buildUpRateSummaryWidget = new BuildUpRateSummary({
                                            id: 'buildUpRateSummary-'+self.billItem.id,
                                            itemId: self.billItem.id,
                                            container: self.baseContainer,
                                            billGridStore: self.billGridStore,
                                            _csrf_token: resourceItem._csrf_token,
                                            currentBillLockedStatus: resp.bill_locked_status,
                                            currentBillVersion: resp.bill_version,
                                            currentItemVersion: self.billItem.version[0]
                                        });

                                        self.baseContainer.addChild(buildUpRateSummaryWidget);
                                    }

                                    var accContainer = dijit.byId('accordian_'+self.billId+'_'+self.billItem.id+'-container'),
                                        resource = resp.resource,
                                        uom = resp.uom,
                                        formatter = new GridFormatter(),
                                        buildUpStore = new dojo.data.ItemFileWriteStore({
                                            url:"billBuildUpRate/getBuildUpRateItemList/bill_item_id/"+self.billItem.id+"/resource_id/"+resource.id,
                                            clearOnClose: true
                                        }),
                                        grid = new BuildUpGrid({
                                            resource: resource,
                                            BQItem: self.billItem,
                                            gridOpts: {
                                                itemId: self.billItem.id,
                                                addUrl:'billBuildUpRate/buildUpRateItemAdd',
                                                updateUrl:'billBuildUpRate/buildUpRateItemUpdate',
                                                deleteUrl:'billBuildUpRate/buildUpRateItemDelete',
                                                pasteUrl:'billBuildUpRate/buildUpRateItemPaste',
                                                store: buildUpStore,
                                                buildUpSummaryWidget: buildUpRateSummaryWidget,
                                                structure: [
                                                    {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                                                    {name: nls.description, field: 'description', width:'auto', formatter: formatter.linkedCellFormatter },
                                                    {name: nls.number, field: 'number-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                                    {name: nls.constant, field: 'constant-value', width:'100px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                                    {name: nls.qty, field: 'quantity-value', width:'70px', styles:'text-align:right;', formatter: formatter.formulaNumberCellFormatter},
                                                    {name: nls.unit, field: 'uom_id', width:'70px', styles:'text-align:center;', formatter: formatter.linkedUnitIdCellFormatter},
                                                    {name: nls.rate, field: 'rate-value', width:'120px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                                    {name: nls.total, field: 'total', width:'120px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter},
                                                    {name: nls.wastage+" (%)", field: 'wastage-value', width:'70px', styles:'text-align:right;', formatter: formatter.formulaCurrencyCellFormatter},
                                                    {name: nls.lineTotal, field: 'line_total', width:'120px', styles:'text-align:right;', formatter: formatter.linkedCurrencyCellFormatter}
                                                ],
                                                currentBillLockedStatus: resp.bill_locked_status,
                                                currentBillVersion: resp.bill_version,
                                                currentItemVersion: self.billItem.version[0]
                                            }
                                        });

                                    var accContentPane = new dijit.layout.ContentPane({
                                        title: resource.name+'<span style="color:blue;float:right;">'+self.currencyAbbr+'&nbsp;'+currency.format(0)+'</span>',
                                        style: "padding:0px;border:0px;",
                                        doLayout: false,
                                        id: 'accPane-'+resource.id+'-'+self.billItem.id,
                                        content: grid
                                    });

                                    accContainer.addChild(accContentPane);
                                    accContainer.selectChild(accContentPane);

                                    var defaultEmptyPane = dijit.byId('accPane-empty_resource-'+self.billItem.id);

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
                            url: 'billBuildUpRate/resourceCategoryDelete',
                            content: { bid: self.billItem.id, rid: resourceItem.id, _csrf_token: resourceItem._csrf_token },
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success){
                                    store.setValue(resourceItem, 'resource_library_exists', false);
                                    store.save();

                                    var accPane = dijit.byId('accPane-'+resp.rid+'-'+self.billItem.id),
                                        accContainer = dijit.byId('accordian_'+self.billId+'_'+self.billItem.id+'-container'),
                                        buildUpRateSummaryWidget = dijit.byId('buildUpRateSummary-'+self.billItem.id);

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
                                                id: 'accPane-empty_resource-'+self.billItem.id,
                                                content: '<div style="text-align:center;"><p><h1>'+nls.emptyResourceCategory+'</h1></p></div> '
                                            }));

                                            var xhrUpdateBuildUpSum = {
                                                url: 'billBuildUpRate/getBuildUpSummary',
                                                content: { id: self.billItem.id },
                                                handleAs: 'json',
                                                load: function(resp) {
                                                    //we just need to update build up summary value in the db. Nothing to do with the return data since the resource category is empty
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

    var Formatter = declare("buildspace.apps.Tendering.BillManager.AddResourceCategoryGrid.CellFormatter", null, {
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

    var Dialog = declare('buildspace.apps.Tendering.BillManager.AddResourceCategoryDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.addResourceCategory,
        billId: null,
        billItem: null,
        currencyAbbr: null,
        billGridStore: null,
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
                    url: "billBuildUpRate/getResourceList/item_id/"+self.billItem.id
                }),
                content = AddResourceCategoryGrid({
                    billId: self.billId,
                    billItem: self.billItem,
                    currencyAbbr: self.currencyAbbr,
                    billGridStore: self.billGridStore,
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