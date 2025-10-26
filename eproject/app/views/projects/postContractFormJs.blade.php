<script>

    $('#trade').on('change', function(){

        var id = this.value; 
        getProjectLabourRate(id);

    });

    function getProjectLabourRate(id){
        var url = '{{ route('daily-labour-report.populatePostContractProjectLabourRate', array($project->id)) }}';

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{{ csrf_token() }}}',
                id: id
            },
            success: function (data, status, xhr) {

                $('#normal_working_hours').val(0);
                $("input[id^='normal_rate_per_hour_']").val(0);
                $("input[id^='ot_rate_per_hour_']").val(0);

                $('#normal_working_hours').attr('readonly', false);
                $("input[id^='normal_rate_per_hour_']").attr('readonly', false);
                $("input[id^='ot_rate_per_hour_']").attr('readonly', false);

                if(data.length > 0)
                {
                   $.each(data, function(index, value) {

                       $('#normal_working_hours').val(value.normal_working_hours);
                       $('#normal_rate_per_hour_'+value.labour_type).val(value.normal_rate_per_hour);
                       $('#ot_rate_per_hour_'+value.labour_type).val(value.ot_rate_per_hour);

                       $('#normal_working_hours').attr('readonly', true);
                       $('#normal_rate_per_hour_'+value.labour_type).attr('readonly', true);
                       $('#ot_rate_per_hour_'+value.labour_type).attr('readonly', true);

                   });
                }

            },
            error: function (jqXHR, textStatus, errorThrown) {
                // error
            }
        });
    }

</script>