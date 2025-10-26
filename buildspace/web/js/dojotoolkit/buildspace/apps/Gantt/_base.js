define([
    'dojo/_base/declare',
    "dojo/html",
    "dijit/form/ToggleButton",
    'buildspace/apps/ProjectManagement/Builder',
    './GanttContainer',
    'dojo/i18n!buildspace/nls/ProjectManagement'], function(declare, html, ToggleButton, ProjectManagement, GanttContainer, nls){

    return declare('buildspace.apps.Gantt', buildspace.apps._App, {
        win: null,
        project: null,
        projectSchedule: null,
        nonWorkingDays: null,
        init: function(args){
            this.project = args.project;
            this.projectSchedule = args.projectSchedule;
            this.nonWorkingDays = args.nonWorkingDays;
            this.planGanttContainer = new GanttContainer({
                id: 'planView-'+this.projectSchedule.id,
                type: 'planView',
                project: this.project,
                projectSchedule: this.projectSchedule,
                nonWorkingDays: this.nonWorkingDays
            });

            this.win = new buildspace.widget.Window({
                title: nls.projectManagement + '::Gantt > ' + buildspace.truncateString(this.project.title, 100)  + ' (' + buildspace.truncateString(this.projectSchedule.title, 100) + ')',
                onClose: dojo.hitch(this, "kill")
            });

            var container = new dijit.layout.BorderContainer({
                style:"padding:0;width:100%;height:100%;overflow:none;",
                gutters: false
            });

            var self = this;

            var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border:0px;padding:2px;overflow:hidden;"});
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.backTo + ' ' + nls.projectManagement,
                    iconClass: "icon-16-container icon-16-directional_left",
                    style:"outline:none!important;",
                    onClick: function(e){
                        return self.openBuilderWin(self.project);
                    }
                })
            );

            toolbar.addChild(new dijit.ToolbarSeparator());

            toolbar.addChild(
                new dijit.form.ToggleButton({
                    id: 'toggleView-'+this.projectSchedule.id,
                    label: nls.switchToActualProgressView,
                    iconClass: "icon-16-container icon-16-stats_bars",
                    style:"outline:none!important;",
                    onChange: function(val){
                        if(val){
                            return self.createActualProgressView();
                        }else{
                            self.createPlanProgressView();
                        }
                    }
                })
            );

            container.addChild(toolbar);
            container.addChild(this.planGanttContainer);

            this.container = container;

            this.win.addChild(container);
            this.win.show();
            this.win.startup();
        },
        createActualProgressView: function(){
            dijit.byId('toggleView-'+this.projectSchedule.id).set("label", nls.switchToPlanProgressView);

            var planViewContainer = dijit.byId('planView-'+this.projectSchedule.id);

            if(planViewContainer){
                planViewContainer.destroyRecursive();
            }

            if(!dijit.byId('actualView-'+this.projectSchedule.id)){
                this.container.addChild(new GanttContainer({
                    id: 'actualView-'+this.projectSchedule.id,
                    type: 'actualView',
                    project: this.project,
                    projectSchedule: this.projectSchedule,
                    nonWorkingDays: this.nonWorkingDays
                }));
            }
        },
        createPlanProgressView: function(){
            dijit.byId('toggleView-'+this.projectSchedule.id).set("label", nls.switchToActualProgressView);

            var actualViewContainer = dijit.byId('actualView-'+this.projectSchedule.id);

            if(actualViewContainer){
                actualViewContainer.destroyRecursive();
            }

            if(!dijit.byId('planView-'+this.projectSchedule.id)){
                this.planGanttContainer = new GanttContainer({
                    id: 'planView-'+this.projectSchedule.id,
                    type: 'planView',
                    project: this.project,
                    projectSchedule: this.projectSchedule,
                    nonWorkingDays: this.nonWorkingDays
                });

                this.container.addChild(this.planGanttContainer);
            }
        },
        openBuilderWin: function(project){
            this.kill();
            this.project = project;

            this.win = new buildspace.widget.Window({
                title: nls.projectManagement + ' > ' + buildspace.truncateString(project.title, 100)  + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            this.win.addChild(new ProjectManagement({
                project: project
            }));
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