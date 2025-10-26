(function () {

    var csrfToken = $('meta[name=_token]').attr("content");

    function disableSpecificDays(date) {
        var m = date.getMonth();
        var d = date.getDate();
        var y = date.getFullYear();

        if (typeof webClaim != 'undefined' && typeof webClaim.events != 'undefined') {
            var disabledSpecificDays = $.map(webClaim.events, function (el) {
                return el;
            });

            for (var i = 0; i < disabledSpecificDays.length; i++) {
                if ($.inArray((m + 1) + '-' + d + '-' + y, disabledSpecificDays) != -1) {
                    return [false];
                }
            }
        }

        return [true];
    }

    // START AND FINISH DATE
    $('.startdate').datepicker({
        dateFormat: 'dd-M-yy',
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        changeMonth: true,
        changeYear: true,
        beforeShowDay: disableSpecificDays,
        onSelect: function (selectedDate) {
            $('.finishdate').datepicker('option', 'minDate', selectedDate);
        }
    });

    $('.finishdate').datepicker({
        dateFormat: 'dd-M-yy',
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        changeMonth: true,
        changeYear: true,
        beforeShowDay: disableSpecificDays,
        onSelect: function (selectedDate) {
            $('.startdate').datepicker('option', 'maxDate', selectedDate);

            if (typeof webClaim != 'undefined' && typeof webClaim.datesCalculateURL != 'undefined') {
                var deadlineDays = $("#deadline_days").val();

                $.post(webClaim.datesCalculateURL, {
                    date: selectedDate,
                    deadlineDays: deadlineDays,
                    '_token': csrfToken
                }, function (data) {
                    $("#new_deadline").html(data.new_deadline);
                });
            }
        }
    });

})();