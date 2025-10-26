define('buildspace/apps/Location/ProjectLocationManagement/_base', ["dojo/_base/declare",
    'dojo/aspect',
    './LocationAssignment/LocationAssignmentContainer',
    './BQLocation/BQLocationContainer',
    './BillSettings/BillSettingsContainer',
    './ProgressClaim/ProgressClaimContainer',
    'buildspace/apps/ProjectBuilder/Builder',
    'buildspace/apps/Tendering/Builder',
    'buildspace/apps/PostContract/Builder',
    'dojo/i18n!buildspace/nls/Location'], function(declare, aspect, LocationAssignmentContainer, BQLocationContainer, BillSettingsContainer, ProgressClaimContainer, ProjectBuilder, Tendering, PostContract, nls){

    return declare('buildspace.apps.ProjectLocationManagement', buildspace.apps._App, {
        type: null,
        win: null,
        project: null,
        locationAssignmentContainer: null,
        canEdit: true,
        init: function(args){
            var project = this.project = args.project;
            this.type = args.type, this.canEdit = args.canEdit;

            var moduleTitle = this.getModuleTitle();

            this.win = new buildspace.widget.Window({
                title: moduleTitle + ' > ' + nls.locationManagement+' - '+buildspace.truncateString(project.title, 100),
                onClose: dojo.hitch(this, "kill")
            });

            var tabContainer = this.createMainTab();

            tabContainer.startup();

            this.win.addChild(tabContainer);
            this.win.show();
            this.win.startup();
        },
        createMainTab: function(){
            var locationAssignmentContainer = new LocationAssignmentContainer({
                title: nls.locationAssignment,
                project: this.project,
                baseApp: this
            });

            var bqLocationContainer = new BQLocationContainer({
                id: this.project.id+"-bqLocationContainer",
                title: nls.bqLocationsView,
                project: this.project,
                baseApp: this
            });

            var toolbar = new dijit.Toolbar({region: "top", style:"border-top:none;border-left:none;border-right:none;padding:2px;width:100%;"});

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backTo + ' ' + this.getModuleTitle(),
                    iconClass: "icon-16-container icon-16-directional_left",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openBuilderWin', this.project)
                })
            );

            var tabContainer = this.tabContainer = new dijit.layout.TabContainer({
                style:"padding:0px;margin:0px;border:0px;width:100%;height:100%;"
            });

            if(parseInt(this.project.status_id) == buildspace.constants.STATUS_POSTCONTRACT){
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(new dijit.form.Button({
                    label: nls.progressClaims,
                    iconClass: "icon-16-container icon-16-list",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openTabByType', tabContainer, "progressClaimContainer")
                }));
            }

            if(this.canEdit){
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(new dijit.form.Button({
                    label: nls.billSettings,
                    iconClass: "icon-16-container icon-16-gear",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openTabByType', tabContainer, "billSettingsContainer")
                }));
                toolbar.addChild(new dijit.ToolbarSeparator());
                toolbar.addChild(new dijit.form.Button({
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(locationAssignmentContainer, 'save')
                }));
            }

            locationAssignmentContainer.addChild(toolbar);

            tabContainer.addChild(locationAssignmentContainer);

            tabContainer.addChild(bqLocationContainer);

            return tabContainer;
        },
        resetBQLocationTab: function(selectTab){
            var tac = this.tabContainer.getChildren();
            var bqLocationContainer;

            for(var i in tac){
                if(tac[i].id == this.project.id+'-bqLocationContainer'){
                    bqLocationContainer = tac[i];
                    break;
                }
            }

            if(bqLocationContainer){
                dojo.xhrGet({
                    url: "location/getLocationCodeLevels",
                    handleAs: "json",
                    preventCache: true,
                    content: {
                        pid: this.project.id
                    },
                    load: function(data){
                        bqLocationContainer.renderContent(data);
                    },
                    error: function(error){
                    }
                });
            }
        },
        openTabByType: function(tabContainer, type){
            var opened = false;
            var tac = tabContainer.getChildren();
            for(var i in tac){
                if(tac[i].id == this.project.id+'-'+type){
                    opened = true;
                    tabContainer.selectChild(tac[i]);
                    break;
                }
            }

            if(!opened){
                var content;
                switch(type){
                    case "progressClaimContainer":
                        content = new ProgressClaimContainer({
                            id: this.project.id+"-"+type,
                            title: nls.progressClaims,
                            closable: true,
                            project: this.project,
                            baseApp: this
                        });
                        break;
                    case "billSettingsContainer":
                        content = new BillSettingsContainer({
                            id: this.project.id+"-"+type,
                            title: nls.billSettings,
                            closable: true,
                            project: this.project,
                            baseApp: this
                        });
                        break;
                    default:
                        break;
                }

                if(content){
                    tabContainer.addChild(content);
                    tabContainer.selectChild(content);
                }
            }
        },
        openBuilderWin: function(project){
            this.kill();
            this.project = project;

            var moduleTitle = this.getModuleTitle();

            this.win = new buildspace.widget.Window({
                title: moduleTitle + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            var builder = null;

            switch(this.type){
                case buildspace.constants.STATUS_PRETENDER:
                    builder = ProjectBuilder({
                        project: project
                    });
                    break;
                case buildspace.constants.STATUS_TENDERING:
                    builder = Tendering({
                        project: project
                    });
                    break;
                default:
                    builder = PostContract({
                        project: project
                    });
                    break;
            }

            this.win.addChild(builder);
            this.win.show();
            this.win.startup();
        },
        getModuleTitle: function() {
            var moduleTitle = null;

            switch(this.type){
                case buildspace.constants.STATUS_PRETENDER:
                    moduleTitle = nls.ProjectBuilder;
                    break;
                case buildspace.constants.STATUS_TENDERING:
                    moduleTitle = nls.tendering;
                    break;
                default:
                    moduleTitle = nls.postContract;
                    break;
            }

            return moduleTitle;
        },
        kill: function(){
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});
