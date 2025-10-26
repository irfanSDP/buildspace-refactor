define('buildspace/apps/PostContractReport/WorkArea',[
    'dojo/_base/declare',
    './ProjectProperties',
    './ProjectBreakdown',
    './StandardBillManager',
    './ClaimRevisionSettingsForm',
    './ClaimCertificateContainer',
    './Preliminaries/PreliminariesBillManager',
    './VariationOrder',
    './MaterialOnSiteReport',
    'dojo/i18n!buildspace/nls/PostContract'], function(declare, ProjectProperties, ProjectBreakdown, StandardBillManager, ClaimRevisionSettingsForm, ClaimCertificateContainer, PreliminariesBillManager, VariationOrder, MaterialOnSiteReport, nls){

    var ClaimRevisionContainer = declare('buildspace.apps.PostContractReport.ProjectRevisionContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
        gutters: false,
        projectId: null,
        rootProject: null,
        workarea: null,
        postCreate: function() {
            this.inherited(arguments);
            var claimRevisionSettingsForm = this.claimRevisionSettingsForm = ClaimRevisionSettingsForm({
                projectId: this.projectId,
                rootProject: this.rootProject,
                workarea: this.workarea
            });
            this.addChild(claimRevisionSettingsForm);
        }
    });

    return declare('buildspace.apps.PostContractReport.WorkArea', dijit.layout.TabContainer, {
        region: "center",
        style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        rootProject: null,
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

                    var projectProperties = new ProjectProperties({
                            title: nls.projectProperties,
                            rootProject: self.rootProject,
                            id: 'projectProperties-' + self.rootProject.id,
                            data: data
                        }),
                        projectBreakdown = new ProjectBreakdown({
                            id: 'main-project_breakdown',
                            title: nls.projectBreakdown,
                            rootProject: self.rootProject,
                            workArea: self
                        });

                    self.addChild(projectBreakdown);
                    self.addChild(projectProperties);

                    var claimRevisionContainer;

                    if(self.rootProject.post_contract_type_id == buildspace.constants.POST_CONTRACT_TYPE_NORMAL) {
                        claimRevisionContainer = new ClaimRevisionContainer({
                            title: nls.claimRevision,
                            id: self.rootProject.id[0]+'-ProjectRevision',
                            projectId: self.rootProject.id[0],
                            rootProject: self.rootProject,
                            workarea: self
                        });
                    }else if(self.rootProject.post_contract_type_id == buildspace.constants.POST_CONTRACT_TYPE_NEW) {
                        claimRevisionContainer = new ClaimCertificateContainer({
                            title: nls.claimCertificates,
                            id: self.rootProject.id[0]+'-ClaimCertificateContainer',
                            project: self.rootProject,
                            workArea: self
                        });
                    }

                    if(claimRevisionContainer){
                        self.addChild(claimRevisionContainer);
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
        removeBillTab: function (){
            var tac = this.getChildren();
            for(var i in tac){
                if(typeof tac[i].pane_info != "object") continue;
                if(tac[i].pane_info.type == buildspace.apps.PostContractReport.ProjectStructureConstants.TYPE_BILL){
                    this.removeChild(tac[i]);
                    tac[i].destroy();
                    continue;
                }
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
            var billType, widget, id, title;

            if (item.bill_type != undefined && parseInt(item.bill_type[0]) == buildspace.apps.PostContractReport.ProjectStructureConstants.BILL_TYPE_PRELIMINARY) {
                billType = buildspace.apps.PostContractReport.getBillTypeText(item.bill_type);
                widget = new PreliminariesBillManager(options);
                id = item.id+'-form_'+item.type;
                title = buildspace.truncateString(item.title, 35)+' :: '+billType;
            } else {
                switch(parseInt(item.type)){
                    case buildspace.apps.PostContractReport.ProjectStructureConstants.TYPE_BILL:
                        billType = buildspace.apps.PostContractReport.getBillTypeText(item.bill_type);
                        widget = new StandardBillManager(options);
                        id = item.id+'-form_'+item.type;
                        title = buildspace.truncateString(item.title, 35)+' :: '+billType;

                        break;
                    case buildspace.apps.PostContractReport.ProjectStructureConstants.TYPE_VARIATION_ORDER:
                        widget = new VariationOrder(options);
                        id = item.id+'-form_'+item.type;
                        title = item.title;

                        break;
                    case buildspace.apps.PostContractReport.ProjectStructureConstants.TYPE_MATERIAL_ON_SITE:
                        widget = new MaterialOnSiteReport(options);
                        id = item.id+'-form_'+item.type;
                        title = item.title;

                        break;

                    default:
                        widget = null;
                        id = item.id+'-form_'+item.type;
                        title = buildspace.truncateString(item.title, 35);
                        break;
                }
            }

            this.createContentPaneTab(id, title, widget, closable, item.type);
        }
    });
    
});
