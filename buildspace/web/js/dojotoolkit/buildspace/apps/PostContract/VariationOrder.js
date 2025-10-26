define('buildspace/apps/PostContract/VariationOrder', [
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/aspect',
    'dojo/currency',
    'dojo/number',
    "dijit/layout/ContentPane",
    "./VariationOrder/VariationOrderGrid",
    "./VariationOrder/VariationOrderItemContainer",
    "buildspace/apps/Attachment/UploadAttachmentContainer",
    'buildspace/widget/grid/cells/Select',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
    ], function(declare, lang, aspect, currency, number, ContentPane, VariationOrderGrid, VariationOrderItemContainer, UploadAttachmentContainer, SelectPlugin, GridFormatter, nls) {

    var CustomFormatter = {
        statusCellFormatter: function(cellValue, rowIdx, cell){
            var item = this.grid.getItem(rowIdx);

            if(item.id == buildspace.constants.GRID_LAST_ROW){
                cell.customClasses.push('disable-cell');
                return "&nbsp;";
            }

            var text;
            switch(cellValue) {
                case buildspace.apps.PostContract.ProjectStructureConstants.STATUS_APPROVED:
                    text = buildspace.apps.PostContract.ProjectStructureConstants.STATUS_APPROVED_TEXT;
                    cell.customClasses.push('green-cell');
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PENDING:
                    text = buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PENDING_TEXT;
                    cell.customClasses.push('yellow-cell');
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PREPARING:
                    text = buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PREPARING_TEXT;
                    break;
                default:
                    text = '';
            }

            return text.toUpperCase();
        },
        nettOmissionAdditionCellFormatter: function(cellValue, rowIdx, cell){
            cell.customClasses.push('disable-cell');
            var value = number.parse(cellValue);

            if(value < 0){
                return '<span style="color:#FF0000">'+currency.format(value)+'</span>';
            }else{
                return value == 0 ? "&nbsp;" : '<span style="color:#42b449;">'+currency.format(value)+'</span>';
            }
        }
    };

    return declare('buildspace.apps.PostContract.VariationOrder', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;border:none;width:100%;height:100%;",
        region: "center",
        gutters: false,
        rootProject: null,
        variationOrder: null,
        borderContainerWidget: null,
        locked: false,
        claimCertificate: null,
        postCreate: function() {
            this.inherited(arguments);

            var self = this;

            var pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + '...'
            });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "postContract/getClaimGridEditableStatus",
                    handleAs: "json",
                    content: {
                        id: self.rootProject.id
                    }
                }).then(function (resp) {
                    if(!self.locked) self.locked = !resp.editable;

                    var formatter = new GridFormatter();

                    var uploadCellFormatter = {
                        blueCellFormatter: function (cellValue, rowIdx, cell){
                            var item = this.grid.getItem(rowIdx);
                            return item.id >= 0 ? '<span style="color:blue;">'+item.attachment+'</span>' : null;
                        }
                    }

                    var gridStructure = [
                        {name: 'No.', field: 'id', width:'30px', styles:'text-align:center;', formatter: formatter.rowCountCellFormatter },
                        {name: nls.rfvNumber, field: 'rfv_number', width:'48px', styles:'text-align:center;' },
                        {name: nls.description, field: 'description', width:'auto', editable:true, cellType:'buildspace.widget.grid.cells.Textarea' },
                        {
                            name: nls.attachment,
                            field: 'attachment',
                            width: '82px',
                            styles: 'text-align:center;',
                            formatter: uploadCellFormatter.blueCellFormatter,
                            noresize: true
                        }
                    ];

                    if(parseInt(String(self.rootProject.post_contract_type_id)) == buildspace.constants.POST_CONTRACT_TYPE_NEW) {
                        gridStructure.push({
                            name: nls.type,
                            field: 'type',
                            styles:'text-align:center;',
                            width:'92px',
                            editable:!self.locked,
                            type: 'dojox.grid.cells.Select',
                            options: [
                                buildspace.constants.VARIATION_ORDER_TYPE_BUDGETARY_TEXT,
                                buildspace.constants.VARIATION_ORDER_TYPE_CLAIMABLE_TEXT,
                                buildspace.constants.VARIATION_ORDER_TYPE_NON_CLAIMABLE_TEXT
                            ],
                            values: [
                                buildspace.constants.VARIATION_ORDER_TYPE_BUDGETARY,
                                buildspace.constants.VARIATION_ORDER_TYPE_CLAIMABLE,
                                buildspace.constants.VARIATION_ORDER_TYPE_NON_CLAIMABLE
                            ],
                            noresize: true
                        });

                        gridStructure.push({
                            name: nls.claimCertificateNumber,
                            field: 'claim_cert_number',
                            styles:'text-align:center;',
                            width:'100px',
                            noresize: true
                        });
                    }

                    var statusOptions = {
                        options: [
                            buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PENDING_TEXT,
                            buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PREPARING_TEXT
                        ],
                        values: [
                            buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PENDING,
                            buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PREPARING
                        ]
                    };

                    var standardColumns = [
                        {name: nls.budget, field: 'reference_amount', width:'100px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                        {name: nls.omission, field: 'omission', width:'100px', styles:'text-align:right;color:#FF0000;', formatter: formatter.unEditableCurrencyCellFormatter},
                        {name: nls.addition, field: 'addition', width:'100px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                        {name: nls.nettOmissionAddition, field: 'nett_omission_addition', width:'120px', styles:'text-align:right;', formatter: CustomFormatter.nettOmissionAdditionCellFormatter},
                        {name: nls.upToDateClaim, field: 'total_claim', width:'100px', styles:'text-align:right;', formatter: formatter.unEditableCurrencyCellFormatter},
                        {name: nls.status, field: 'status', width:'80px', styles:'text-align:center;', editable: true, type: 'dojox.grid.cells.Select', options: statusOptions.options, values: statusOptions.values, formatter: CustomFormatter.statusCellFormatter },
                        {name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
                    ];

                    dojo.forEach(standardColumns, function(standardColumn){
                        gridStructure.push(standardColumn);
                    });

                    var url = "variationOrder/getVariationOrderList/pid/"+self.rootProject.id;

                    if(self.variationOrder) url += "/oid/"+self.variationOrder.id;

                    if(self.claimCertificate) url += "/claimRevision/"+self.claimCertificate.post_contract_claim_revision_id;

                    var store = dojo.data.ItemFileWriteStore({
                            url: url,
                            clearOnClose: true
                        }),
                        grid = new VariationOrderGrid({
                            id: 'variation_order-'+self.rootProject.id,
                            stackContainerTitle: nls.scheduleOfRates,
                            variationOrderFirstLevelContainer: self,
                            pageId: 'variation_order-'+self.rootProject.id,
                            project: self.rootProject,
                            type: 'vo',
                            locked: self.locked,
                            claimCertificate: self.claimCertificate,
                            gridOpts: {
                                store: store,
                                addUrl: 'variationOrder/variationOrderAdd',
                                updateUrl: 'variationOrder/variationOrderUpdate',
                                deleteUrl: 'variationOrder/variationOrderDelete',
                                structure: gridStructure,
                                onRowDblClick: function(e){
                                    var _item = this.getItem(e.rowIndex);
                                    if(_item && !isNaN(parseInt(String(_item.id))) && String(_item.description).length > 0){
                                        new VariationOrderItemContainer({
                                            stackContainerTitle: String(_item.description),
                                            project: this.project,
                                            variationOrder: _item,
                                            locked: this.locked,
                                            claimCertificate: self.claimCertificate,
                                            id: 'variation_order_items-'+_item.id+'-'+this.project.id,
                                            pageId: 'variation_order_items-'+_item.id+'-'+this.project.id+'-page'
                                        });
                                    }
                                }
                            }
                        });

                    var gridContainer = self.makeGridContainer(grid, nls.variationOrders);

                    self.borderContainerWidget = gridContainer;

                    self.addChild(gridContainer);

                    pb.hide();
                });
            });
        },
        makeGridContainer: function(content, title){
            var id = this.rootProject.id;
            var stackContainer = dijit.byId('variationOrder-'+id+'-stackContainer');
            if(stackContainer){
                dijit.byId('variationOrder-'+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:none;',
                region: "center",
                id: 'variationOrder-'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'variationOrder-'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0;margin:0;width:100%;height:100%;border:none;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('variationOrder-'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('variationOrder-'+id+'-stackContainer');
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
        },
        addUploadAttachmentContainer: function(item){

            var self = this;
            var id = 'project-'+self.rootProject.id+'-uploadAttachment';
            var container = dijit.byId(id);

            if(container){
                this.borderContainerWidget.removeChild(container);
                container.destroy();
            }

            container = new UploadAttachmentContainer({
                id: id,
                region: 'bottom',
                item: item,
                disableEditing: ! item.can_be_edited[0],
                style:"padding:0;margin:0;border:none;width:100%;height:40%;"
            });

            this.borderContainerWidget.addChild(container);

            return container;
        }

    });
});
