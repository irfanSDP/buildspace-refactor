define('buildspace/apps/ProjectManagement/Builder',[
    'dojo/_base/declare',
    './ProjectProperties',
    './ProjectCalendarMaintenanceForm',
    './ProjectScheduleList',
    'dojo/i18n!buildspace/nls/ProjectManagement'
], function(declare, ProjectProperties, ProjectCalendarMaintenanceForm, ProjectScheduleList, nls){

    var WorkArea = declare('buildspace.apps.ProjectManagement.WorkArea', dijit.layout.TabContainer, {
        region: "center",
        style:"padding:0;margin:0;border:none;outline:none;width:100%;height:100%;",
        project: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                var mainInfoFormXhr = dojo.xhrGet({
                    url: "tendering/mainInfoForm",
                    content: { id: self.project.id },
                    handleAs: "json"
                });

                mainInfoFormXhr.then(function(data){
                    pb.hide();

                    var projectScheduleList = new ProjectScheduleList({
                        id: 'main-project_schedule_list'+self.project.id,
                        project: self.project,
                        workArea: self,
                        title: nls.projectSchedules
                    });

                    self.addChild(projectScheduleList);
                    self.addChild(new ProjectProperties({
                        title: nls.projectProperties,
                        project: self.project,
                        id: 'projectManagement-projectProperties-' + self.project.id,
                        data: data
                    }));
                    self.addChild(new ProjectCalendarMaintenanceForm({
                        title: nls.projectCalendar,
                        project: self.project,
                        id: 'projectManagement-projectCalendar-' + self.project.id
                    }));

                    self.selectChild(projectScheduleList);
                });
            });
        }
    });

    return declare('buildspace.apps.ProjectManagement.Builder', dijit.layout.BorderContainer, {
        style: "padding:0;margin:0;outline:none;width:100%;height:100%;",
        gutters: false,
        project: null,
        postCreate: function(){
            this.inherited(arguments);

            var workarea = this.workarea = new WorkArea({
                project: this.project,
                id: 'workArea_TabContainer'+this.project.id
            });

            workarea.startup();

            this.addChild(workarea);
        }
    });
});