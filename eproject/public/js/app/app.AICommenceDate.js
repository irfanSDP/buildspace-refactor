$(document).ready(function () {
    $('[data-ai-selector]').change(function () {
        var aiId = $(this).val();
        var csrfToken = $('meta[name=_token]').attr("content");
        var aiCommencementDateInput = $("#aiCommencementDate");
        var newDeadline = $("#new_deadline");

        if (aiId < 0) {
            aiCommencementDateInput.val(null);
            newDeadline.html('-');

            return false;
        }

        $.post(webClaim.getAIDeadlineDateURL, {aiId: aiId, '_token': csrfToken}, function (data) {
            aiCommencementDateInput.val(data.new_created_at);
            // aiCommencementDateInput.prop("disabled", true);

            newDeadline.html(data.new_deadline);
        });
    });
});