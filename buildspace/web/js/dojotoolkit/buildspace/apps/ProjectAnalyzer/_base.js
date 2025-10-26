define(["../../../dojo/_base/declare",
    "dojo/when",
    'buildspace/apps/ProjectBuilder/Builder',
    'buildspace/apps/Tendering/Builder',
    'buildspace/apps/PostContract/Builder',
    'dojo/i18n!buildspace/nls/ProjectAnalyzer'], function(declare, when, ProjectBuilder, Tendering, PostContract, nls){

    return declare('buildspace.apps.ProjectAnalyzer', buildspace.apps._App, {
        win: null,
        type: null,
        opt: null,
        project: null,
        init: function(args){
            this.type = args.type;
            var project = this.project = args.project,
                opt = this.opt = args.opt,
                container = new dijit.layout.BorderContainer({
                    style:"padding:0;margin:0;width:100%;height:100%;",
                    gutters: false,
                    liveSplitters: true
                });

            var moduleTitle = this.getModuleTitle();

            var win = this.win = new buildspace.widget.Window({
                title: moduleTitle + ' > ' + nls.projectAnalyzer+' - '+buildspace.truncateString(project.title, 100),
                onClose: dojo.hitch(this, "kill")
            });

            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border-top:0px;padding:2px;overflow:hidden;"});
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backTo + ' ' + moduleTitle,
                    iconClass: "icon-16-container icon-16-directional_left",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openBuilderWin', project)
                })
            );

            if(this.type == buildspace.constants.STATUS_TENDERING && opt == 'scheduleOfRateAnalysis'){
                var contractorsXhr = dojo.xhrGet({
                    url: "projectAnalyzer/getContractors/id/"+project.id,
                    handleAs: "json"
                });

                contractorsXhr.then(function(data){
                    analysisContainer = new buildspace.apps.ProjectAnalyzer.ScheduleOfRateContainer({
                        project: project,
                        contractorList: data,
                        region: "center"
                    });

                    analysisContainer.startup();

                    container.addChild(toolbar);
                    container.addChild(analysisContainer);

                    win.addChild(container);
                    win.show();
                    win.startup();
                });
            }else{
                var analysisContainer = opt == 'scheduleOfRateAnalysis' ? new buildspace.apps.ProjectAnalyzer.ScheduleOfRateContainer({
                    project: project,
                    region: "center"
                }) : new buildspace.apps.ProjectAnalyzer.ResourceContainer({
                    project: project,
                    region: "center",
                    type: this.type
                });

                analysisContainer.startup();

                container.addChild(toolbar);
                container.addChild(analysisContainer);

                win.addChild(container);
                win.show();
                win.startup();
            }
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
                case buildspace.constants.STATUS_POSTCONTRACT:
                    moduleTitle = nls.postContract;
                    break;
                default:
                    moduleTitle = nls.ProjectBuilder;
                    break;
            }

            return moduleTitle;
        },
        openBuilderWin: function(project){
            this.kill();
            this.project = project;
            this.opt = null;

            var moduleTitle = this.getModuleTitle();

            this.win = new buildspace.widget.Window({
                title: moduleTitle + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            var builder;

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
                case buildspace.constants.STATUS_POSTCONTRACT:
                    builder = PostContract({
                        project: project
                    });
                    break;
                default:
                    builder = ProjectBuilder({
                        project: project
                    });
                    break;
            }

            this.win.addChild(builder);
            this.win.show();
            this.win.startup();
        },
        kill: function(){
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});