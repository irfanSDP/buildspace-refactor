var DependentSelection = {
    forms: {
        first: null,
        second: null
    },
    urls: {
        first: null,
        second: null
    },
    selectedIds: {
        first: null,
        second: null
    },
    firstFormHasEventListener: false,
    preSelectOnLoad: {
        first: true,
        second: true
    },
    setForms: function(forms){
        this.forms = forms;
    },
    setUrls: function(urls){
        this.urls = urls;
    },
    setSelectedIds: function(selectedIds){
        this.selectedIds = selectedIds;
    },
    setPreSelectOnLoad: function(options) {
        this.preSelectOnLoad = options;
    },
    init: function() {
        var forms = this.forms,
            self = this;

        self.refreshFirstSelect();

        const fIsMultiple = forms.first.attr('multiple') || false;
        const fWidth = forms.first.attr('data-select-width') || '100%';
        const fPlaceholder = forms.first.attr('placeholder') || 'Please Select';
        const fAllowClear = (forms.first.attr('data-allow-clear') && forms.first.attr('data-allow-clear') == 'false') ? false : true;
        var fOptions = (fIsMultiple) ? {
            theme: 'bootstrap',
            width : fWidth
        }: {
            placeholder: {
                id: '', // the value of the option
                text: fPlaceholder
            },
            theme: 'bootstrap',
            allowClear : fAllowClear,
            width : fWidth
        };
        forms.first.select2(fOptions);

        const sIsMultiple = forms.second.attr('multiple') || false;
        const sWidth = forms.second.attr('data-select-width') || '100%';
        const sPlaceholder = forms.second.attr('placeholder') || 'Please Select';
        const sAllowClear = (forms.second.attr('data-allow-clear') && forms.second.attr('data-allow-clear') == 'false') ? false : true;
        var sOptions = (sIsMultiple) ? {
            theme: 'bootstrap',
            width : sWidth
        }: {
            placeholder: {
                id: '', // the value of the option
                text: sPlaceholder
            },
            theme: 'bootstrap',
            allowClear : sAllowClear,
            width : sWidth
        };
        forms.second.select2(sOptions);
    },
    refreshFirstSelect: function () {
        var forms = this.forms,
            self = this;

        $.ajax({
            type: "GET",
            url: self.urls.first,
            dataType: 'json'

        }).done(function( response ) {
            $.each(response.data, function(key, object) {

                forms.first
                    .append($("<option></option>")
                        .attr("value", object.id)
                        .text(object.description));
            });

            if(self.selectedIds.first)
            {
                forms.first.val(self.selectedIds.first).change();
                self.refreshSecondSelect(self.selectedIds.second);
            }
            else if(self.preSelectOnLoad.first)
            {
                forms.first.val(response.default).change();
                self.refreshSecondSelect();
            }

            // We add the listener after the first onChange is fired, to avoid the first onChange refresh from overwriting the preselected values.
            if(!self.firstFormHasEventListener){
                forms.first.on('change', function (e) {
                    self.refreshSecondSelect(null, 'onchange');
                });
            }
        });
    },
    refreshSecondSelect: function (selectedSecondId) {

        var forms = this.forms,
            self = this,
            firstId = forms.first.val(),
            defaultSelectedObject;

        forms.second.val('').change();

        if(firstId && self.urls.second) {
            $.ajax({
                type: "GET",
                url: self.urls.second + "/" + firstId,
                dataType: 'json'

            }).done(function( response ) {
                $('[data-type=dependentSelection][data-dependent-id=second] option[value!=""]').remove();

                forms.second.children().remove();

                $.each(response.data, function(key, object) {
                    forms.second
                        .append($("<option></option>")
                            .attr("value", object.id)
                            .text(object.description));
                });

                if(selectedSecondId)
                {
                    if(Array.isArray(selectedSecondId)){
                        forms.second.val(selectedSecondId).change();
                    }
                    else{
                        forms.second.val(selectedSecondId).change();
                    }
                }
                else if(self.preSelectOnLoad.second && (defaultSelectedObject = response.data[0]))
                {
                    forms.second.val(defaultSelectedObject.id).change();
                }
            });
        } else {
            forms.second.select2("val", '');
        }
    }
};