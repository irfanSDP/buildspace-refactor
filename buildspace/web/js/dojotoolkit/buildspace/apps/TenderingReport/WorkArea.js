define('buildspace/apps/TenderingReport/WorkArea',[
    'dojo/_base/declare',
    './ProjectProperties',
    './ProjectBreakdown',
    'dojo/i18n!buildspace/nls/Tendering'], function(declare, ProjectProperties, ProjectBreakdown, nls){

    return declare('buildspace.apps.TenderingReport.WorkArea', dijit.layout.TabContainer, {
        region: "center",
        style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        rootProject: null,
        explorer: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "projectAnalyzer/getAnalysisStatus/pid/"+self.rootProject.id,
                    content: { pid: self.rootProject.id },
                    handleAs: "json"
                }).then(function(status){
                    dojo.xhrGet({
                        url: "tendering/mainInfoForm",
                        content: { id: self.rootProject.id },
                        handleAs: "json"
                    }).then(function(data){
                        pb.hide();

                        buildspace.currencyAbbreviation = buildspace.billCurrencyAbbreviation = data.currency;

                        var projectProperties = new ProjectProperties({
                                id: 'main-project_properties',
                                title: nls.projectProperties,
                                rootProject: self.rootProject,
                                id: 'projectProperties-' + self.rootProject.id,
                                explorer: self.explorer,
                                data: data
                            }),
                            projectBreakdown = new ProjectBreakdown({
                                id: 'main-project_breakdown',
                                title: nls.projectBreakdown,
                                rootProject: self.rootProject,
                                explorer: self.explorer,
                                analysisStatus: status
                            });

                        self.addChild(projectBreakdown);
                        self.addChild(projectProperties);

                        self.selectChild(projectBreakdown);
                    });
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
                if(tac[i].pane_info.type == buildspace.apps.TenderingReport.ProjectStructureConstants.TYPE_BILL){
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
        makeTab: function(item, options, closable){
            var billType, widget, id, title;
            switch(parseInt(item.type)){
                case buildspace.apps.TenderingReport.ProjectStructureConstants.TYPE_BILL:
                    billType = buildspace.apps.TenderingReport.getBillTypeText(item.bill_type);
                    widget = new buildspace.apps.TenderingReport.BillManager(options);
                    id = item.id+'-form_'+item.type;
                    title = buildspace.truncateString(item.title, 35)+' :: '+billType;
                    break;
                case 9999999://sub packages
                    widget = new buildspace.apps.TenderingReport.SubPackages(options);
                    id = item.id+'-form_'+item.type;
                    title = nls.subPackages;
                    break;
                default:
                    widget = null;
                    id = item.id+'-form_'+item.type;
                    title = buildspace.truncateString(item.title, 35);
                    break;
            }

            this.createContentPaneTab(id, title, widget, closable, item.type);
        }
    });
});