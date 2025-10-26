define('buildspace/apps/Approval/ClaimCertificate/ViewArea',[
    'dojo/_base/declare',
    "dojo/_base/lang",
    'buildspace/apps/PostContract/ClaimCertificateContainer',
    'buildspace/apps/PostContract/ClaimCertificateNoteContainer',
    'buildspace/apps/PostContract/ProjectBreakdown',
    'buildspace/apps/PostContract/StandardBillManager',
    'buildspace/apps/PostContract/Preliminaries/PreliminariesBillManager',
    'buildspace/apps/PostContract/VariationOrder',
    'buildspace/apps/PostContract/RequestForVariationClaim',
    'buildspace/apps/PostContract/MaterialOnSite',
    'buildspace/apps/PostContract/PostContractClaim',
    'buildspace/apps/PostContract/DebitCreditNote/DebitCreditNoteContainer',
    'dojo/i18n!buildspace/nls/Approval'], function(declare, lang, ClaimCertificateContainer, ClaimCertificateNoteContainer, ProjectBreakdown, StandardBillManager, PreliminariesBillManager, VariationOrder, RequestForVariationClaim, MaterialOnSite, PostContractClaim, DebitCreditNoteContainer, nls){

    return declare('buildspace.apps.Approval.ClaimCertificate.ViewArea', dijit.layout.TabContainer, {
        region: "center",
        style: "padding:0;border:none;margin:0;width:100%;height:100%;",
        project: null,
        claimCertificate: null,
        postCreate: function(){
            this.inherited(arguments);

            this.addChild(new ClaimCertificateContainer({
                isApproval: true,
                title: nls.claimCertificate,
                project: this.project,
                claimCertificate: this.claimCertificate,
                locked: true
            }));

            this.addChild(new ProjectBreakdown({
                id: 'main-project_breakdown',
                title: nls.projectBreakdown,
                rootProject: this.project,
                workArea: this,
                locked: true,
                claimCertificate: this.claimCertificate
            }));
            
            this.addChild(new ClaimCertificateNoteContainer({
                id: String(this.claimCertificate.id)+'-ClainCertificateNote',
                claimCertificate: this.claimCertificate
            }));
        },
        createContentPaneTab: function(id, title, content, closable, type){
            var pane = new dijit.layout.ContentPane({
                closable: closable,
                id: id,
                style: "padding:0px;border:0px;margin:0px;overflow:hidden;",
                title: buildspace.truncateString(title, 35),
                content: content
            });

            this.addChild(pane);
            this.selectChild(pane);

            pane.pane_info = {
                name: title,
                type: type,
                id: id
            };
        },
        removeBillTab: function (){
            var tac = this.getChildren();
            for(var i in tac){
                if(typeof tac[i].pane_info != "object") continue;
                this.removeChild(tac[i]);
                tac[i].destroy();
            }
        },
        removeTabByType: function (type) {
            var tac = this.getChildren();
            for(var i in tac){
                if(typeof tac[i].pane_info != "object") continue;
                if(tac[i].pane_info.type == type){
                    this.removeChild(tac[i]);
                    tac[i].destroy();
                    continue;
                }
            }
        },
        initTab: function(item, options){
            var opened = false;
            var tac = this.getChildren();
            for(var i in tac){
                if(typeof tac[i].pane_info != "object") continue;
                if(tac[i].pane_info.id == item.id+'-form_'+item.type){
                    opened = true;
                    this.selectChild(tac[i]);
                    continue;
                }
            }
            if(!opened){
                this.makeTab(item, options, true);
            }
        },
        makeTab: function(item, options, closable){
            var billType = null,
                widget = null,
                id = null,
                title = null;

            if (item.bill_type != undefined && parseInt(item.bill_type[0]) == buildspace.apps.PostContract.ProjectStructureConstants.BILL_TYPE_PRELIMINARY) {
                billType = buildspace.apps.PostContract.getBillTypeText(item.bill_type);
                widget = new PreliminariesBillManager(options);
                id = item.id+'-form_'+item.type;
                title = buildspace.truncateString(item.title, 35)+' :: '+billType;
            } else {
                if(isNaN(item.id)){
                    switch(parseInt(item.type)){
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_VARIATION_ORDER:
                            widget = new VariationOrder(options);
                            id = item.id+'-form_'+item.type;
                            title = item.title;

                        break;
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_REQUEST_FOR_VARIATION_CLAIM:
                            widget = new RequestForVariationClaim(options);
                            id = item.id+'-form_'+item.type;
                            title = item.title;

                        break;
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_MATERIAL_ON_SITE:
                            widget = new MaterialOnSite(options);
                            id = item.id+'-form_'+item.type;
                            title = item.title;

                            break;
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT:
                            widget = new PostContractClaim(options);
                            id = item.id+'-form_'+item.type;
                            title = item.title;

                            break;
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEBIT_CREDIT_NOTE:
                            lang.mixin(options, {
                                locked: true,
                            });
                            widget = new DebitCreditNoteContainer(options);
                            id = item.id+'-form_'+item.type;
                            title = item.title;
                            break;
                        default:
                            widget = null;
                            id = item.id+'-form_'+item.type;
                            title = buildspace.truncateString(item.title, 35);
                            break;
                    }
                }else{
                    switch(parseInt(item.type)){
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_BILL:
                            billType = buildspace.apps.PostContract.getBillTypeText(item.bill_type);
                            widget = null;
                            id = item.id+'-form_'+item.type;
                            title = buildspace.truncateString(item.title, 35)+' :: '+billType;

                            widget = new StandardBillManager(options);
                            break;
                        default:
                            widget = null;
                            id = item.id+'-form_'+item.type;
                            title = buildspace.truncateString(item.title, 35);
                            
                            break;
                    }
                }
            }

            this.createContentPaneTab(id, title, widget, closable, item.type);
        }
    });
});
