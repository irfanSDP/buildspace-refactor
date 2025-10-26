<script type="text/javascript">
$(document).ready(function () {
    $('#{{ $formPrefix }}-modal').on('show.bs.modal', function () {
        resetForm("{{ $formPrefix }}-form");
    })

    $('#ext_app_{{ $formPrefix }}-btn').on('click', function(e){
        $('#{{ $formPrefix }}-modal').modal('show');
    });
});

const submitForm = (form, successCallback) => {
    const formData  = new FormData();
    const submitBtn = $("#{{ $formPrefix }}_submit-btn");

    submitBtn.prop("disabled", true);

    for ( var i = 0; i < form.elements.length; i++ ) {
        var el = form.elements[i];
        if(el.name.length && (el.getAttribute("type") != "checkbox" || (el.getAttribute("type") == "checkbox" && el.checked)) && (el.getAttribute("type") != "radio" || (el.getAttribute("type") == "radio" && el.checked))){
            var selected = [];
            if(el.options) {
                selected = [...el.selectedOptions].map(option => option.value);
            }

            if (selected.length > 1){
                formData.append(el.name, selected);
            } else {
                formData.append(el.name, el.value); 
            }
        }
    }

    fetch(form.getAttribute('action'), {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Csrf-Token': form.querySelector('[name="_token"]').value,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    }).then((response) => {
        if (!response.ok) {
            return;
        }
        return response.json(); 
    })
    .then((data) =>{
        
        submitBtn.prop("disabled", false);

        if (data.status == "success"){
            $('#{{ $formPrefix }}-modal').modal('hide');
            if (typeof successCallback == "function"){
                successCallback(data);
            }
        }else{
            for(var i in data.errors){
                var el = form.querySelector('em[data-field="form_error-'+i+'"]');
                if(el){
                    form.querySelector('label[data-field="form_error_label-'+i+'"]').classList.add("state-error");
                    el.textContent = data.errors[i];
                }
            }
        }
    }).catch((error) => {
        console.log(error);
        submitBtn.prop("disabled", false);
    });
}

const resetForm = (formId) => {
    const form = $('#'+formId);
    const errElems = form.find("em[data-field^='form_error-']");
    form.trigger("reset");
    errElems.each(function(i, elem) {
        $(this).text("");
        let n = $(this).data('field');
        let nArr = n.split("-");
        if(nArr.length > 1){
            $('label[data-field="form_error_label-'+nArr[1]+'"]').removeClass('state-error');
        }
    });
}
</script>