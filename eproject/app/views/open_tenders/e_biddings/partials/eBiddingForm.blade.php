<fieldset>
    <div class="row">
        <div class="col col-xs-12 col-md-6 col-lg-6">
            <!-- preview start time -->
            <div class="row">
                <section class="col col-xs-12 col-md-6 col-lg-4">
                    <label class="label">{{ trans('eBidding.preview_start_time') }}<span class="required">*</span></label>
                    <label class="input {{{ $errors->has('preview_start_time') ? 'state-error' : null }}}">
                        <input type="datetime-local" name="preview_start_time" value="{{ Input::old('preview_start_time') ? Input::old('preview_start_time') : (isset($preview_start_time) ? $preview_start_time : '') }}">
                    </label>
                    {{ $errors->first('preview_start_time', '<em class="invalid">:message</em>') }}
                </section>
                <section class="col col-xs-12 col-md-6 col-lg-4">
                    <label class="checkbox mt-10">
                        <input type="checkbox" name="reminder_preview_start_time" value="1" {{ isset($reminder_preview_start_time) && $reminder_preview_start_time ? 'checked' : '' }}>
                        <i></i>{{ trans('eBidding.reminder') }}
                    </label>
                </section>
            </div>
            <!-- bidding start time -->
            <div class="row">
                <section class="col col-xs-12 col-md-6 col-lg-4">
                    <label class="label">{{ trans('eBidding.bidding_start_time') }}<span class="required">*</span></label>
                    <label class="input {{{ $errors->has('bidding_start_time') ? 'state-error' : null }}}">
                        <input type="datetime-local" name="bidding_start_time" value="{{ Input::old('bidding_start_time') ? Input::old('bidding_start_time') : (isset($bidding_start_time) ? $bidding_start_time : '') }}">
                    </label>
                    {{ $errors->first('bidding_start_time', '<em class="invalid">:message</em>') }}
                </section>
                <section class="col col-xs-12 col-md-6 col-lg-4">
                    <label class="checkbox mt-10">
                        <input type="checkbox" name="reminder_bidding_start_time" value="1" {{ isset($reminder_bidding_start_time) && $reminder_bidding_start_time ? 'checked' : '' }}>
                        <i></i>{{ trans('eBidding.reminder') }}
                    </label>
                </section>
            </div>
            <!-- duration -->
            <div>
                <label class="label">{{ trans('time.duration') }}</label>
                <div class="row">
                    <section class="col col-xs-12 col-md-4 col-lg-4">
                        <div class="input-group {{{ $errors->has('duration_hours') ? 'state-error' : null }}}">
                            <input type="number" name="duration_hours" class="form-control padded-less-left" aria-describedby="duration_hours" value="{{ Input::old('duration_hours') ? Input::old('duration_hours') : (isset($duration_hours) ? $duration_hours : '')  }}" step="1">
                            <span class="input-group-addon" id="duration_hours">{{ trans('time.hours') }}</span>
                        </div>
                        {{ $errors->first('duration_hours', '<em class="invalid">:message</em>') }}
                    </section>
                    <section class="col col-xs-12 col-md-4 col-lg-4">
                        <div class="input-group {{{ $errors->has('duration_minutes') ? 'state-error' : null }}}">
                            <input type="number" name="duration_minutes" class="form-control padded-less-left" aria-describedby="duration_minutes" value="{{ Input::old('duration_minutes') ? Input::old('duration_minutes') : (isset($duration_minutes) ? $duration_minutes : '') }}" step="1">
                            <span class="input-group-addon" id="duration_minutes">{{ trans('time.minutes') }}</span>
                        </div>
                        {{ $errors->first('duration_minutes', '<em class="invalid">:message</em>') }}
                    </section>
                    <section class="col col-xs-12 col-md-4 col-lg-4">
                        <div class="input-group {{{ $errors->has('duration_seconds') ? 'state-error' : null }}}">
                            <input type="number" name="duration_seconds" class="form-control padded-less-left" aria-describedby="duration_seconds" value="{{ Input::old('duration_seconds') ? Input::old('duration_seconds') : (isset($duration_seconds) ? $duration_seconds : '') }}" step="1">
                            <span class="input-group-addon" id="duration_seconds">{{ trans('time.seconds') }}</span>
                        </div>
                        {{ $errors->first('duration_seconds', '<em class="invalid">:message</em>') }}
                    </section>
                </div>
            </div>
            <!-- start overtime -->
            <div>
                <label class="label">{{ trans('eBidding.start_overtime') }}</label>
                <div class="row">
                    <section class="col col-xs-12 col-md-6 col-lg-4">
                        <div class="input-group {{{ $errors->has('start_overtime') ? 'state-error' : null }}}">
                            <input type="number" name="start_overtime" class="form-control padded-less-left" aria-describedby="start_overtime" value="{{ Input::old('start_overtime') ? Input::old('start_overtime') : (isset($start_overtime) ? $start_overtime : '') }}" step="1">
                            <span class="input-group-addon" id="start_overtime">{{ trans('time.minutes') }}</span>
                        </div>
                        {{ $errors->first('start_overtime', '<em class="invalid">:message</em>') }}
                    </section>
                    <section class="col col-xs-12 col-md-6 col-lg-4">
                        <div class="input-group {{{ $errors->has('start_overtime_seconds') ? 'state-error' : null }}}">
                            <input type="number" name="start_overtime_seconds" class="form-control padded-less-left" aria-describedby="start_overtime_seconds" value="{{ Input::old('start_overtime_seconds') ? Input::old('start_overtime_seconds') : (isset($start_overtime_seconds) ? $start_overtime_seconds : '') }}" step="1">
                            <span class="input-group-addon" id="start_overtime_seconds">{{ trans('time.seconds') }}</span>
                        </div>
                        {{ $errors->first('start_overtime_seconds', '<em class="invalid">:message</em>') }}
                    </section>
                </div>
            </div>
            <!-- overtime period -->
            <div>
                <label class="label">{{ trans('eBidding.overtime_period') }}</label>
                <div class="row">
                    <section class="col col-xs-12 col-md-6 col-lg-4">
                        <div class="input-group {{{ $errors->has('overtime_period') ? 'state-error' : null }}}">
                            <input type="number" name="overtime_period" class="form-control padded-less-left" aria-describedby="overtime_period" value="{{ Input::old('overtime_period') ? Input::old('overtime_period') : (isset($overtime_period) ? $overtime_period : '') }}" step="1">
                            <span class="input-group-addon" id="overtime_period">{{ trans('time.minutes') }}</span>
                        </div>
                        {{ $errors->first('overtime_period', '<em class="invalid">:message</em>') }}
                    </section>
                    <section class="col col-xs-12 col-md-6 col-lg-4">
                        <div class="input-group {{{ $errors->has('overtime_seconds') ? 'state-error' : null }}}">
                            <input type="number" name="overtime_seconds" class="form-control padded-less-left" aria-describedby="overtime_seconds" value="{{ Input::old('overtime_seconds') ? Input::old('overtime_seconds') : (isset($overtime_seconds) ? $overtime_seconds : '') }}" step="1">
                            <span class="input-group-addon" id="overtime_seconds">{{ trans('time.seconds') }}</span>
                        </div>
                        {{ $errors->first('overtime_seconds', '<em class="invalid">:message</em>') }}
                    </section>
                </div>
            </div>

            @if (\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_EBIDDING_MODES))
                <!-- bid mode -->
                <div class="row">
                    <section class="col col-xs-12 col-md-6 col-lg-4">
                        <label class="label">{{ trans('eBiddingMode.bidMode') }}</label>
                        {{ Form::select('bid_mode', \PCK\EBiddings\EBiddingMode::getBidModeSelections(),  Input::old('bid_mode') ? Input::old('bid_mode') : (isset($bidMode) ? $bidMode : ''), array('class' => 'form-control padded-less-left padded-less-left', 'id' => 'bid_mode')) }}
                    </section>
                </div>
            @endif

            <!-- set budget -->
            <div class="row budget">
                <section class="col col-xs-12 col-md-6 col-lg-4">
                    <label class="label">{{ trans('eBidding.set_budget') }}</label>
                    {{ Form::select('set_budget', \PCK\EBiddings\EBidding::setBudget(),  Input::old('set_budget') ? Input::old('set_budget') : (isset($set_budget) ? $set_budget : ''), array('class' => 'form-control padded-less-left padded-less-left', 'id' => 'set_budget')) }}
                </section>
                <section class="col col-xs-12 col-md-6 col-lg-4 budget_section">
                    <label class="label">{{ trans('eBidding.budget') }}</label>
                    <div class="input-group {{{ $errors->has('budget') ? 'state-error' : null }}}">
                        <span class="input-group-addon" id="budget_addon">{{ $currencyCode }}</span>
                        <input type="number" name="budget" class="form-control padded-less-left" aria-describedby="budget_addon" value="{{ Input::old('budget') ? Input::old('budget') : (isset($budget) ? $budget : '') }}" step="0.01">
                    </div>
                    {{ $errors->first('budget', '<em class="invalid">:message</em>') }}
                </section>
            </div>
            <div class="row budget">
                <section class="col col-xs-12 col-md-6 col-lg-4 budget_section">
                    <label class="checkbox">
                        <input type="checkbox" name="show_budget_to_bidder" id="show_budget_to_bidder" value="1" {{ isset($showBudgetToBidder) && $showBudgetToBidder ? 'checked' : '' }}>
                        <i></i>{{ trans('eBidding.showBudgetToBidder') }}
                    </label>
                </section>
            </div>
            <!-- bid decrement percentage -->
            <div class="row percentage">
                <section class="col col-xs-12 col-md-6 col-lg-4">
                    <label class="label bid-label-decrement" style="display: none;">{{ trans('eBidding.bid_decrement_percent') }}</label>
                    <label class="label bid-label-increment" style="display: none;">{{ trans('eBidding.bidIncrementPercent') }}</label>
                    <div class="input-group {{{ $errors->has('decrement_percent') ? 'state-error' : null }}}">
                        <input type="number" id="decrement_percent" name="decrement_percent" class="form-control padded-less-left" aria-describedby="decrement_percent_addon" value="{{ (isset($decrement_percent)) ? $decrement_percent : '' }}" step="0.01">
                        <span class="input-group-addon" id="decrement_percent_addon">%</span>
                    </div>
                    {{ $errors->first('decrement_percent', '<em class="invalid">:message</em>') }}
                </section>
                <section class="col col-xs-12 col-md-6 col-lg-4">
                    <label class="checkbox mt-10">
                        <input onclick="uncheckOther('bid_decrement_value', this, 'decrement_percent')" type="checkbox" name="bid_decrement_percent" id="bid_decrement_percent" value="1" {{ isset($bid_decrement_percent) && $bid_decrement_percent ? 'checked' : '' }}>
                        <i></i>{{ trans('eBidding.not_applicable') }}
                    </label>
                </section>
            </div>
            <!-- bid decrement value -->
            <div class="row fixed-amount">
                <section class="col col-xs-12 col-md-6 col-lg-4">
                    <label class="label bid-label-decrement" style="display: none;">{{ trans('eBidding.bid_decrement_value') }}</label>
                    <label class="label bid-label-increment" style="display: none;">{{ trans('eBidding.bidIncrementValue') }}</label>
                    <div class="input-group {{{ $errors->has('decrement_value') ? 'state-error' : null }}}">
                        <span class="input-group-addon" id="decrement_value_addon">{{ $currencyCode }}</span>
                        <input type="number" id="decrement_value" name="decrement_value" class="form-control padded-less-left" aria-describedby="decrement_value_addon" value="{{ (isset($decrement_value)) ? $decrement_value : '' }}" step="0.01">
                    </div>
                    {{ $errors->first('decrement_value', '<em class="invalid">:message</em>') }}
                </section>
                <section class="col col-xs-12 col-md-6 col-lg-4">
                    <label class="checkbox mt-10">
                        <input onclick="uncheckOther('bid_decrement_percent', this, 'decrement_value')" type="checkbox" name="bid_decrement_value" id="bid_decrement_value" value="1" {{ isset($bid_decrement_value) && $bid_decrement_value ? 'checked' : '' }}>
                        <i></i>{{ trans('eBidding.not_applicable') }}
                    </label>
                </section>
            </div>
            @if (\PCK\SystemModules\SystemModuleConfiguration::isEnabled(\PCK\SystemModules\SystemModuleConfiguration::MODULE_ID_EBIDDING_MODES))
                <!-- Custom Bid Value -->
                <div class="row custom-amount">
                    <section class="col col-xs-12 col-md-6 col-lg-4">
                        <label class="checkbox">
                            <input type="checkbox" name="enable_custom_bid_value" id="enable_custom_bid_value" value="1" {{ isset($enableCustomBidValue) && $enableCustomBidValue ? 'checked' : '' }}>
                            <i></i>{{ trans('eBidding.enableCustomBidValue') }}
                        </label>
                    </section>
                </div>
                <!-- hide other bidder info -->
                <div class="row hide-other-bidder-info">
                    <section class="col col-xs-12 col-md-6 col-lg-4">
                        <label class="checkbox">
                            <input type="checkbox" name="hide_other_bidder_info" value="1" {{ isset($hideOtherBidderInfo) && $hideOtherBidderInfo ? 'checked' : '' }}>
                            <i></i>{{ trans('eBidding.hideOtherBidderInfo') }}
                        </label>
                    </section>
                </div>
                <!-- no tie bid -->
                <div class="row no-tie-bid">
                    <section class="col col-xs-12 col-md-6 col-lg-4">
                        <label class="checkbox">
                            <input type="checkbox" name="enable_no_tie_bid" value="1" {{ isset($enable_no_tie_bid) && $enable_no_tie_bid ? 'checked' : '' }}>
                            <i></i>{{ trans('eBidding.noTieBid') }}
                        </label>
                    </section>
                </div>
                <!-- min bid amount diff -->
                <div class="row min-bid-amount-diff">
                    <section class="col col-xs-12 col-md-6 col-lg-4" id="min_bid_amount_diff">
                        <label class="label bid-label-decrement" style="display: none;">{{ trans('eBidding.minBidDecrementAmount') }}</label>
                        <label class="label bid-label-increment" style="display: none;">{{ trans('eBidding.minBidIncrementAmount') }}</label>
                        <div class="input-group {{{ $errors->has('min_bid_amount_diff') ? 'state-error' : null }}}">
                            <span class="input-group-addon" id="min_bid_amount_diff_addon">{{ $currencyCode }}</span>
                            <input type="number" name="min_bid_amount_diff" class="form-control padded-less-left" aria-describedby="min_bid_amount_diff_addon" value="{{ Input::old('min_bid_amount_diff') ? Input::old('min_bid_amount_diff') : ($min_bid_amount_diff ?? '') }}" step="0.01">
                        </div>
                        {{ $errors->first('min_bid_amount_diff', '<em class="invalid">:message</em>') }}
                    </section>
                </div>
            @endif
        </div>
    </div>
</fieldset>