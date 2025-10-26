define(["../../../dojo/_base/declare",
    "dojo/when",
    'buildspace/apps/ProjectBuilderReport/Builder',
    'buildspace/apps/TenderingReport/Builder',
    'buildspace/apps/PostContractReport/Builder',
    'dojo/i18n!buildspace/nls/ProjectAnalyzer'], function(declare, when, ProjectBuilderReport, TenderingReport, PostContractReport, nls){

    return declare('buildspace.apps.ProjectAnalyzerReport', buildspace.apps._App, {
        win: null,
        type: null,
        opt: null,
        project: null,
        init: function(args){
            this.type = args.type;
            var project = this.project = args.project,
                opt = this.opt = args.opt,
                container = new dijit.layout.BorderContainer({
                    style:"padding:0;width:100%;height:100%;",
                    gutters: false,
                    liveSplitters: true
                });

            var moduleName = this.getModuleName();

            var win = this.win = new buildspace.widget.Window({
                title: moduleName + ' > ' + nls.projectAnalyzer+' - '+buildspace.truncateString(project.title, 100),
                onClose: dojo.hitch(this, "kill")
            });

            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border-top:0px;padding:2px;overflow:hidden;"});
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backTo + ' ' + moduleName,
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
                    analysisContainer = new buildspace.apps.ProjectAnalyzerReport.ScheduleOfRateContainer({
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
                var analysisContainer = opt == 'scheduleOfRateAnalysis' ? new buildspace.apps.ProjectAnalyzerReport.ScheduleOfRateContainer({
                    project: project,
                    region: "center"
                }) : new buildspace.apps.ProjectAnalyzerReport.ResourceContainer({
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
        openBuilderWin: function(project){
            this.kill();
            this.project = project;
            this.opt = null;

            var moduleName = this.getModuleName();

            this.win = new buildspace.widget.Window({
                title: moduleName + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            var builder;

            switch(this.type)
            {
                case buildspace.constants.STATUS_PRETENDER:
                    builder = new ProjectBuilderReport({
                        project: project
                    });
                    break;
                case buildspace.constants.STATUS_TENDERING:
                    builder = new TenderingReport({
                        project: project
                    });
                    break;
                case buildspace.constants.STATUS_POSTCONTRACT:
                    builder = PostContractReport({
                        project: project
                    });
                    break;
                default:
                    builder = new ProjectBuilderReport({
                        project: project
                    });
                    break;
            }

            this.win.addChild(builder);
            this.win.show();
            this.win.startup();
        },
        getModuleName: function() {
            var moduleName = null;

            switch(this.type)
            {
                case buildspace.constants.STATUS_PRETENDER:
                    moduleName = nls.ProjectBuilderReport;
                    break;
                case buildspace.constants.STATUS_TENDERING:
                    moduleName = nls.tenderingReport;
                    break;
                case buildspace.constants.STATUS_POSTCONTRACT:
                    moduleName = nls.postContractReport;
                    break;
                default:
                    moduleName = nls.ProjectBuilderReport;
                    break;
            }

            return moduleName;
        },
        kill: function(){
            if (typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});