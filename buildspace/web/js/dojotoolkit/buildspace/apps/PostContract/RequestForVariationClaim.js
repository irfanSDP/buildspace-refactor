define('buildspace/apps/PostContract/RequestForVariationClaim', [
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/aspect',
    'dojo/currency',
    'dojo/number',
    "dijit/layout/ContentPane",
    "./RequestForVariationClaim/RequestForVariationClaimGrid",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
    ], function(declare, lang, aspect, currency, number, ContentPane, RequestForVariationClaimGrid, GridFormatter, nls) {

    return declare('buildspace.apps.PostContract.RequestForVariationClaim', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;border:none;width:100%;height:100%;",
        region: "center",
        gutters: false,
        project: null,
        borderContainerWidget: null,
        locked: false,
        postCreate: function() {
            this.inherited(arguments);

            var self = this;

            var formatter = new GridFormatter();

            var gridStructure = [
                {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter, noresize: true },
                {name: nls.rfvNumber, field: 'rfv_number', width:'64px', styles:'text-align:center;' },
                {name: nls.description, field: 'description', width:'auto'},
                {name: nls.totalClaimAmount, field: 'total_claim_amount', width:'140px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                {name: nls.deductionAtClaimCert, field: 'claim_cert_number', width:'180px', styles:'text-align:center;'}
            ];

            var url = "requestForVariationClaim/getRequestForVariationClaims/pid/"+this.project.id;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "postContract/getClaimGridEditableStatus",
                    handleAs: "json",
                    content: {
                        id: self.project.id
                    }
                }).then(function (resp) {
                    if(!self.locked) self.locked = !resp.editable;

                    var grid = new RequestForVariationClaimGrid({
                        id: 'request_for_variation_claim-'+self.project.id,
                        stackContainerTitle: nls.requestForVariationClaims,
                        pageId: 'request_for_variation_claim-'+self.project.id,
                        project: self.project,
                        type: 'requestForVariationClaim',
                        locked: self.locked,
                        gridOpts: {
                            store: dojo.data.ItemFileWriteStore({
                                url: url,
                                clearOnClose: true
                            }),
                            structure: gridStructure,
                            onRowDblClick: function(e){
                                var _item = this.getItem(e.rowIndex);
                                if(_item && !isNaN(parseInt(String(_item.id)))){
                                    var CustomFormatter = {
                                        editableItemClaimPercentageFormatter: function(cellValue, rowIdx, cell){
                                            var value = number.parse(cellValue),
                                            item = this.grid.getItem(rowIdx);

                                            if(isNaN(value) || value == 0 || value == null){
                                                cellValue = "&nbsp;";
                                            }else{
                                                var formattedValue = number.format(value, {places:2})+"%";
                                                cellValue = value >= 0 ? '<span style="color:blue;">'+formattedValue+'</span>' : '<span style="color:#FF0000">'+formattedValue+'</span>';
                                            }

                                            if (!item.can_claim[0] || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                                                cell.customClasses.push('disable-cell');
                                            }

                                            return cellValue;
                                        },
                                        editableItemClaimAmountCellFormatter: function(cellValue, rowIdx, cell){
                                            var value = number.parse(cellValue),
                                                item = this.grid.getItem(rowIdx);

                                            if(isNaN(value) || value == 0 || value == null){
                                                cellValue = "&nbsp;";
                                            }else{
                                                cellValue = currency.format(value);
                                                cellValue = value >= 0 ? cellValue : '<span style="color:#FF0000">'+cellValue+'</span>';
                                            }

                                            if (!item.can_claim[0] || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID || item.type == buildspace.widget.grid.constants.HIERARCHY_TYPE_ITEM_RATE_ONLY) {
                                                cell.customClasses.push('disable-cell');
                                            }

                                            return cellValue;
                                        }
                                    };

                                    var gridLocked = this.locked;
                                    if(!gridLocked){
                                        gridLocked = (!isNaN(parseInt(String(_item.claim_cert_number))));
                                    }
                                    var g = new RequestForVariationClaimGrid({
                                        id: 'request_for_variation_claim_items-'+_item.id+'-'+this.project.id,
                                        stackContainerTitle: nls.requestForVariationClaimItems,
                                        pageId: 'request_for_variation_claim_items-'+_item.id+'-'+this.project.id+'-page',
                                        project: this.project,
                                        requestForVariationClaim: _item,
                                        type: 'requestForVariationClaimItems',
                                        locked: gridLocked,
                                        gridOpts: {
                                            store: dojo.data.ItemFileWriteStore({
                                                url: "requestForVariationClaim/getRequestForVariationClaimItems/id/"+_item.id,
                                                clearOnClose: true
                                            }),
                                            structure: {
                                                cells: [
                                                    [{
                                                        name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter, noresize: true, rowSpan: 2
                                                    },{
                                                        name: nls.billReference, field: 'bill_ref', styles: "text-align:center; color:red;", width: '80px', formatter: formatter.unEditableCellFormatter, noresize: true, rowSpan: 2
                                                    },{
                                                        name: nls.description, field: 'description', width:'auto', noresize: true, rowSpan: 2
                                                    },{
                                                        name: nls.type, field: 'type', width: '70px', styles: 'text-align:center;', formatter: formatter.typeCellFormatter, noresize: true, rowSpan: 2
                                                    },{
                                                        name: nls.unit, field: 'uom_id', width: '70px', styles: 'text-align:center;', formatter: formatter.unitIdCellFormatter, noresize: true, rowSpan: 2
                                                    },{
                                                        name: nls.rate, field: 'rate', styles: "text-align:right;", width: '120px', noresize: true, rowSpan: 2, formatter: formatter.unEditableCurrencyCellFormatter
                                                    },{
                                                        name: nls.qty, field: 'quantity', styles: "text-align:right;", width: '90px', formatter: formatter.unEditableNumberCellFormatter, noresize: true, rowSpan: 2
                                                    },{
                                                        name: nls.total, field: 'total', styles: "text-align:right;", width: '120px', formatter: formatter.unEditableCurrencyCellFormatter, noresize: true, rowSpan: 2
                                                    },{
                                                        name: '%', field: 'percentage-value', styles: "text-align:right;", width: '60px',
                                                        editable: (!this.locked), cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                                                        formatter: (!this.locked) ? CustomFormatter.editableItemClaimPercentageFormatter : formatter.unEditablePercentageCellFormatter,
                                                        noresize: true
                                                    },{
                                                        name: nls.amount, field: 'amount-value', styles: "text-align:right;", width: '120px',
                                                        editable: (!this.locked), cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                                                        formatter: (!this.locked) ? CustomFormatter.editableItemClaimAmountCellFormatter : formatter.unEditableCurrencyCellFormatter,
                                                        noresize: true
                                                    },{
                                                        name: nls.claimCertificateNumber, field: 'claim_cert_number', width:'100px', styles:'text-align:center;', noresize: true, rowSpan: 2
                                                    }],
                                                    [{
                                                        name: nls.claims, field: 'id', styles: "text-align:center;", headerClasses: "staticHeader", noresize: true, colSpan: 2
                                                    }]
                                                ]
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    });

                    var gridContainer = self.makeGridContainer(grid, nls.requestForVariationClaims);

                    self.borderContainerWidget = gridContainer;

                    self.addChild(gridContainer);

                    pb.hide();
                });
            });
        },
        makeGridContainer: function(content, title){
            var id = this.project.id;
            var stackContainer = dijit.byId('requestForVariationClaim-'+id+'-stackContainer');
            if(stackContainer){
                stackContainer.destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:none;',
                region: "center",
                id: 'requestForVariationClaim-'+id+'-stackContainer'
            });

            stackContainer.addChild(new ContentPane({
                title: title,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'requestForVariationClaim-'+id+'-stackContainer'
            });

            var controllerPane = new ContentPane({
                style: "padding:0;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = dijit.byId('requestForVariationClaim-'+id+'-borderContainer');
            if(borderContainer){
                borderContainer.destroyRecursive(true);
            }

            borderContainer = new dijit.layout.BorderContainer({
                id: 'requestForVariationClaim-'+id+'-borderContainer',
                style:"padding:0;margin:0;width:100%;height:100%;border:none;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('requestForVariationClaim-'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('requestForVariationClaim-'+id+'-stackContainer');
                if(widget){
                    var children = widget.getChildren(),
                        index = dojo.indexOf(children, page);

                    index = index + 1;

                    if(children.length > index){
                        while(children.length > index) {
                            widget.removeChild(children[index]);
                            children[index].destroyDescendants();
                            children[index].destroyRecursive();
                            index = index + 1;
                        }

                        var selectedIndex = page.grid.selection.selectedIndex;

                        page.grid.store.save();
                        page.grid.store.close();

                        var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                            handle.remove();

                            if(selectedIndex > -1){
                                this.scrollToRow(selectedIndex, true);
                                this.selection.setSelected(selectedIndex, true);
                            }
                        });

                        page.grid.sort();
                    }
                }
            });

            return borderContainer;
        }
    });
});
