/*
 Intercepts onclick and submit events and prompts for confirmation.
 Add the property [data-intercept=confirmation] to the DOM.
 Add the property [data-form-id] if you want the action to submit a form, but the event caller does not submit by default (i.e. not the submit button).
 Optionally, add the property [data-intercept-condition=<methodName>] to check for the condition.
 Optionally, add the property [data-intercept-timeout-duration=<timeoutValue>] to set the duration before the confirm button can be clicked.
 OPtionally, add the property [data-confirmation-title=<title>] to override the default title.
 Optionally, add the property [data-confirmation-message=<message>] to override the default message.
 Optionally, add the property [data-confirmation-with-remarks='<inputName>'] include an input for remarks.
 Optionally, add the property [data-confirmation-with-remarks-required='<boolean>'] will set remarks as required.
 Does not intercept/interrupt inline 'onclick' methods.
 * */

var app_confirmation = {
    isIntercepted  : false,
    byPass         : false,
    event          : null,
    timeoutDuration: 0,
    confirmationMessage: null,
    confirmationTitle: null,
    defaultTimeoutDuration: 3000,
    checkCondition : function(e, target) {
        var execute = true;

        // Check for intercept condition.
        if( window[ $(target).data('intercept-condition') ] ) execute = window[ $(target).data('intercept-condition') ](e);

        return execute;
    },
    prompt         : function(e, target) {
        if( this.checkCondition(e, target) ) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var timeoutDuration = $(target).data('intercept-timeout-duration');

            if( timeoutDuration || timeoutDuration == 0){
                this.timeoutDuration = timeoutDuration;
            }
            else{
                this.timeoutDuration = this.defaultTimeoutDuration;
            }

            this.event = e;

            var confirmationMessage = $(target).data('confirmation-message');

            if( ! confirmationMessage ) confirmationMessage = this.confirmationMessage;
            if( confirmationMessage )
            {
                $('#confirmationModal [data-category=confirmation-message]').html(confirmationMessage);
            }

            var confirmationTitle = $(target).data('confirmation-title');

            if( ! confirmationTitle ) confirmationTitle = this.confirmationTitle;
            if( confirmationTitle )
            {
                $('#confirmationModal [data-confirmation-title=confirmation-title]').html(confirmationTitle);
            }

            $('#confirmationModal').find('[data-type=hidden-by-default]').each(function(){
                $(this).hide();
            });

            if( $(target).data('confirmation-with-remarks') ) $('#confirmationModal [data-input=remarks-input]').show();

            $('#confirmationModal').modal(
                {
                    backdrop: 'static',
                    keyboard: true
                }, 'show'
            );
        }
    },
    reset          : function() {
        this.isIntercepted   = false;
        this.byPass          = false;
    }
};

// Handle clicks from DOM elements added after page load.
$(document).on(
    'click submit', "[data-intercept=confirmation]", function(e) {
        var event = $(this).data('event') ? $(this).data('event') : 'click';

        if( e.type !== event ) return;

        if( app_confirmation.byPass ) return;

        // Execute only if event has not yet been intercepted.
        if( !app_confirmation.isIntercepted ) {
            app_confirmation.prompt(e, this);
        }
        app_confirmation.isIntercepted = false;
    }
);

$(document).on(
    'click', '#confirmationModal [data-action]', function(e) {
        if( $(this).data('action') == 'abort' ) {
            app_confirmation.reset();
            return;
        }

        if( $(this).data('action') == 'proceed' ) {
            app_confirmation.byPass = true;

            if( $(app_confirmation.event.target).data('confirmation-with-remarks') ){
                if( $(app_confirmation.event.target).data('confirmation-with-remarks-required') && $('#confirmationModal').find('[name=remarks]').val().trim() == '')
                {
                    app_confirmation.reset();
                    alert($(app_confirmation.event.target).data('confirmation-with-remarks-required-message') ? $(app_confirmation.event.target).data('confirmation-with-remarks-required-message') : 'Remarks Required');
                    return;
                }
            }

            var remarksName;
            if( remarksName = $(app_confirmation.event.target).data('confirmation-with-remarks') )
            {
                var form;

                if($(app_confirmation.event.target).data('form-id')){
                    form = $('#'+$(app_confirmation.event.target).data('form-id'));
                }
                else if($(app_confirmation.event.target).closest('form').length === 1){
                    form = $(app_confirmation.event.target).closest('form');
                }

                if(form){
                    $('<input>').attr({
                        type: 'hidden',
                        name: remarksName,
                        value: $('#confirmationModal').find('[name=remarks]').val()
                    }).appendTo(form);
                }
            }

            if( $(app_confirmation.event.target).is('a') )
            {
                window.location.href = $(app_confirmation.event.target).attr('href');
            }
            else if( $(app_confirmation.event.target).is('form') )
            {
                $(app_confirmation.event.target).trigger('submit');
            }
            else {
                $(app_confirmation.event.target).trigger('click');

                if($(app_confirmation.event.target).data('form-id')){
                    $(form).trigger('submit');
                }
            }

            app_confirmation.reset();
        }
    }
);

$(document).on(
    'shown.bs.modal', '#confirmationModal', function() {
        var abortButton = $('#confirmationModal [data-action=abort]');
        abortButton.focus();

        var confirmationButton = $('#confirmationModal [data-action=proceed]');
        confirmationButton.prop('disabled', true);
        confirmationVue.startCountdown(confirmationVue.iteration+1);
    }
);

var confirmationVue = new Vue({
    el: '#confirmationModal',
    data: {
        countdownMessage: '',
        countdown: [],
        updateInterval: 1000,
        iteration: 0,
        confirmationMessageClass: ''
    },
    methods:{
        startCountdown: function(iteration){
            this.iteration = iteration;
            this.countdown[iteration] = app_confirmation.timeoutDuration;
            this.updateCountdown(iteration);
            var self = this;
            self.confirmationMessageClass = '';
            setTimeout(function(){self.confirmationMessageClass = 'text-danger';}, 500);
        },
        updateMessage: function(iteration){
            var countdownInSeconds = this.getCountdown(iteration)/1000;
            this.countdownMessage = '(' + countdownInSeconds + ')';
        },
        getCountdown: function(iteration){
            return this.countdown[iteration];
        },
        updateCountdown:function(iteration){
            if(iteration != this.iteration) return;
            var self = this;
            self.updateMessage(iteration);
            if(this.countdown[iteration] > 0) {
                setTimeout(function(){
                    self.countdown[iteration] -= 1000;
                    self.updateCountdown(iteration);
                }, this.updateInterval);
            }
            if(this.countdown[iteration] <= 0) {
                this.countdownMessage = '';
                var confirmationButton = $('#confirmationModal [data-action=proceed]');
                confirmationButton.prop('disabled', false);
                this.confirmationMessageClass = '';
            }
        }
    }

});