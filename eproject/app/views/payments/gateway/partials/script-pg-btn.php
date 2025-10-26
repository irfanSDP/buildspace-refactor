<script>
    function initPaymentGatewayBtn() {
        $('#pg-container').on('click', '.pg-btn-container .pg-btn', function(e) {
            e.preventDefault();
            let t = $(this);
            let cont = t.parents('.pg-btn-container');
            let pg = atob(cont.data('pg'));
            let d = cont.data('d');

            $.ajax({
                url: atob(cont.data('lnk')),
                method: 'GET',
                data: {
                    pg: pg,
                    d: d
                },
                success: function (response) {
                    if (response.success) {
                        $('#pg-form-container').html(atob(response.data));

                        let pgForm = $('#pg-form-container .pg-form');
                        pgForm.submit();
                    } else {
                        notifyMsg('error', response.msg);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    // error
                }
            });
        });
    }
</script>