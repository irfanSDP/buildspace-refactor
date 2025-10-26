define('buildspace/apps/PostContract/PostContractClaim',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/aspect',
    'dojo/currency',
    "dijit/focus",
    'dojo/number',
    "dijit/layout/ContentPane",
    'dojox/grid/EnhancedGrid',
    './PostContractClaim/PostContractClaimGrid',
    './PostContractClaim/PostContractClaimItemContainer',
    "buildspace/apps/Attachment/UploadAttachmentContainer",
    'buildspace/widget/grid/cells/Select',
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
],function(declare, lang, aspect, currency, focusUtil, number, ContentPane, EnhancedGrid, PostContractClaimGrid, PostContractClaimItemContainer, UploadAttachmentContainer, SelectPlugin, Rearrange, Formatter, nls){

    return declare('buildspace.apps.PostContract.PostContractClaim', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;border:none;width:100%;height:100%;",
        region: "center",
        title: null,
        gutters: false,
        rootProject: null,
        type:null,
        locked: false,
        withProgressClaim:null,
        borderContainerWidget: null,
        claimCertificate: null,
        postCreate: function() {
            this.inherited(arguments);

            var self = this;

            var formatter = new Formatter();

            var uploadCellFormatter = {
                blueCellFormatter: function (cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx);

                    return item.id >= 0 ? '<span style="color:blue;">'+item.attachment+'</span>' : null;
                }
            }

            var gridStructure = [
                { name: nls.no, field: 'id', width: '30px' , editable: false, styles: 'text-align: center;', formatter: formatter.rowCountCellFormatter},
                { name: nls.description, field: 'description', width: 'auto' , styles: 'text-align: left;', editable: true, cellType:'buildspace.widget.grid.cells.Textarea'},
                {
                    name: nls.attachment,
                    field: 'attachment',
                    width: '128px',
                    styles: 'text-align:center;',
                    formatter: uploadCellFormatter.blueCellFormatter,
                    noresize: true
                },
                { name: nls.claimCertificateNumber, field: 'claim_cert_number', width: '120px', editable: false, styles: 'text-align: center;'},
                { name: nls.amount, field: 'amount', width: '120px', styles: 'text-align: right;', formatter: formatter.unEditableCurrencyCellFormatter}
            ];

            var urlList,addUrl,updateUrl,deleteUrl,verifierListUrl,id,typeText;

            switch(parseInt(self.type)){
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT:
                    gridStructure.splice(2,0,{
                    name: nls.priority,
                    field: 'priority',
                    width: '100px',
                    styles: 'text-align: center;',
                    editable: true,
                    type: 'dojox.grid.cells.Select',
                    options: [
                        buildspace.apps.PostContract.ProjectStructureConstants.ADVANCE_PAYMENT_PRIORITY_NORMAL_TEXT,
                        buildspace.apps.PostContract.ProjectStructureConstants.ADVANCE_PAYMENT_PRIORITY_HIGH_TEXT
                    ],
                    values: [
                        buildspace.apps.PostContract.ProjectStructureConstants.ADVANCE_PAYMENT_PRIORITY_NORMAL,
                        buildspace.apps.PostContract.ProjectStructureConstants.ADVANCE_PAYMENT_PRIORITY_HIGH
                    ],
                    noresize: true
                    });

                    urlList = "advancePayment/getAdvancePaymentList/pid/"+this.rootProject.id;
                    addUrl = "advancePayment/advancePaymentAdd";
                    updateUrl = "advancePayment/advancePaymentUpdate";
                    deleteUrl = "advancePayment/advancePaymentDelete";
                    verifierListUrl = "advancePayment/getVerifierList";
                    typeText = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT_TEXT;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                    urlList = "postContractClaimMaterialOnSite/getMaterialOnSiteList/pid/"+this.rootProject.id;
                    addUrl = "postContractClaimMaterialOnSite/materialOnSiteAdd";
                    updateUrl = "postContractClaimMaterialOnSite/materialOnSiteUpdate";
                    deleteUrl = "postContractClaimMaterialOnSite/materialOnSiteDelete";
                    verifierListUrl = "postContractClaimMaterialOnSite/getVerifierList";
                    typeText = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_MATERIAL_ON_SITE_TEXT;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT:
                    urlList = "postContractClaimDeposit/getDepositList/pid/"+this.rootProject.id;
                    addUrl = "postContractClaimDeposit/depositAdd";
                    updateUrl = "postContractClaimDeposit/depositUpdate";
                    deleteUrl = "postContractClaimDeposit/depositDelete";
                    verifierListUrl = "postContractClaimDeposit/getVerifierList";
                    typeText = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT_TEXT;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF:
                    urlList = "postContractClaimPurchaseOnBehalf/getPurchaseOnBehalfList/pid/"+this.rootProject.id;
                    addUrl = "postContractClaimPurchaseOnBehalf/purchaseOnBehalfAdd";
                    updateUrl = "postContractClaimPurchaseOnBehalf/purchaseOnBehalfUpdate";
                    deleteUrl = "postContractClaimPurchaseOnBehalf/purchaseOnBehalfDelete";
                    verifierListUrl = "postContractClaimPurchaseOnBehalf/getVerifierList";
                    typeText = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF_TEXT;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT:
                    urlList = "postContractClaimPermit/getPermitList/pid/"+this.rootProject.id;
                    addUrl = "postContractClaimPermit/permitAdd";
                    updateUrl = "postContractClaimPermit/permitUpdate";
                    deleteUrl = "postContractClaimPermit/permitDelete";
                    verifierListUrl = "postContractClaimPermit/getVerifierList";
                    typeText = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT_TEXT;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG:
                    urlList = "postContractClaimKongSiKong/getKongSiKongList/pid/"+this.rootProject.id;
                    addUrl = "postContractClaimKongSiKong/kongSiKongAdd";
                    updateUrl = "postContractClaimKongSiKong/kongSiKongUpdate";
                    deleteUrl = "postContractClaimKongSiKong/kongSiKongDelete";
                    verifierListUrl = "postContractClaimKongSiKong/getVerifierList";
                    typeText = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG_TEXT;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF:
                    urlList = "postContractClaimWorkOnBehalf/getWorkOnBehalfList/pid/"+this.rootProject.id;
                    addUrl = "postContractClaimWorkOnBehalf/workOnBehalfAdd";
                    updateUrl = "postContractClaimWorkOnBehalf/workOnBehalfUpdate";
                    deleteUrl = "postContractClaimWorkOnBehalf/workOnBehalfDelete";
                    verifierListUrl = "postContractClaimWorkOnBehalf/getVerifierList";
                    typeText = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_TEXT;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE:
                    urlList = "postContractClaimWorkOnBehalfBackcharge/getWorkOnBehalfBackchargeList/pid/"+this.rootProject.id;
                    addUrl = "postContractClaimWorkOnBehalfBackcharge/workOnBehalfBackchargeAdd";
                    updateUrl = "postContractClaimWorkOnBehalfBackcharge/workOnBehalfBackchargeUpdate";
                    deleteUrl = "postContractClaimWorkOnBehalfBackcharge/workOnBehalfBackchargeDelete";
                    verifierListUrl = "postContractClaimWorkOnBehalfBackcharge/getVerifierList";
                    typeText = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE_TEXT;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY:
                    urlList = "postContractClaimPenalty/getPenaltyList/pid/"+this.rootProject.id;
                    addUrl = "postContractClaimPenalty/penaltyAdd";
                    updateUrl = "postContractClaimPenalty/penaltyUpdate";
                    deleteUrl = "postContractClaimPenalty/penaltyDelete";
                    verifierListUrl = "postContractClaimPenalty/getVerifierList";
                    typeText = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY_TEXT;
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT:
                    urlList = "postContractClaimWaterDeposit/getWaterDepositList/pid/"+this.rootProject.id;
                    addUrl = "postContractClaimWaterDeposit/waterDepositAdd";
                    updateUrl = "postContractClaimWaterDeposit/waterDepositUpdate";
                    deleteUrl = "postContractClaimWaterDeposit/waterDepositDelete";
                    verifierListUrl = "postContractClaimWaterDeposit/getVerifierList";
                    typeText = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT_TEXT;
                    break;
                default:
                    break;
            }

            if(self.withProgressClaim){
                gridStructure.push(
                    { name: nls.currentPayback, field: 'current_payback', width: '120px', styles: 'text-align: right;', formatter: formatter.unEditableCurrencyCellFormatter},
                    { name: nls.upToDatePayback, field: 'up_to_date_payback', width: '120px', styles: 'text-align: right;', formatter: formatter.unEditableCurrencyCellFormatter}
                );
            }

            if(self.claimObject) urlList += "/oid/"+this.claimObject.id;

            if(self.claimCertificate) urlList += "/claimRevision/"+this.claimCertificate.post_contract_claim_revision_id;

            var claimSubmissionFormatter = {
                statusCellFormatter:function (cellValue, rowIdx, cell){
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
                }
            };

            gridStructure.push({
                  name: nls.status,
                  field: 'status',
                  width: '120px',
                  styles: 'text-align: center;',
                  editable: true,
                  type: 'dojox.grid.cells.Select',
                  formatter: claimSubmissionFormatter.statusCellFormatter,
                  options: [
                        buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PENDING_TEXT,
                        buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PREPARING_TEXT
                  ],
                  values: [
                        buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PENDING,
                        buildspace.apps.PostContract.ProjectStructureConstants.STATUS_PREPARING
                  ],
                  noresize: true
                },
                { name: nls.lastUpdated, field: 'updated_at', width:'120px', styles:'text-align: center;'}
            );

            var store = dojo.data.ItemFileWriteStore({
                url: urlList,
                clearOnClose: true
            });

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

                    var grid = self.grid = new PostContractClaimGrid({
                        project: self.rootProject,
                        type: self.type,
                        itemLevel: false,
                        locked: self.locked,
                        postContractClaimFirstLevelContainer: self,
                        gridOpts: {
                            structure: gridStructure,
                            store: store,
                            addUrl: addUrl,
                            updateUrl: updateUrl,
                            deleteUrl: deleteUrl,
                            verifierListUrl: verifierListUrl,
                            withProgressClaim: self.withProgressClaim,
                            claimCertificate: self.claimCertificate,
                            locked: self.locked,
                            onRowDblClick: function(e){
                                var self = this,
                                _item = self.getItem(e.rowIndex);
                                if(_item && !isNaN(parseInt(String(_item.id))) && String(_item.description).length > 0){
                                    new PostContractClaimItemContainer({
                                        stackContainerTitle: String(_item.description),
                                        project: this.project,
                                        itemType: this.type,
                                        withProgressClaim: this.withProgressClaim,
                                        locked: this.locked,
                                        postContractClaim: _item,
                                        claimCertificate: self.claimCertificate,
                                        id: 'post_contract_claim_items-'+_item.id+'-'+this.project.id,
                                        pageId: 'post_contract_claim_items-'+_item.id+'-'+this.project.id+'-page'
                                    });
                                }
                            }
                        }
                    });

                    var gridContainer = self.makeGridContainer(grid, typeText);
                    self.borderContainerWidget = gridContainer;

                    self.addChild(gridContainer);

                    pb.hide();
                });
            });
        },
        makeGridContainer: function(content, title){
            var id = this.rootProject.id, stackContainerName;

            switch(parseInt(content.type)){
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT:
                    stackContainerName = "advancePayment-";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                    stackContainerName = "materialOnSite-";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT:
                    stackContainerName = "deposit-";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF:
                    stackContainerName = "purchaseOnBehalf-";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT:
                    stackContainerName = "permit-";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG:
                     stackContainerName = "kongSiKong-";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF:
                     stackContainerName = "workOnBehalf-";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE:
                     stackContainerName = "workOnBehalfBackcharge-";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY:
                     stackContainerName = "penalty-";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT:
                     stackContainerName = "waterDeposit-";
                    break;
                default:
                    break;
            }

            var stackContainer = dijit.byId(stackContainerName+id+'-stackContainer');
            if(stackContainer){
                dijit.byId(stackContainerName+id+'-stackContainer').destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:none;',
                region: "center",
                id: stackContainerName+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: stackContainerName+id+'-stackContainer'
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

            dojo.subscribe(stackContainerName+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId(stackContainerName+id+'-stackContainer');
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
            var id = 'project-'+this.rootProject.id+'-uploadAttachment';
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
