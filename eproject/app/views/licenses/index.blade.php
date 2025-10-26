@extends('layout.main', array('hide_ribbon'=>true))

@section('content')
<div class="row">
    <article class="col col-xs-12">
        <div class="jarviswidget" id="licenseWidget" data-widget-editbutton="false" data-widget-custombutton="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-edit"></i> </span>
                <h2>{{ trans('licenses.enterLicenseKey') }}</h2>				
            </header>
            <div>
                <div class="jarviswidget-editbox"></div>
                <div class="widget-body no-padding">
                    <form id="activate-license-form" method="POST" action="{{ route('license.store') }}" class="smart-form">
                        <input type="hidden" name="_token" value="{{{ csrf_token() }}}">
                        <fieldset>
                            <section>
                                <label class="label">{{ trans('licenses.licenseKey') }}</label>
                                <label class="textarea">
                                    <textarea id="txtLicenseKey" rows="3" name="licenseKey" id="licenseKey"></textarea>
                                </label>
                            </section>
                        </fieldset>	
                        <footer>
                            <button id="btnActivateLicense" type="submit" class="btn btn-primary" disabled>{{ trans('licenses.activateLicense') }}</button>
                        </footer>
                    </form>
                </div>
            </div>
        </div>
    </article>
</div>
@if($licenseDetails)
<div class="row">
    <article class="col col-xs-12">
        <div class="jarviswidget" id="licenseDetails" data-widget-editbutton="false" data-widget-custombutton="false">
            <header>
                <span class="widget-icon"> <i class="fa fa-id-card" aria-hidden="true"></i> </span>
                <?php $licenseStatusText = $isLicenseValid ? trans('licenses.licenseActive') : trans('licenses.licenseExpired'); ?>
                <?php $licenseStatusLabelClass = $isLicenseValid ? 'success' : 'danger'; ?>
                <h2>{{ trans('licenses.licenseKeyDetails') }} <span class="label label-{{ $licenseStatusLabelClass }}">{{ $licenseStatusText }}</span></h2>				
            </header>
            <div>
                <div class="jarviswidget-editbox"></div>
                <div class="widget-body no-padding">
                    <form id="checkout-form" class="smart-form">
                        <fieldset>
                            <section>
                                <label class="label">{{ trans('licenses.licenseKey') }}</label>
                                <label class="textarea">
                                    <textarea rows="3" disabled>{{ $licenseDetails['licenseKey'] }}</textarea>
                                </label>
                            </section>
                            <div class="row">
                                <section class="col col-4">
                                    <label class="input"><span>{{ trans('licenses.companyLimit') }}</span>
                                        <label class="label">{{ $licenseDetails['companyLimit'] }}</label>
                                    </label>
                                </section>
                                <section class="col col-4">
                                    <label class="input"><span>{{ trans('licenses.validUntil') }}</span>
                                        <label class="label">{{ $licenseDetails['validUntilDateTime'] }}</label>
                                    </label>
                                </section>
                                <section class="col col-4">
                                    <label class="input"><span>{{ trans('licenses.remainingDays') }}</span>
                                        <label class="label">{{ $licenseDetails['daysRemaining'] }}</label>
                                    </label>
                                </section>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </article>
</div>
@endif
<script type="text/javascript">
    $(document).ready(function() {
        $('#txtLicenseKey').bind('input propertychange', function() {
            $('#btnActivateLicense').prop('disabled', ($(this).val().length === 0));
        });
    });
</script>

@endsection