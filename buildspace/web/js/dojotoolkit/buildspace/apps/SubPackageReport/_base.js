define(["dojo/_base/declare",
    'dojo/_base/lang',
    "dojo/aspect",
    "dojo/when",
    'buildspace/apps/ProjectBuilderReport/Builder',
    'buildspace/apps/TenderingReport/Builder',
    './SubPackageContainer',
    "buildspace/widget/grid/cells/Formatter",
    'dojo/i18n!buildspace/nls/SubPackages'], function(declare, lang, aspect, when, ProjectBuilder, Tendering, SubPackageContainer, GridFormatter, nls){

    return declare('buildspace.apps.SubPackageReport', buildspace.apps._App, {
        type: null,
        win: null,
        project: null,
        init: function(args){
            var project = this.project = args.project;
            this.type = args.type;

            var moduleName = this.getModuleName();

            this.win = new buildspace.widget.Window({
                title: moduleName + ' > ' + nls.subPackages+' - '+buildspace.truncateString(project.title, 100),
                onClose: dojo.hitch(this, "kill")
            });

            var container = new dijit.layout.BorderContainer({
                style:"padding:0;width:100%;height:100%;",
                gutters: false,
                liveSplitters: true
            });

            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border:0px;padding:2px;overflow:hidden;"});
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backTo + ' ' + moduleName,
                    iconClass: "icon-16-container icon-16-directional_left",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'openBuilderWin', project)
                })
            );

            var content = new SubPackageContainer({
                rootProject: project
            });
            content.startup();

            container.addChild(toolbar);
            container.addChild(content);

            this.win.addChild(container);
            this.win.show();
            this.win.startup();
        },
        openBuilderWin: function(project){
            this.kill();
            this.project = project;

            var moduleName = this.getModuleName();

            this.win = new buildspace.widget.Window({
                title: moduleName + ' > ' + buildspace.truncateString(project.title, 100) + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
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
                    builder = ProjectBuilder({
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

            switch(this.type){
                case buildspace.constants.STATUS_PRETENDER:
                    moduleName = nls.ProjectBuilderReport;
                    break;
                case buildspace.constants.STATUS_TENDERING:
                    moduleName = nls.tenderingReport;
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