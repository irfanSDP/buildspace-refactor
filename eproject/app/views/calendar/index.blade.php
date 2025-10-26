@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>Calendars</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h1 class="page-title txt-color-blueDark"><i class="fa fa-calendar fa-fw "></i>
                Calendar
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 col-md-4 col-lg-3">
            <div class="jarviswidget ">
                <header>
                    <h2> Filters </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <form class="form-horizontal smart-form" id="filter-form">
                            <fieldset>
                                <section>
                                    <label class="select">
                                        <select style="width:100%" name="country_id" id="country">
                                            <option value="">Select Country</option>
                                        </select>
                                    </label>
                                </section>
                                <section>
                                    <label class="select">
                                        <select style="width:100%" name="state_id" id="state">
                                            <option value="">Select State</option>
                                        </select>
                                    </label>
                                </section>
                                <section>
                                    <label class="select">
                                        <select name="event_type" id="eventType">
                                            <option value="0" selected="" disabled="">Select Event Type</option>
                                            <option value="1">Public Holiday</option>
                                            <option value="2">State Holiday</option>
                                            <option value="4">Others</option>
                                        </select>
                                        <i></i>
                                    </label>
                                </section>
                            </fieldset>
                            <footer>
                                <button class="btn btn-info" type="button" id="set-default">
                                    Set As Default Country
                                </button>
                            </footer>
                        </form>
                    </div>
                </div>
            </div>
            <div class="jarviswidget ">
                <header>
                    <h2> Add Events </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <form id="event-form" class="smart-form" novalidate="novalidate">
                            <fieldset>
                                <input type="hidden" name="id" id="_id" value="-1">
                                <input type="hidden" name="_token" id="_token" value="<?php echo csrf_token(); ?>">
                                <section>
                                    <label class="label">Event Title <span class="required">*</span>:</label>
                                    <label class="input">
                                        <input class="form-control" id="eventTitle" name="event_title" maxlength="40"
                                               type="text">
                                    </label>
                                </section>
                                <section>
                                    <label class="label">Event Description:</label>
                                    <label class="textarea">
                                        <textarea class="form-control" name="event_description"
                                                  placeholder="Please be brief" rows="3" maxlength="40"
                                                  id="eventDescription" style="width: 100%;"></textarea>
                                    </label>

                                    <div class="note">
                                        <strong>Maxlength</strong> is set to 40 characters
                                    </div>
                                </section>
                                <div class="row">
                                    <section class="col col-6">
                                        <label class="label">Start Date <span class="required">*</span>:</label>
                                        <label class="input"> <i class="icon-append fa fa-calendar"></i>
                                            <input type="text" name="start_date" id="startDate" class="field-startDate">
                                        </label>
                                    </section>
                                    <section class="col col-6">
                                        <label class="label">End Date <span class="required">*</span>:</label>
                                        <label class="input"> <i class="icon-append fa fa-calendar"></i>
                                            <input type="text" name="end_date" id="endDate" class="field-endDate">
                                        </label>
                                    </section>
                                </div>
                            </fieldset>
                            <footer>
                                <button class="btn btn-default" type="button" id="add-event">
                                    Add Event
                                </button>
                                <button class="btn btn-default btn-success pull-right hide" type="button"
                                        id="new-event">
                                    <i class="fa fa-plus"></i>
                                </button>
                                <button class="btn btn-default btn-primary hide" type="button" id="update-event">
                                    Update Event
                                </button>
                                <button class="btn btn-default btn-danger pull-left hide" type="button"
                                        id="delete-event">
                                    <i class="fa fa-times"></i>
                                </button>
                            </footer>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 col-md-8 col-lg-9">
            <div class="jarviswidget" id="wid-id-8" data-widget-colorbutton="false" data-widget-editbutton="false"
                 data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false"
                 data-widget-custombutton="false" data-widget-sortable="false">
                <header>
                    <h2>Calendar</h2>
                    <ul class="nav nav-tabs pull-right in">
                        <li class="active">
                            <a data-toggle="tab" href="#calendarTab">
                                <i class="fa fa-lg fa-calendar"></i>
                                <span class="hidden-mobile hidden-tablet"> Calendar </span>
                            </a>
                        </li>

                        <li>
                            <a data-toggle="tab" href="#tableTab">
                                <i class="fa fa-lg fa-list"></i>
								<span class="hidden-mobile hidden-tablet"> List Of Events
								</span>
                            </a>
                        </li>

                    </ul>
                </header>
                <div>


                    <div class="widget-body no-padding">
                        <div class="tab-content">
                            <div class="tab-pane active" id="calendarTab">
                                <div class="widget-body-toolbar white-bg">
                                </div>
                                <div id="calendar"></div>
                            </div>
                            <div class="tab-pane" id="tableTab">
                                <table id="dt_basic" class="table  table-hover"
                                       width="100%">
                                    <thead>
                                    <tr>
                                        <th data-hide="id">ID</th>
                                        <th data-class="expand">
                                            Title
                                        </th>
                                        <th data-hide="phone">
                                            Description
                                        </th>
                                        <th>Type</th>
                                        <th>Country</th>
                                        <th>State</th>
                                        <th data-hide="phone,tablet">
                                            Date From
                                        </th>
                                        <th data-hide="phone,tablet">
                                            Date To
                                        </th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('js/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/plugin/fullcalendar/jquery.fullcalendar.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/app/app.calendar.js') }}"></script>
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
@endsection