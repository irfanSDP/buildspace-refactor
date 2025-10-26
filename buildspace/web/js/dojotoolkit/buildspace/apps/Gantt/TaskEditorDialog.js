define('buildspace/apps/Gantt/TaskEditorDialog',[
    'dojo/_base/declare',
    'dojo/aspect',
    'dojo/_base/lang',
    "dojo/_base/connect",
    "dojo/on",
    "dojo/date/locale",
    "dojo/when",
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-attr",
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/taskEditorForm.html",
    'dijit/form/SimpleTextarea',
    'buildspace/apps/ProjectManagement/TagBillItemGrid',
    'dojo/i18n!buildspace/nls/ProjectManagement'
], function(declare, aspect, lang, connect, on, locale, when, html, dom, keys, domAttr, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, SimpleTextarea, TagBillItemGrid, nls){

    var TaskEditorForm = declare("buildspace.apps.Gantt.TaskEditorForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        ganttMaster: null,
        task: null,
        taskRow: null,
        projectSchedule: null,
        nls: nls,
        style: "padding:5px;overflow:auto;",
        taskProgress: 0,
        postCreate: function(){
            var self = this;

            this.inherited(arguments);
            new SimpleTextarea({
                name: 'description',
                id: 'description',
                trim: true,
                rows: "5",
                value: this.task.description
            }).placeAt(this.descriptionDivNode);

            function toggleCompletedDate(value){
                if(value == 'STATUS_DONE'){
                    self.completedDateNode.required = true;

                    var completedDate = self.task.completedDate ? locale.format(new Date(self.task.completedDate), {datePattern: "yyyy-MM-dd", selector: "date"}) : locale.format(new Date(), {datePattern: "yyyy-MM-dd", selector: "date"});

                    self.completedDateNode.set("value", completedDate);

                    domStyle.set(self.completedDateLabelNode, "display", "inline");
                    domStyle.set(self.completedDateSpanNode, "display", "inline");
                }else{
                    domStyle.set(self.completedDateLabelNode, "display", "none");
                    domStyle.set(self.completedDateSpanNode, "display", "none");

                    self.completedDateNode.required = false;
                    self.completedDateNode.set("value", null);
                }
            };

            toggleCompletedDate(this.task.status);

            this.statusSelectNode.on("change", function(value){
                toggleCompletedDate(value);
            });
        },
        startup: function(){
            var self = this;
            this.inherited(arguments);

            var task = lang.mixin({}, this.task);

            task.progress = this.task.progress ? parseFloat(this.task.progress) : 0;
            task.start = locale.format(new Date(this.task.start), {datePattern: "yyyy-MM-dd", selector: "date"});
            task.end = locale.format(new Date(this.task.end), {datePattern: "yyyy-MM-dd", selector: "date"});
            task.completedDate = locale.format(new Date(this.task.completedDate), {datePattern: "yyyy-MM-dd", selector: "date"});

            //start is readonly in case of deps
            if (task.depends) {
                domAttr.set(this.startDateNode, 'readonly', true);
                domAttr.set(this.startDateNode, 'disabled', true);
            }else{
                domAttr.set(this.startDateNode, 'readonly', false);
                domAttr.set(this.startDateNode, 'disabled'. false);
            }

            this.taskEditorForm.setFormValues(task);

            this.startDateNode.on("blur", function(){
                var value = locale.format(new Date(this.get("value")), {datePattern: "dd/MM/yyyy", selector: "date"});
                if (typeof value == 'undefined' || !Date.isValid(value)) {
                    var oldValue = locale.format(new Date(this.dropDownDefaultValue), {datePattern: "dd/MM/yyyy", selector: "date"});
                    value = oldValue;
                }

                self.startDateChangeCallback(Date.parseString(value));
            });

            this.endDateNode.on("blur", function(){
                var value = locale.format(new Date(this.get("value")), {datePattern: "dd/MM/yyyy", selector: "date"});
                if (typeof value == 'undefined' || !Date.isValid(value)) {
                    var oldValue = locale.format(new Date(this.dropDownDefaultValue), {datePattern: "dd/MM/yyyy", selector: "date"});
                    value = oldValue;
                }
                self.endDateChangeCallback(Date.parseString(value));
            });

            this.durationTextNode.on("change", function(value){
                var startDate = Date.parseString(locale.format(new Date(self.startDateNode.get("value")), {datePattern: "dd/MM/yyyy", selector: "date"}));
                var dur = parseInt(value);
                dur = dur <= 0 ? 1 : dur;
                this.set("value", dur);
                self.endDateNode.set("value", locale.format(new Date(self.ganttMaster.computeEndByDuration(startDate.getTime(), dur)), {datePattern: "yyyy-MM-dd", selector: "date"}));
            });

            this.startIsMilestoneNode.set("checked", this.task.startIsMilestone);
            this.endIsMilestoneNode.set("checked", this.task.endIsMilestone);

        },
        startDateChangeCallback: function(startDate){
            var dur = parseInt(this.durationTextNode.get("value"));
            startDate.clearTime();
            this.endDateNode.set("value", locale.format(new Date(this.ganttMaster.computeEndByDuration(startDate.getTime(), dur)), {datePattern: "yyyy-MM-dd", selector: "date"}));
        },
        endDateChangeCallback: function(endDate){
            var startDate = Date.parseString(locale.format(new Date(this.startDateNode.get("value")), {datePattern: "dd/MM/yyyy", selector: "date"}));
            endDate.setHours(23, 59, 59, 999);

            if (endDate.getTime() < startDate.getTime()) {
                var dur = parseInt(this.durationTextNode.get("value"));
                startDate = incrementDateByWorkingDays(endDate.getTime(), -dur);
                this.startDateNode.set("value", locale.format(new Date(this.ganttMaster.computeStart(startDate)), {datePattern: "yyyy-MM-dd", selector: "date"}));
            } else {
                this.durationTextNode.set("value", recomputeDuration(startDate.getTime(), endDate.getTime()));
            }
        },
        close: function(){

        },
        save: function() {
            if (this.taskEditorForm.validate()) {
                var taskId = this.taskRow.attr("taskId");
                var task = this.ganttMaster.getTask(taskId); // get task again because in case of rollback old task is lost

                var values = dojo.formToObject(this.taskEditorForm.id);

                this.ganttMaster.beginTransaction();

                task.name = values.name;
                task.description = values.description;
                task.code = values.code;
                task.progress = parseFloat(values.progress);
                task.duration = parseInt(values.duration);
                task.totalCost = parseFloat(values.totalCost);
                task.startIsMilestone = values.hasOwnProperty('startIsMilestone') ? true : false;
                task.endIsMilestone = values.hasOwnProperty('endIsMilestone') ? true : false;

                var startDate = Date.parseString(locale.format(new Date(values.start), {datePattern: "dd/MM/yyyy", selector: "date"}));
                var endDate = Date.parseString(locale.format(new Date(values.end), {datePattern: "dd/MM/yyyy", selector: "date"}));

                task.setPeriod(startDate.getTime(), endDate.getTime() + (3600000 * 24));

                //change status
                task.changeStatus(values.status);

                if(values.status == 'STATUS_DONE'){
                    var c = Date.parseString(locale.format(new Date(values.completedDate), {datePattern: "dd/MM/yyyy", selector: "date"}));
                    task.completedDate = c.getTime();
                }else{
                    task.completedDate = null;
                }

                if (this.ganttMaster.endTransaction()) {
                    this.dialogObj.hide();
                }
            }
        }
    });

    return declare( 'buildspace.apps.Gantt.TaskEditorDialog', dijit.Dialog, {
        style: "padding:0px;margin:0px;",
        title: nls.taskEditor,
        ganttMaster: null,
        task: null,
        taskRow: null,
        projectSchedule: null,
        buildRendering: function() {
            this.content = this.createContent();
            this.inherited( arguments );
        },
        postCreate: function() {
            domStyle.set( this.containerNode, {
                padding: "0px",
                margin: "0px"
            } );
            this.closeButtonNode.style.display = "none";
            this.inherited( arguments );
        },
        _onKey: function(e) {
            var key = e.keyCode;
            if( key == keys.ESCAPE ) {
                dojo.stopEvent( e );
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createContent: function() {
            var tabContainer = this.tabContainer = new dijit.layout.TabContainer( {
                region: "center",
                style: "padding:0px;width:780px;height:420px;",
                id: "TaskEditor-TabContainer"
            } );

            var form = this.createForm();
            form.startup();

            tabContainer.addChild( form );

            // Unsaved tasks do not have valid ids.
            if( (!isNaN( parseInt( this.task.id ) ) || !isNaN( parseInt( this.task.idFromDB ))) && !this.task.hasChild){
                tabContainer.addChild( this.createTagBillItemGrid( this.task ) );
            }

            return tabContainer;
        },
        createForm: function() {
            var borderContainer = new dijit.layout.BorderContainer( {
                style: "padding:0px;width:780px;height:380px;",
                title: nls.taskDetails,
                gutters: false
            } );

            var form = new TaskEditorForm( {
                dialogObj: this,
                projectSchedule: this.projectSchedule,
                ganttMaster: this.ganttMaster,
                task: this.task,
                taskRow: this.taskRow
            } );

            var toolbar = new dijit.Toolbar( {
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            } );

            toolbar.addChild(
                new dijit.form.Button( {
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style: "outline:none!important;",
                    onClick: dojo.hitch( this, 'hide' )
                } )
            );
            toolbar.addChild( new dijit.ToolbarSeparator() );

            toolbar.addChild(
                new dijit.form.Button( {
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style: "outline:none!important;",
                    onClick: dojo.hitch( form, 'save' )
                } )
            );

            borderContainer.addChild( toolbar );
            borderContainer.addChild( form );

            return borderContainer;
        },
        createTagBillItemGrid: function(item) {
            var self = this;
            var toolbar = new dijit.Toolbar( {
                region: "top",
                style: "outline:none!important;border:none;padding:2px;width:100%;"
            } );

            var itemId = (item.hasOwnProperty("idFromDB") && !isNaN(parseInt(item.idFromDB))) ? item.idFromDB : item.id;

            var store = dojo.data.ItemFileWriteStore( {
                url: "projectManagement/getTaggedBillItemList/id/" + itemId,
                clearOnClose: true,
                urlPreventCache: true
            } );

            var grid = new TagBillItemGrid( {
                region: "center",
                store: store,
                scheduleTaskItem: item,
                projectSchedule: self.projectSchedule,
                projectScheduleWidget: this
            } );

            toolbar.addChild(
                new dijit.form.Button( {
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style: "outline:none!important;",
                    onClick: dojo.hitch( this, 'hide' )
                } )
            );
            toolbar.addChild( new dijit.ToolbarSeparator() );

            toolbar.addChild(
                new dijit.form.Button( {
                    label: nls.tagBillItems,
                    style: "outline:none!important;",
                    iconClass: "icon-16-container icon-16-connect",
                    onClick: dojo.hitch( grid, 'openBillDialog' )
                } )
            );

            toolbar.addChild( new dijit.ToolbarSeparator() );

            toolbar.addChild(
                new dijit.form.Button( {
                    id: 'ProjectManagement' + item.id + '-TaggedBillItemDeleteRow-button',
                    label: nls.deleteRow,
                    style: "outline:none!important;",
                    disabled: true,
                    iconClass: "icon-16-container icon-16-delete",
                    onClick: function() {
                        if( grid.selection.selectedIndex > -1 ) {
                            var item = grid.getItem( grid.selection.selectedIndex );
                            if( item && item.id[ 0 ] > 0 && item.hasOwnProperty( 'type' ) && item.type > 0 && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_HEADER_N && item.type != buildspace.widget.grid.constants.HIERARCHY_TYPE_NOID ) {
                                grid.deleteRow( item );
                            }
                        }
                    }
                } )
            );

            toolbar.addChild( new dijit.ToolbarSeparator() );

            var hoursPerDayTxtBox = new dijit.form.NumberTextBox( {
                name: "hours_per_day",
                style: "width:40px;padding:2px;",
                required: true,
                value: item.hoursPerDay,
                constraints: {min: 0, max: 24}
            } );

            toolbar.addChild( hoursPerDayTxtBox );

            toolbar.addChild(
                new dijit.form.Button( {
                    label: nls.saveHoursPerDay,
                    style: "outline:none!important;",
                    iconClass: "icon-16-container icon-16-save",
                    onClick: function() {

                        if( hoursPerDayTxtBox.validate() ) {
                            var pb = buildspace.dialog.indeterminateProgressBar( {
                                title: nls.pleaseWait + '...'
                            } );

                            pb.show().then(function(){
                                dojo.xhrPost( {
                                    url: 'projectManagement/hoursPerDayUpdate',
                                    content: {
                                        id: itemId,
                                        val: hoursPerDayTxtBox.get( 'value' ),
                                        _csrf_token: item._csrf_token
                                    },
                                    handleAs: 'json',
                                    load: function(resp) {
                                        if( resp.success ) {
                                            item.hoursPerDay = resp.val;
                                            hoursPerDayTxtBox.set( 'value', item.hoursPerDay );

                                            grid.store.save();
                                            grid.store.close();

                                            var handle = aspect.after( grid, "_onFetchComplete", function() {
                                                handle.remove();
                                                if( this.selection.selectedIndex != -1 ) {
                                                    this.scrollToRow( this.selection.selectedIndex );
                                                }
                                            } );

                                            grid._refresh();

                                            pb.hide();
                                        }
                                    },
                                    error: function(error) {
                                        pb.hide();
                                    }
                                } );
                            });
                        }
                    }
                } )
            );

            var baseContainer = new dijit.layout.BorderContainer( {
                style: "padding:0;margin:0;width:100%;height:100%;border:none;outline:none;",
                gutters: false
            } );

            baseContainer.addChild(toolbar);
            baseContainer.addChild(grid);

            var stackContainer = new dijit.layout.StackContainer( {
                style: 'padding:0;margin:0;border:none;width:100%;height:100%;',
                region: "center",
                id: 'projectSchedule' + self.projectSchedule.id[ 0 ] + '-stackContainer'
            } );
            stackContainer.addChild( new dojox.layout.ContentPane( {
                title: buildspace.truncateString( item.name, 45 ),
                content: baseContainer,
                id: 'tagBillItemPage-' + self.projectSchedule.id[ 0 ] + '_' + item.id,
                grid: grid
            } ) );

            stackContainer.selectChild( 'tagBillItemPage-' + self.projectSchedule.id[ 0 ] + '_' + item.id );

            // Breadcrumb.
            var controller = new dijit.layout.StackController( {
                region: "top",
                containerId: 'projectSchedule' + self.projectSchedule.id[ 0 ] + '-stackContainer'
            } );

            var controllerPane = new dijit.layout.ContentPane( {
                style: "padding:0;margin:0;overflow:hidden;",
                class: 'breadCrumbTrail',
                region: 'top',
                content: controller
            } );

            // Main container.
            var borderContainer = new dijit.layout.BorderContainer( {
                style: "padding:0px;width:780px;height:420px;",
                title: nls.billItems,
                gutters: false,
                id: 'tabChild-billItemList'
            } );
            borderContainer.addChild( stackContainer );
            borderContainer.addChild( controllerPane );

            return borderContainer;
        }
    } );

});