@if (! empty($paymentGatewayData['image_url']))
    <a href="javascript:void(0)" class="pg-btn"><img src="{{ $paymentGatewayData['image_url'] }}" alt="pay" /></a>
@else
    <button type="button" class="btn btn-primary pg-btn">Pay</button>
@endif