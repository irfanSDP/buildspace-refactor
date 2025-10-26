@extends('layout.main')

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        <li>{{ link_to_route('requestForVariation.index', trans('requestForVariation.requestForVariation'), [$project->id]) }}</li>
        <li>{{ trans('requestForVariation.contractAndContingencySum') }}</li>
	</ol>
	@include('projects.partials.project_status')
@endsection
<?php use \Carbon\Carbon; ?>
<?php $currencyCode = $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code); ?>
@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-edit"></i>&nbsp;&nbsp;{{ trans('requestForVariation.contractAndContingencySum') }}
            </h1>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <div>
                    <div class="widget-body no-padding">
                        <form id="rfvForm" action="{{ route('requestForVariation.cncsum.save', [$project->id]) }}" method="POST" class="smart-form">
                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                            <input type="hidden" name="original_contract_sum" value="{{{ $postContractProjectOverallTotal }}}">
                            <header>{{ trans('requestForVariation.contractAndContingencySumDetails') }}</header>
                            <fieldset>
                                <div class="row">
                                    <section class="col col-xs-12 col-md-3 col-lg-3">
                                        <label class="label">{{ trans('requestForVariation.originalContractSum') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</label>
                                        <label class="fill-horizontal input">
                                            <label>{{{ $currencyCode . ' ' . number_format($postContractProjectOverallTotal, 2) }}}</label>
                                        </label>
                                    </section>
                                    <section class="col col-xs-12 col-md-3 col-lg-3">
                                        <label class="label">&nbsp;</label>
                                        <div class="inline-group">
                                            <label class="checkbox">
                                                <?php $disabled = $isContractAndContingencySumUpdated ? 'disabled' : ''; ?>
                                                <?php $checked = $contractSumIncludesContingencySum ? 'checked' : ''; ?>
                                                <input type="checkbox" name="contract_sum_includes_contingency" {{{ $checked }}} {{{ $disabled }}}>
                                                <i></i>{{ trans('requestForVariation.contractSumIncludesContingencySum') }}
                                            </label>
                                        </div>
                                    </section>
                                    <section class="col col-xs-12 col-md-4 col-lg-4">
                                        <label class="label">{{ trans('requestForVariation.contingencySum') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</label>
                                        <label class="fill-horizontal input">
                                            @if ($isContractAndContingencySumUpdated)
                                                <label>{{{ $currencyCode . ' ' . number_format($project->requestForVariationContractAndContingencySum->contingency_sum, 2) }}}</label>
                                            @else
                                                <input type="number" min="0" step="0.01" class="text-left" name="contingency_sum" value="0" required>
                                            @endif
                                        </label>
                                    </section>
                                </div>
                                <section class="col-xs-12 col-md-12 col-lg-12">
                                    @if ($isContractAndContingencySumUpdated)
                                        <label><span style="color:black;">{{ trans('requestForVariation.submittedBy') }}&nbsp;</span><span style="color:blue;">{{{ $project->requestForVariationContractAndContingencySum->user->name }}}</span>&nbsp;<span style="color:black;">::</span>&nbsp;<span style="color:red;">{{{ Carbon::parse($project->getProjectTimeZoneTime($project->requestForVariationContractAndContingencySum->created_at))->format(\Config::get('dates.full_format')) }}}</span></label>
                                    @endif
                                </section>
                            </fieldset>
                            <footer>
                                <div class="pull-left">
                                    {{ link_to_route('requestForVariation.index', trans('forms.back'), [$project->id], array('class' => 'btn btn-default')) }}
                                    @if (!$isContractAndContingencySumUpdated)
                                        <input type="submit" id="btnSubmit" value="Submit" class="btn btn-primary" data-intercept="confirmation" data-confirmation-message="{{ trans('general.sureToProceed') }}">
                                    @endif
                                </div>
                            </footer>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
