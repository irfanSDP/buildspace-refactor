@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.index', trans('navigation/projectnav.openTender'), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.openTender.show', $tender->current_tender_name, [$project->id, $tender->id]) }}</li>
        <li>{{ link_to_route('open_tender.award_recommendation.report.show', trans('openTenderAwardRecommendation.awardRecommendation'), [$project->id, $tender->id]) }}</li>
        <li>{{ link_to_route('open_tender.award_recommendation.report.tender_analysis_table.index', 'Tender Analysis', [$project->id, $tender->id]) }}</li>
        <li>{{ trans('openTenderAwardRecommendation.contractSum') }}</li>
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
                <i class="fa fa-money-check-alt fa-fw"></i> {{ trans('openTenderAwardRecommendation.contractSum') }}
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
                        <span class="widget-icon"> <i class="fa fa-money-check-alt"></i> </span>
                        <h2>{{ trans('openTenderAwardRecommendation.contractSum') }}</h2>
                    </header>
                    <div>
                        <div class="jarviswidget-editbox"></div>
                        <div class="widget-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="text-middle text-center" style="width:64px;">{{ trans('openTenderAwardRecommendation.no') }}</th>
                                            <th class="text-middle text-left">{{ trans('openTenderAwardRecommendation.description') }}</th>
                                            <th class="text-middle text-right" style="width:180px;">{{ trans('openTenderAwardRecommendation.amount') }} ({{{ $project->getModifiedCurrencyCodeAttribute($project->modified_currency_code) }}})</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $number = 1; ?>
                                        @if(!is_null($results['billData']))
                                            @foreach ($results['billData'] as $billData)
                                                <tr>
                                                    <td class="text-middle text-center squeeze">{{{ $number++ }}}</td>
                                                    <td class="text-middle text-left">{{{ $billData['description'] }}}</td>
                                                    <td class="text-middle text-right">{{ formatAmount($billData['billAmount']) }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        <td>&nbsp;</td>
                                        <td class="text-middle text-left">{{ trans('openTenderAwardRecommendation.totalContractSum') }}</td>
                                        <td class="text-middle text-right">{{ is_null($results['billData']) ? '-' : formatAmount($results['billAmountTotal']) }}</td>
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