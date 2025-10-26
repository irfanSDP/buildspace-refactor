@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ trans('generalSettings.generalSettings') }}</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-cog"></i> {{ trans('generalSettings.generalSettings') }}
            </h1>
        </div>
    </div>
    <div class="jarviswidget" data-widget-editbutton="false" data-widget-custombutton="false">
        <div class="widget-body padding-bottom-0">
            {{ Form::open(array('url' => '#', 'class' => 'smart-form')) }}
                <section class="row margin-bottom-10">
                    <div class="col col-xs-12">
                        <label class="checkbox">
                            <input type="checkbox" id="view_subsidiary" name="view_subsidiary" value="1"
                                {{ ($generalSetting->view_own_created_subsidiary) ? 'checked' : '' }}>
                            <i></i>{{ trans('generalSettings.onlyAllowBusinessUnitToViewOwnCreatedSubsidiary') }}
                        </label>
                    </div>
                </section>
                <section class="row margin-bottom-10">
                    <div class="col col-xs-12">
                        <label class="checkbox">
                            <input type="checkbox" id="view_tenders" name="view_tenders" value="1"
                                {{ ($generalSetting->view_tenders) ? 'checked' : '' }}>
                            <i></i>{{ trans('generalSettings.viewTenders') }}
                        </label>
                    </div>
                </section>
                <section class="row margin-bottom-10">
                    <div class="col col-xs-12">
                        <label class="checkbox">
                            <input type="checkbox" id="enable_e_bidding" name="enable_e_bidding" value="1"
                                {{ ($generalSetting->enable_e_bidding) ? 'checked' : '' }}>
                            <i></i>{{ trans('generalSettings.enableEbidding') }}
                        </label>
                    </div>
                </section>
            {{ Form::close() }}
        </div>
    </div>
@endsection

@section('js')
    @include('common.scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            async function updateSetting(setting, value) {
                const url = "{{ route('general_settings.store') }}";
                const options = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        _token: "{{ csrf_token() }}",
                        [setting]: value,
                    })
                };

                try {
                    const response = await fetch(url, options);
                    const data = await response.json();

                    if (! response.ok || response.status !== 200) {
                        throw new Error("{{ trans('errors.anErrorOccurred') }}");
                    }

                    if (! data.success) {
                        throw new Error("{{ trans('forms.updateFailed') }}");
                    }

                    notifyMsg('success', "{{ trans('forms.updateSuccessful') }}");
                } catch (err) {
                    notifyMsg('error', "{{ trans('errors.anErrorOccurred') }}");
                }
            }

            document.getElementById('view_subsidiary').addEventListener('change', function() {
                const value = this.checked ? 1 : 0;
                updateSetting('view_subsidiary', value);
            });

            document.getElementById('view_tenders').addEventListener('change', function() {
                const value = this.checked ? 1 : 0;
                updateSetting('view_tenders', value);
            });

            document.getElementById('enable_e_bidding').addEventListener('change', function() {
                const value = this.checked ? 1 : 0;
                updateSetting('enable_e_bidding', value);
            });
        });
    </script>
@endsection