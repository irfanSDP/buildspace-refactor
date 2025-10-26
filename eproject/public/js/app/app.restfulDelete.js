(function () {

    var laravel = {

        initialize: function () {
            this.elements = $(document.body);

            this.registerEvents();
        },

        registerEvents: function () {
            this.elements.on('click', '[data-method]', this.handleMethod);
        },

        handleMethod: function (e) {
            e.preventDefault();

            const el = $(this);
            const httpMethod = el.data('method').toUpperCase();

            // If the data-method attribute is not PUT or DELETE,
            // then we don't know what to do. Just ignore.
            if ($.inArray(httpMethod, ['PUT', 'DELETE']) === -1) {
                return;
            }

            $('#restful_delete-modal').one('show.bs.modal', function (e) {
                const modal = $(this);
                var confirmTxt = 'You are about to remove this entry. Are you sure?';
                //if data-confirm="confirmation text..." is set the use it
                if ( el.data('confirm') ) {
                    confirmTxt =  el.data('confirm');
                }
                modal.find('.modal-body span').text(confirmTxt);
            });

            $('#restful_delete_yes-btn').one('click', function(){
                const form = laravel.createForm(el);
                form.submit();
            });

            $('#restful_delete-modal').modal('show');
        },

        createForm: function (el) {
            const form =
                $('<form>', {
                    'method': 'POST',
                    'action': (el.data('url') === undefined) ? el.attr('href') : el.data('url')
                });

            const token =
                $('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': el.data('csrf_token')
                });

            const hiddenInput =
                $('<input>', {
                    'name': '_method',
                    'type': 'hidden',
                    'value': el.data('method')
                });

            return form.append(token, hiddenInput).appendTo('body');
        }
    };

    laravel.initialize();

})();