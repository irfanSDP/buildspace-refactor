<div class="row" id="bid-container">
    <div class="col col-xs-12 col-md-12">
        <div class="jarviswidget">
            <header>
                <h2> {{ trans('eBiddingConsole.console') }} </h2>
            </header>
            <div>
                <div class="widget-body padding-bottom-0">
                    {{ Form::open(['url' => '#', 'method' => 'GET', 'id' => 'form-console', 'class' => 'smart-form', 'data-mode' => $bidMode->slug ]) }}
                        @if ($bidMode->slug !== \PCK\EBiddings\EBiddingMode::BID_MODE_ONCE)
                            <div class="row">
                                <div class="col col-xs-12 col-md-6">
                        @endif
                            <div class="row">
                                @foreach ($bidParams as $bidParam)
                                    <div class="col col-xs-12 col-md-4">
                                        <label class="label">
                                            @if ($bidMode->slug === \PCK\EBiddings\EBiddingMode::BID_MODE_DECREMENT)
                                                {{ trans('eBiddingConsole.decreaseBy') }}
                                            @elseif($bidMode->slug === \PCK\EBiddings\EBiddingMode::BID_MODE_INCREMENT)
                                                {{ trans('eBiddingConsole.increaseBy') }}
                                            @else
                                                {{ trans('eBiddingConsole.myBid') }}
                                            @endif
                                            {{ ' ' . $bidParam['amount'] }}
                                        </label>
                                        @if ($bidParam['type'] !== 'C')
                                            <div>
                                                <button type="button" class="btn btn-primary bid-btn" data-type="{{ $bidParam['type'] }}" data-amt="{{ $bidParam['amount'] }}" data-lnk="{{ $bidParam['url'] }}">
                                                    <i class="fa fa-lg fa-fw fa-gavel"></i> {{ trans('eBiddingConsole.bid') }}
                                                </button>
                                            </div>
                                        @else
                                            <div class="bid-btn-container" data-type="{{ $bidParam['type'] }}" data-lnk="{{ $bidParam['url'] }}">
                                                <div class="input-group">
                                                    <span class="input-group-addon">{{ $currencySymbol }}</span>
                                                    <input type="number" name="custom_bid" class="form-control padded-less-left custom-bid-val" value="" step="0.01" min="0">
                                                </div>
                                                <div>
                                                    <button type="button" class="btn btn-primary margin-top-10 custom-bid-btn">
                                                        <i class="fa fa-lg fa-fw fa-gavel"></i> {{ trans('eBiddingConsole.bid') }}
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @if ($bidMode->slug !== \PCK\EBiddings\EBiddingMode::BID_MODE_ONCE)
                                </div>
                            </div>
                        @endif
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    @include('e_bidding.console.partials.bid_confirmation_modal')
</div>