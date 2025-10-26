define('buildspace/apps/PostContract/WorkArea',[
    'dojo/_base/declare',
    './ProjectPropertiesTabContainer',
    './ProjectBreakdown',
    './StandardBillManager',
    './ClaimRevisionSettingsForm',
    './ClaimCertificateContainer',
    './Preliminaries/PreliminariesBillManager',
    './VariationOrder',
    './RequestForVariationClaim',
    './MaterialOnSite',
    './PostContractClaim',
    './DebitCreditNote/DebitCreditNoteContainer',
    'buildspace/apps/Location/ProjectStructureLocationCode/LocationCodeTabContainer',
    'buildspace/apps/PostContract/BudgetReport/GridContainer',
    'buildspace/apps/PostContract/SubProjectItemLink/GridContainer',
    'buildspace/apps/PostContract/SubPackageClaim/GridContainer',
    'dojo/i18n!buildspace/nls/PostContract'],
    function(declare, ProjectPropertiesTabContainer, ProjectBreakdown, StandardBillManager, ClaimRevisionSettingsForm, ClaimCertificateContainer, PreliminariesBillManager, VariationOrder, RequestForVariationClaim, MaterialOnSite, PostContractClaim, DebitCreditNoteContainer, LocationCodeTabContainer, BudgetReportContainer, SubProjectItemLinkContainer, SubProjectClaimContainer, nls){

    var ClaimRevisionContainer = declare('buildspace.apps.PostContract.ProjectRevisionContainer', dijit.layout.BorderContainer, {
        style: "padding:0;width:100%;margin:0;border:none;height:100%;",
        gutters: false,
        projectId: null,
        rootProject: null,
        workarea: null,
        postCreate: function() {
            this.inherited(arguments);

            this.claimRevisionSettingsForm = ClaimRevisionSettingsForm({
                projectId: this.projectId,
                rootProject: this.rootProject,
                workarea: this.workarea
            });

            this.addChild(this.claimRevisionSettingsForm);
        }
    });

    return declare('buildspace.apps.PostContract.WorkArea', dijit.layout.TabContainer, {
        region: "center",
        style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        rootProject: null,
        hidden: [],
        locked: false,
        claimCertificate: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "tendering/mainInfoForm",
                    content: { id: self.rootProject.id },
                    handleAs: "json"
                }).then(function(data){
                    pb.hide();

                    buildspace.currencyAbbreviation = buildspace.billCurrencyAbbreviation = data.currency;

                    var projectBreakdown = self.projectBreakdown = new ProjectBreakdown({
                        id: 'main-project_breakdown',
                        title: nls.projectBreakdown,
                        rootProject: self.rootProject,
                        workArea: self,
                        locked: self.locked,
                        claimCertificate: self.claimCertificate
                    });

                    self.addChild(projectBreakdown);

                    if(self.hidden.indexOf('Project Properties') < 0){
                        self.addChild(new ProjectPropertiesTabContainer({
                            title: nls.projectProperties,
                            rootProject: self.rootProject,
                            data: data
                        }));
                    }

                    if(self.rootProject.post_contract_type_id == buildspace.constants.POST_CONTRACT_TYPE_NORMAL) {
                        self.addChild( new ClaimRevisionContainer({
                            title: nls.claimRevision,
                            id: self.rootProject.id[0]+'-ProjectRevision',
                            projectId: self.rootProject.id[0],
                            rootProject: self.rootProject,
                            workarea: self
                        }));
                    } else if(self.rootProject.post_contract_type_id == buildspace.constants.POST_CONTRACT_TYPE_NEW) {
                        // Use New Post Contract WorkArea.
                        if(self.hidden.indexOf('Claim Certificate') < 0){
                            self.addChild(new ClaimCertificateContainer({
                                title: nls.claimCertificates,
                                id: self.rootProject.id[0]+'-ClaimCertificate',
                                project: self.rootProject,
                                workArea: self
                            }));
                        }
                    }

                    self.selectChild(projectBreakdown);
                });
            });
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
        initLocationCodeContainerTab: function(){
            var id = this.rootProject.id+'-LocationContainerCodeTab';
            var widget = dijit.byId(id);

            if(!widget){
                widget = new LocationCodeTabContainer({
                    title: nls.defineLocations,
                    closable: true,
                    rootProject: this.rootProject,
                    id: id
                });

                this.addChild(widget);
            }

            this.selectChild(widget);
        },
        initBudgetReportContainerTab: function(){
            var id = this.rootProject.id+'-BudgetReportTab';
            var widget = dijit.byId(id);

            if(!widget){
                widget = new BudgetReportContainer({
                    title: nls.budgetReport,
                    closable: true,
                    project: this.rootProject,
                    id: id
                });

                this.addChild(widget);
            }

            this.selectChild(widget);
        },
        initSubProjectItemLinkContainerTab: function(){
            var id = this.rootProject.id+'-SubProjectItemLinkTab';
            var widget = dijit.byId(id);

            if(!widget){
                widget = new SubProjectItemLinkContainer({
                    title: nls.tagSubProjectItems,
                    closable: true,
                    project: this.rootProject,
                    id: id
                });

                this.addChild(widget);
            }

            this.selectChild(widget);
        },
        initSubPackageClaimsContainerTab: function(){
            var id = this.rootProject.id+'-SubPackageClaimsTab';
            var widget = dijit.byId(id);

            if(!widget){
                widget = new SubProjectClaimContainer({
                    title: nls.subPackageClaims,
                    closable: true,
                    project: this.rootProject,
                    id: id
                });

                this.addChild(widget);
            }

            this.selectChild(widget);
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
                id = item.id+'-form_'+item.type,
                title = null;
            var container;

            if (item.bill_type != undefined && parseInt(item.bill_type[0]) == buildspace.apps.PostContract.ProjectStructureConstants.BILL_TYPE_PRELIMINARY) {
                billType = buildspace.apps.PostContract.getBillTypeText(item.bill_type);
                widget = new PreliminariesBillManager(options);
                title = buildspace.truncateString(item.title, 35)+' :: '+billType;
            } else {
                if(isNaN(item.id)){
                    title = item.title;
                    switch(parseInt(item.type)){
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_VARIATION_ORDER:
                            widget = new dijit.layout.BorderContainer({
                                id: 'variationOrderContainer',
                                region: "center",
                                style:"padding:0px;border:none;width:100%;height:100%;",
                                gutters: false
                            });

                            container = new VariationOrder(options);

                            widget.addChild(container);

                            break;
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_REQUEST_FOR_VARIATION_CLAIM:
                            widget = new RequestForVariationClaim(options);

                        break;
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_MATERIAL_ON_SITE:
                            widget = new MaterialOnSite(options);

                            break;
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_POST_CONTRACT_CLAIM_MATERIAL_ON_SITE:
                            widget = new dijit.layout.BorderContainer({
                                id: 'importedMaterialOnSiteContainer',
                                region: "center",
                                style:"padding:0px;border:none;width:100%;height:100%;",
                                gutters: false
                            });

                            container = new PostContractClaim(options);

                            widget.addChild(container);

                            break;
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_ADVANCE_PAYMENT:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEPOSIT:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PURCHASE_ON_BEHALF:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PERMIT:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_KONGSIKONG:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WORK_ON_BEHALF_BACKCHARGE:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_PENALTY:
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_WATER_DEPOSIT:
                            widget = new PostContractClaim(options);

                            break;
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_DEBIT_CREDIT_NOTE:
                            widget = new DebitCreditNoteContainer(options);
                            break;
                        default:
                            widget = null;
                            title = buildspace.truncateString(item.title, 35);

                            break;
                    }
                }else{
                    switch(parseInt(item.type)){
                        case buildspace.apps.PostContract.ProjectStructureConstants.TYPE_BILL:
                            billType = buildspace.apps.PostContract.getBillTypeText(item.bill_type);
                            widget = null;
                            title = buildspace.truncateString(item.title, 35)+' :: '+billType;

                            widget = new StandardBillManager(options);
                            break;
                        default:
                            widget = null;
                            title = buildspace.truncateString(item.title, 35);
                            
                            break;
                    }
                }
            }

            // widget = new dijit.layout.ContentPane({content: id});

            this.createContentPaneTab(id, title, widget, closable, item.type);
        }
    });
});
