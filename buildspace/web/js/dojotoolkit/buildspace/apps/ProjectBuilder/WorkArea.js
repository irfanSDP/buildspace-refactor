define('buildspace/apps/ProjectBuilder/WorkArea',[
    'dojo/_base/declare',
    'dojo/_base/connect',
    './ProjectProperties',
    './ProjectBreakdown',
    'buildspace/apps/TenderAlternative/TenderAlternativeContainer',
    'buildspace/apps/Location/ProjectStructureLocationCode/LocationCodeTabContainer',
    'dojo/i18n!buildspace/nls/ProjectBuilder'], function(declare, connect, ProjectProperties, ProjectBreakdown, TenderAlternativeContainer, LocationCodeTabContainer, nls){
    return declare('buildspace.apps.ProjectBuilder.WorkArea', dijit.layout.TabContainer, {
        region: "center",
        style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;",
        rootProject: null,
        postCreate: function(){
            this.inherited(arguments);

            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
            
            var projectBreakdownTab = this.projectBreakdownTab = this.createProjectBreakdownTab(this.rootProject, true);

            pb.show().then(function(){
                dojo.xhrGet({
                    url: "projectBuilder/mainInfoForm",
                    content: { id: self.rootProject.id },
                    handleAs: "json"
                }).then(function(data){
                    pb.hide();

                    buildspace.currencyAbbreviation = buildspace.billCurrencyAbbreviation = data.currency;

                    self.addChild(new ProjectProperties({
                        title: nls.projectProperties,
                        rootProject: self.rootProject,
                        id: 'projectProperties-' + self.rootProject.id,
                        data: data
                    }), 1);

                    if(parseInt(String(self.rootProject.has_tender_alternative))){
                        self.addChild(new TenderAlternativeContainer({
                            title: nls.tenderAlternatives,
                            project: self.rootProject,
                            id: self.rootProject.id+'-form_1337',
                            projectBreakdownGrid: projectBreakdownTab.grid,
                            workArea: self
                        }), 2);
                    }

                    self.selectChild(projectBreakdownTab);
                });
            });
        },
        createProjectBreakdownTab: function(project, focusTab){
            var projectBreakdownTab = dijit.byId('main-project_breakdown');
            if(projectBreakdownTab){
                this.removeChild(projectBreakdownTab);
                projectBreakdownTab.destroy();
            }
           
            projectBreakdownTab = new ProjectBreakdown({
                id: 'main-project_breakdown',
                title: nls.projectBreakdown,
                rootProject: project,
                workArea: this
            });

            this.addChild(projectBreakdownTab, 0);

            if(focusTab){
                this.selectChild(projectBreakdownTab);
            }

            this.projectBreakdownTab = projectBreakdownTab;

            return projectBreakdownTab;
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
            }
        },
        removeBillTab: function (){
            var tac = this.getChildren();
            for(var i in tac){
                if(typeof tac[i].pane_info != "object") continue;
                if(tac[i].pane_info.type == buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL){
                    this.removeChild(tac[i]);
                    tac[i].destroy();
                    continue;
                }
            }
        },
        initTab: function(item, options, closeable){
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
                closeable = (typeof closeable === 'undefined' || closeable) ?  true : false;
                this.makeTab(item, options, closeable);
            }
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
        makeTab: function(item, options, closable){
            var widget, id, title;
            switch(parseInt(item.type)){
                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_BILL:
                    var billType = buildspace.apps.ProjectBuilder.getBillTypeText(item.bill_type);
                    widget = new buildspace.apps.ProjectBuilder.BillManager(options);
                    id = item.id+'-form_'+item.type;
                    title = buildspace.truncateString(item.title, 35)+' :: '+billType;
                    break;
                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                    widget = new buildspace.apps.ProjectBuilder.SupplyOfMaterialBillManager(options);
                    id = item.id+'-form_'+item.type;
                    title = buildspace.truncateString(item.title, 35);
                    break;
                case buildspace.apps.ProjectBuilder.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                    widget = new buildspace.apps.ProjectBuilder.ScheduleOfRateBillManager(options);
                    id = item.id+'-form_'+item.type;
                    title = buildspace.truncateString(item.title, 35);
                    break;
                case 1337:
                    var projectBreakdown = dijit.byId('main-project_breakdown');
                    widget = new TenderAlternativeContainer({
                        title: nls.tenderAlternatives,
                        project: this.rootProject,
                        id: this.rootProject.id+'-form_1337',
                        projectBreakdownGrid: projectBreakdown.grid,
                        editable: options.editable,
                        workArea: this
                    });
                    
                    this.addChild(widget, 2);//stick tender alternative tab to 3rd tab after project properties tab
                    this.selectChild(widget);

                    return true;
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