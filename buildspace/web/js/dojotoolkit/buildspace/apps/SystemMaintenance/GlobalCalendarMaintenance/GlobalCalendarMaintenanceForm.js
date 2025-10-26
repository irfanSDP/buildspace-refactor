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
        "dijit/Toolbar",
        "dijit/TitlePane",
        'dojox/calendar/Calendar',
        'dijit/Calendar',
        'dojo/i18n!buildspace/nls/GlobalCalendarMaintenance',
        "dojo/text!./templates/eventForm.html",
        "dojo/text!./templates/calendarSelectForm.html",
        'dojo/store/Observable',
        'dojo/store/Memory',
        "dojo/data/ObjectStore",
        "dojo/store/DataStore",
        'dojo/data/ItemFileWriteStore',
        "dijit/form/FilteringSelect",
        "dijit/form/Select",
        "dojo/html",
        'dojo/date'
    ],
    function(declare, lang, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, arr, _WidgetsInTemplateMixin, Form, Button, ValidateTextBox, Toolbar, TitlePane, Calendar, CalendarWidget, nls, template, calendarSelectTemplate, Observable, Memory, ObjectStore, DataStore, ItemFileWriteStore, FilteringSelect, Select, html, date){

        var CalendarForm = declare("buildspace.apps.GlobalCalendarMaintenance.GlobalCalendarMaintenanceForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
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

                var stateSelectStore = this.stateSelectStore = new dojo.data.ItemFileReadStore({
                    url:"projectBuilder/getStateByCountry/regionId/-1"
                });

                this.stateSelect = new FilteringSelect({
                    name: "global_calendar[subregion_id]",
                    store: stateSelectStore,
                    style: "width: 100%;padding:2px 0;margin-top:3px;",
                    searchAttr: "name",
                    readOnly: true,
                    required: false
                }).placeAt(this.stateSelectDivNode);

                dojo.xhrGet({
                    url: 'globalCalendarMaintenance/getEventTypeOptions',
                    handleAs: 'json',
                    load: function(data){

                        self.eventForm.setFormValues(data.formValues);

                        self.eventTypeSelect = new Select({
                            required: true,
                            name: "global_calendar[event_type]",
                            style: "width:100%;padding:2px 0!important;margin-top:3px;",
                            store: new ObjectStore({
                                objectStore: new Memory({
                                    data: data.eventTypeOptions
                                })
                            }),
                            onChange: function(eventTypeValue){
                                self.formContainerObj.refreshStateSelect(eventTypeValue);
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

        var CalendarSelectForm = declare("buildspace.apps.GlobalCalendarMaintenance.CalendarSelectForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
            templateString: calendarSelectTemplate,
            baseClass: "buildspace-form",
            region: 'center',
            calendarObj: null,
            mainFormObj: null,
            formContainerObj: null,
            defaultCountryId: null,
            nls: nls,
            _csrf_token: null,
            style: "overflow:auto;",
            postCreate: function(){

                this.inherited(arguments);

                var self = this,
                    countrySelectStore = new dojo.data.ItemFileReadStore({
                        url:"projectBuilder/getCountry"
                    });

                this.countrySelect = new FilteringSelect({
                    name: "global_calendar[region_id]",
                    store: countrySelectStore,
                    value: this.defaultCountryId,
                    style: "padding:2px 0;width: 100%;margin-top:3px;",
                    searchAttr: "name",
                    onChange: function(value){
                        self.formContainerObj.refreshStateSelect();
                        self.formContainerObj.refreshCalendar(value);
                    }
                }).placeAt(this.countrySelectDivNode);

                this.saveItemButton.on("click", function(e){
                    self.save();
                });
            },
            save: function(){
                var self = this,
                    countryId = this.countrySelect.get('value'),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.savingData+'. '+nls.pleaseWait+'...'
                    });

                if(this.eventForm.validate()){
                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: 'globalCalendarMaintenance/setDefaultCalendar',
                            content: {countryId: countryId},
                            handleAs: 'json',
                            load: function(resp) {
                                self.formContainerObj.resetForm();
                                pb.hide();
                            },
                            error: function(error) {
                                pb.hide();
                            }
                        });
                    });
                }
            }
        });


        var ExtCalendar = declare("buildspace.apps.GlobalCalendarMaintenance.Calendar", Calendar, {
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

        return declare('buildspace.apps.GlobalCalendarMaintenance.GlobalCalendarMaintenanceFormContainer', dijit.layout.BorderContainer, {
            style:"padding:0px;width:100%;height:100%;border:0px;",
            gutters: false,
            resource: null,
            postCreate: function(){
                this.inherited(arguments);

                var self = this;

                dojo.xhrGet({
                    url: 'globalCalendarMaintenance/getCalendarSetting',
                    handleAs: 'json'
                }).then(function(response){
                    var countryId = (response.data.default_region_id) ? response.data.default_region_id : -1,
                        mainWrapper = new dijit.layout.BorderContainer({
                            style:"padding:0;margin:0;width:100%;height:100%;border:0;",
                            region: 'center',
                            gutters: true
                        }),
                        calendar = self.calendar = new ExtCalendar({
                            store: new Observable(new DataStore({store: new ItemFileWriteStore({
                                clearOnClose: true,
                                url: "globalCalendarMaintenance/getEventsByCountryId/id/" + countryId
                            })})),
                            dateInterval: "month",
                            cssClassFunc: function(item){
                                return item.calendar;
                            },
                            style: "position:absolute;left:10px;top:10px;bottom:30px;right:10px;"
                        }),
                        form = self.form = new CalendarForm({
                            formContainerObj: self
                        }),
                        csForm = self.csForm = new CalendarSelectForm({
                            calendarObj: calendar,
                            formContainerObj: self,
                            mainFormObj: form,
                            defaultCountryId: countryId
                        }),
                        calendarPane = new dijit.layout.ContentPane({
                            content: calendar,
                            region: "center",
                            style: 'border:0;'
                        }),
                        sidebarPane = new dijit.layout.ContentPane({
                            splitter: false,
                            region:'leading',
                            style: 'border:0;padding:10px;margin:0;width:260px;'
                        }),
                        datePicker = self.datePicker = new CalendarWidget({
                            value: new Date(),
                            style: 'width:250px;',
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
                        }),
                        calendarSelectPane = new TitlePane({
                            title: nls.calendar,
                            content: csForm,
                            region:  "center",
                            style:"padding:0;width:250px;margin-top: 10px;"
                        });

                    self.datePicker.on("change", function(e){
                        var d = self.datePicker.get("value");
                        self.calendar.set("date", d);
                    });

                    sidebarPane.addChild(datePicker);
                    sidebarPane.addChild(calendarSelectPane);
                    sidebarPane.addChild(eventPane);

                    mainWrapper.addChild(calendarPane);
                    mainWrapper.addChild(sidebarPane);

                    self.addChild(mainWrapper);
                    self.configureCalendar();
                });
            },
            selectionChanged: function(item){
                var itemNull = item == null,
                    self = this;

                this.editedItem = itemNull ? null : lang.mixin({}, item);

                if(!itemNull){
                    this.form.eventForm.setFormValues({
                        'global_calendar[description]' : item.summary,
                        'global_calendar[start_date]' : item.startTime,
                        'global_calendar[end_date]' : item.endTime,
                        'id' : item.id
                    });

                    this.form.isHolidayCheckBox.set("checked", item.isHoliday);

                    this.csForm.countrySelect.set('value', item.regionId);
                    this.form.eventTypeSelect.set('value', item.eventType);

                    setTimeout(function() {
                        self.form.stateSelect.set('value', item.subRegionId);
                    },320);

                    this.form.deleteItemButton.set("disabled", false);
                }else{
                    this.resetForm();
                }
            },
            resetForm: function(){
                this.form.eventForm.setFormValues({
                    'global_calendar[description]' : "",
                    'global_calendar[start_date]' : new Date(),
                    'global_calendar[end_date]' : new Date(),
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

                    if(item){
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

                // configure item properties panel
                calendar.on("timeIntervalChange", function(e){
                    updateDatePicker(e.startTime, e.endTime);
                });

                this.calendar.set("date", currentDate);
            },
            makePane: function(name, content){
                var stackContainer = dijit.byId('GlobalCalendarMaintenance-stackContainer');
                var pane = new dijit.layout.ContentPane({
                    title: name,
                    id:"GlobalCalendarForm",
                    content: content
                });

                stackContainer.addChild(pane);
                stackContainer.selectChild(pane);
            },
            refreshCalendar: function (countryValue){

                countryValue = (countryValue) ? countryValue : -1;

                var store = Observable(new DataStore({store: new ItemFileWriteStore({
                    clearOnClose: true,
                    url: "globalCalendarMaintenance/getEventsByCountryId/id/" + countryValue
                })}));

                this.calendar.set('store', store);
            },
            refreshStateSelect: function(eventTypeValue, countryValue, stateValue) {

                countryValue = (countryValue) ? countryValue : this.csForm.countrySelect.get("value");

                eventTypeValue = (eventTypeValue) ? eventTypeValue : this.form.eventTypeSelect.get("value");

                if(eventTypeValue == buildspace.constants.EVENT_TYPE_STATE){
                    if(countryValue != null && countryValue != ''){
                        this.form.stateSelect.set('readOnly' , false);
                        this.form.stateSelect.set('required' , true);
                    }
                }else{
                    this.form.stateSelect.set('readOnly' , true);
                    this.form.stateSelect.set('required' , false);
                }

                if(countryValue != null && countryValue != ''){
                    this.updateStateSelectStore(countryValue, stateValue);
                }else{
                    this.form.stateSelect.set('value', '');
                }
            },
            refreshStateSelectStore: function(stateValue){
                stateValue = (stateValue == undefined || stateValue == '') ?  '' : stateValue;
                this.form.stateSelect.store.close();
                this.form.stateSelect.set('store', this.form.stateSelectStore);
                this.form.stateSelect.set('value', stateValue);
            },
            updateStateSelectStore: function(country, stateValue){
                var countryId = (country) ? country : 0;

                this.form.stateSelectStore = new dojo.data.ItemFileReadStore({
                    url:"projectBuilder/getStateByCountry/regionId/"+countryId,
                    clearOnClose: true
                });

                this.refreshStateSelectStore(stateValue);
            },
            deleteEvent: function (){
                var values = dojo.formToObject(this.form.eventForm.id),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.deletingData+'. '+nls.pleaseWait+'...'
                    });

                if(values.id){
                    var store = this.calendar.store;
                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: 'globalCalendarMaintenance/eventDelete',
                            content: { id: values.id },
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success) {
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
                var self = this,
                    values = dojo.formToObject(self.form.eventForm.id),
                    csFormValues = dojo.formToObject(self.csForm.eventForm.id),
                    pb = buildspace.dialog.indeterminateProgressBar({
                        title:nls.savingData+'. '+nls.pleaseWait+'...'
                    });

                for (var attrname in csFormValues) {
                    values[attrname] = csFormValues[attrname];
                }

                if(this.form.eventForm.validate() && this.csForm.eventForm.validate()){
                    pb.show().then(function(){
                        dojo.xhrPost({
                            url: 'globalCalendarMaintenance/eventAdd',
                            content: values,
                            handleAs: 'json',
                            load: function(resp) {
                                if(resp.success) {
                                    var store  = self.calendar.store;
                                    var item   = resp.values;
                                    self.form.eventForm.setFormValues({
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