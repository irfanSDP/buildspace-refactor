
$(document).ready(function() {

    pageSetUp();

    var s,
        GlobalCalendar = {

        calendar: null,
        selectedDate: null,
        currentEventSource: '/calendar/events',
        eventSource: '/calendar/events',

        form: {
            id: $('#_id'),
            startDate: $('.field-startDate'),
            country: $('#country'),
            state: $('#state'),
            endDate: $('.field-endDate'),
            eventTitle: $('#eventTitle'),
            eventDescription: $('#eventDescription'),
            eventType: $('#eventType'),
            btnAdd: $('#add-event'),
            btnSetDefault: $('#set-default'),
            btnUpdate: $('#update-event'),
            btnDelete: $('#delete-event'),
            btnNew: $('#new-event'),
            calendarBtnPrev: $('#calendar-buttons #btn-prev'),
            calendarBtnNext: $('#calendar-buttons #btn-next'),
            calendarBtnToday: $('#calendar-buttons #btn-today'),
            calendarBtnPrevYear: $('#calendar-buttons #btn-prev-year'),
            calendarBtnNextYear: $('#calendar-buttons #btn-next-year')
        },

        init: function() {

            var date = new Date(),
                d = date.getDate(),
                m = date.getMonth(),
                y = date.getFullYear(),
                self = this,
                form = this.form,
                hdr  =  {
                    left: 'title',
                    center: 'month,agendaWeek,agendaDay',
                    right: 'previousYearButton,prev,today,next,nextYearButton'
                },
                calendar = this.calendar = $('#calendar').fullCalendar({

                    customButtons : {
                        previousYearButton: {
                            text: 'Previous Year',
                            click: function() {
                                calendar.fullCalendar('prevYear');
                            }
                        },
                        nextYearButton: {
                            text: 'Next Year',
                            click: function() {
                                calendar.fullCalendar('nextYear');
                            }
                        }
                    },
                    header: hdr,

                    editable: true,

                    events: '/calendar/events',

                    dayClick: function(date, jsEvent, view) {

                        self.prepareNewEvent();

                        self.setSelected(date);
                    },

                    eventClick: function( event, jsEvent, view ) {

                        self.removeSelected();

                        $(this).addClass('selected');

                        self.setFormValue(event);

                        self.prepareUpdateEvent();
                    },

                    eventMouseover: function( event, jsEvent, view ) {

                        self.removeSelected();

                        $(this).addClass('selected');

                        self.setFormValue(event);

                        self.prepareUpdateEvent();
                    },

                    windowResize: function (event, ui) {
                        self.calendar.fullCalendar('render');
                    },

                    eventRender: function (event, element, icon) {
                        if (!event.description == "") {
                            element.find('.fc-event-title').append("<br/><span class='ultra-light'>" + event.description +
                                "</span>");
                        }
                        if (!event.icon == "") {
                            element.find('.fc-event-title').append("<i class='air air-top-right fa " + event.icon +
                                " '></i>");
                        }
                    },

                    eventResizeStop:function( event, jsEvent, ui, view ) {

                        setTimeout(function(){
                            self.setDatePickerDate(event.start, (event.end) ? event.end : event.start);
                            self.doUpdateEvent();
                        }, 50);
                    },

                    eventDragStop: function( event, jsEvent, ui, view ) {

                        setTimeout(function(){
                            self.setDatePickerDate(event.start, (event.end) ? event.end : event.start);
                            self.doUpdateEvent();
                        }, 50);
                    }
                }),
                responsiveHelper_dt_basic = undefined,
                responsiveHelper_datatable_fixed_column = undefined,
                responsiveHelper_datatable_col_reorder = undefined,
                responsiveHelper_datatable_tabletools = undefined,
                breakpointDefinition = {
                    tablet : 1024,
                    phone : 480
                },
                dtable = this.dtable = $('#dt_basic').dataTable({
                    "processing": true,
                    "ajax": {
                        "url": "/calendar/dt_events",
                        "data": function ( d ) {

                            d.countryId = form.country.val();
                            d.stateId = form.state.val();
                            d.eventType = form.eventType.val();
                        }
                    },
                    "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-12 hidden-xs'l>r>"+
                        "t"+
                        "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
                    "autoWidth" : true,
                    "preDrawCallback" : function() {
                        // Initialize the responsive datatables helper once.
                        if (!responsiveHelper_dt_basic) {
                            responsiveHelper_dt_basic = new ResponsiveDatatablesHelper($('#dt_basic'), breakpointDefinition);
                        }
                    },
                    "rowCallback" : function(nRow) {
                        responsiveHelper_dt_basic.createExpandIcon(nRow);
                    },
                    "drawCallback" : function(oSettings) {
                        responsiveHelper_dt_basic.respond();
                    }
                });

            /* hide default buttons */
            $('.fc-header-right, .fc-header-center').hide();

            form.eventType.on('change', function (e) {

                var calendar = self.calendar,
                    param = [];

                self.removeEventSource();

                if($(this).val() && $(this).val() != null) {
                    if($(this).val() == 2) {
                        if(form.country.val() && form.country.val() != null) {
                            self.refreshStateSelect();
                        }
                    } else {
                        form.state.select2('enable', false);
                        form.state.select2("val", "");
                    }

                    param.push(encodeURIComponent('eventType') + "=" + encodeURIComponent($(this).val()));
                }

                if(form.state.val()) {
                    param.push(encodeURIComponent('stateId') + "=" + encodeURIComponent(form.state.val()));
                }

                if(form.country.val()) {
                    param.push(encodeURIComponent('countryId') + "=" + encodeURIComponent(form.country.val()));
                }

                dtable.fnReloadAjax();

                self.addEventSource(self.eventSource + '?' + param.join("&"));
            });

            self.refreshCountrySelect();

            form.country.select2({
                placeholder: "Select Country",
                theme: 'bootstrap'
            }).on('change', function (e) {

                dtable.fnReloadAjax();

                if(e.val) {
                    var eventType = form.eventType.val();

                    self.removeEventSource();

                    self.addEventSource(self.eventSource + '?countryId=' + e.val + ((eventType) ? '&eventType=' + eventType : ''));

                    if($(form.eventType).val() == 2) {

                        self.refreshStateSelect();
                    }
                }
            });

            this.stateForm = form.state.select2({
                placeholder: "Select a State",
                theme: 'bootstrap'
            }).on('change', function (e) {

                self.removeEventSource();

                dtable.fnReloadAjax();

                if(e.val) {
                    self.addEventSource(self.eventSource + '?countryId=' + form.country.val() + '&stateId=' + e.val + ((eventType) ? '&eventType=' + form.eventType.val() : ''));
                } else {

                }
            });

            form.state.select2('enable', false);

            form.startDate.datepicker({
                dateFormat : 'M dd, yy',
                prevText : '<i class="fa fa-chevron-left"></i>',
                nextText : '<i class="fa fa-chevron-right"></i>',
                onSelect : function(selectedDate) {
                    form.endDate.datepicker('option', 'minDate', selectedDate);
                    calendar.fullCalendar('gotoDate', new Date(selectedDate));
                    calendar.fullCalendar( 'select', new Date(selectedDate))
                }
            });

            form.endDate.datepicker({
                dateFormat : 'M dd, yy',
                prevText : '<i class="fa fa-chevron-left"></i>',
                nextText : '<i class="fa fa-chevron-right"></i>',
                onSelect : function(selectedDate) {
                    form.startDate.datepicker('option', 'maxDate', selectedDate);
                }
            });

            form.btnAdd.click(function () {

                var eventValid = $('#event-form').valid(),
                    filterValid = $('#filter-form').valid();

                if(eventValid && filterValid) {
                    self.doUpdateEvent();
                }
            });

            form.btnNew.click(function () {
                var currentDate = form.startDate.datepicker('getDate');

                self.prepareNewEvent();

                if(currentDate != null) {
                    self.setSelected(currentDate);
                }

            });

            form.btnUpdate.click(function () {
                var eventValid = $('#event-form').valid(),
                    filterValid = $('#filter-form').valid();

                if(eventValid && filterValid) {
                    self.doUpdateEvent();
                }
            });

            form.btnDelete.click(function () {

                var id = form.id.val(),
                    dtable = self.dtable;

                if(id) {
                    $.ajax({
                        type: "GET",
                        url: "/calendar/delete/" + id,
                        dataType: 'json'
                    }).done(function( response ) {

                        calendar.fullCalendar( 'removeEvents', id);

                        dtable.fnReloadAjax();

                        self.prepareNewEvent();
                    });
                }
            });

            form.btnSetDefault.click(function () {

                var countryId = form.country.val();

                if(countryId) {
                    $.ajax({
                        type: "GET",
                        url: "/calendar/setDefaultCountry/" + countryId,
                        dataType: 'json'
                    }).done(function( response ) {
                        $.smallBox({
                            title : "Successfull",
                            content : "Default Country Has Been Set",
                            color : "#296191",
                            iconSmall : "fa fa-thumbs-up",
                            timeout : 4000
                        });
                    });
                }
            });

            $('#event-form').validate({
                rules : {
                    event_title : {
                        required : true
                    },
                    start_date : {
                        required : true
                    },
                    end_date : {
                        required : true
                    }
                },
                messages : {
                    event_title : {
                        required : 'Title is required'
                    },
                    event_description : {
                        required : 'Description is required'
                    },
                    start_date : {
                        required : 'Start Date Required'
                    },
                    end_date : {
                        required : 'End Date Required'
                    }
                },
                errorPlacement : function(error, element) {
                    error.insertAfter(element.parent());
                }
            });

            $('#filter-form').validate({
                rules : {
                    country_id : {
                        required : true
                    },
                    state_id : {
                        required : true
                    },
                    event_type : {
                        required : true
                    }
                },
                messages : {
                    country_id : {
                        required : 'Country is required'
                    },
                    state_id: {
                        required : 'State is required'
                    },
                    event_type : {
                        required : 'Event Type is required'
                    }
                },
                errorPlacement : function(error, element) {
                    error.insertAfter(element.parent());
                }
            });
        },

        setSelected: function (date) {

            var self = this,
                calendar = this.calendar,
                form = this.form,
                date = new Date(date);

            calendar.fullCalendar('select', date);

            self.setDatePickerDate(date, date);
        },

        setDatePickerDate: function (startDate, endDate) {

            var self = this,
                form = this.form;

            self.setDatePickerMinMax(startDate, endDate);

            form.endDate.datepicker('setDate', endDate);

            form.startDate.datepicker('setDate', startDate);
        },

        removeSelected: function (){
            this.calendar.fullCalendar( 'unselect' );
            $('#calendar .fc-event').removeClass('selected');
        },

        setFormValue: function (formValues) {

            var self = this,
                form = self.form;

            form.id.val(formValues._id);

            form.eventTitle.val(formValues.title);

            form.eventDescription.val(formValues.description);

            self.setDatePickerMinMax(formValues.start, (formValues.end == null) ? formValues.start : formValues.end);

            form.endDate.datepicker('setDate', (formValues.end == null) ? formValues.start : formValues.end);

            form.startDate.datepicker('setDate', formValues.start);

            form.eventType.val(formValues.eventType);

            form.country.select2("val", formValues.countryId);

            this.refreshStateSelect(formValues.stateId);
        },

        refreshCountrySelect: function () {

            var form = this.form;

            $.ajax({
                type: "GET",
                url: "/country",
                dataType: 'json'

            }).done(function( response ) {
                $.each(response.data, function(key, country) {
                    form.country
                        .append($("<option></option>")
                        .attr("value", country.id)
                        .text(country.text));
                });

                form.country.select2('val', response.default);
            });
        },

        refreshStateSelect: function (selectedState) {

            var form = this.form,
                countryId = form.country.val();

            if($(form.eventType).val() == 2 && countryId) {
                $.ajax({
                    type: "GET",
                    url: "/country/states/" + countryId,
                    dataType: 'json'

                }).done(function( response ) {
                    $('#state option[value!=""]').remove();

                    $.each(response.data, function(key, state) {
                        form.state
                            .append($("<option></option>")
                            .attr("value", state.id)
                            .text(state.text));
                    });

                    form.state.select2('enable', true);

                    form.state.select2("val", (selectedState) ? selectedState : '');
                });
            } else {
                form.state.select2('enable', false);

                form.state.select2("val", '');
            }
        },
        removeEventSource: function () {
            var self = this,
                calendar = this.calendar;

            calendar.fullCalendar( 'removeEventSource', self.currentEventSource);
        },
        addEventSource: function (eventSource) {

            var self = this,
                calendar = this.calendar;

            self.currentEventSource = eventSource;

            calendar.fullCalendar( 'addEventSource', self.currentEventSource );

            calendar.fullCalendar( 'refetchEvents');
        },

        doUpdateEvent: function () {

            var self = this,
                form = this.form;

            var id = form.id.val(),
                title = form.eventTitle.val(),
                color = self.getEventBackground(form.eventType.val()),
                description = form.eventDescription.val(),
                eventType = form.eventType.val(),
                countryId = form.country.val(),
                stateId = form.state.val();

            var fullStartDate = form.startDate.datepicker( "getDate" ),
                startDay = fullStartDate.getDate(),
                startMonth = fullStartDate.getMonth()+1,
                startYear = fullStartDate.getFullYear(),
                startDate = startYear+'/'+startMonth+'/'+startDay;

            var fullEndDate = form.endDate.datepicker( "getDate" ),
                endDay = fullEndDate.getDate(),
                endMonth = fullEndDate.getMonth()+1,
                endYear = fullEndDate.getFullYear(),
                endDate = endYear+'/'+endMonth+'/'+endDay;

            self.addEvent(id, title, description, color + ' selected', startDate, endDate, eventType, countryId, stateId);
        },

        prepareNewEvent: function () {

            var form = this.form;

            $('form#event-form').find("input[type=text], textarea").val("");

            form.id.val('-1');

            //Remove All Selected Event
            $('#calendar .fc-event').removeClass('selected');

            form.btnAdd.removeClass('hide');
            form.btnUpdate.addClass('hide');
            form.btnDelete.addClass('hide');
            form.btnNew.addClass('hide');
        },

        prepareUpdateEvent: function () {

            var form = this.form;

            form.btnAdd.addClass('hide');
            form.btnUpdate.removeClass('hide');
            form.btnDelete.removeClass('hide');
            form.btnNew.removeClass('hide');
        },

        setDatePickerMinMax: function (minDate, maxDate){

            var form = this.form;

            form.endDate.datepicker('option', 'minDate', minDate);
            form.startDate.datepicker('option', 'maxDate', maxDate);

        },

        getEventBackground: function (eventType) {

            switch(eventType) {
                case '1':
                    return ('bg-color-greenLight txt-color-white');
                    break;
                case '2':
                    return ('bg-color-blue txt-color-white');
                    break;
                default:
                    return ('bg-color-blueLight txt-color-white');
            }
        },

        addEvent: function (id, title, description, color, startDate, endDate, eventType, countryId, stateId) {

            var self = this,
                form = this.form,
                calendar = this.calendar,
                dtable = this.dtable;

            title = title.length === 0 ? "Untitled Event" : title;
            description = description.length === 0 ? "" : description;
            color = color.length === 0 ? "label label-default" : color;

            var formValues = {
                id: id,
                name: title,
                description: description,
                start_date: startDate,
                end_date: endDate,
                event_type: eventType,
                country_id: countryId,
                state_id: stateId,
                _token: $('#_token').val()
            };

            $.ajax({
                type: "POST",
                url: "/calendar/update",
                dataType: 'json',
                data: formValues
            }).done(function( response ) {

                if(response.success) {

                    if(id != -1) {

                        var currentEvent = calendar.fullCalendar( 'clientEvents', id );

                        $.extend(currentEvent[0], eventObject);

                        eventObject = $.extend({}, currentEvent[0] );

                        eventObject.title = title;
                        eventObject.description = description;
                        eventObject.className = $.trim(color);
                        eventObject.start = new Date(startDate);
                        eventObject.end = new Date(endDate);
                        eventObject.eventType = eventType;
                        eventObject.countryId = countryId;
                        eventObject.stateId = stateId;

                        calendar.fullCalendar( 'updateEvent', eventObject);

                    }else {
                        var eventObject = {
                            id: response.data.id,
                            title: title,
                            start: new Date(startDate),
                            end: new Date(endDate),
                            description: description,
                            allDay: true,
                            className: $.trim(color),
                            eventType: eventType,
                            countryId: countryId,
                            stateId: stateId
                        };

                        calendar.fullCalendar( 'renderEvent', eventObject, true);
                    }

                    calendar.fullCalendar( 'rerenderEvents' );

                    dtable.fnReloadAjax();

                    form.id.val(eventObject._id);

                    // $.smallBox({
                    //     title : eventObject.title,
                    //     content : "<i class='fa fa-clock-o'></i> <i>2 seconds ago...</i>",
                    //     color : "#296191",
                    //     timeout : 3000
                    // });

                    self.prepareUpdateEvent();
                }
            });
        }

    };

    GlobalCalendar.init();
});