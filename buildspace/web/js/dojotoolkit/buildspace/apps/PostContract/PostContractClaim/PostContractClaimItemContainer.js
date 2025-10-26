define('buildspace/apps/PostContract/PostContractClaim/PostContractClaimItemContainer',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    'dojo/aspect',
    'dojo/currency',
    "dojo/when",
    'dojo/number',
    "dijit/layout/TabContainer",
    "dijit/layout/ContentPane",
    'dojox/grid/EnhancedGrid',
    './PostContractClaimGrid',
    "./PostContractClaimClaimGrid",
    "buildspace/apps/Attachment/UploadAttachmentContainer",
    'buildspace/widget/grid/cells/Select',
    "dojox/grid/enhanced/plugins/Rearrange",
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/PostContract'
],function(declare, lang, aspect, currency, when, number, TabContainer, ContentPane, EnhancedGrid, PostContractClaimGrid, PostContractClaimClaimGrid, UploadAttachmentContainer, SelectPlugin, Rearrange, Formatter, nls){

    return declare('buildspace.apps.PostContract.PostContractClaim.PostContractClaimItemContainer', TabContainer, {
        pageId: 'page-00',
        style: "margin:0;padding:0;border:none;width:100%;height:100%;",
        gutters: false,
        stackContainerTitle: null,
        gridOpts: {},
        project: null,
        nested: true,
        postContractClaim: null,
        withProgressClaim: null,
        borderContainerWidget: null,
        itemType: null,
        claimCertificate: null,
        locked: false,
        postCreate: function() {
            var self = this;
            this.inherited(arguments);

            var unitQuery = dojo.xhrGet({
                    url: "postContractClaim/getUnits",
                    handleAs: "json"
                }),
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
            var claimQueryContent = {id: this.postContractClaim.id};
            if(this.claimCertificate) claimQueryContent['claimRevision'] = this.claimCertificate.post_contract_claim_revision_id;
            var claimQuery = dojo.xhrGet({
                url: "postContractClaim/getClaimStatus",
                content: claimQueryContent,
                handleAs: "json"
            });

            when(unitQuery, function(uom){
                pb.show().then(function(){
                    claimQuery.then(function(status){
                        pb.hide();
                        self.createPostContractClaimItemGrid(uom, status, true);
                        if(self.withProgressClaim)
                        {
                            self.createPostContractClaimClaimGrid(status);
                        }
                    });
                });
            });

            switch(parseInt(self.itemType))
            {
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

            var container = dijit.byId(stackContainerName+this.project.id+'-stackContainer');

            if(container){
                container.addChild(new dojox.layout.ContentPane({
                    title: buildspace.truncateString(this.stackContainerTitle, 45),
                    id: this.pageId,
                    content: this,
                    executeScripts: true
                }));
                container.selectChild(this.pageId);
            }

        },
        createPostContractClaimItemGrid: function(uom, claimStatus, addToTabContainer){
            var stackPane = dijit.byId('postContractClaimItems_tab-'+this.project.id+'_'+this.postContractClaim.id+'-StackPane');
            var title;
            var self = this;
            switch(parseInt(self.itemType)){
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT:
                    title = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT_TEXT + ' ' + "Items";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                    title = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_MATERIAL_ON_SITE_TEXT + ' ' + "Items";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT:
                    title = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT_TEXT + ' ' + "Items";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF:
                    title = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF_TEXT + ' ' + "Items";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT:
                    title = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT_TEXT + ' ' + "Items";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG:
                    title = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG_TEXT + ' ' + "Items";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF:
                    title = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_TEXT + ' ' + "Items";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE:
                    title = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE_TEXT + ' ' + "Items";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY:
                    title = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY_TEXT + ' ' + "Items";
                    break;
                case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT:
                    title = buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT_TEXT + ' ' + "Items";
                    break;
                default:
                    break;
            }

            if(!stackPane){
                stackPane = new dijit.layout.StackContainer({
                    id: 'postContractClaimItems_tab-'+this.project.id+'_'+this.postContractClaim.id+'-StackPane',
                    style:'margin:0;padding:0;border:none;width:100%;height:100%;',
                    region:'center',
                    title: title
                });
            }

            var borderContainer = dijit.byId('postContractClaimItems-'+this.project.id+'_'+this.postContractClaim.id+'-borderContainer');
            if(borderContainer){
                stackPane.removeChild(borderContainer);
                borderContainer.destroyRecursive();
            }

            var uploadCellFormatter = {
                blueCellFormatter: function (cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx);
                    return item.id >= 0 ? '<span style="color:blue;">'+item.attachment+'</span>' : null;
                }
            }

            var self = this,
                hierarchyTypes = {
                    options: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_TEXT,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM_TEXT
                    ],
                    values: [
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER,
                        buildspace.widget.grid.constants.HIERARCHY_TYPE_WORK_ITEM
                    ]
                },
                formatter = new Formatter();

                var listUrl, addUrl, updateUrl, deleteUrl, indentUrl, outdentUrl, pasteUrl,itemType;

                switch(parseInt(self.itemType)){
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT:
                        listUrl = 'advancePayment/getAdvancePaymentItemList/id/'+self.postContractClaim.id;
                        addUrl =  'advancePayment/advancePaymentItemAdd';
                        updateUrl = claimStatus.count > 0 ? 'postContractClaim/claimItemUpdate' : 'advancePayment/advancePaymentItemUpdate';
                        deleteUrl =  'advancePayment/advancePaymentItemDelete';
                        indentUrl = 'advancePayment/advancePaymentItemIndent';
                        outdentUrl = 'advancePayment/advancePaymentItemOutdent';
                        pasteUrl = 'advancePayment/advancePaymentItemPaste';
                        itemType =  self.itemType;
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                        listUrl = 'postContractClaimMaterialOnSite/getMaterialOnSiteItemList/id/'+self.postContractClaim.id;
                        addUrl =  'postContractClaimMaterialOnSite/materialOnSiteItemAdd';
                        updateUrl = claimStatus.count > 0 ? 'postContractClaim/claimItemUpdate' : 'postContractClaimMaterialOnSite/materialOnSiteItemUpdate';
                        deleteUrl =  'postContractClaimMaterialOnSite/materialOnSiteItemDelete';
                        indentUrl = 'postContractClaimMaterialOnSite/materialOnSiteItemIndent';
                        outdentUrl = 'postContractClaimMaterialOnSite/materialOnSiteItemOutdent';
                        pasteUrl = 'postContractClaimMaterialOnSite/materialOnSiteItemPaste';
                        itemType =  self.itemType;
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT:
                        listUrl = 'postContractClaimDeposit/getDepositItemList/id/'+self.postContractClaim.id;
                        addUrl =  'postContractClaimDeposit/depositItemAdd';
                        updateUrl = claimStatus.count > 0 ? 'postContractClaim/claimItemUpdate' : 'postContractClaimDeposit/depositItemUpdate';
                        deleteUrl =  'postContractClaimDeposit/depositItemDelete';
                        indentUrl = 'postContractClaimDeposit/depositItemIndent';
                        outdentUrl = 'postContractClaimDeposit/depositItemOutdent';
                        pasteUrl = 'postContractClaimDeposit/depositItemPaste';
                        itemType =  self.itemType;
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF:
                        listUrl = 'postContractClaimPurchaseOnBehalf/getPurchaseOnBehalfItemList/id/'+self.postContractClaim.id;
                        addUrl =  'postContractClaimPurchaseOnBehalf/purchaseOnBehalfItemAdd';
                        updateUrl = claimStatus.count > 0 ? 'postContractClaim/claimItemUpdate' : 'postContractClaimPurchaseOnBehalf/purchaseOnBehalfItemUpdate';
                        deleteUrl =  'postContractClaimPurchaseOnBehalf/purchaseOnBehalfItemDelete';
                        indentUrl = 'postContractClaimPurchaseOnBehalf/purchaseOnBehalfItemIndent';
                        outdentUrl = 'postContractClaimPurchaseOnBehalf/purchaseOnBehalfItemOutdent';
                        pasteUrl = 'postContractClaimPurchaseOnBehalf/purchaseOnBehalfItemPaste';
                        itemType =  self.itemType;
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT:
                        listUrl = 'postContractClaimPermit/getPermitItemList/id/'+self.postContractClaim.id;
                        addUrl =  'postContractClaimPermit/permitItemAdd';
                        updateUrl = claimStatus.count > 0 ? 'postContractClaim/claimItemUpdate' : 'postContractClaimPermit/permitItemUpdate';
                        deleteUrl =  'postContractClaimPermit/permitItemDelete';
                        indentUrl = 'postContractClaimPermit/permitItemIndent';
                        outdentUrl = 'postContractClaimPermit/permitItemOutdent';
                        pasteUrl = 'postContractClaimPermit/permitItemPaste';
                        itemType =  self.itemType;
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG:
                        listUrl = 'postContractClaimKongSiKong/getKongSiKongItemList/id/'+self.postContractClaim.id;
                        addUrl =  'postContractClaimKongSiKong/kongSiKongItemAdd';
                        updateUrl = claimStatus.count > 0 ? 'postContractClaim/claimItemUpdate' : 'postContractClaimKongSiKong/kongSiKongItemUpdate';
                        deleteUrl =  'postContractClaimKongSiKong/kongSiKongItemDelete';
                        indentUrl = 'postContractClaimKongSiKong/kongSiKongItemIndent';
                        outdentUrl = 'postContractClaimKongSiKong/kongSiKongItemOutdent';
                        pasteUrl = 'postContractClaimKongSiKong/kongSiKongItemPaste';
                        itemType =  self.itemType;
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF:
                        listUrl = 'postContractClaimWorkOnBehalf/getWorkOnBehalfItemList/id/'+self.postContractClaim.id;
                        addUrl =  'postContractClaimWorkOnBehalf/workOnBehalfItemAdd';
                        updateUrl = claimStatus.count > 0 ? 'postContractClaim/claimItemUpdate' : 'postContractClaimWorkOnBehalf/workOnBehalfItemUpdate';
                        deleteUrl =  'postContractClaimWorkOnBehalf/workOnBehalfItemDelete';
                        indentUrl = 'postContractClaimWorkOnBehalf/workOnBehalfItemIndent';
                        outdentUrl = 'postContractClaimWorkOnBehalf/workOnBehalfItemOutdent';
                        pasteUrl = 'postContractClaimWorkOnBehalf/workOnBehalfItemPaste';
                        itemType =  self.itemType;
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE:
                        listUrl = 'postContractClaimWorkOnBehalfBackcharge/getWorkOnBehalfBackchargeItemList/id/'+self.postContractClaim.id;
                        addUrl =  'postContractClaimWorkOnBehalfBackcharge/workOnBehalfBackchargeItemAdd';
                        updateUrl = claimStatus.count > 0 ? 'postContractClaim/claimItemUpdate' : 'postContractClaimWorkOnBehalfBackcharge/workOnBehalfBackchargeItemUpdate';
                        deleteUrl =  'postContractClaimWorkOnBehalfBackcharge/workOnBehalfBackchargeItemDelete';
                        indentUrl = 'postContractClaimWorkOnBehalfBackcharge/workOnBehalfBackchargeItemIndent';
                        outdentUrl = 'postContractClaimWorkOnBehalfBackcharge/workOnBehalfBackchargeItemOutdent';
                        pasteUrl = 'postContractClaimWorkOnBehalfBackcharge/workOnBehalfBackchargeItemPaste';
                        itemType =  self.itemType;
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY:
                        listUrl = 'postContractClaimPenalty/getPenaltyItemList/id/'+self.postContractClaim.id;
                        addUrl =  'postContractClaimPenalty/penaltyItemAdd';
                        updateUrl = claimStatus.count > 0 ? 'postContractClaim/claimItemUpdate' : 'postContractClaimPenalty/penaltyItemUpdate';
                        deleteUrl =  'postContractClaimPenalty/penaltyItemDelete';
                        indentUrl = 'postContractClaimPenalty/penaltyItemIndent';
                        outdentUrl = 'postContractClaimPenalty/penaltyItemOutdent';
                        pasteUrl = 'postContractClaimPenalty/penaltyItemPaste';
                        itemType =  self.itemType;
                        break;
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT:
                        listUrl = 'postContractClaimWaterDeposit/getWaterDepositItemList/id/'+self.postContractClaim.id;
                        addUrl =  'postContractClaimWaterDeposit/waterDepositItemAdd';
                        updateUrl = claimStatus.count > 0 ? 'postContractClaim/claimItemUpdate' : 'postContractClaimWaterDeposit/waterDepositItemUpdate';
                        deleteUrl =  'postContractClaimWaterDeposit/waterDepositItemDelete';
                        indentUrl = 'postContractClaimWaterDeposit/waterDepositItemIndent';
                        outdentUrl = 'postContractClaimWaterDeposit/waterDepositItemOutdent';
                        pasteUrl = 'postContractClaimWaterDeposit/waterDepositItemPaste';
                        itemType =  self.itemType;
                        break;
                    default:
                        break;
                }

                if(this.claimCertificate) listUrl += '/claimRevision/' + this.claimCertificate.post_contract_claim_revision_id;

                var store = dojo.data.ItemFileWriteStore({
                    url: listUrl,
                    clearOnClose: true
                }),

                structure = {
                    cells: [
                    [{
                        name: 'No',
                        field: 'id',
                        styles: "text-align:center;",
                        width: '30px',
                        formatter: formatter.rowCountCellFormatter,
                        noresize: true,
                        rowSpan: 2
                    },{
                        name: nls.description,
                        field: 'description',
                        styles: "text-align:left;",
                        width: '500px',
                        editable: self.postContractClaim.can_be_edited[0],
                        cellType: 'buildspace.widget.grid.cells.Textarea',
                        formatter: formatter.treeCellFormatter,
                        noresize: true,
                        rowSpan: 2
                    },{
                        name: nls.attachment,
                        field: 'attachment',
                        width: '100px',
                        styles: 'text-align:center;',
                        formatter: uploadCellFormatter.blueCellFormatter,
                        noresize: true,
                        rowSpan: 2
                    },{
                        name: nls.type,
                        field: 'type',
                        width: '120px',
                        styles: 'text-align:center;',
                        editable: self.postContractClaim.can_be_edited[0],
                        type: 'dojox.grid.cells.Select',
                        options: hierarchyTypes.options,
                        values: hierarchyTypes.values,
                        noresize: true,
                        rowSpan: 2
                    },{
                        name: nls.unit,
                        field: 'uom_id',
                        width: '90px',
                        editable: self.postContractClaim.can_be_edited[0],
                        styles: 'text-align:center;',
                        type: 'dojox.grid.cells.Select',
                        options: uom.options,
                        values: uom.values,
                        formatter: formatter.unitIdCellFormatter,
                        noresize: true,
                        rowSpan: 2
                    },{
                        name: nls.qty,
                        field: 'quantity',
                        styles: "text-align:right;",
                        width: '120px',
                        editable: self.postContractClaim.can_be_edited[0],
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: formatter.numberCellFormatter,
                        noresize: true,
                        rowSpan: 2
                    },{
                        name: nls.rate,
                        field: 'rate',
                        styles: "text-align:right;",
                        width: '120px',
                        editable: self.postContractClaim.can_be_edited[0],
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: !self.postContractClaim.can_be_edited[0] ? formatter.unEditableCurrencyCellFormatter : formatter.currencyCellFormatter,
                        noresize: true,
                        rowSpan: 2
                    },{
                        name: nls.amount,
                        field: 'amount',
                        styles: "text-align:right;",
                        width: '120px',
                        formatter: formatter.unEditableCurrencyCellFormatter,
                        noresize: true,
                        rowSpan: 2
                   }],
                   []
                ]};

                if(self.withProgressClaim){
                    structure.cells[0].push({
                        name: '%',
                        field: 'previous_percentage-value',
                        styles: "text-align:right;",
                        width: '60px',
                        formatter: formatter.unEditableNumberCellFormatter,
                        noresize: true
                    },{
                        name: nls.qty,
                        field: 'previous_quantity-value',
                        styles: "text-align:right;",
                        width: '90px',
                        formatter: formatter.unEditableNumberCellFormatter,
                        noresize: true
                    },{
                        name: nls.amount,
                        field: 'previous_amount-value',
                        styles: "text-align:right;",
                        width: '120px',
                        formatter: formatter.unEditableNumberCellFormatter,
                        noresize: true
                    },{
                        name: '%',
                        field: 'current_percentage-value',
                        styles: "text-align:right;",
                        width: '60px',
                        editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? formatter.editablePercentageCellFormatter : formatter.unEditablePercentageCellFormatter,
                        noresize: true
                    },{
                        name: nls.qty,
                        field: 'current_quantity-value',
                        styles: "text-align:right;",
                        width: '90px',
                        editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? formatter.currencyCellFormatter : formatter.unEditableNumberCellFormatter,
                        noresize: true
                    },{
                        name: nls.amount,
                        field: 'current_amount-value',
                        styles: "text-align:right;",
                        width: '120px',
                        editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? formatter.currencyCellFormatter : formatter.unEditableCurrencyCellFormatter,
                        noresize: true
                    },{
                        name: '%',
                        field: 'up_to_date_percentage-value',
                        styles: "text-align:right;",
                        width: '60px',
                        editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? formatter.editablePercentageCellFormatter : formatter.unEditablePercentageCellFormatter,
                        noresize: true
                    },{
                        name: nls.qty,
                        field: 'up_to_date_quantity-value',
                        styles: "text-align:right;",
                        width: '90px',
                        editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? formatter.currencyCellFormatter : formatter.unEditableNumberCellFormatter,
                        noresize: true
                    },{
                        name: nls.amount,
                        field: 'up_to_date_amount-value',
                        styles: "text-align:right;",
                        width: '120px',
                        editable: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? true : false,
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: (claimStatus.count > 0 && claimStatus.can_edit_claim_amount) ? formatter.currencyCellFormatter : formatter.unEditableNumberCellFormatter,
                        noresize: true
                    });

                    structure.cells[1].push({
                        name: 'Previous Pay Back',
                        field: 'previous_payback',
                        styles: "text-align:center;",
                        headerClasses: "staticHeader",
                        noresize: true,
                        colSpan: 3
                    },{
                        name: 'Current Pay Back',
                        field: 'current_payback',
                        styles: "text-align:center;",
                        headerClasses: "staticHeader",
                        noresize: true,
                        colSpan: 3
                    },{
                        name: 'Up To Date Pay Back',
                        field: 'up_to_date_payback',
                        styles: "text-align:center;",
                        headerClasses: "staticHeader",
                        noresize: true,
                        colSpan: 3
                    });
                }

                switch(parseInt(self.itemType)){
                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                        structure.cells[0].splice(8,0, {
                        name: '%',
                        field: 'reduction_percentage-value',
                        styles: "text-align:right;",
                        width: '60px',
                        editable: self.postContractClaim.can_be_edited[0],
                        cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                        formatter: formatter.editablePercentageCellFormatter,
                        noresize: true
                        },{
                            name: nls.amount,
                            field: 'reduction_amount-value',
                            styles: "text-align:right;",
                            width: '120px',
                            editable: self.postContractClaim.can_be_edited[0],
                            cellType: 'buildspace.widget.grid.cells.FormulaTextBox',
                            formatter: formatter.currencyCellFormatter,
                            noresize: true
                        });

                        structure.cells[1].splice(0,0, {
                            name: nls.reduction,
                            field: 'reduction',
                            styles: "text-align:center;",
                            headerClasses: "staticHeader",
                            noresize: true,
                            colSpan: 2
                        });
                        break;

                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF:
                        structure.cells[0].splice(1,0, {
                            name: nls.document_number,
                            field: 'document_number',
                            styles: "text-align:center;",
                            width: '120px',
                            editable: self.postContractClaim.can_be_edited[0],
                            cellType: 'buildspace.widget.grid.cells.Textarea',
                            formatter: formatter.treeCellFormatter,
                            noresize: true,
                            rowSpan: 2
                        });
                        break;

                    case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG:
                        structure.cells[0].splice(1,0, {
                            name: nls.labourType,
                            field: 'labour_type',
                            width: '120px',
                            styles: 'text-align:center;',
                            editable: self.postContractClaim.can_be_edited[0],
                            type: 'dojox.grid.cells.Select',
                            options: [
                                buildspace.apps.PostContract.ProjectStructureConstants.LABOUR_TYPE_SKILLED_TEXT,
                                buildspace.apps.PostContract.ProjectStructureConstants.LABOUR_TYPE_UNSKILLED_TEXT
                            ],
                            values: [
                                buildspace.apps.PostContract.ProjectStructureConstants.LABOUR_TYPE_SKILLED,
                                buildspace.apps.PostContract.ProjectStructureConstants.LABOUR_TYPE_UNSKILLED
                            ],
                            noresize: true,
                            rowSpan: 2
                        });
                        break;
                    default:
                        break;
                }

                var grid = self.grid = new PostContractClaimGrid({
                    project: self.project,
                    postContractClaim: self.postContractClaim,
                    postContractClaimItemContainer: self,
                    type: itemType,
                    itemLevel: true,
                    locked: !self.postContractClaim.can_be_edited[0],
                    hideMosToolbar: true,
                    gridOpts: {
                        store: store,
                        addUrl: addUrl,
                        updateUrl: updateUrl,
                        deleteUrl: deleteUrl,
                        indentUrl: indentUrl,
                        outdentUrl: outdentUrl,
                        pasteUrl: pasteUrl,
                        structure: structure
                    }
                });

                var gridBorderContainer = this.makeItemGridContainer(grid, title);
                stackPane.addChild(gridBorderContainer);

                this.borderContainerWidget = gridBorderContainer;

                if(addToTabContainer){
                    this.addChild(stackPane);
                }
        },
        createPostContractClaimClaimGrid: function(status){
            this.addChild(
                new PostContractClaimClaimGrid({
                    title: nls.claimRevisions,
                    project: this.project,
                    postContractClaim: this.postContractClaim,
                    postContractClaimItemContainer: this,
                    claimCertificate: this.claimCertificate,
                    claimStatus: status,
                    locked: this.locked
                })
            );
        },
        makeItemGridContainer: function(content, title){
            var id = this.project.id+'_'+this.postContractClaim.id,
                stackContainer = dijit.byId('postContractClaimItems-'+id+'-stackContainer');

            if(stackContainer){
                stackContainer.destroyRecursive();
            }

            stackContainer = new dijit.layout.StackContainer({
                style:'width:100%;height:100%;border:none;',
                region: "center",
                id: 'postContractClaimItems-'+id+'-stackContainer'
            });

            stackContainer.addChild(new dijit.layout.ContentPane({
                title: title,
                content: content,
                grid: content.grid
            }));

            var controller = new dijit.layout.StackController({
                region: "top",
                containerId: 'postContractClaimItems-'+id+'-stackContainer'
            });

            var controllerPane = new dijit.layout.ContentPane({
                style: "padding:0;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            });

            var borderContainer = dijit.byId('postContractClaimItems-'+id+'-borderContainer');
            if(borderContainer){
                borderContainer.destroyRecursive(true);
            }

            borderContainer = new dijit.layout.BorderContainer({
                id: 'postContractClaimItems-'+id+'-borderContainer',
                style:"padding:0;margin:0;width:100%;height:100%;border:none;",
                gutters: false,
                region: 'center'
            });

            borderContainer.addChild(stackContainer);
            borderContainer.addChild(controllerPane);

            dojo.subscribe('postContractClaimItems-'+id+'-stackContainer-selectChild',"",function(page){
                var widget = dijit.byId('postContractClaimItems-'+id+'-stackContainer');
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

                        if(page.grid){
                            var selectedIndex = page.grid.selection.selectedIndex;

                            page.grid.store.save();
                            page.grid.store.close();

                            var handle = aspect.after(page.grid, "_onFetchComplete", function() {
                                handle.remove();

                                if(selectedIndex > -1){
                                    this.scrollToRow(selectedIndex);
                                    this.selection.setSelected(selectedIndex, true);
                                }
                            });

                            page.grid.sort();
                        }
                    }
                }
            });

            return borderContainer;
        },
        addUploadAttachmentContainer: function(item){
            var id = 'project-'+this.project.id+'-uploadAttachment';
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
