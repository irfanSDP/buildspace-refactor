@if (empty($tenderAlternativeCustomDescription))
    @include($viewName, $viewData)
@else
    <?php echo $tenderAlternativeCustomDescription; ?>
@endif