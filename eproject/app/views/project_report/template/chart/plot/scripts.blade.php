<script>
    $(document).ready(function() {
        getPartials('categoryColumn');

        $(document).on('change', '#categoryColumn', function() {
            getPartials('categoryColumn');
        });

        $(document).on('change', '#valueColumn', function() {
            getPartials('valueColumn');
        });

        function getPartials(fieldName) {
            var dataObject = {};

            @if (! empty($record)) {
                dataObject['plotId'] = '{{ $record->id }}';
            }
           @endif
           dataObject[fieldName + 'Id'] = $('#' + fieldName).val();

            $.ajax({
                url: '{{ route('projectReport.chart.plot.template.partials', array($chart->id)) }}',
                method: 'GET',
                data: dataObject,
                before: function () {
                    $('#'+fieldName+'Partials').html('');
                },
                success: function (data) {
                    if (data) {
                        $('#'+fieldName+'Partials').html(data);

                        // Initialize select2 on new fields with select2 class
                        $('#'+fieldName+'Partials select:not([data-select2-id])').each(function() {
                            if ($(this).hasClass('select2')) {
                                $(this).select2({
                                    theme: 'bootstrap'
                                });
                            }
                        });

                        if (fieldName === 'categoryColumn') {
                            getPartials('valueColumn');
                        }
                    }
                    //app_progressBar.hide();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        }
    });
</script>