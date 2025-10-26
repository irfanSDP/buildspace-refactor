@extends('layout.main')

@section('css')
    <style>
		/*custom styling since table fully occupied container*/
        .tabulator .tabulator-tableHolder {
			border: none;
		}
    </style>
@endsection

@section('breadcrumb')
	<ol class="breadcrumb">
		<li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
		<li>{{ trans('paymentGateway/settings.paymentGatewaySettings') }}</li>
	</ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-credit-card"></i> {{ trans('paymentGateway/settings.paymentGatewaySettings') }}
			</h1>
		</div>
	</div>
	<div class="jarviswidget" data-widget-editbutton="false" data-widget-custombutton="false">
        <header>
            <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
            <h2>{{ trans('paymentGateway/settings.settings') }}</h2>
        </header>
        <div>
            <div class="jarviswidget-editbox"></div>
            <div class="widget-body">
                {{ Form::open(array('id' => 'paymentGatewaySettings', 'class' => 'smart-form', 'method' => 'post', 'url' => route('payment-gateway.settings.update', [$record->id]))) }}
                    <fieldset>
                        <section class="row mb-5">
                            <div class="col col-xs-12">
                                <label class="label">{{ trans('paymentGateway/settings.paymentGateway') }}</label>
                                <img src="{{ $gatewayImg }}" alt="{{ trans('paymentGateway/settings.paymentGateway') }}" />
                            </div>
                        </section>
                        <section class="row">
                            <div class="col col-xs-12 col-md-3">
                                <label class="label">{{ trans('paymentGateway/settings.merchantId') }}</label>
                                <label class="input">
                                    <input type="text" name="merchantId" value="{{ $record->merchant_id }}">
                                </label>
                            </div>
                            <div class="col col-xs-12 col-md-3">
                                <label class="label">{{ trans('paymentGateway/settings.secretKey') }}</label>
                                <label class="input">
                                    <input type="text" name="key1" value="{{ $record->key1 }}">
                                </label>
                            </div>
                            <div class="col col-xs-12 col-md-3">
                                <label class="label">{{ trans('paymentGateway/settings.buttonImgUrl') }}</label>
                                <label class="input">
                                    <input type="url" name="buttonImageUrl" value="{{ $record->button_image_url }}" placeholder="Leave blank to use default image">
                                </label>
                            </div>
                            <div class="col col-xs-12 col-md-3">
                                <div class="row">
                                    <div class="col col-xs-12 col-md-12">
                                        <label class="checkbox">
                                            <input type="checkbox" name="isActive" {{ $record->is_active ? 'checked' : '' }}>
                                            <i></i>{{ trans('paymentGateway/settings.isActive') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col col-xs-12 col-md-12">
                                        <label class="checkbox">
                                            <input type="checkbox" name="isSandbox" {{ $record->is_sandbox ? 'checked' : '' }}>
                                            <i></i>{{ trans('paymentGateway/settings.isSandbox') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <section class="row">
                            <div class="col col-xs-12 col-md-3">
                                <label class="label">&nbsp;</label>
                                <label class="input">
                                    <button type="submit" class="btn btn-primary">{{ trans('forms.update') }}</button>
                                </label>
                            </div>
                        </section>
                    </fieldset>
                {{ Form::close() }}
            </div>
        </div>		
    </div>
@endsection

@section('js')
@endsection