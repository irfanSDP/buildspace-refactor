define('buildspace/apps/Gantt/GanttContainer',[
    "jquery",
    "jqueryui",
    'dojo/_base/declare',
    "dojo/dom-geometry",
    "dojo/dom",
    "dojo/json",
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "./TaskEditorDialog",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dojo/text!./templates/gantt.html",
    "dojo/text!./templates/readOnlyGantt.html",
    'dojo/i18n!buildspace/nls/ProjectManagement'
], function($, jqueryui, declare, domGeom, dom, JSON, DropDownButton, DropDownMenu, MenuItem,  TaskEditorDialog, _WidgetBase, _TemplatedMixin, template, readOnlyTemplate, nls){

    var GanttTemplate = declare("buildspace.apps.Gantt.GanttTemplate", [_WidgetBase, _TemplatedMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        style: "border:none;padding:0;overflow:hidden;",
        type: 'planView',
        project: null,
        projectSchedule: null,
        nonWorkingDays: null,
        relativeUrl: relativeUrlRoot,
        workspaceId: "workSpaceGantt",
        nls: nls,
        ganttMaster: null,
        constructor: function(args){
            this.templateString = args.type == 'planView' ? template : readOnlyTemplate;

            this.inherited(arguments);
        },
        postCreate: function(){
            this.inherited(arguments);

            // here starts gantt initialization
            this.ganttMaster = new GanttMaster();
            this.ganttMaster.projectScheduleStartDate = new Date(""+this.projectSchedule.start_date[0]+"");
            this.ganttMaster.calendarSettings = {
                'satIsHoly': this.nonWorkingDays ? this.nonWorkingDays.sat_is_holy : true,
                'sunIsHoly': this.nonWorkingDays ? this.nonWorkingDays.sun_is_holy : true,
                'holidays': this.nonWorkingDays ? this.nonWorkingDays.holidays : ""
            };

            this.ganttMaster.customTaskEditorCallBack = this.openTaskEditor;
            this.ganttMaster.projectSchedule = this.projectSchedule;
            this.ganttMaster.resourceUrl = '' + this.relativeUrl + '/css/gantt/res/';
        },
        startup: function(){

            this.inherited(arguments);

            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });

            pb.show().then(function(){
                dojo.xhrGet({
                    url: self.type == 'planView' ? 'TaskItemList/'+self.projectSchedule.id : 'ActualTaskItemList/'+self.projectSchedule.id,
                    handleAs: 'json',
                    load: function(resp) {
                        pb.hide();
                        self.initGantt(resp);
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
        initGantt: function(data){
            var workSpace = $("#"+this.workspaceId);
            this.ganttMaster.init(workSpace);

            //data load
            this.ganttMaster.loadProject(data);
            this.ganttMaster.checkpoint(); //empty the undo stack
        },
        initToolbar: function(){
            var ganttToolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border:0px;padding:2px;overflow:hidden;"});

            if(this.type == "planView"){
                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.insertAbove,
                        iconClass: "icon-16-container icon-16-directional_up",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttInsertAbove')
                    })
                );

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.insertBelow,
                        iconClass: "icon-16-container icon-16-directional_down",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttInsertBelow')
                    })
                );

                ganttToolbar.addChild(new dijit.ToolbarSeparator());

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.indent,
                        iconClass: "icon-16-container icon-16-indent",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttIndent')
                    })
                );

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.outdent,
                        iconClass: "icon-16-container icon-16-outdent",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttOutdent')
                    })
                );

                ganttToolbar.addChild(new dijit.ToolbarSeparator());

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.moveUp,
                        iconClass: "icon-16-container icon-16-arrow_up2",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttMoveUp')
                    })
                );

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.moveDown,
                        iconClass: "icon-16-container icon-16-arrow_down2",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttMoveDown')
                    })
                );

                ganttToolbar.addChild(new dijit.ToolbarSeparator());

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.zoomIn,
                        iconClass: "icon-16-container icon-16-zoom_in",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttZoomIn')
                    })
                );

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.zoomOut,
                        iconClass: "icon-16-container icon-16-zoom_out",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttZoomOut')
                    })
                );

                ganttToolbar.addChild(new dijit.ToolbarSeparator());

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.delete,
                        iconClass: "icon-16-container icon-16-delete",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttDelete')
                    })
                );

                ganttToolbar.addChild(new dijit.ToolbarSeparator());

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.criticalPath,
                        iconClass: "icon-16-container icon-16-random",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttCriticalPath')
                    })
                );

                ganttToolbar.addChild(new dijit.ToolbarSeparator());

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.print,
                        iconClass: "icon-16-container icon-16-print",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttPrint', buildspace.constants.PROJECT_SCHEDULE_PRINT_TYPE_PLAN)
                    })
                );

                ganttToolbar.addChild(new dijit.ToolbarSeparator());

                var createScheduleDropDownMenu = new DropDownMenu({ style: "display: none;"});

                createScheduleDropDownMenu.addChild(new MenuItem({
                    label: nls.costVsTime,
                    onClick: dojo.hitch(this, 'ganttChart')
                }));

                createScheduleDropDownMenu.addChild(new MenuItem({
                    label: nls.accumulativeCost,
                    onClick: dojo.hitch(this, 'ganttAccumulativeChart')
                }));

                ganttToolbar.addChild(new DropDownButton({
                    label: nls.charts,
                    iconClass: "icon-16-container icon-16-stats_lines",
                    dropDown: createScheduleDropDownMenu
                }));

            }else{
                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.zoomIn,
                        iconClass: "icon-16-container icon-16-zoom_in",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttZoomIn')
                    })
                );

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.zoomOut,
                        iconClass: "icon-16-container icon-16-zoom_out",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttZoomOut')
                    })
                );

                ganttToolbar.addChild(new dijit.ToolbarSeparator());

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.criticalPath,
                        iconClass: "icon-16-container icon-16-random",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttCriticalPath')
                    })
                );

                ganttToolbar.addChild(new dijit.ToolbarSeparator());

                ganttToolbar.addChild(
                    new dijit.form.Button({
                        label: nls.print,
                        iconClass: "icon-16-container icon-16-print",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'ganttPrint', buildspace.constants.PROJECT_SCHEDULE_PRINT_TYPE_ACTUAL)
                    })
                );
            }

            return ganttToolbar;
        },
        ganttInsertAbove: function(){
            $('#'+this.workspaceId).trigger('addAboveCurrentTask.gantt');
        },
        ganttInsertBelow: function(){
            $('#'+this.workspaceId).trigger('addBelowCurrentTask.gantt');
        },
        ganttIndent: function(){
            $('#'+this.workspaceId).trigger('indentCurrentTask.gantt');
        },
        ganttOutdent: function(){
            $('#'+this.workspaceId).trigger('outdentCurrentTask.gantt');
        },
        ganttMoveUp: function(){
            $('#'+this.workspaceId).trigger('moveUpCurrentTask.gantt');
        },
        ganttMoveDown: function(){
            $('#'+this.workspaceId).trigger('moveDownCurrentTask.gantt');
        },
        ganttZoomIn: function(){
            $('#'+this.workspaceId).trigger('zoomPlus.gantt');
        },
        ganttZoomOut: function(){
            $('#'+this.workspaceId).trigger('zoomMinus.gantt');
        },
        ganttDelete: function(){
            $('#'+this.workspaceId).trigger('deleteCurrentTask.gantt');
        },
        ganttPrint: function(printType){
            window.open('Gantt/'+printType+'/'+this.projectSchedule.id+'/'+this.projectSchedule._csrf_token, '_blank');
        },
        ganttChart: function(){
            window.open('Chart/'+this.projectSchedule.id+'/'+this.projectSchedule._csrf_token, '_blank');
        },
        ganttAccumulativeChart: function(){
            window.open('ChartAccumulativeCost/'+this.projectSchedule.id+'/'+this.projectSchedule._csrf_token, '_blank');
        },
        ganttCriticalPath: function(){
            this.ganttMaster.gantt.showCriticalPath=!this.ganttMaster.gantt.showCriticalPath; this.ganttMaster.redraw();
        },
        ganttSave: function(){
            if(!this.ganttMaster.canWrite)
                return;

            var prj = this.ganttMaster.saveProject();

            var self = this;

            var save = function(){
                var pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.savingData+'...'
                });

                pb.show().then(
                    dojo.xhrPost({
                        url: 'projectManagement/taskItemUpdate',
                        content: {id: self.projectSchedule.id, prj:JSON.stringify(prj), _csrf_token:self.projectSchedule._csrf_token},
                        handleAs: 'json',
                        load: function(resp) {
                            if (resp.data) {
                                self.ganttMaster.loadProject(resp.data); //must reload as "tmp_" ids are now the good ones
                                self.ganttMaster.checkpoint(); //empty the undo stack
                            } else {
                                self.ganttMaster.reset();
                            }
                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    })
                );
            };

            if (this.ganttMaster.deletedTaskIds.length>0) {
                var content = "<div>"+nls.tasksThatWillBePermanentlyRemoved+" : "+this.ganttMaster.deletedTaskIds.length+"</div>";
                var diag = buildspace.dialog.confirm(nls.deletedTasks, content, 80, 280, save);
                diag.show();
            }else{
                save();
            }
        },
        openTaskEditor: function(ganttMaster, task, taskRow){
            var d = TaskEditorDialog({
                ganttMaster: ganttMaster,
                task: task,
                taskRow: taskRow,
                projectSchedule: ganttMaster.projectSchedule
            });

            d.show();
        },
        resize: function(){
            var contentBox   = domGeom.getContentBox(this.domNode.parentNode);
            var ganttDomNode = dom.byId(this.workspaceId);

            domGeom.setContentSize(ganttDomNode, {w: contentBox.w - 1, h: contentBox.h - 38});

            var workSpace = $("#"+this.workspaceId);
            workSpace.trigger("resize.gantt");
        }
    });

    return declare('buildspace.apps.Gantt.GanttContainer', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0;margin:0;border:none;outline:none;width:100%;height:100%;",
        gutters: false,
        type: 'planView',
        project: null,
        projectSchedule: null,
        nonWorkingDays: null,
        ganttMaster: null,
        postCreate: function(){

            var self = this;
            Date.prototype.isHoliday = function(){
                self.isHoliday(this);
            };

            Date.prototype.incrementDateByWorkingDays=function (days) {
                //console.debug("incrementDateByWorkingDays start ",d,days)
                var q = Math.abs(days);
                while (q > 0) {
                    this.setDate(this.getDate() + (days > 0 ? 1 : -1));
                    if (!this.isHoliday())
                        q--;
                }
                return this;
            };

            Date.prototype.distanceInWorkingDays= function (toDate){
                var pos = new Date(this.getTime());
                pos.setHours(23, 59, 59, 999);
                var days = 0;
                var nd=new Date(toDate.getTime());
                nd.setHours(23, 59, 59, 999);
                var end=nd.getTime();
                while (pos.getTime() <= end) {
                    days = days + (self.isHoliday(pos) ? 0 : 1);
                    pos.setDate(pos.getDate() + 1);
                }
                return days;
            };

            this.inherited(arguments);

            var gantt = new GanttTemplate({
                type: this.type,
                project: this.project,
                projectSchedule: this.projectSchedule,
                nonWorkingDays: this.nonWorkingDays
            });

            this.ganttMaster = gantt.ganttMaster;

            this.addChild(gantt.initToolbar());
            this.addChild(gantt);
        },
        isHoliday: function(date){
            var friIsHoly = false;
            var satIsHoly = this.nonWorkingDays ? this.nonWorkingDays.sat_is_holy : true;
            var sunIsHoly = this.nonWorkingDays ? this.nonWorkingDays.sun_is_holy : true;

            pad = function (val) {
                val = "0" + val;
                return val.substr(val.length - 2);
            };

            var holidays = this.nonWorkingDays ? this.nonWorkingDays.holidays : "";

            var ymd = "#" + date.getFullYear() + "_" + pad(date.getMonth() + 1) + "_" + pad(date.getDate()) + "#";
            var md = "#" + pad(date.getMonth() + 1) + "_" + pad(date.getDate()) + "#";
            var day = date.getDay();

            return  (day == 5 && friIsHoly) || (day == 6 && satIsHoly) || (day == 0 && sunIsHoly) || holidays.indexOf(ymd) > -1 || holidays.indexOf(md) > -1;
        }
    });
});