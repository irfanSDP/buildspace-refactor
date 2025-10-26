@extends('layout.main')

@section('css')
    <style>
        /*custom styling since table fully occupied container*/
        .tabulator .tabulator-tableHolder {
            border: none;
        }

        .button-row {
            display: flex;
            gap: 5px;
            justify-content: flex-end;
        }
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('eBidding.ebidding') }}</li>
    </ol>
@endsection

@section('content')
<?php
use Carbon\Carbon;
$currentTime = Carbon::now();
$beforeSessionStartTime = $currentTime->lt($eBidding->biddingStartTime());
$projectInClosedTenderStatus = $project->inClosedTender();
$projectInEBiddingStatus = $project->inEBidding();
$eBiddingInOpenStatus = $eBidding->status === \PCK\EBiddings\EBidding::STATUS_OPEN;
$eBiddingInApprovedStatus = $eBidding->status === \PCK\EBiddings\EBidding::STATUS_APPROVED;

$bidMode = $eBidding->eBiddingMode;
?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="well">
            <header class="ms-5">
				<h2>{{ trans('eBidding.ebiddingSummary') }} : {{ $project->title }}</h2>
			</header>
            <!-- preview start time -->
            <div class="row ms-5">
                <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ trans('eBidding.preview_start_time') }}</div>
                <div class="col-sm-8 col-md-6 col-lg-4 padding-5 bg-light">
                    {{ $eBidding->biddingPreviewStartTimeText() }}
                    @if($eBidding->reminder_preview_start_time)
                        <span class="ms-5 margin-top-5">({{ trans('eBidding.reminder') }})</span>
                    @endif
                </div>
            </div>
            <!-- bidding start time -->
            <div class="row ms-5 margin-top-5">
                <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ trans('eBidding.bidding_start_time') }}</div>
                <div class="col-sm-8 col-md-6 col-lg-4 padding-5 bg-light">
                    {{ $eBidding->biddingStartTimeText() }}
                    @if($eBidding->reminder_bidding_start_time)
                        <span class="ms-5 margin-top-5">({{ trans('eBidding.reminder') }})</span>
                    @endif
                </div>
            </div>
            <!-- bidding end time -->
            <div class="row ms-5 margin-top-5">
                <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ trans('eBidding.bidding_end_time') }}</div>
                <div class="col-sm-8 col-md-6 col-lg-4 padding-5 bg-light">
                    {{ $eBidding->biddingEndTimeText() }}
                </div>
            </div>
            <!-- duration (initial) -->
            <div class="row ms-5 margin-top-5">
                <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ trans('eBidding.duration') }}</div>
                <div class="col-sm-4 col-md-4 col-lg-4 padding-5 bg-light">
                    <i class="fas fa-clock"></i>
                    {{ $eBidding->biddingDurationText(true, false) }}
                </div>
            </div>
            <!-- duration -->
            <div class="row ms-5 margin-top-5">
                <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ trans('eBidding.duration_extended') }}</div>
                <div class="col-sm-4 col-md-4 col-lg-4 padding-5 bg-light">
                    <i class="fas fa-clock"></i>
                    {{ $eBidding->biddingExtendedTimeText(true) }}
                </div>
            </div>
            <!-- start overtime -->
            @if($eBidding->biddingHasOvertime())
                <div class="row ms-5 margin-top-5">
                    <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ trans('eBidding.start_overtime') }}</div>
                    <div class="col-sm-4 col-md-4 col-lg-4 padding-5 bg-light">{{ $eBidding->biddingStartOvertimeText(true) }}</div>
                </div>
                <div class="row ms-5 margin-top-5">
                    <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ trans('eBidding.overtime_period') }}</div>
                    <div class="col-sm-4 col-md-4 col-lg-4 padding-5 bg-light">{{ $eBidding->biddingOvertimePeriodText(true) }}</div>
                </div>
            @endif

            @if ($showBudget)
                <!-- budget -->
                <div class="row ms-5 margin-top-5">
                    <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ trans('eBidding.budget') }}</div>
                    <div class="col-sm-4 col-md-4 col-lg-4 padding-5 bg-light">{{ $currencySymbol .' '. \PCK\Helpers\NumberHelper::formatNumber($eBidding->budget) }}</div>
                </div>
            @endif

            @if ($bidMode->slug !== \PCK\EBiddings\EBiddingMode::BID_MODE_ONCE)
                <!-- bid decrement percentage -->
                <div class="row ms-5 margin-top-5">
                    <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ $bidMode->slug === \PCK\EBiddings\EBiddingMode::BID_MODE_DECREMENT ? trans('eBidding.bidDecrement') : trans('eBidding.bidIncrement') }} (%)</div>
                    <div class="col-sm-4 col-md-4 col-lg-4 padding-5 bg-light">{{ (! $eBidding->bid_decrement_percent) ? $eBidding->decrement_percent : trans('eBidding.not_applicable') }} %</div>
                </div>
                <!-- bid decrement value -->
                <div class="row ms-5 margin-top-5">
                    <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ $bidMode->slug === \PCK\EBiddings\EBiddingMode::BID_MODE_DECREMENT ? trans('eBidding.bidDecrement') : trans('eBidding.bidIncrement') }} ({{ $currencySymbol }})</div>
                    <div class="col-sm-4 col-md-4 col-lg-4 padding-5 bg-light">{{ (! $eBidding->bid_decrement_value) ? $currencySymbol .' '. \PCK\Helpers\NumberHelper::formatNumber($eBidding->decrement_value) : trans('eBidding.not_applicable') }}</div>
                </div>
            @endif

            @if ($eBidding->enable_custom_bid_value && $eBidding->min_bid_amount_diff > 0)
                <!-- minimum bid amount difference -->
                <div class="row ms-5 margin-top-5">
                    <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ $bidMode->slug === \PCK\EBiddings\EBiddingMode::BID_MODE_INCREMENT ? trans('eBidding.minBidIncrementAmount') : trans('eBidding.minBidDecrementAmount') }}</div>
                    <div class="col-sm-4 col-md-4 col-lg-4 padding-5 bg-light">{{ $currencySymbol .' '. \PCK\Helpers\NumberHelper::formatNumber($eBidding->min_bid_amount_diff) }}</div>
                </div>
            @endif

            <!-- created by -->
            <div class="row ms-5 margin-top-5">
                <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ trans('eBidding.created_by') }}</div>
                <div class="col-sm-4 col-md-4 col-lg-4 padding-5 bg-light">{{ $created_by }}</div>
            </div>
            <!-- committee -->
            <div class="row ms-5 margin-top-5">
                <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ trans('eBidding.committee') }}</div>
                <div class="col-sm-4 col-md-4 col-lg-4 bg-light">
                    <div class="margin-bottom-5">
                        <?php 
                        $buCounter = 0; 
                        $gcdCounter = 0; 
                        ?>
                        @if($buContractGroup)
                            <p class="margin-top-5">{{ $buContractGroup->name }}</p>
                            @foreach ($buAssignedCommittees as $name => $is_committee)
                                @if($is_committee)
                                    <span>{{{ ++$buCounter }}}.</span><span class="ms-5">{{ $name }}</span><br>
                                @endif
                            @endforeach
                        @endif
                        <hr>
                        @if($gcdContractGroup)
                            <p>{{ $gcdContractGroup->name }}</p>
                            @foreach ($gcdAssignedCommittees as $name => $is_committee)
                                @if($is_committee)
                                    <span>{{{ ++$gcdCounter }}}.</span><span class="ms-5">{{ $name }}</span><br>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <!-- verifier -->
            <div class="row ms-5 margin-top-5">
                <div class="col-sm-4 col-md-4 col-lg-2 padding-5 bg-light-primary">{{ trans('eBidding.verifier') }}</div>
                <div class="col-sm-8 col-md-8 col-lg-6 bg-white">
                    
                    @include('verifiers.verifier_simple_status', [
                        'verifierRecords' => $verifierLogs,
                    ])

                    <button type="button" id="btnViewLogs" class="btn btn-sm btn-info pull-right" style="margin-right:4px;">{{ trans('verifiers.viewLogs') }}</button>
                    @if(!$isVerified)
                        @if($isCurrentVerifier)
                            <div class="pull-right" style="margin-right: 5px;">
                                @include('verifiers.approvalForm', [
                                    'object'	=> $eBidding,
                                ])
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            <div class="row padding-5 text-right">
                <div class="col-sm-12 col-md-12 col-lg-12">
                    <footer>
                        @if ($isEditor)
                            @if ($beforeSessionStartTime)
                                @if($projectInClosedTenderStatus)
                                    <button type="button" class="btn btn-success margin-right-5" id="enable-ebidding" data-lnk="{{ route('projects.e_bidding.enable', [$project->id]) }}"><i class="fa-regular fa-circle-check"></i> {{ trans('general.enable') }}</button>
                                @endif

                                @if($projectInEBiddingStatus && $eBiddingInOpenStatus)
                                    <button type="button" class="btn btn-danger margin-right-5" id="disable-ebidding" data-lnk="{{ route('projects.e_bidding.disable', [$project->id]) }}"><i class="fa fa-ban"></i> {{ trans('general.disable') }}</button>
                                @endif
                            @endif

                            @if(($beforeSessionStartTime && ($eBiddingInOpenStatus || $eBiddingInApprovedStatus)) || (! $beforeSessionStartTime && $eBiddingInOpenStatus))
                                <a href="{{ route('projects.e_bidding.edit', array($project->id, $eBidding->id)) }}" class="btn btn-primary margin-right-5"><i class="fa fa-pencil-alt"></i> {{ trans('general.edit') }}</a>
                                <button type="button" class="btn btn-info margin-right-5" id="notify-participants"><i class="fa fa-envelope"></i> {{ trans('eBiddingNotify.send') }}</button>
                            @endif

                            @if($beforeSessionStartTime && $projectInEBiddingStatus)
                                <button type="button" class="btn btn-info margin-right-5" id="compose-reminder-email"><i class="fa fa-pencil-alt"></i> {{ trans('messaging.editEmailReminder') }}</button>
                            @endif
                        @endif

                        @if ($eBiddingInApprovedStatus && $isCommitteeMember)
                            <a href="{{ route('e-bidding.console.show', [$eBidding->id]) }}" class="btn btn-primary"><i class="fa fa-gavel"></i> {{ trans('eBiddingConsole.console') }}</a>
                        @endif
                    </footer>
                </div>
            </div>
        </div>

        @if($currentTime->gte($eBidding->biddingEndTime()) && $eBidding->status === \PCK\EBiddings\EBidding::STATUS_APPROVED)
            @include('e_bidding.console.partials.rankings_and_history')

            @if ($eBidding->enable_zones)
                <div class="row">
                    <div class="col col-xs-12 col-md-6">
                        @include('e_bidding.console.partials.legend')
                    </div>
                </div>
            @endif
        @endif

        @include('open_tenders.e_biddings.email_reminders.edit')
    </div>
</div>
@include('open_tenders.e_biddings.partials.verifier_log_modal')
@endsection
@section('js')
    @include('common.scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Notify participants
            let notifyParticipantsButton = document.getElementById('notify-participants');
            if (notifyParticipantsButton) {
                notifyParticipantsButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    let url = "{{ route('e-bidding.notify.email', [$eBidding->id]) }}";
                    let options = {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            _token: '{{ csrf_token() }}',
                        })
                    };

                    fetch(url, options)
                        .then(response => {
                            if (!response.ok || response.status !== 200) {
                                throw new Error("{{ trans('errors.anErrorHasOccurred') }}");
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                notifyMsg('success', data.message);
                            } else {
                                notifyMsg('error', data.message);
                            }
                        })
                        .catch(err => {
                            console.error(err.message);
                        });
                });
            }

            // Email reminder
            let composeEmailReminderButton = document.getElementById('compose-reminder-email');
            let editEmailReminderModalBox = $('#editEmailReminderModalBox');
            let editEmailReminderForm = $('#editEmailReminderForm-' + "{{ $emailReminder->id }}");

            if (composeEmailReminderButton) {
                composeEmailReminderButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    editEmailReminderModalBox.modal('show');
                });
            }

            editEmailReminderForm.on('shown.bs.modal', function () {
                $(this).children('.textarea').each(function() {
                    $(this).autosize();
                });
            });

            editEmailReminderForm.submit(function(e) {
                e.preventDefault();
                app_progressBar.toggle();

                var form = $(this);
                var targetURL = form.data('submitUrl');
                var dataString = form.serialize();

                $.ajax({
                    type: 'PUT',
                    url: targetURL,
                    data: dataString,
                    success: function(data) {
                        editEmailReminderModalBox.modal('hide');
                        app_progressBar.maxOut();
                        app_progressBar.toggle();
                        if (data.success) {
                            notifyMsg('success', data.message);
                        } else {
                            notifyMsg('error', data.message);
                        }
                    },
                    error: function(jqXHR) {
                        app_progressBar.toggle();
                        let data = JSON.parse(jqXHR.responseText);

                        for (let fieldName in data.errors) {
                            let message = data.errors[fieldName];

                            $("#email_reminder-input-" + fieldName).html(message).show();
                        }
                    }
                });
            });

            // View logs
            let viewLogsButton = document.getElementById('btnViewLogs');
            if (viewLogsButton) {
                viewLogsButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    $('#eBiddingLogModal').modal('show');
                });
            }

            // Toggle enable/disable eBidding
            let enableEBiddingButton = document.getElementById('enable-ebidding');
            let disableEBiddingButton = document.getElementById('disable-ebidding');
            if (enableEBiddingButton || disableEBiddingButton) {
                function EBiddingToggle(elem) {
                    const url = elem.dataset.lnk;
                    const options = {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            _token: '{{ csrf_token() }}',
                        })
                    };

                    fetch(url, options)
                        .then(response => {
                            if (!response.ok || response.status !== 200) {
                                throw new Error("{{ trans('errors.anErrorHasOccurred') }}");
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (!data.success) {
                                throw new Error(data.message);
                            }
                            location.reload(); // Reload the page
                        })
                        .catch(err => {
                            console.error(err.message);
                        });
                }

                if (enableEBiddingButton) {
                    enableEBiddingButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        EBiddingToggle(this);
                    });
                }
                if (disableEBiddingButton) {
                    disableEBiddingButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        EBiddingToggle(this);
                    });
                }
            }

            // Rankings table
            let rankTableElem = document.getElementById('rank-table');
            if (rankTableElem) {
                // Custom formatter for +/- amounts
                function textColourFormatter(cell, formatterParams, onRendered) {
                    const value = cell.getValue();

                    switch ("{{ $bidMode->slug }}") {
                        case "{{ \PCK\EBiddings\EBiddingMode::BID_MODE_INCREMENT }}":
                            if (value.startsWith('+')) {
                                // Positive value: set color to green
                                return "<span style='color:#28a745'>" + value + "</span>";
                            } else if (value.startsWith('-')) {
                                // Negative value: set color to red
                                return "<span style='color:red'>" + value + "</span>";
                            }
                            break;
                        default:
                            if (value.startsWith('+')) {
                                // Positive value: set color to red
                                return "<span style='color:red'>" + value + "</span>";
                            } else if (value.startsWith('-')) {
                                // Negative value: set color to green
                                return "<span style='color:#28a745'>" + value + "</span>";
                            }
                    }

                    // Default value
                    return value;
                }

                fetch("{{ route('e-bidding.list.rankings', [$eBidding->id]) }}")
                    .then(response => response.json())
                    .then(data => {
                        let hasDiffWithBudget = data.some(row => row.diffWithBudget !== undefined);
                        let hasDiffWithLowestTender = data.some(row => row.diffWithLowestTender !== undefined);

                        let hasZone = data.some(row => row.zone !== undefined);
                        let rankTableColumns = [];
                        rankTableColumns.push({ title: "{{ trans('eBiddingConsole.rank') }}", width: 60, hozAlign: "center", cssClass: "text-center text-middle", headerSort: false, formatter: "rownum" });

                        if (hasZone) {
                            rankTableColumns.push({ title: "{{ trans('eBiddingConsole.zone') }}", field: "zone", width:80, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:"color" });
                        }

                        rankTableColumns.push({ title: "{{ trans('companies.company') }}", field: "companyName", width: 240, cssClass: "auto-width text-left", headerSort: false });
                        rankTableColumns.push({ title: "{{ trans('eBiddingConsole.bidAmount') }}", field: "bidAmount", cssClass: "auto-width text-left", headerSort: false });

                        if (hasDiffWithBudget) {
                            rankTableColumns.push({ title: "{{ trans('eBiddingConsole.differenceBudget') }}", field: "diffWithBudget", formatter: textColourFormatter, cssClass: "auto-width text-left", headerSort: false });
                        }

                        if (hasDiffWithLowestTender) {
                            switch ("{{ $bidMode->slug }}") {
                                case "{{ \PCK\EBiddings\EBiddingMode::BID_MODE_INCREMENT }}":
                                    rankTableColumns.push({ title: "{{ trans('eBiddingConsole.differenceHighestTender') }}", field: "diffWithLowestTender", formatter: textColourFormatter, cssClass: "auto-width text-left", headerSort: false });
                                    break;
                                default:
                                    rankTableColumns.push({ title: "{{ trans('eBiddingConsole.differenceLowestTender') }}", field: "diffWithLowestTender", formatter: textColourFormatter, cssClass: "auto-width text-left", headerSort: false });
                            }
                        }

                        let rankTable = new Tabulator(rankTableElem, {
                            height: 350,
                            pagination: false,
                            paginationSize: 10,
                            columns: rankTableColumns,
                            layout: "fitColumns",
                            data: data,
                            ajaxLoader: false,
                            placeholder: "{{ trans('errors.noDataAvailable') }}",
                            columnHeaderSortMulti: false,
                            rowFormatter: function(row) {
                                if (row.getPosition(true) === 0) {  // First row
                                    let cell = row.getCell('companyName');
                                    let companyCell = cell.getElement();
                                    let icon = '<i class="fa fa-lg fa-fw fa-trophy" style="color: #ffbc0b;"></i>';
                                    companyCell.innerHTML = `${icon} ${companyCell.innerText}`;
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

            // Bid history table
            let bidTableElem = document.getElementById('bid-table');
            if (bidTableElem) {
                let bidTableColumns = [];
                bidTableColumns.push({ title: "{{ trans('eBiddingConsole.dateTime') }}", field: "dateTime", width: 80, cssClass: "auto-width text-left", headerSort: false });
                bidTableColumns.push({ title: "{{ trans('companies.company') }}", field: "companyName", width: 200, cssClass: "auto-width text-left", headerSort: false });

                switch ("{{ $bidMode->slug }}") {
                    case "{{ \PCK\EBiddings\EBiddingMode::BID_MODE_DECREMENT }}":
                        bidTableColumns.push({ title: "{{ trans('eBiddingConsole.decreasedBy') }}", field: "lowestBidDiff", width: 120, cssClass: "auto-width text-left", headerSort: false });
                        break;
                    case "{{ \PCK\EBiddings\EBiddingMode::BID_MODE_INCREMENT }}":
                        bidTableColumns.push({ title: "{{ trans('eBiddingConsole.increasedBy') }}", field: "lowestBidDiff", width: 120, cssClass: "auto-width text-left", headerSort: false });
                        break;
                    default:
                        // Do nothing
                }

                bidTableColumns.push({ title: "{{ trans('eBiddingConsole.bidAmount') }}", field: "bidAmount", width: 140, cssClass: "auto-width text-left", headerSort: false });

                let bidTable = new Tabulator(bidTableElem, {
                    height: 350,
                    pagination: 'local',
                    paginationSize: 10,
                    columns: bidTableColumns,
                    layout: "fitColumns",
                    ajaxURL: "{{ route('e-bidding.list.bid-history', [$eBidding->id]) }}",
                    ajaxLoader: false,
                    placeholder: "{{ trans('errors.noDataAvailable') }}",
                    columnHeaderSortMulti: false,
                });
            }

            // Legend
            let legendTable;
            let legendTableElem = document.getElementById('legend-table');
            if (legendTableElem) {
                fetch("{{ route('e-bidding.list.legend', [$eBidding->id, 'size' => 10]) }}")
                    .then(response => response.json())
                    .then(data => {
                        let hasDescription = data.data.some(row => row.description !== undefined);
                        let legendTableColumns = [];
                        //legendTableColumns.push({ title:"{{ trans('eBiddingZone.rowNo') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false });
                        legendTableColumns.push({ title:"{{ trans('eBiddingConsole.zone') }}", field:"colour", width:80, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:"color" })
                        legendTableColumns.push({ title:"{{ trans('eBiddingZone.name') }}", field:"name", hozAlign:'left', cssClass:"text-middle text-left" });

                        if (hasDescription) {
                            legendTableColumns.push({ title:"{{ trans('eBiddingZone.description') }}", field:"description", hozAlign:'left', cssClass:"text-middle text-left", headerSort:false });
                        }

                        legendTableColumns.push({ title:"{{ trans('eBiddingZone.upperLimit') }}", field:"upper_limit", width:300, hozAlign:'center', cssClass:"text-center text-middle", formatter:"money", formatterParams: {
                                decimal: '.',
                                thousand: ',',
                                symbol: "{{ $currencySymbol }}" + ' ',
                                precision: 2,
                            }});

                        legendTable = new Tabulator(legendTableElem, {
                            height: 350,
                            pagination: false,
                            paginationSize: 10,
                            columns: legendTableColumns,
                            layout: "fitColumns",
                            data: data.data,
                            ajaxLoader: false,
                            placeholder: "{{ trans('errors.noDataAvailable') }}",
                            columnHeaderSortMulti: false
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }
        });
    </script>
@endsection