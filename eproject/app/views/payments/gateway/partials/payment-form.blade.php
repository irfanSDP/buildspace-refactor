{{ Form::open(array('class' => 'pg-form', 'method' => 'post', 'url' => $paymentGatewayData['payment_url'])) }}
    <input type="hidden" name="detail" value="{{ $paymentGatewayData['detail'] }}">
    <input type="hidden" name="amount" value="{{ $paymentGatewayData['amount'] }}">
    <input type="hidden" name="order_id" value="{{ $paymentGatewayData['order_id'] }}">
    <input type="hidden" name="name" value="{{ $paymentGatewayData['name'] }}">
    <input type="hidden" name="email" value="{{ $paymentGatewayData['email'] }}">
    <input type="hidden" name="phone" value="{{ $paymentGatewayData['phone'] }}">
    <input type="hidden" name="hash" value="{{ $paymentGatewayData['hash'] }}">
{{ Form::close() }}