define(['dojo/_base/declare',
        "dojo/_base/lang",
        "dojo/dom-style",
        "dijit/_WidgetBase",
        "dijit/_OnDijitClickMixin",
        "dijit/_TemplatedMixin",
        "dojo/_base/array",
        "dijit/_WidgetsInTemplateMixin",
        "dijit/form/Form",
        "dijit/form/Button",
        "dijit/form/ValidationTextBox",
        "dijit/form/CheckBox",
        "dijit/Toolbar",
        "dijit/TitlePane",
        'dojox/calendar/Calendar',
        'dijit/Calendar',
        'dojo/i18n!buildspace/nls/ProjectCalendarMaintenance',
        "dojo/text!./templates/eventForm.html",
        'dojo/store/Observable',
        'dojo/store/Memory',
        "dojo/data/ObjectStore",
        "dojo/store/DataStore",
        'dojo/data/ItemFileWriteStore',
        "dijit/form/Select",
        "dojo/html",
        'dojo/date'],
    function(declare, lang, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, arr, _WidgetsInTemplateMixin, Form, Button, ValidateTextBox, CheckBox, Toolbar, TitlePane, Calendar, CalendarWidget, nls, template, Observable, Memory, ObjectStore, DataStore, ItemFileWriteStore, Select, html, date){

        var CalendarForm = declare("buildspace.apps.ProjectManagement.ProjectCalendarMaintenanceForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
            templateString: template,
            baseClass: "buildspace-form",
            region: 'center',
            calendarObj: null,
            formContainerObj: null,
            nls: nls,
            _csrf_token: null,
            style: "overflow:auto;",
            postCreate: function(){

                this.inherited(arguments);

                var self = this;

                this.isHolidayCheckBox.set("checked", true);//by default its true

                dojo.xhrGet({
                    url: 'projectManagementCalendar/getEventTypeOptions',
                    handleAs: 'json',
                    load: function(data){

                        self.eventForm.setFormValues(data.formValues);

                        self.eventTypeSelect = new Select({
                            required: true,
                            name: "project_management_calendar[event_type]",
                            style: "width:100%;padding:2px 0!important;margin-top:3px;",
                            store: new ObjectStore({
                                objectStore: new Memory({
                                    data: data.eventTypeOptions
                                })
                            }),
                            onChange: function(eventTypeValue){
                                self.toogleIsHolidayDiv(eventTypeValue);
                            }
                        }).placeAt(self.eventTypeSelectDivNode);
                    }
                });

                this.evtStartDate.on("change", function(){
                    self.evtEndDate.constraints.min = this.get('value');
                });

                this.evtEndDate.on("change", function(){
                    self.evtStartDate.constraints.max = this.get('value');
                });

                this.evtStartDate.set("value", new Date());
                this.evtEndDate.set("value", new Date());

                this.saveItemButton.on("click", function(e){
                    self.formContainerObj.save();
                });

                this.deleteItemButton.on("click", function(e){
                    self.formContainerObj.deleteEvent();
                });
            },
            toogleIsHolidayDiv: function(eventType){
                if(eventType == buildspace.constants.EVENT_TYPE_OTHER){
                    domStyle.set(this.isHolidayDiv, "display", "block");
                }else{
                    domStyle.set(this.isHolidayDiv, "display", "none");
                }
            }
        });

        var ExtCalendar = declare("buildspace.apps.ProjectManagement.Calendar", Calendar, {
            isItemEditable: function(item, rendererKind){
                return false;
            },

            isItemResizeEnabled: function(item, rendererKind){
                return this.isItemEditable(item, rendererKind) && item.resizeEnabled;
            },

            isItemMoveEnabled: function(item, rendererKind){
                return this.isItemEditable(item, rendererKind) && item.moveEnabled;
            }
        });

        return declare('buildspace.apps.ProjectManagement.ProjectCalendarMaintenanceFormContainer', dijit.layout.BorderContainer, {
            style: "padding:0;margin:0;width:100%;height:100%;border:0px;",
            gutters: true,
            design: 'sidebar',
            resource: null,
            project: null,
            postCreate: function(){
                this.inherited(arguments);

                var self = this;

                var calendar = this.calendar = new ExtCalendar({
                        store: new Observable(new DataStore({store: new ItemFileWriteStore({
                            clearOnClose: true,
                            url: "projectManagementCalendar/getEventsByProjectId/pid/" + this.project.id[0]
                        })})),
                        dateInterval: "month",
                        cssClassFunc: function(item){
                            return item.calendar;
                        },
                        style: "position:absolute;left:10px;top:10px;bottom:30px;right:10px;"
                    }),
                    form = this.form = new CalendarForm({
                        formContainerObj: this
                    }),
                    calendarPane = new dijit.layout.ContentPane({
                        content: calendar,
                        region: "center",
                        style: 'border:0px;'
                    }),
                    sidebarPane = new dijit.layout.ContentPane({
                        splitter: false,
                        region:'leading',
                        style: 'border:0px;padding:10px;'
                    }),
                    datePicker = this.datePicker = new CalendarWidget({
                        value: new Date(),
                        style: 'width:100%;',
                        minDate: null,
                        maxDate: null,
                        getClassForDate: function(date, locale){
                            if(this.minDate && this.maxDate){
                                var cal = this.dateModule;
                                if(cal.compare(date, this.minDate) >= 0 && cal.compare(date, this.maxDate) <= 0){
                                    return "Highlighted";
                                }
                            }
                            return null;
                        }
                    });

                var eventPane = new TitlePane({
                    title: nls.eventProperties,
                    content: form,
                    region:  "center",
                    style:"padding:0;width:250px;margin-top: 10px;"
                });

                this.datePicker.on("change", function(e){
                    var d = self.datePicker.get("value");
                    self.calendar.set("date", d);
                });

                sidebarPane.addChild(datePicker);
                sidebarPane.addChild(eventPane);

                this.addChild(calendarPane);
                this.addChild(sidebarPane);

                this.configureCalendar();
            },
            onShow: function () {
                var date = this.datePicker.get("value");
                this.calendar.set("date", date);
            },
            selectionChanged: function(item){

                var itemNull = item == null;

                this.editedItem = itemNull ? null : lang.mixin({}, item);

                if(!itemNull){
                    this.form.eventForm.setFormValues({
                        'project_management_calendar[description]': item.summary,
                        'project_management_calendar[start_date]': item.startTime,
                        'project_management_calendar[end_date]': item.endTime,
                        'id' : item.id
                    });

                    this.form.isHolidayCheckBox.set("checked", item.isHoliday);
                    this.form.eventTypeSelect.set('value', item.eventType);
                    this.form.deleteItemButton.set("disabled", false);
                }else{
                    this.resetForm();
                }
            },
            resetForm: function(){
                this.form.eventForm.setFormValues({
                    'project_management_calendar[description]' : "",
                    'project_management_calendar[start_date]' : new Date(),
                    'project_management_calendar[end_date]' : new Date(),
                    'id' : -1
                });

                this.form.isHolidayCheckBox.set("checked", true);
                this.form.eventTypeSelect.set('value', buildspace.constants.EVENT_TYPE_PUBLIC);
                this.form.deleteItemButton.set("disabled", true);
            },
            configureCalendar: function(){

                var self = this,
                    calendar = this.calendar,
                    currentDate = this.datePicker.get("value");

                this.datePicker.set("value", calendar.get("date"));

                calendar.on("change", function(e){
                    var item = e.newValue;

                    if(item && item.editable){
                        self.selectionChanged(item);
                    }else{
                        self.resetForm();
                    }
                });

                var updateDatePicker = function(startTime, endTime){
                    self.datePicker.set("currentFocus", startTime, false);
                    self.datePicker.set("minDate", startTime);
                    self.datePicker.set("maxDate", endTime);
                };

                calendar.on("timeIntervalChange", function(e){
                    updateDatePicker(e.startTime, e.endTime);
                });

                this.calendar.set("date", currentDate);
            },
            makePane: function(name, content){
                var stackContainer = dijit.byId('ProjectCalendarMaintenance-stackContainer');
                var pane = new dijit.layout.ContentPane({
                    title: name,
                    id:"GlobalCalendarForm",
                    content: content
                });

                stackContainer.addChild(pane);
                stackContainer.selectChild(pane);
            },
            deleteEvent: function (){
                var self = this,
                    values = dojo.formToObject(self.form.eventForm.id),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.deletingData+'. '+nls.pleaseWait+'...'
                    });

                if(values.id){
                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: 'projectManagementCalendar/eventDelete',
                            content: { id: values.id },
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success) {
                                    var store  = self.calendar.store;
                                    store.remove(values.id);
                                    pb.hide();
                                }
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        });
                    });
                }
            },
            save: function(){
                var form = this.form,
                    values = dojo.formToObject(form.eventForm.id),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.savingData+'. '+nls.pleaseWait+'...'
                    });

                values['project_management_calendar[project_structure_id]'] = this.project.id[0];

                if(form.eventForm.validate()){
                    var store = this.calendar.store;
                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: 'projectManagementCalendar/eventAdd',
                            content: values,
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success) {
                                    var item   = resp.values;
                                    form.eventForm.setFormValues({
                                        'id' : item.id
                                    });
                                    store.put(item);
                                    pb.hide();
                                }
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        });
                    });
                }
            }
        });
    });