@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{{ trans('projects.skipToPostContract') }}}</li>
    </ol>

    @include('projects.partials.project_status')
@endsection

@section('content')

<div id="skipToPostContract">
    {{ Form::open(['route' => ['projects.skip.postContract', $project->id]]) }}
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-fast-forward"></i> {{{ trans('projects.skipToPostContract') }}}
            </h1>
        </div>

        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <div class="btn-group pull-right header-btn">
                <div class="pull-right">
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="select-contractor">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>
                        {{{ trans('projects.selectContractor') }}}
                    </h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <div class="table-responsive" style="display: none">
                            <table class="table  table-hover" id="contractorTable">
                                <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>&nbsp;</th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Name"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Current CPE"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Previous CPE"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Type of work"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Subcategory"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter Country"/>
                                    </th>
                                    <th class="hasinput">
                                        <input type="text" class="form-control" placeholder="Filter State"/>
                                    </th>
                                </tr>
                                <tr>
                                    <th style="width: 20px;">&nbsp;</th>
                                    <th style="width: 40px;">{{ trans('general.no') }}</th>
                                    <th>{{ trans('tenders.contractors') }}</th>
                                    <th style="text-align: center;">{{ trans('tenders.currentCPE') }}</th>
                                    <th style="text-align: center;">{{ trans('tenders.previousCPE') }}</th>
                                    <th style="text-align: center;">{{ trans('tenders.typeOfWork') }}</th>
                                    <th style="text-align: center;">{{ trans('tenders.subCategory') }}</th>
                                    <th style="text-align: center;">{{ trans('projects.country') }}</th>
                                    <th style="text-align: center;">{{ trans('projects.state') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if ( $contractors->count() > 0 )
                                    <?php $counter = 1; ?>
                                    @foreach ( $contractors->chunk(30) as $chunkContractors )
                                        @foreach ( $chunkContractors as $contractor )
                                            <tr>
                                                <td style="text-align: center;">
                                                    <label>
                                                        <input type="radio" name="contractor_id" value="{{{ $contractor->id }}}" data-action="hideContractorSelection" data-name="{{{ $contractor->name }}}">
                                                    </label>
                                                </td>
                                                <td class="text-middle text-center">
                                                    <?php echo $counter ++; ?>
                                                </td>
                                                <td>{{{ $contractor->name }}}</td>

                                                @if ( $contractor->contractor )
                                                    <td style="text-align: center;">
                                                        {{{ $contractor->contractor->currentCPEGrade ? $contractor->contractor->currentCPEGrade->grade : '-' }}}
                                                    </td>
                                                    <td style="text-align: center;">
                                                        {{{ $contractor->contractor->previousCPEGrade ? $contractor->contractor->previousCPEGrade->grade : '-' }}}
                                                    </td>
                                                    <td>
                                                        {{{ implode(', ', $contractor->contractor->workCategories->lists('name')) }}}
                                                    </td>
                                                    <td>
                                                        {{{ implode(', ', $contractor->contractor->workSubcategories->lists('name')) }}}
                                                    </td>
                                                @else
                                                    <td style="text-align: center;">-</td>
                                                    <td style="text-align: center;">-</td>
                                                    <td style="text-align: center;">-</td>
                                                    <td style="text-align: center;">-</td>
                                                @endif

                                                <td>
                                                    {{{ $contractor->country->country }}}
                                                </td>
                                                <td>
                                                    {{{ $contractor->state->name }}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="post-contract-information">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body no-padding">
                        <div class="smart-form">
                            @if($project->contract->type == \PCK\Contracts\Contract::TYPE_PAM2006)
                                @include('projects.partials.postContractForms.projectFormPostContract')
                            @elseif($project->contract->type == \PCK\Contracts\Contract::TYPE_INDONESIA_CIVIL_CONTRACT)
                                @include('projects.partials.postContractForms.indonesiaCivilContractInformation')
                            @endif
                            @include('daily_labour_reports.project_labour_rates')
                            <footer>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fa fa-fast-forward"></i> {{{ trans('projects.skipToPostContract') }}}
                                </button>
                            </footer>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ Form::close() }}

</div>

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
    <script src="{{ asset('js/plugin/jquery-validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/plugin/bootstrap-timepicker/bootstrap-timepicker.min.js') }}"></script>
    @include('projects.postContractFormJs')
    <script>
        var table = $('#contractorTable').DataTable({
            "autoWidth": true,
            fnDrawCallback: function(){
                $('.table-responsive').show();
            }
        });

        $("#contractorTable thead th input[type=text]").on( 'keyup', function () {
            table.column( $(this).parent().index()+':visible' )
                    .search( this.value )
                    .draw();
        } );

        $(document).ready(function() {
            $('#post-contract-information' ).hide();

            $('#add-form').validate({
                errorPlacement : function(error, element) {
                    error.insertAfter(element.parent());
                }
            });

            $.each(["cpc_date", "extension_of_time_date", "certificate_of_making_good_defect_date", "cnc_date", "performance_bond_validity_date", "insurance_policy_coverage_date"], function(idx, val) {
                $('#'+val).datepicker({
                    dateFormat : 'dd-M-yy',
                    prevText : '<i class="fa fa-chevron-left"></i>',
                    nextText : '<i class="fa fa-chevron-right"></i>'
                });
            });

            $('#commencement_date').datepicker({
                dateFormat : 'dd-M-yy',
                prevText : '<i class="fa fa-chevron-left"></i>',
                nextText : '<i class="fa fa-chevron-right"></i>',
                onSelect : function(selectedDate) {
                    $('#completion_date').datepicker('option', 'minDate', selectedDate);
                }
            });

            $('#completion_date').datepicker({
                dateFormat : 'dd-M-yy',
                prevText : '<i class="fa fa-chevron-left"></i>',
                nextText : '<i class="fa fa-chevron-right"></i>',
                onSelect : function(selectedDate) {
                    $('#commencement_date').datepicker('option', 'maxDate', selectedDate);
                }
            });
        });

        $(document ).on('change', '[data-action=hideContractorSelection]', function(){
            $('#contractor_id-hidden').val($(this).val());
            var contractorName = $(this ).data('name').toUpperCase();
            $('#select-contractor' ).hide();
            $('#post-contract-information' ).show();
            $('#contractor-name' ).text(contractorName);
        });
    </script>

@endsection