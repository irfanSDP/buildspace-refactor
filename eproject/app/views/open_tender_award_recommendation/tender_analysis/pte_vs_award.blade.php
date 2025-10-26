@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.show', $tender->current_tender_name, [$project->id, $tender->id]) }}</li>
        <li>{{ link_to_route('open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendation'), [$project->id, $tender->id]) }}</li>
        <li>{{ link_to_route('open_tender.award_recommendation.report.tender_analysis_table.index', 'Tender Analysis', [$project->id, $tender->id]) }}</li>
        <li>{{ trans('openTenderAwardRecommendation.pteVSaward') }}</li>
    </ol>
    @include('projects.partials.project_status')
@endsection
<?php
    function formatAmount($amount) {
        $formattedAmount = number_format(abs($amount), 2, '.', ',');
        return ($amount < 0) ? HTML::decode('<font class="invalid">(' . $formattedAmount . ')</font>') : $formattedAmount;
    }
?>
@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h1 class="page-title txt-color-bluedark">
                <i class="fa fa-dollar-sign fa-fw"></i> {{ trans('openTenderAwardRecommendation.pteVSaward') }}
            </h1>
        </div>
    </div>
    <section id="widget-grid">
        <div class="row">
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget " id="wid-id-5" 
                    data-widget-colorbutton="false"
                    data-widget-editbutton="false"
                    data-widget-togglebutton="false"
                    data-widget-deletebutton="false"
                    data-widget-fullscreenbutton="false"
                    data-widget-custombutton="false"
                    data-widget-collapsed="false"
                    data-widget-sortable="false">
                    <header>
                        <span class="widget-icon"> <i class="fa fa-dollar-sign"></i> </span>
                        <h2>{{ trans('openTenderAwardRecommendation.pteVSaward') }}</h2>
                    </header>
                    <div>
                        <div class="jarviswidget-editbox"></div>
                        <div class="widget-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="text-middle text-center squeeze" style="width:64px;">{{ trans('openTenderAwardRecommendation.item') }}</th>
                                            <th rowspan="2" class="text-middle text-left">{{ trans('openTenderAwardRecommendation.description') }}</th>
                                            <th class="text-middle text-right">{{ trans('openTenderAwardRecommendation.consultantPTE') }}</th>
                                            <th class="text-middle text-right">{{{ $results['company_name'] }}}</th>
                                        </tr>
                                        <tr>
                                            <th class="text-middle text-right" style="width:160px;">{{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}}</th>
                                            <th class="text-middle text-right">{{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $number = 1; ?>
                                        <form class="smart-form" action="{{ route('open_tender.award_recommendation.report.tender_analysis_table.ptevsaward.update', [$project->id, $tender->id]) }}" method="POST">
                                            <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                                            @if(!is_null($results['billData']))
                                                @foreach ($results['billData'] as $billData)
                                                    <tr>
                                                        <td class="text-middle text-center">{{{ $number++ }}}</td>
                                                        <td class="text-middle text-left">{{{ $billData['description'] }}}</td>
                                                        <td class="text-middle text-right">
                                                            @if ($isEditable)
                                                                <input type="number" min="0.00" max="999999999999999.00" step="0.01" name="{{{ $billData['billId'] }}}" value="{{{ $billData['consultant_pte'] }}}" required>
                                                            @else
                                                                {{ formatAmount($billData['consultant_pte']) }}
                                                            @endif
                                                        </td>
                                                        <td class="text-middle text-right">{{ formatAmount($billData['billAmount']) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.totalAmount') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</td>
                                                <td class="text-middle text-right">{{ is_null($results['billData']) ? '-' : formatAmount($results['consultantEstimateTotal']) }}</td>
                                                <td class="text-middle text-right">{{ is_null($results['billData']) ? '-' : formatAmount($results['billAmountTotal']) }}</td>
                                            </tr>
                                            @if ($isEditable && (!is_null($results['billData'])))
                                                <tr>
                                                    <td colspan="4"><button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('openTenderAwardRecommendation.submit') }}</button></td>
                                                </tr>
                                            @endif
                                        </form>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>
@endsection