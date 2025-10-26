define('buildspace/apps/PostContractReport/VariationOrder/VariationOrderClaim',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/_base/html',
    "dijit/focus",
    'dojo/_base/event',
    'dojo/keys',
    'dojox/grid/EnhancedGrid',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
], function(declare, lang, html, focusUtil, evt, keys, EnhancedGrid, GridFormatter, nls){
    var IN_PROGRESS = 1;
    var LOCKED = 2;
    var CustomFormatter = {
        descriptionCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            cell.customClasses.push('disable-cell');
            if(item.id > 0){
                return nls.version+' '+nls.no+' '+cellValue;
            }else{
                return "&nbsp;";
            }
        },
        currentViewingCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(item.id > 0){
                if(cellValue){
                    return '<span class="icon-16-container icon-16-checkmark2" style="margin:0px auto; display:block;"></span>';
                }else{
                    return '<a href="#" onclick="return false;">'+nls.printThisRevision+'</a>';
                }
            }else{
                cell.customClasses.push('disable-cell');
                return "&nbsp;";
            }
        },
        statusCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);
            if(!item.can_be_edited[0]){
                cell.customClasses.push('disable-cell');
            }
            if(item.id > 0){
                if(parseInt(cellValue) == IN_PROGRESS){
                    return nls.addendumProgressing;
                }else{
                    return nls.addendumLocked;
                }
            }else{
                return "&nbsp;";
            }
        }
    };

    var VariationOrderClaimGrid = declare('buildspace.apps.PostContractReport.VariationOrder.VariationOrderClaimEnhancedGrid', EnhancedGrid, {
        style: "border-top:none;",
        rowSelector: '0px',
        variationOrder: null,
        variationOrderItemContainer: null,
        region: 'center',
        constructor:function(args){
            var formatter = new GridFormatter(),
                statusOptions = {
                options: [
                    nls.addendumProgressing,
                    nls.addendumLocked
                ],
                values: [
                    IN_PROGRESS,
                    LOCKED
                ]
            };
            this.store = new dojo.data.ItemFileWriteStore({
                url:"variationOrder/getClaimList/id/"+args.variationOrder.id,
                clearOnClose: true
            });
            this.structure = [
                {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                {name: nls.claimVersion, field: 'revision', width:'auto', formatter: CustomFormatter.descriptionCellFormatter },
                {name: nls.currentPrintingRevision, field: 'is_viewing', width:'140px', styles:'text-align: center;', formatter: CustomFormatter.currentViewingCellFormatter},
                {name: nls.status, field: 'status', width:'120px', styles:'text-align: center;', formatter: CustomFormatter.statusCellFormatter}
            ];
        },
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            var self = this;
            self.inherited(arguments);

            this.on('CellClick', function(e) {
                var colField = e.cell.field,
                    item = self.getItem(e.rowIndex);

                if (colField == 'is_viewing' && item && item.id > 0 && !item.is_viewing[0]){
                    self.changeCurrentViewingRevision(item);
                }
            });
        },
        changeCurrentViewingRevision: function(item){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;
            pb.show();

            dojo.xhrPost({
                url: "variationOrder/viewClaimRevision",
                content: { id: item.id, _csrf_token: item._csrf_token },
                handleAs: 'json',
                load: function(resp) {
                    if(resp.success){
                        var store = self.store;

                        dojo.forEach(resp.items, function(node){
                            store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(item){
                                for(var property in node){
                                    if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                        store.setValue(item, property, node[property]);
                                    }
                                }
                            }});
                        });
                        store.save();

                        var claimQuery = dojo.xhrGet({
                            url: "variationOrder/getClaimStatus",
                            content: {id: self.variationOrder.id},
                            handleAs: "json"
                        });
                        claimQuery.then(function(status){
                            var unitQuery = dojo.xhrGet({
                                url: "variationOrder/getUnits",
                                handleAs: "json"
                            });
                            unitQuery.then(function(uom){
                                self.variationOrderItemContainer.createVariationOrderItemGrid(uom, status, false);
                            });
                        });
                    }
                    pb.hide();
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                },
                error: function(error) {
                    self.selection.clear();
                    self.disableToolbarButtons(true);
                    pb.hide();
                }
            });
        }
    });

    return declare('buildspace.apps.PostContractReport.VariationOrder.VariationOrderClaim', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        variationOrder: null,
        variationOrderItemContainer: null,
        claimStatus: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this,
                grid = new VariationOrderClaimGrid({variationOrder: this.variationOrder, variationOrderItemContainer: this.variationOrderItemContainer});

            this.addChild(new dijit.layout.ContentPane({style: 'width:100%', content:grid, region:'center'}));
        }
    });
});