@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('requestForVariation.categories.index', trans('modulePermissions.kpiOfRfv'), []) }}</li>
		<li>{{{ $rfvCategory->name }}}</li>
	</ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-pencil-square-o" aria-hidden="true"></i> {{ trans('general.edit') . ' ' . trans('modulePermissions.kpiOfRfv') }}
            </h1>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2>{{ trans('requestForVariation.kpiLimit') }}</h2>
                </header>
                <div>
                    <div class="widget-body no-padding">
                        <form id="kpiForm" class="smart-form" method="POST" action="{{ route('requestForVariation.category.kpi.update', [$rfvCategory->id]) }}">
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
							<fieldset>
								<div class="row">
                                    <section class="col col-2">
                                        <label class="checkbox">
                                            <input type="checkbox" name="disableKpiLimit" id="chkDisableKpiLimit">
                                            <i></i>{{ trans('requestForVariation.disableKpiLimit') }}</label>
                                    </section>
									<section id="sectionKpiLimit" class="col col-3">
                                        <label class="label">{{ trans('requestForVariation.kpiLimit') }} (%)</label>
										<label class="input">
											<input type="number" id="kpiLimit" name="kpiLimit" min="0.01" max="100.0" step="0.01" required>
										</label>
									</section>
								</div>
                                <section>
                                    <label class="label">{{ trans('general.remarks') }}</label>
                                    <label class="textarea"></i> 										
                                        <textarea name="remarks" id="remarks"></textarea> 
                                    </label>
                                </section>
							</fieldset>
                            <footer>

                                <a type="button" id="btnBack" class="btn btn-default" href="{{ route('requestForVariation.categories.index') }}">{{ trans('forms.back') }}</a>
                                <button type="button" id="btnViewLogs" class="btn btn-success" data-toggle="modal" data-target="#kpiLimitUpdateLogModal" data-backdrop="static" data-keyboard="false"><i class="fa fa-search" aria-hidden="true"></i> {{ trans('requestForVariation.viewLogs') }}</button>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-save" aria-hidden="true"></i> {{ trans('general.save') }}</button>
                            </footer>
						</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('request_for_variation.rfv.partials.kpiLimitUpdateLogModal')
@endsection

@section('js')
    <script>
        $(document).ready(function(e) {
            $(document).on('change', '#chkDisableKpiLimit', function(e) {
                if ($(this).is(':checked')) {
                    $('#sectionKpiLimit').hide();
                    $('#kpiLimit').removeAttr('required');
                } else {
                    $('#sectionKpiLimit').show();
                    $('#kpiLimit').attr('required', 'required');
                }
            });

            @if(is_null($rfvCategory->kpi_limit))
                $('#chkDisableKpiLimit').prop('checked', true).trigger('change');
            @else
                $('#kpiLimit').val("{{{ $rfvCategory->kpi_limit }}}");
            @endif

            var currentKpiLimitFormatter = function(cell, formatterParams, onRendered) {
				if(cell.getRow().getData().current_kpi_limit == null) {
					return 'N/A';
				}

				return cell.getRow().getData().current_kpi_limit;
            };

            var previouskpiLimitFormatter = function(cell, formatterParams, onRendered) {
				if(cell.getRow().getData().previous_kpi_limit == null) {
					return 'N/A';
				}

				return cell.getRow().getData().previous_kpi_limit;
            };

            $('#kpiLimitUpdateLogModal').on('shown.bs.modal', function() {
                var rfvCategoryKpiLimitUpdateLogTable = new Tabulator("#rfvCategoryKpiLimitUpdateLogTable", {
                    height: 400,
                    layout:"fitColumns",
                    columns:[
                        { title:"{{ trans('general.previous') }} (%)", field: 'previous_kpi_limit', align:"center", cssClass: 'text-center', width: 100, resizable:false, headerSort:false, formatter: previouskpiLimitFormatter },
                        { title:"{{ trans('general.current') }} (%)", field: 'kpi_limit', align:"center", cssClass: 'text-center', width: 100, resizable:false, headerSort:false, formatter: currentKpiLimitFormatter },
                        { title:"{{ trans('users.name') }}", field: 'updated_by', width: 220, align:"left", resizable:false, headerSort:false },
						{ title:"{{ trans('general.date') . ' & ' . trans('general.time')  }}", field: 'updated_at', width: 220, align:"left", resizable:false, headerSort:false },
						{ title:"{{ trans('general.remarks') }}", field: 'remarks', align:"left", resizable:true, headerSort:false },
                    ],
                    ajaxURL: "{{route('requestForVariation.category.kpi.update.logs.get', [$rfvCategory->id])}}",
                    ajaxConfig: 'GET',
                    pagination: 'local',
                });
            });
        });
    </script>
@endsection