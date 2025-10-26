define('buildspace/apps/Tendering/WorkArea',[
    'dojo/_base/declare',
    './ProjectProperties',
    './ProjectBreakdown',
    './ProjectRevisionSettingsForm',
    'buildspace/apps/TenderAlternative/TenderAlternativeContainer',
    'dojo/i18n!buildspace/nls/Tendering'], function(declare, ProjectProperties, ProjectBreakdown, ProjectRevisionSettingsForm, TenderAlternativeContainer, nls){
    var ProjectRevisionContainer = declare('buildspace.apps.Tendering.ProjectRevisionContainer', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;margin:0px;border:0px;height:100%;",
        gutters: false,
        projectId: null,
        rootProject: null,
        workarea: null,
        postCreate: function() {
            this.inherited(arguments);
            var projectRevisionSettingsForm = this.projectRevisionSettingsForm = ProjectRevisionSettingsForm({
                projectId: this.projectId,
                rootProject: this.rootProject,
                workarea: this.workarea
            });
            this.addChild(projectRevisionSettingsForm);
        }
    });

    return declare('buildspace.apps.Tendering.WorkArea', dijit.layout.TabContainer, {
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
                    url: "tendering/mainInfoForm",
                    content: { id: parseInt(String(self.rootProject.id)) },
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

                    var projectRevisionTabIdx = 2;

                    if(parseInt(String(self.rootProject.tender_type_id)) && parseInt(String(self.rootProject.has_tender_alternative))){
                        self.addChild(new TenderAlternativeContainer({
                            title: nls.tenderAlternatives,
                            project: self.rootProject,
                            id: String(self.rootProject.id)+'-form_1337',
                            projectBreakdownGrid: projectBreakdownTab.grid,
                            workArea: self
                        }), 2);

                        projectRevisionTabIdx = 3;
                    }

                    self.addChild(new ProjectRevisionContainer({
                        title: nls.billRevisionSettings,
                        id: String(self.rootProject.id)+'-ProjectRevision',
                        projectId: parseInt(String(self.rootProject.id)),
                        rootProject: self.rootProject,
                        workarea: self
                    }), projectRevisionTabIdx);
                    
                    self.selectChild(projectBreakdownTab);

                    self.checkInprogressImportAddendum();
                });
            });
        },
        checkInprogressImportAddendum: function(){
            if(this.rootProject && !isNaN(parseInt(String(this.rootProject.id))) && parseInt(String(this.rootProject.tender_type_id)) === buildspace.constants.TENDER_TYPE_PARTICIPATED){
                var pb = new dijit.ProgressBar({
                    value: 0,
                    title: "Importing Addendum Bills",
                    layoutAlign:"center"
                });

                var box = new dijit.Dialog({
                    content: pb,
                    style: "background:#fff;padding:5px;height:78px;width:280px;",
                    splitter: false
                });
                box.closeButtonNode.style.display = "none";
                box._onKey = function(evt){
                    var key = evt.keyCode;
                    if (key == keys.ESCAPE) {
                        dojo.stopEvent(evt);
                    }
                };
                box.onHide = function() {
                    box.destroyRecursive();
                };

                this.importAddendumProgress(box, pb);
            }
        },
        importAddendumProgress: function(box, pb){
            var project = this.rootProject,
                self = this;
            dojo.xhrPost({
                url: 'tendering/getImportAddendumBillProgress',
                content: {
                    id: parseInt(String(project.id))
                },
                handleAs: 'json',
                sync: false,
                load: function(data) {
                    var totalImportedBills = parseInt(data.total_imported_bills);
                    var totalBills = parseInt(data.total_bills);

                    if(data.exists && totalBills > 0 && totalImportedBills != totalBills){
                        if(!box.open){
                            box.show();
                        }

                        box.set({title:"Importing "+totalImportedBills+"/"+totalBills+" Addendum Bills"});

                        var i = totalImportedBills / totalBills * 100;
                        pb.set({value: i});

                        setTimeout(function(){self.importAddendumProgress(box, pb);}, 5000);
                    }else{
                        if(box.open){
                            box.hide();
                        }
                        
                        self.reloadProjectBreakdown();
                        setTimeout(function(){self.reloadRevision();}, 1000);// a bit delay to wait for DOM to be ready before redraw revision table
                    }
                },
                error: function(error) {
                    if(box.open){
                        box.hide();
                    }
                }
            });
        },
        reloadProjectBreakdown: function(){
            var projectBreakdown = dijit.byId('main-project_breakdown');
            if(projectBreakdown){
                projectBreakdown.grid.reload();
            }
            var projectTenderAlternativeGrid = dijit.byId(String(this.rootProject.id)+"-tenderAlternative-tenderAlternativeListGrid");
            if(projectTenderAlternativeGrid){
                projectTenderAlternativeGrid.reload();
            }
        },
        reloadRevision: function(){
            var revisionContainer = dijit.byId(parseInt(String(this.rootProject.id))+'-ProjectRevision');
            if(revisionContainer){
                dojo.empty(revisionContainer.projectRevisionSettingsForm.tableContainer);
                revisionContainer.projectRevisionSettingsForm.masterGenerateProjectRevisionTableRow();
            }
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
            };
        },
        removeBillTab: function (){
            var tac = this.getChildren();
            for(var i in tac){
                if(typeof tac[i].pane_info != "object") continue;
                if(tac[i].pane_info.type == buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL){
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
            var widget, id, title;
            switch(parseInt(item.type)){
                case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_BILL:
                    var billType = buildspace.apps.Tendering.getBillTypeText(item.bill_type);
                    widget = new buildspace.apps.Tendering.BillManager(options);
                    id = item.id+'-form_'+item.type;
                    title = buildspace.truncateString(item.title, 35)+' :: '+billType;
                    break;
                case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SUPPLY_OF_MATERIAL_BILL:
                    widget = new buildspace.apps.Tendering.SupplyOfMaterialBillManager(options);
                    id = item.id+'-form_'+item.type;
                    title = buildspace.truncateString(item.title, 35);
                    break;
                case buildspace.apps.Tendering.ProjectStructureConstants.TYPE_SCHEDULE_OF_RATE_BILL:
                    widget = new buildspace.apps.Tendering.ScheduleOfRateBillManager(options);
                    id = item.id+'-form_'+item.type;
                    title = buildspace.truncateString(item.title, 35);
                    break;
                case 9999999://sub packages
                    widget = new buildspace.apps.Tendering.SubPackages(options);
                    id = item.id+'-form_'+item.type;
                    title = nls.subPackages;
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