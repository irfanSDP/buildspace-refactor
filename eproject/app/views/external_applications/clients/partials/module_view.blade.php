<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header class="ps-2 pt-2 pb-14">
                <label class="input" style="min-width:300px;">
                    <select class="select2 fill-horizontal" id="client_add_module-select" placeholder="Please Select Module">
                        <option value=""></option>
                        @foreach($modules as $module)
                        <option value="{{ $module }}" @if($selectedModule && $selectedModule->module == $module) selected @endif>{{ $module }}</option>
                        @endforeach
                    </select>
                </label>
            </header>
            <div>
                <div class="widget-body no-padding">
                    @if($selectedModule)
                    <ul id="module-tabs" class="nav nav-tabs">
                        <li class="active">
                            <a href="#module_settings-tab" data-toggle="tab">{{{ trans('users.settings') }}}</a>
                        </li>
                        <li>
                            <a href="#module_records-tab" data-toggle="tab">Inbound Records</a>
                        </li>
                        <li>
                            <a href="#module_outbound_configs-tab" data-toggle="tab">Outbound Configs</a>
                        </li>
                        <li>
                            <a href="#module_outbound_logs-tab" data-toggle="tab">Outbound Logs</a>
                        </li>
                    </ul>

                    <div id="consultant-management-contract-tab-content" class="tab-content">
                        <div class="tab-pane fade in padding-10 active" id="module_settings-tab">
                            @include('external_applications.clients.partials.module_form')
                        </div>
                        <div class="tab-pane fade in" id="module_records-tab">
                            <div class="row padding-10">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <button type="button" class="btn btn-primary btn-md pull-right" id="created_records_reload-btn">Reload</button>
                                </section>
                            </div>
                            <div class="row">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <div id="created_records-table"></div>
                                </section>
                            </div>
                        </div>
                        <div class="tab-pane fade in padding-10" id="module_outbound_configs-tab">
                            @include('external_applications.clients.partials.module_outbound_form')
                        </div>
                        <div class="tab-pane fade in" id="module_outbound_logs-tab">
                            <div class="row padding-10">
                                <section class="col col-xs-12 col-md-12 col-lg-12">
                                    <button type="button" class="btn btn-primary btn-md pull-right" id="module_outbound_logs_reload-btn">Reload</button>
                                </section>
                            </div>
                            <section class="col col-xs-12 col-md-12 col-lg-12">
                                <div id="module_outbound_logs-table"></div>
                            </section>
                        </div>
                    </div>
                    @else
                    <div class="padding-10">
                        <div class="alert alert-warning text-center">Please select module.</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>