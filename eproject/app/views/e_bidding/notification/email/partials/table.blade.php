<div style="width:100%;font-family:helvetica,'helvetica neue',arial,verdana,sans-serif;padding:0;Margin:0;background-color:#f6f8fc;margin:0;color:#24262c;box-sizing:border-box">
    <table style="margin:0 auto;padding:24px 0;max-width:650px;width:100%">
        <tbody>
            <tr>
                <td bgcolor="white" style="border-top: 6px solid #3170e7;border-radius:8px;padding-bottom:24px;border-left:1px solid #c1cbe0;border-right:1px solid #c1cbe0;border-bottom:1px solid #c1cbe0">

                    <table style="width:100%">
                        <tbody>
                            <tr>
                                <td colspan="3" style="padding:0;margin:0;padding-left:24px;padding-right:24px;padding-top:24px;font-weight:bold;font-size:18px;line-height:21px">
                                    {{ $projectName }}
                                </td>
                            </tr>

                            @include('e_bidding.notification.email.partials.row', ['label' => trans('eBidding.preview_start_time'), 'value' => $previewStartTimeFormat])

                            @include('e_bidding.notification.email.partials.divider')
                            @include('e_bidding.notification.email.partials.row', ['label' => trans('eBidding.bidding_start_time'), 'value' => $biddingStartTimeFormat])

                            @include('e_bidding.notification.email.partials.divider')
                            @include('e_bidding.notification.email.partials.row', ['label' => trans('eBidding.bidding_end_time'), 'value' => $biddingEndTimeFormat])

                            @include('e_bidding.notification.email.partials.divider')
                            @include('e_bidding.notification.email.partials.row', ['label' => trans('eBidding.duration'), 'value' => $biddingDuration])

                            @if (! empty($biddingStartOvertime))
                                @include('e_bidding.notification.email.partials.divider')
                                @include('e_bidding.notification.email.partials.row', ['label' => trans('eBidding.start_overtime'), 'value' => $biddingStartOvertime])

                                @if (! empty($biddingOvertimePeriod))
                                    @include('e_bidding.notification.email.partials.divider')
                                    @include('e_bidding.notification.email.partials.row', ['label' => trans('eBidding.overtime_period'), 'value' => $biddingOvertimePeriod])
                                @endif
                            @endif

                            @if ($bidMode !== \PCK\EBiddings\EBiddingMode::BID_MODE_ONCE)
                                @if ($budgetEnabled)
                                    @include('e_bidding.notification.email.partials.divider')
                                    @include('e_bidding.notification.email.partials.row', ['label' => trans('eBidding.budget'), 'value' => $budgetAmount])
                                @endif

                                @include('e_bidding.notification.email.partials.divider')
                                @include('e_bidding.notification.email.partials.row', ['label' => $bidDecrementPercLabel, 'value' => $bidDecrementPerc])

                                @include('e_bidding.notification.email.partials.divider')
                                @include('e_bidding.notification.email.partials.row', ['label' => $bidDecrementAmountLabel, 'value' => $bidDecrementAmount])
                            @endif


                            @include('e_bidding.notification.email.partials.divider')
                            @include('e_bidding.notification.email.partials.row2', ['label' => $linkHtml, 'value' => null])

                        </tbody>
                    </table>

                </td>
            </tr>
        </tbody>
    </table>
</div>