@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('navigation/mainnav.financeModule') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fas fa-dollar-sign"></i> {{{ trans('navigation/mainnav.financeModule') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <div class="btn-group pull-right header-btn">
                @include('finance.claim-certificate.projects_action_menu')
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2> {{{ trans('finance.claimCertificatesReport') }}} </h2>
                </header>
                <div>
                    <div class="widget-body">
                        <div class="form-inline padded-bottom padded-left padded-less-top">
                            <label for="subsidiaryFilter"><strong>{{ trans('subsidiaries.filterBySubsidiary') }} </strong></label>
                            <select class="select2 form-control fill-horizontal" name="subsidiaryFilter">
                                <option value="">{{ trans('forms.none') }}</option>
                                @foreach ($subsidiaries as $subsidiaryId => $subsidiaryName)
                                    <option value="{{{ $subsidiaryId }}}">
                                        {{{ $subsidiaryName }}}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="projects-table"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('js/app/app.functions.js') }}"></script>
    <script>
        var url = "{{ route('finance.claim-certificate.projects.getList') }}";

        var checkbox = function(cell, formatterParams, onRendered) {
            var cellValue = cell.getValue();
            if(cellValue) return '<input type="checkbox" value="'+cellValue+'" name="selected-projects[]"/>'
        };

        var projectsTable = new Tabulator("#projects-table", {
            layout:"fitColumns",
            height: '460px',
            ajaxURL: url,
            ajaxFiltering: true,
            ajaxConfig: "GET",
            placeholder: "No projects available",
            columns:[
                {title:"{{ trans('forms.select') }}", align:"center", field: 'id',  cssClass:"text-center", width: '5px', formatter:checkbox, frozen: true, headerSort:false},
                {title:"{{ trans('general.no') }}", field: 'no', align:"center", cssClass:"text-center", width: '5px', frozen: true, headerSort:false},
                {title:"{{ trans('projects.reference') }}", field: 'reference', align:"center", cssClass:"text-center", width: '15px', headerFilter: "input", headerSort:false},
                {title:"{{ trans('projects.project') }}", field: 'title', align:"left", headerFilter: "input", headerSort:false},
                {title:"{{ trans('finance.claims') }}", field: 'claimCertCount', cssClass:"text-center", width: '5px', align:"center", headerSort:false},
            ]
        });

        $('[name=subsidiaryFilter]').on('change', function() {
            projectsTable.clearFilter(true);
            projectsTable.setData(url, { subsidiaryId: this.options[this.selectedIndex].value });
        });

        var selectedProjectIds = [];

        $('#projects-table').on('change', "[name='selected-projects[]']", function(){
            if($(this).prop('checked')){
                arrayFx.push(selectedProjectIds, $(this).val());
            }
            else{
                arrayFx.remove(selectedProjectIds, $(this).val());
            }
        });

        $('[data-action=export-claim-cert-reports]').on('click', function(e){
            var queryStringIds = [];

            for(var i in selectedProjectIds)
            {
                queryStringIds[i] = 'projectIds[]='+selectedProjectIds[i];
            }

            window.open($(this).data('route') + '?' + queryStringIds.join('&'), '_blank');
        });
    </script>
@endsection