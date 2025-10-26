@extends('layout.main')

@section('breadcrumb')
<ol class="breadcrumb">
    <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), []) }}</li>
    <li>{{ trans('vendorManagement.vmVendorMigration') }}</li>
</ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-lg fa-fw fa-users"></i> {{ trans('vendorManagement.vmVendorMigration') }}
            </h1>
        </div>
        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 mb-4">
            <button type="button" id="btnMigrateVendors" class="btn btn-primary btn-md pull-right header-btn" disabled>
                {{{ trans('general.migrate') }}}
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('vendorManagement.listOfVendors') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div id="vendors-table"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('vm_vendor_migration.partials.vendor_group_select_modal', [
        'modalId'              => 'vendorGroupSelectModal',
        'externalVendorGroups' => $externalVendorGroups,
    ])
@endsection

@section('js')
<script>
  $(document).ready(function() {
    const btnMigrate = document.getElementById('btnMigrateVendors');

    const actionsFormatter = function(cell, formatterParams, onRendered) {
        const data = cell.getRow().getData();

        const migrateButton = document.createElement('button');
        migrateButton.innerText = '{{ trans("general.migrate") }}';
        migrateButton.dataset.id = data.id;
        migrateButton.className = 'btn btn-xs btn-primary';

        migrateButton.addEventListener('click', function(e) {
            e.preventDefault();

            $('#vendorGroupSelectModal [data-action="actionSave"]').data('ids', [this.dataset.id]);
            $('#vendorGroupSelectModal').modal('show');
        });

        return migrateButton;
    };

    const vendorsTable = new Tabulator('#vendors-table', {
        height:520,
        placeholder: "{{ trans('general.noRecordsFound') }}",
        ajaxURL: "{{ route('vm.vendor.migration.list') }}",
        ajaxConfig: "GET",
        pagination: "local",
        layout:"fitColumns",
        columns:[
            {formatter:"rowSelection", width:60, cssClass:"text-center text-middle", align:"center", headerSort:false},
            {title:"{{ trans('general.name') }}", field:"name", minWidth: 300, hozAlign:"left", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"textarea"},
            {title:"{{ trans('vendorManagement.vendorGroup') }}", field:"vendor_group", width: 250, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, headerFilter:"input", headerFilterPlaceholder: "{{ trans('general.filter') }}", formatter:"textarea"},
            {title:"{{ trans('general.actions') }}", width: 100, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:actionsFormatter}
        ],
        rowSelectionChanged: function(data, rows) {
            if(data.length > 0) {
                btnMigrateVendors.removeAttribute('disabled');
            } else {
                btnMigrateVendors.setAttribute('disabled', 'disabled');
            }
        }
    });

    btnMigrateVendors.addEventListener('click', function(e) {
        const selectedIds = vendorsTable.getSelectedData().map(el => el.id);

        if(selectedIds.length == 0) return;

        $('#vendorGroupSelectModal [data-action="actionSave"]').data('ids', selectedIds);
        $('#vendorGroupSelectModal').modal('show');
    });

    $('#vendorGroupSelectModal').on('show.bs.modal', function() {
        $(this).find('[data-field="form_error-vendor_group"]').text('');
    });

    $('#vendorGroupSelectModal [data-action="actionSave"]').on('click', submit);

    async function submit(e) {
        e.preventDefault();

        const ids = $(this).data('ids');
        const vendorGroupId = $('#vendorGroupSelectModal [name="vendor_group"]').val();

        let isFormValidationError = false;

        app_progressBar.toggle();

        $('#vendorGroupSelectModal [data-action="actionSave"]').attr('disabled', 'disabled');

        try {
            const options = {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    vendorGroupId: vendorGroupId,
                    ids: [...ids],
                    _token: '{{{ csrf_token() }}}'
                }),
            };

            const promise = await fetch("{{ route('vm.vendor.migrate.submit') }}", options);
            const response = await promise.json();
            
            if(!promise.ok || (promise.status !== 200) || !response.success) {
                if(response.errors !== null) {
                    if(response.errors.hasOwnProperty('ids')) {
                        isFormValidationError = true;
                        $('#vendorGroupSelectModal [data-field="form_error-vendor_group"]').text(response.errors.ids[0]);
                    }

                    if(response.errors.hasOwnProperty('vendorGroupId')) {
                        isFormValidationError = true;
                        $('#vendorGroupSelectModal [data-field="form_error-vendor_group"]').text(response.errors.vendorGroupId[0]);
                    }
                }

                throw new Error("{{ trans('vendorManagement.vendorMigrationFailed') }}");
            }

            SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('vendorManagement.vendorMigrationSuccess')}}");

            $('#vendorGroupSelectModal').modal('hide');
            vendorsTable.deleteRow([...ids]);
            btnMigrateVendors.setAttribute('disabled', 'disabled');
        } catch(err) {
            console.error(err.message);

            if( ! isFormValidationError ) {
                SmallErrorBox.refreshAndRetry();
            }
        } finally {
            $('#vendorGroupSelectModal [data-action="actionSave"]').removeAttr('disabled');
            app_progressBar.maxOut();
            app_progressBar.hide();
        }
    };
  });
</script>
@endsection