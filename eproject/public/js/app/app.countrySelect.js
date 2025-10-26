$(document).ready(function() {

    var countrySelection = {

        form: {
            country: $('#country'),
            state: $('#state')
        },

        init: function() {

            var form = this.form,
                self = this;

            self.refreshCountrySelect();

            form.country.select2({
                placeholder: "Select Country",
                theme: 'bootstrap'
            }).on('change', function (e) {
                self.refreshStateSelect();
            });

            form.state.select2({
                placeholder: "Select a State",
                theme: 'bootstrap'
            });
        },

        refreshCountrySelect: function () {

            var form = this.form,
                self = this;

            $.ajax({
                type: "GET",
                url: webClaim.urlCountry,
                dataType: 'json'

            }).done(function( response ) {
                $.each(response.data, function(key, country) { 
                    form.country
                        .append($("<option></option>")
                        .attr("value", country.id)
                        .text(country.text)); 
                });

                if(webClaim.countryId){
                    form.country.val(webClaim.countryId).trigger("change");
                    self.refreshStateSelect(webClaim.stateId);
                }else{
                    form.country.val(response.default).trigger("change");
                    self.refreshStateSelect();
                }
            });
        },

        refreshStateSelect: function (selectedState) {

            var form = this.form,
                countryId = form.country.val();

            form.state.val('').trigger("change");

            if(countryId) {
                $.ajax({
                    type: "GET",
                    url: webClaim.urlStates + "/" + countryId,
                    dataType: 'json'

                }).done(function( response ) {
                    $('#state option[value!=""]').remove();

                    $.each(response.data, function(key, state) {
                        form.state
                            .append($("<option></option>")
                            .attr("value", state.id)
                            .text(state.text));
                    });

                    if(selectedState){
                        form.state.val(selectedState).trigger("change");
                    }
                });
            } else {
                form.state.select2("val", '');
            }
        }
    };

    countrySelection.init();
});