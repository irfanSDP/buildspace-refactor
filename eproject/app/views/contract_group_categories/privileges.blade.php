@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{{ trans('contractGroupCategories.dashboardPermissions') }}}</li>
    </ol>
@endsection

@section('content')

<div id="content">
    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-chart-line"></i> {{{ trans('contractGroupCategories.dashboardPermissions') }}}
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('contractGroupCategoryPrivileges.dashboard') }}} </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed table-striped table-hover">
                                <thead>
                                <tr>
                                    <th class="text-middle text-center squeze">{{{ trans('contractGroupCategories.no') }}}</th>
                                    <th class="text-middle text-center">{{{ trans('contractGroupCategories.contractGroupCategory') }}}</th>
                                    <th class="text-middle text-center squeeze text-nowrap">{{{ trans('contractGroupCategoryPrivileges.systemOverview') }}}</th>
                                    <th class="text-middle text-center squeeze text-nowrap">{{{ trans('contractGroupCategoryPrivileges.projectDesignStage') }}}</th>
                                    <th class="text-middle text-center squeeze text-nowrap">{{{ trans('contractGroupCategoryPrivileges.projectTenderingStage') }}}</th>
                                    <th class="text-middle text-center squeeze text-nowrap">{{{ trans('contractGroupCategoryPrivileges.projectPostContractStage') }}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $count = 0; ?>
                                @foreach ($categories as $category)
                                    <tr>
                                        <td class="text-middle text-center squeeze">{{{ ++$count }}}</td>
                                        <td class="text-middle text-left">{{{ $category->name }}}</td>
                                        <td class="text-middle text-center squeeze">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox"
                                                           class="checkbox"
                                                           data-action="update-privileges"
                                                           data-contract-group-category-id="{{{ $category->id }}}"
                                                           data-privilege-identifier="{{{ $privilegeIdentifiers['systemOverview'] }}}"
                                                           {{{ $category->hasPrivilege($privilegeIdentifiers['systemOverview']) ? 'checked' : '' }}}
                                                    >
                                                    <span></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-middle text-center squeeze">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox"
                                                           class="checkbox"
                                                           data-action="update-privileges"
                                                           data-contract-group-category-id="{{{ $category->id }}}"
                                                           data-privilege-identifier="{{{ $privilegeIdentifiers['designStage'] }}}"
                                                            {{{ $category->hasPrivilege($privilegeIdentifiers['designStage']) ? 'checked' : '' }}}
                                                    >
                                                    <span></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-middle text-center squeeze">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox"
                                                           class="checkbox"
                                                           data-action="update-privileges"
                                                           data-contract-group-category-id="{{{ $category->id }}}"
                                                           data-privilege-identifier="{{{ $privilegeIdentifiers['tenderingStage'] }}}"
                                                            {{{ $category->hasPrivilege($privilegeIdentifiers['tenderingStage']) ? 'checked' : '' }}}
                                                    >
                                                    <span></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-middle text-center squeeze">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox"
                                                           class="checkbox"
                                                           data-action="update-privileges"
                                                           data-contract-group-category-id="{{{ $category->id }}}"
                                                           data-privilege-identifier="{{{ $privilegeIdentifiers['postContract'] }}}"
                                                            {{{ $category->hasPrivilege($privilegeIdentifiers['postContract']) ? 'checked' : '' }}}
                                                    >
                                                    <span></span>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
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
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        $('[data-action=update-privileges]').on('click', function(){
            app_progressBar.toggle();
            $.ajax({
                url: '{{ route('contractGroupCategories.privileges.update') }}',
                method: 'POST',
                data: {
                    _token: '{{{ csrf_token() }}}',
                    contract_group_category_id: $(this).data('contract-group-category-id'),
                    privilege_identifier: $(this).data('privilege-identifier')
                },
                success: function (data) {
                    if (data['success']) {
                        app_progressBar.maxOut();
                        location.reload();
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        });
    </script>
@endsection