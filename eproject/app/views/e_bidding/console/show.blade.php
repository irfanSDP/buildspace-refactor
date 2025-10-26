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
        @if ($isBidder)
            <li>{{ link_to_route('e-bidding.sessions.index', trans('navigation/mainnav.eBidding'), array()) }}</li>
        @else
            <li>{{ link_to_route('projects.e_bidding.index', trans('eBidding.ebidding_detail'), array($ebidding->project_id)) }}</li>
        @endif
		<li>{{ trans('eBiddingConsole.pageTitle') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-gavel"></i> {{ trans('eBiddingConsole.pageTitle') }}
			</h1>
		</div>
	</div>

    <div class="row">
        <div class="col col-xs-12">
            <div class="padding-bottom-10 margin-bottom-10">
                {{ Form::open(['url' => '#', 'method' => 'GET', 'id' => 'form-countdown', 'class' => 'smart-form']) }}
                <div class="well">
                    <div class="row">
                        @if ($biddingStart > $now)
                            <div class="col col-xs-6 col-md-3">
                                <label class="label">{{ trans('eBiddingConsole.biddingStart') }}:</label>
                                <h3 id="bidding-start">{{ $biddingStart->format('d/m/Y g:i A') }}</h3>
                            </div>
                            <div class="col col-xs-6 col-md-3">
                                <label class="label">{{ trans('eBiddingConsole.countdown') }}:</label>
                                <h3 id="countdown-bidding-start" class="countdown" data-countdown="{{ $biddingStart->format('Y-m-d H:i:s') }}"></h3>
                            </div>
                        @else
                            <div class="col col-xs-6 col-md-3">
                                <label class="label">{{ trans('eBiddingConsole.biddingEnd') }}:</label>
                                <h3 id="bidding-end">{{ $biddingEnd->format('d/m/Y g:i A') }}</h3>
                            </div>
                            <div class="col col-xs-6 col-md-3">
                                <label class="label">{{ trans('eBiddingConsole.countdown') }}:</label>
                                <h3 id="countdown-bidding-end" class="countdown" data-countdown="{{ $biddingEnd->format('c') }}" data-lnk="{{ route('e-bidding.console.bid-countdown', [$ebidding->id]) }}"></h3>
                                <div id="countdown-overtime-start" class="countdown" style="display:none;" data-countdown="{{ $overtimeStart ? $overtimeStart->format('Y-m-d H:i:s') : '' }}" data-ended="N"></div>
                            </div>
                        @endif

                        <div class="col col-xs-6 col-md-3">
                            <label class="label">{{ trans('general.status') }}:</label>
                            <h3 class="{{ $sessionStatus['class'] }} text-uppercase" id="bid-session-status">{{ $sessionStatus['text'] }}</h3>
                        </div>
                    </div>

                    @if(! empty($overtimeStart) || $showBudget || ($ebidding->enable_custom_bid_value && $ebidding->min_bid_amount_diff > 0))
                        <div class="row margin-top-10 padding-top-10">
                            @if (! empty($overtimeStart))
                                <div class="col col-xs-6 col-md-3">
                                    <label class="label">{{ trans('eBidding.start_overtime') }}:</label>
                                    <h3>{{ $startOvertimeText }}</h3>
                                </div>

                                <div class="col col-xs-6 col-md-3">
                                    <label class="label">{{ trans('eBidding.overtime_period') }}:</label>
                                    <h3>{{ $overtimePeriodText }}</h3>
                                </div>
                            @endif

                            @if ($ebidding->enable_custom_bid_value && $ebidding->min_bid_amount_diff > 0)
                                <div class="col col-xs-6 col-md-3">
                                    <label class="label">{{ $bidMode->slug === \PCK\EBiddings\EBiddingMode::BID_MODE_INCREMENT ? trans('eBidding.minBidIncrementAmount') : trans('eBidding.minBidDecrementAmount') }}:</label>
                                    <h3>{{ $currencySymbol .' '. \PCK\Helpers\NumberHelper::formatNumber($ebidding->min_bid_amount_diff) }}</h3>
                                </div>
                            @endif

                            @if ($showBudget)
                                <div class="col col-xs-6 col-md-3">
                                    <label class="label">{{ trans('eBidding.budget') }}:</label>
                                    <h3>{{ $ebidding->budget > 0 ? $currencySymbol .' '. \PCK\Helpers\NumberHelper::formatNumber($ebidding->budget) : trans('general.none') }}</h3>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    @include('e_bidding.console.partials.rankings_and_history')

    @if ($isBidder && ! empty($bidParams) && $now->between($biddingStart, $biddingEnd))
        @if ($bidMode->slug === \PCK\EBiddings\EBiddingMode::BID_MODE_ONCE)
            <div class="row">
                <div class="col col-xs-12 col-md-6">
                    @include('e_bidding.console.partials.legend')
                </div>
                <div class="col col-xs-12 col-md-6">
                    @include('e_bidding.console.partials.bid')
                </div>
            </div>
        @else
            @include('e_bidding.console.partials.bid')
        @endif
    @else
        @if ($bidMode->slug === \PCK\EBiddings\EBiddingMode::BID_MODE_ONCE)
            <div class="row">
                <div class="col col-xs-12 col-md-6">
                    @include('e_bidding.console.partials.legend')
                </div>
            </div>
        @endif
    @endif
@endsection

@section('js')
    @include('common.scripts')
    <script src="{{ asset('js/easytimer/easytimer.min.js').'?v='.time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bidSessionEnd = document.getElementById('bidding-end');
            const bidSessionStatus = document.getElementById('bid-session-status');
            const countdownStart = document.getElementById('countdown-bidding-start');
            const countdownEnd = document.getElementById('countdown-bidding-end');
            const countdownEndUrl = countdownEnd ? countdownEnd.dataset.lnk : null;
            const countdownOvertimeStart = document.getElementById('countdown-overtime-start');
            const bidContainer = document.getElementById('bid-container');
            const bidButtons = document.querySelectorAll('.btn.bid');
            const zeroCountdown = '0:00:00:00';
            const checkDelay = 1000;         // delay check after timer hits zero
            let countdownEndTimer;
            let refreshTime = 3000;          // Refresh every 3 seconds
            let refreshTableIntervalId;      // Table interval ID
            let refreshCountdownIntervalId;  // Countdown interval ID
            let endedFinalized = false;
            let decisionInFlight = false;
            let showCheckingStatus = true;

            // Rankings
            let rankTable;
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

                fetch("{{ route('e-bidding.list.rankings', [$ebidding->id]) }}")
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

                        rankTable = new Tabulator(rankTableElem, {
                            height: 350,
                            pagination: false,
                            paginationSize: 10,
                            columns: rankTableColumns,
                            layout: "fitColumns",
                            data: data,
                            ajaxLoader: false,
                            placeholder: "{{ trans('errors.noDataAvailable') }}",
                            columnHeaderSortMulti: false,
                            rowFormatter: function (row) {
                                if (row.getPosition(true) === 0) {  // First row
                                    let cell = row.getCell('companyName');
                                    let companyCell = cell.getElement();
                                    let icon = '<i class="fa fa-lg fa-fw fa-star" style="color: #ffbc0b;" title="{{ trans('eBiddingConsole.isLeading') }}"></i>';
                                    companyCell.innerHTML = `${icon} ${companyCell.innerText}`;
                                }
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                    });
            }

            // Bid History
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
                default:    // Others
                    // Do nothing
            }

            bidTableColumns.push({ title: "{{ trans('eBiddingConsole.bidAmount') }}", field: "bidAmount", width: 140, cssClass: "auto-width text-left", headerSort: false });

            let bidTable = new Tabulator('#bid-table', {
                height: 350,
                pagination: false,
                paginationSize: 10,
                columns: bidTableColumns,
                layout: "fitColumns",
                ajaxURL: "{{ route('e-bidding.list.bid-history', [$ebidding->id, 'limit' => 10]) }}",
                ajaxLoader: false,
                placeholder: "{{ trans('errors.noDataAvailable') }}",
                columnHeaderSortMulti: false,
            });

            // Legend
            let legendTable;
            let legendTableElem = document.getElementById('legend-table');
            if (legendTableElem) {
                fetch("{{ route('e-bidding.list.legend', [$ebidding->id, 'size' => 10]) }}")
                    .then(response => response.json())
                    .then(data => {
                        let hasDescription = data.data.some(row => row.description !== undefined);
                        let hasUpperLimit = data.data.some(row => row.upper_limit !== undefined);
                        let legendTableColumns = [];
                        //legendTableColumns.push({ title:"{{ trans('eBiddingZone.rowNo') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false });
                        legendTableColumns.push({ title:"{{ trans('eBiddingConsole.zone') }}", field:"colour", width:80, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:"color" })
                        legendTableColumns.push({ title:"{{ trans('eBiddingZone.name') }}", field:"name", hozAlign:'left', cssClass:"text-middle text-left" });

                        if (hasDescription) {
                            legendTableColumns.push({ title:"{{ trans('eBiddingZone.description') }}", field:"description", hozAlign:'left', cssClass:"text-middle text-left", headerSort:false });
                        }

                        if (hasUpperLimit) {
                            legendTableColumns.push({ title:"{{ trans('eBiddingZone.upperLimit') }}", field:"upper_limit", width:300, hozAlign:'center', cssClass:"text-center text-middle", formatter:"money", formatterParams: {
                                    decimal: '.',
                                    thousand: ',',
                                    symbol: "{{ $currencySymbol }}" + ' ',
                                    precision: 2,
                                }
                            });
                        }

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

            // Function to start refreshing the tables
            function startTableRefresh() {
                refreshTableIntervalId = setInterval(function() {
                    fetch("{{ route('e-bidding.list.rankings', [$ebidding->id]) }}")
                        .then(response => response.json())
                        .then(data => {
                            rankTable.replaceData(data);    // Replace rank table data with new data
                        })
                        .catch(error => {
                            console.error('Error fetching rankTable data:', error);
                        });

                    fetch("{{ route('e-bidding.list.bid-history', [$ebidding->id, 'limit' => 10]) }}")
                        .then(response => response.json())
                        .then(data => {
                            bidTable.replaceData(data);    // Replace rank table data with new data
                        })
                        .catch(error => {
                            console.error('Error fetching bidTable data:', error);
                        });
                }, refreshTime);  // Refresh interval
            }

            // Initially start refreshing tables
            startTableRefresh();

            // Initialize countdown
            function startEasyTimerCountdown(element, endTime, onEndCallback) {
                const now = new Date();
                const remainingTimeInSeconds = Math.floor((endTime - now) / 1000);

                if (remainingTimeInSeconds > 0) {
                    const timer = new easytimer.Timer();

                    timer.start({
                        countdown: true,
                        startValues: { seconds: remainingTimeInSeconds },
                        precision: 'seconds',
                        callback: function (timer) {
                            element.innerHTML = timer.getTimeValues().toString(['days', 'hours', 'minutes', 'seconds']);
                        }
                    });

                    timer.addEventListener('targetAchieved', function() {
                        element.innerHTML = zeroCountdown;
                        if (typeof onEndCallback === 'function') {
                            onEndCallback();
                        }
                    });
                } else {
                    element.innerHTML = zeroCountdown;
                    if (typeof onEndCallback === 'function') {
                        onEndCallback();
                    }
                }
            }

            // Countdown - Bidding Start (if it exists)
            if (countdownStart) {
                const countdownStartDateTime = new Date(countdownStart.dataset.countdown);
                startEasyTimerCountdown(countdownStart, countdownStartDateTime, function() {
                    location.reload();  // Reload the page when countdown ends
                });
            }

            // Change status text to "Checking"
            function setStatusChecking() {
                if (!bidSessionStatus) return;
                bidSessionStatus.classList.remove('text-success', 'text-danger');
                bidSessionStatus.innerHTML = "{{ trans('eBiddingConsole.statusChecking') }}";
            }

            // Change status text to "Live"
            function setStatusLive() {
                if (!bidSessionStatus) return;
                bidSessionStatus.classList.remove('text-danger');
                bidSessionStatus.classList.add('text-success');
                bidSessionStatus.innerHTML = "{{ trans('eBiddingConsole.statusLive') }}";
            }

            // Check if current time is past the initial bidding end time
            function isPastInitialEnd() {
                return Date.now() >= new Date(countdownEnd.dataset.countdown).getTime();
            }

            // Stop intervals/timers and update UI when bidding has ended
            function finalizeEndedUI() {
                if (endedFinalized) return;
                endedFinalized = true;

                notifyMsg('info', "{{ trans('eBiddingConsole.biddingEnded') }}");
                stopIntervals(refreshTableIntervalId);      // Stop refreshing tables
                stopIntervals(refreshCountdownIntervalId);  // Stop refreshing countdown
                if (countdownOvertimeStart) countdownOvertimeStart.dataset.ended = 'Y';
                if (bidContainer) bidContainer.remove();
                if (bidSessionStatus) {
                    bidSessionStatus.classList.remove('text-success');
                    bidSessionStatus.classList.add('text-danger');
                    bidSessionStatus.innerHTML = "{{ trans('eBiddingConsole.statusEnded') }}";
                }
                countdownEnd.innerHTML = zeroCountdown;
            }

            // If the fetched bidding end time has changed, update and restart countdown
            function handleDurationExtension(data) {
                if (!data || !data.biddingEndIso) return false;
                const serverMs = new Date(data.biddingEndIso).getTime();
                const localMs  = new Date(countdownEnd.dataset.countdown).getTime();

                if (serverMs > localMs) {
                    setStatusLive();    // Set status is "Live"

                    notifyMsg('info', data.biddingEndDisplay, "{{ trans('eBiddingConsole.biddingExtended') }}");
                    // update to new end and restart the end-countdown
                    countdownEnd.dataset.countdown = data.biddingEndIso;
                    startCountdownEnd(new Date(serverMs));
                    bidSessionEnd.innerHTML = data.biddingEndDisplay;

                    // ensure overtime logic keeps running
                    if (countdownOvertimeStart) {
                        countdownOvertimeStart.dataset.ended = 'N';
                    }
                    return true;
                }
                return false; // no changes
            }

            // Countdown - Bidding End (if it exists)
            function startCountdownEnd(endTime) {
                // Stop any existing countdown timer if it exists
                if (countdownEndTimer) {
                    countdownEndTimer.stop();
                }

                // Initialize a new countdown timer with the updated end time
                countdownEndTimer = new easytimer.Timer();
                const remainingTimeInSeconds = Math.floor((endTime - new Date()) / 1000);

                if (remainingTimeInSeconds > 0) {
                    countdownEndTimer.start({
                        countdown: true,
                        startValues: { seconds: remainingTimeInSeconds },
                        precision: 'seconds',
                        callback: function (timer) {
                            countdownEnd.innerHTML = timer.getTimeValues().toString(['days', 'hours', 'minutes', 'seconds']);
                        }
                    });

                    countdownEndTimer.addEventListener('targetAchieved', async function() {
                        countdownEnd.innerHTML = zeroCountdown;
                        decisionInFlight = true;

                        setStatusChecking();    // Update status to "Checking"

                        setTimeout(() => {  // Delay the check slightly
                            // Fetch the latest bidding end time when countdown finishes
                            fetch(countdownEndUrl)
                                .then(response => response.json())
                                .then(data => {
                                    if (!data.ended) {
                                        // still live (or extended just now) — apply extension if any
                                        const extended = handleDurationExtension(data);
                                        //if (!extended) setStatusLive();
                                        return;
                                    }
                                    finalizeEndedUI();  // Finalize the UI changes for ended state
                                })
                                .catch(error => {
                                    throw new Error("{{ trans('errors.anErrorHasOccurred') }}");
                                })
                                .finally(() => { decisionInFlight = false; });
                        }, checkDelay);
                    });
                } else {
                    // If already past end time, mark as ended
                    finalizeEndedUI();
                }
            }

            // Initial setup for Countdown - Bidding End (if it exists)
            if (countdownEnd) {
                const countdownEndDateTime = new Date(countdownEnd.dataset.countdown);
                startCountdownEnd(countdownEndDateTime);  // Start or initialize the countdown initially
            }

            // Countdown - Overtime Start (if it exists)
            if (countdownOvertimeStart) {
                const countdownOvertimeStartDateTime = new Date(countdownOvertimeStart.dataset.countdown);

                function startOvertimeCountdown() {
                    startEasyTimerCountdown(countdownOvertimeStart, countdownOvertimeStartDateTime, async function () {
                        setTimeout(() => {  // Delay the check slightly
                            if (refreshCountdownIntervalId) {
                                stopIntervals(refreshCountdownIntervalId);  // Stop existing interval if any
                            }

                            refreshCountdownIntervalId = setInterval(function() {
                                if (endedFinalized || decisionInFlight) return; // <— skip while zero-check deciding
                                const localEndMs = new Date(countdownEnd.dataset.countdown).getTime();
                                if (Date.now() >= localEndMs) {
                                    decisionInFlight = true;
                                    setStatusChecking();    // Update status to "Checking"
                                }

                                fetch(countdownEndUrl)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.ended) {
                                            finalizeEndedUI();
                                            return;
                                        }
                                        handleDurationExtension(data);
                                    })
                                    .catch(error => {
                                        throw new Error("{{ trans('errors.anErrorHasOccurred') }}");
                                    });
                            }, refreshTime);  // Refresh interval

                            if (countdownOvertimeStart.dataset.ended === 'Y') {
                                stopIntervals(refreshCountdownIntervalId);  // Stop refreshing countdown
                            }
                        }, checkDelay);
                    });
                }

                startOvertimeCountdown();
            }

            // Extend the bidding end by X minutes on button click
            async function bidHandler(e) {
                e.preventDefault();

                const bidButton = e.target.closest('.btn.bid');
                if (! bidButton) return;

                let customBidAmount = null;
                if (bidButton.dataset.amt && bidButton.dataset.amt > 0) {
                    customBidAmount = parseFloat(bidButton.dataset.amt);
                }

                // close modal if button is inside a modal
                if (bidButton.closest('.modal')) {
                    $(bidButton.closest('.modal')).modal('hide');
                }

                // Disable the buttons to prevent multiple clicks
                document.querySelectorAll('.btn.bid, .btn.bid-btn').forEach(function(button) {
                    button.disabled = true;
                });
                const customBidInput = document.querySelector('.custom-bid-val');
                if (customBidInput) {
                    customBidInput.disabled = true;
                }
                const customBidButton = document.querySelector('.custom-bid-btn');
                if (customBidButton) {
                    customBidButton.disabled = true;
                }

                const url = bidButton.dataset.lnk;
                const payload = {
                    _token: '{{ csrf_token() }}'
                };

                // Attach custom value if applicable
                if (customBidAmount !== null) {
                    payload.bid_amount = customBidAmount;
                }

                const options = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                };

                try {
                    const response = await fetch(url, options);
                    const data = await response.json();

                    if (!response.ok || response.status !== 200) {
                        throw new Error("{{ trans('errors.anErrorHasOccurred') }}");
                    }
                    if (! data.success) {
                        throw new Error(data.message);
                    }

                    notifyMsg('success', data.message);

                    const rankData = await fetch("{{ route('e-bidding.list.rankings', [$ebidding->id]) }}").then(res => res.json());
                    rankTable.replaceData(rankData);    // Replace rank table data with new data

                    const bidData = await fetch("{{ route('e-bidding.list.bid-history', [$ebidding->id, 'limit' => 10]) }}").then(res => res.json());
                    bidTable.replaceData(bidData);  // Replace bid table data with new data

                    if (! data.stopBidding) {
                        setTimeout(function() {
                            document.querySelectorAll('.btn.bid, .btn.bid-btn').forEach(function(button) {
                                button.disabled = false;
                            });
                            if (customBidInput) {
                                customBidInput.disabled = false;
                            }
                            if (customBidButton) {
                                customBidButton.disabled = false;
                            }
                        }, {{ \PCK\EBiddings\EBiddingBid::BID_COOLDOWN * 1000 }});
                    } else {
                        finalizeEndedUI();
                    }
                } catch (err) {
                    console.error(err.message);
                    document.querySelectorAll('.btn.bid, .btn.bid-btn').forEach(function(button) {
                        button.disabled = false;
                    });
                    if (customBidInput) {
                        customBidInput.disabled = false;
                    }
                    if (customBidButton) {
                        customBidButton.disabled = false;
                    }
                    notifyMsg('error', err.message);
                }
            }

            function bidConfirmation(type, lnk, amt) {
                const confirmationModal = document.querySelector('#bidConfirmationModal');
                if (! confirmationModal) return;

                const confirmMsgAmt = confirmationModal.querySelector('.confirm-msg-amt');
                if (! confirmMsgAmt) return;

                if (type === 'C') {
                    if (!amt || amt === '' || isNaN(amt)) {
                        return;
                    }
                    amt = parseFloat(amt);

                    // Validate input
                    if (amt <= 0) {
                        notifyMsg('error', "{{ trans('eBiddingConsole.errorInvalidBidAmount') }}");
                        return;
                    }
                    confirmMsgAmt.innerHTML = "{{ $currencySymbol }} " + numberFormat(amt, 2, '.', ',');
                } else {
                    confirmMsgAmt.innerHTML = amt;
                }

                const confirmButton = confirmationModal.querySelector('.btn.bid');
                confirmButton.dataset.type = type;
                confirmButton.dataset.lnk = lnk;
                confirmButton.dataset.amt = amt;

                $(confirmationModal).modal('show');
            }

            if (bidButtons.length > 0) {
                document.querySelectorAll('.btn.bid').forEach(button => {
                    button.addEventListener('click', bidHandler);
                });

                document.querySelectorAll('.bid-btn').forEach(btn => {
                    btn.addEventListener('click', function (e) {
                        e.preventDefault();
                        const t = this;
                        let bidAmount = t.dataset.amt;
                        bidConfirmation(t.dataset.type, t.dataset.lnk, bidAmount);
                    });
                });

                document.querySelector('.custom-bid-btn')?.addEventListener('click', function (e) {
                    e.preventDefault();
                    const bidContainer = e.target.closest('.bid-btn-container');
                    let amt = bidContainer.querySelector('.custom-bid-val').value;
                    bidConfirmation(bidContainer.dataset.type, bidContainer.dataset.lnk, amt);
                });

                document.querySelector('.custom-bid-val')?.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const t = this;
                        const bidContainer = e.target.closest('.bid-btn-container');
                        let amt = t.value;
                        bidConfirmation(bidContainer.dataset.type, bidContainer.dataset.lnk, amt);
                    }
                });
            }
        });
    </script>
@endsection