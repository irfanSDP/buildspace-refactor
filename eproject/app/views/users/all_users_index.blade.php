@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{{ $backRoute }}}">{{ trans('navigation/mainnav.home') }}</a></li>
        <li>{{{ trans('navigation/mainnav.allUsers') }}}</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fas fa-users"></i> {{{ trans('navigation/mainnav.allUsers') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <div class="btn-group pull-right header-btn">
                @include('users.partials.index_action_menu')
            </div>
        </div>
    </div>
    <div class="jarviswidget ">
        <header>
            <h2> {{{ trans('navigation/mainnav.allUsers') }}} </h2>
        </header>
        <div>
            <div class="widget-body no-padding">
                <div id="all-users-table"></div>
            </div>
        </div>
    </div>
    @include('users.partials.userFosterCompaniesModal')
@endsection

@section('js')
    <script src="{{ asset('js/app/app.restfulDelete.js') }}"></script>
    <script>
        $(document).ready(function() {
            var allUsersTable = null;
            var userFosterCompaniesTable = null;
            var userFosterCompaniesUrl = null;

            var actionsFormatter = function(cell, formatterParams, onRendered) {
                if(cell.getRow().getData().isSuperAdmin) {
                    return null;
                }

                var viewFosterCompaniesButton = document.createElement('a');
                viewFosterCompaniesButton.id = 'btnViewFosterCompanies_' + cell.getRow().getData().id;
                viewFosterCompaniesButton.className = 'btn btn-xs btn-warning';
                viewFosterCompaniesButton.innerHTML = '<i class="fa fa-building"></i>';
                viewFosterCompaniesButton.style['margin-right'] = '5px';

                viewFosterCompaniesButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    userFosterCompaniesUrl = cell.getRow().getData().route_get_foster_companies;
                    $('#userFosterCompaniesModal').modal('show');
                });

                var editButton = document.createElement('a');
                editButton.id = 'btnEditUser_' + cell.getRow().getData().id;
                editButton.href = cell.getRow().getData().route_edit_user;
                editButton.className = 'btn btn-xs btn-default';
                editButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';
                editButton.style['margin-right'] = '5px';

                var deleteButton;

                if(cell.getRow().getData().route_delete_user)
                {
                    deleteButton = document.createElement('a');
                    deleteButton.id = 'btnDeleteUser_' + cell.getRow().getData().id;
                    deleteButton.href = cell.getRow().getData().route_delete_user;
                    deleteButton.className = 'btn btn-xs btn-danger';
                    deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                    deleteButton.dataset.method = 'delete';
                    deleteButton.dataset.csrf_token = cell.getRow().getData().csrf_token;
                    deleteButton.style['margin-right'] = '5px';
                }

                var container = document.createElement('div');
                container.appendChild(viewFosterCompaniesButton);
                container.appendChild(editButton);
                if(deleteButton) container.appendChild(deleteButton);

                if(!cell.getRow().getData().isUserConfirmed) {
                    var resendValidationEmailButton = document.createElement('a');
                    resendValidationEmailButton.id = 'btnResendValidationEmail_' + cell.getRow().getData().id;
                    resendValidationEmailButton.className = 'btn btn-xs btn-warning';
                    resendValidationEmailButton.innerHTML = '<i class="fa fa-envelope"></i>';
                    resendValidationEmailButton.style['margin-right'] = '5px';
                    resendValidationEmailButton.dataset.action = 'resendValidationEmail';
                    resendValidationEmailButton.dataset.url = cell.getRow().getData().route_resend_validation_email;

                    container.appendChild(resendValidationEmailButton);
                }

                if(cell.getRow().getData().userCanBeTransferred) {
                    var switchCompanyButton = document.createElement('a');
                    switchCompanyButton.id = 'btnSwitchCompany_' + cell.getRow().getData().id;
                    switchCompanyButton.href = cell.getRow().getData().route_switch_company;
                    switchCompanyButton.className = 'btn btn-xs btn-success';
                    switchCompanyButton.innerHTML = '<i class="fa fa-exchange-alt"></i>';

                    container.appendChild(switchCompanyButton);
                }

                return container;
            }

            var userConfirmedStatus = {
                0:"None",
                1:"{{ trans('users.confirmed') }}",
                2:"{{ trans('users.pending') }}",
            };

            var yesNoStatus = {
                0:"None",
                1:"{{ trans('general.yes') }}",
                2:"{{ trans('general.no') }}",
            };
            
            var columns = [
                { title: "id", field: 'id', visible:false },
                { title: "{{ trans('general.no') }}", field: 'indexNo', width: 60, 'align': 'center', headerSort:false },
                { title: "{{ trans('users.actions') }}", width: 150, 'align': 'left', headerSort:false, formatter: actionsFormatter },
                { title: "{{ trans('users.name') }}", field: 'name', width:280, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter name' },
                { title: "{{ trans('users.designation') }}", field: 'designation', width:250, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter designation' },
                { title: "{{ trans('users.email') }}", field: 'email', width: 200, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter email' },
                { title: "{{ trans('users.contactNumber') }}", field: 'contactNumber', width: 120, headerSort:false, headerFilter: 'input', headerFilterPlaceholder: 'filter contact number' },
                { title: "{{ trans('companies.name') }}", field: 'companyName', width: 250, headerSort: false, headerFilter: 'input', headerFilterPlaceholder: 'filter company name' },
                { title: "{{ trans('companies.role') }}", field: 'companyRole', width: 150, headerSort: false, headerFilter: 'input', headerFilterPlaceholder: 'filter role' },
                { title: "{{ trans('users.status') }}", field: 'isUserConfirmed_text', width: 80, align: 'center', headerSort:false, headerFilter: 'select', headerFilterParams: userConfirmedStatus, headerFilterPlaceholder: 'filter confirmed status' },
                { title: "{{ trans('users.blocked') }}", field: 'isUserBlocked_text', width: 80, align: 'center', headerSort:false, headerFilter: 'select', headerFilterParams: yesNoStatus, headerFilterPlaceholder: 'filter blocked status' },
                { title: "{{ trans('users.admin') }}", field: 'isAdmin_text', width: 100, align: 'center', headerSort:false, headerFilter: 'select', headerFilterParams: yesNoStatus, headerFilterPlaceholder: 'filter Admin' },
            ];

            allUsersTable = new Tabulator('#all-users-table', {
                height:480,
                columns: columns,
                layout:"fitColumns",
                ajaxURL: "{{{ route('users.all.get') }}}",
                movableColumns:true,
                placeholder:"No Data Available",
                columnHeaderSortMulti:false,
                pagination: "remote",
                ajaxFiltering:true,
            });

            $('[data-action=export-all-users]').on('click', function(e){
                window.open($(this).data('route'), '_self');
            });

            $('#userFosterCompaniesModal').on('shown.bs.modal', function() {
                userFosterCompaniesTable = new Tabulator('#userFosterCompaniesTable', {
                    height:400,
                    columns: [
                        { title: "{{ trans('general.no') }}", field: 'indexNo', width: 60, 'align': 'center', headerSort:false },
                        { title: "{{ trans('companies.name') }}", field: 'name', headerSort: false, headerFilter: 'input', headerFilterPlaceholder: 'filter column' },
                        { title: "{{ trans('companies.contractGroupCategory') }}", field: 'type', width: 250, align: 'center', cssClass:"text-center", headerSort: false, headerFilter: 'input', headerFilterPlaceholder: 'filter column' },
                        { title: "{{ trans('companies.referenceNumber') }}", field: 'roc', width: 150, align: 'center', cssClass:"text-center", headerSort: false, headerFilter: 'input', headerFilterPlaceholder: 'filter column' },
                    ],
                    layout:"fitColumns",
                    ajaxURL: userFosterCompaniesUrl,
                    movableColumns:true,
                    placeholder:"No Data Available",
                    columnHeaderSortMulti:false,
                    pagination:"local",
                });
            });

            $(document).on('click', '[data-action="resendValidationEmail"]', function(e) {
                e.preventDefault();

                var url = $(this).data('url');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function (response) {
                        if (response.success) {
                            $.smallBox({
                                title : "{{ trans('general.notification') }}",
                                content : "<i class='fa fa-check'></i> <i>{{ trans('users.reSentValidationEmail') }}.</i>",
                                color : "#739E73",
                                sound: false,
                                timeout : 5000
                            });
                        } else {
                            $.smallBox({
                                title : "{{ trans('general.anErrorHasOccured') }}",
                                content : "<i class='fa fa-close'></i> <i>" + response.error + "</i>",
                                color : "#C46A69",
                                sound: false,
                                iconSmall : "fa fa-exclamation-triangle shake animated"
                            });
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            });
        });
    </script>
@endsection