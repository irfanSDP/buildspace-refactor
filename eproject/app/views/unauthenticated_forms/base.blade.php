<!DOCTYPE html>
<html lang="en-us">
<head>
    @include('layout.main_partials.head')
</head>
<body>
<!-- Link to Google CDN's jQuery + jQueryUI; fall back to local -->
<script src="{{ asset('js/jquery/dist/jquery.min.js') }}"></script>
<script src="{{ asset('js/jquery/dist/jquery-migrate-3.5.2.min.js') }}"></script>
<script src="{{ asset('js/jquery-ui-1.14.0/jquery-ui.min.js') }}"></script>

<script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>

@section('content')
@show

@include('templates.app_confirmation_modal')

<!--================================================== -->
<!-- Link to Google CDN's jQuery + jQueryUI; fall back to local -->

<!-- IMPORTANT: APP CONFIG -->
<script src="{{ asset('js/app.config.js') }}"></script>

<!-- BOOTSTRAP JS -->
<script src="{{ asset('js/bootstrap/bootstrap.min.js') }}"></script>

<!-- CUSTOM NOTIFICATION -->
<script src="{{ asset('js/notification/SmartNotification.min.js') }}"></script>

<!-- JARVIS WIDGETS -->
<script src="{{ asset('js/smartwidgets/jarvis.widget.min.js') }}"></script>

<!-- JQUERY SELECT2 INPUT -->
<script src="{{ asset('js/plugin/select2/js/select2.min.js') }}"></script>

<!-- JQUERY UI + Bootstrap Slider -->
<script src="{{ asset('js/plugin/bootstrap-slider/bootstrap-slider.min.js') }}"></script>

<!-- browser msie issue fix -->
<script src="{{ asset('js/plugin/msie-fix/jquery.mb.browser.min.js') }}"></script>

<!--[if IE 8]>

<h1>Your browser is out of date, please update your browser by going to www.microsoft.com/download</h1>

<![endif]-->

<!-- MAIN APP JS FILE -->
<script src="{{ asset('js/app.min.js') }}"></script>

<script src="{{ asset('js/jquery.autosize.min.js') }}"></script>
<script src="{{ asset('js/app/app.datePicker.js') }}"></script>
<script src="{{ asset('js/app/app.expandable.js') }}"></script>
<script src="{{ asset('js/app/app.confirmation.js') }}"></script>

<script>
    $(document).ready(function () {
        $.sound_path = "{{{ url('/sound').'/' }}}";

        $('body').addClass(localStorage.getItem('bodyClass'));

        autosize($('textarea'));

        $('table th .checkall').on('click', function () {
            if($(this).is(':checked')){
                $(this).closest('table').find(':checkbox').prop('checked', true);
            }
            else{
                $(this).closest('table').find(':checkbox').prop('checked', false);
            }
        });

        @if($translatedText && $userLocale)

        var translatedText = {{ $translatedText }};
        var userLocale = "{{{ $userLocale }}}";

        // apply translation for default locale
        var translationForDefaultLocale = translatedText[userLocale];
        applyTranslation(translationForDefaultLocale);

        $('#displayLanguageSelect').on('change', function(e) {
            e.preventDefault();
            var selectedLocale = $(this).find(":selected").val();
            var translationForLocale = translatedText[selectedLocale];
            
            $('#selectedLocale').val(selectedLocale);
            applyTranslation(translationForLocale);
        });

        function applyTranslation(translationForLocale){
            $('#languageLabel').text(translationForLocale['languageLabel']);
            $('#pleaseConfirmInterestToTender').text(translationForLocale['pleaseConfirmInterestToTender']);
            $('#currentlyLoggedInAs').text(translationForLocale['currentlyLoggedInAs']);
            $('#project').text(translationForLocale['project']);
            $('#descriptionOfWork').text(translationForLocale['descriptionOfWork']);
            $('#statusConfirmationIsSuccessful').text(translationForLocale['statusConfirmationIsSuccessful']);
            $('#btn_' + "{{{ \PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus::OK }}}").text(translationForLocale['commitmentYes']);
            $('#btn_' + "{{{ \PCK\TenderRecommendationOfTendererInformation\ContractorCommitmentStatus::REJECT }}}").text(translationForLocale['commitmentNo']);
        }
        @endif

        $('button[data-action=respond]').on('click', function(e) {
            $('#status-form input[name=option]').val($(this).data('option-id'));
        });
    });
</script>
<script>
    $('[data-toggle=tooltip]').tooltip();
</script>
</body>
</html>