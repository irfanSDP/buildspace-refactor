<script>
    $(document).ready(function() {
        $('#periodValue').select2({
            theme: 'bootstrap',
            //tags: true,
        });

        getPartials('categoryColumn');

        $(document).on('change', '#categoryColumn', function() {
            getPartials('categoryColumn');
        });

        $('#reminderForm').on('submit', 'form', function(e) {
            e.preventDefault();
            let form = $(this);

            if (! $('#periodValue').val()) {
                $.smallBox({
                    title : "{{ trans('projectReportNotification.period') }}",
                    content : "<i class='fa fa-check'></i> <i>{{ trans('projectReportNotification.errorNoPeriodSelected') }}</i>",
                    color : "#C46A69",
                    sound: false,
                    //iconSmall : "fa fa-paper-plane",
                    timeout : 5000
                });
                return false;
            }
            form.submit();
        });

        function getPartials(fieldName) {
            var dataObject = {};

            @if (! empty($record)) {
                dataObject['recordId'] = '{{ $record->id }}';
            }
            @endif
            dataObject[fieldName + 'Id'] = $('#' + fieldName).val();

            $.ajax({
                url: '{{ route('projectReport.notification.partials', [$project->id, $mappingId]) }}',
                method: 'GET',
                data: dataObject,
                before: function () {
                    $('#'+fieldName+'Partials').html('');
                },
                success: function (data) {
                    if (data) {
                        $('#'+fieldName+'Partials').html(data);

                        // Initialize select2 on new fields with select2 class
                        /*$('#'+fieldName+'Partials select:not([data-select2-id])').each(function() {
                            if ($(this).hasClass('select2')) {
                                $(this).select2({
                                    theme: 'bootstrap'
                                });
                            }
                        });*/
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