<script>
    $(document).ready(function() {

        function getSubsidiaryId()
        {
            var subsidiaryId;
            @if(isset($fixedSubsidiary))
                subsidiaryId = "{{{ $fixedSubsidiary->id }}}";
            @else
                subsidiaryId = $('#subsidiary_select').val();
            @endif

            return subsidiaryId;
        }

        function getWorkCategoryId()
        {
            return $('#work_category_select').val();
        }

        $('[data-on-change=generateReference]').on('keyup blur change', function(){
            vue.generateReference();
        });

        $(document).on(
            'keyup blur change', '[data-type=reference]', function()
            {
                vue.validateContractNumber(false);
            }
        );

        $('select#subsidiary_select').on('change', function(){
            if($(this).val() > 0) vue.generateRunningNumber();
        });

        var vue = new Vue({
            el: '#add-form',

            data: {
                reference      : '',
                runningNumber  : '{{{ $initRunningNumber }}}',
                referenceSuffix: '{{{ $initSuffix }}}',
                validate       : true
            },

            methods: {
                generateRunningNumber: function() {
                    $.ajax({
                        url    : "{{{ route('projects.generateRunningNumber') }}}",
                        method : 'POST',
                        data   : {
                            _token          : '{{{ csrf_token() }}}',
                            subsidiary_id   : getSubsidiaryId(),
                            reference_suffix: vue.referenceSuffix
                        },
                        success: function(data) {
                            vue.runningNumber = data;
                            $('[data-error=runningNumberExists]').hide();
                            vue.generateReference();
                        },
                        error  : function(jqXHR, textStatus, errorThrown) {
                            // error
                        }
                    });
                },
                validateRunningNumber: function(){
                    if( !vue.validate ) return false;

                    $('[data-error=runningNumberExists]').hide();
                    $('[data-valid=runningNumberValid]').hide();
                    $.ajax({
                        url    : "{{{ route('projects.runningNumber.check') }}}",
                        method : 'POST',
                        data   : {
                            _token   : '{{{ csrf_token() }}}',
                            subsidiary_id   : getSubsidiaryId(),
                            reference_suffix: vue.referenceSuffix,
                            running_number: vue.runningNumber
                        },
                        success: function(resp) {
                            if(!resp['available'])
                            {
                                $('[data-error=runningNumberExists]').show();
                            }
                            else
                            {
                                $('[data-valid=runningNumberValid]').show();
                            }
                        },
                        error  : function(jqXHR, textStatus, errorThrown) {
                            // error
                        }
                    });
                },
                validateContractNumber      : function(highlight) {
                    if( !vue.validate ) return false;

                    var referenceView       = $('label.input[data-for=reference]');
                    referenceView.addClass('has-warning');
                    $.ajax({
                        url    : "{{{ route('projects.contractNumber.check') }}}",
                        method : 'POST',
                        data   : {
                            _token   : '{{{ csrf_token() }}}',
                            reference: vue.reference
                        },
                        success: function(data) {
                            var referenceView       = $('label.input[data-for=reference]');
                            var classDefault        = 'state-success';
                            var classHighlight      = 'has-warning';
                            var classError          = 'state-error';
                            var referenceUsageLabel = $('#referenceUsageLabel');
                            referenceUsageLabel.hide();

                            if( !data[ 'available' ] )
                            {
                                referenceView.removeClass(classDefault);
                                referenceView.removeClass(classHighlight);
                                referenceView.addClass(classError);
                                referenceUsageLabel.show();
                            }
                            else {
                                var activeClass   = classDefault;
                                var inactiveClass = classHighlight;

                                if( highlight )
                                {
                                    activeClass   = classHighlight;
                                    inactiveClass = classDefault;
                                }

                                referenceView.removeClass(classError);
                                referenceView.removeClass(inactiveClass);
                                referenceView.addClass(activeClass);
                            }
                        },
                        error  : function(jqXHR, textStatus, errorThrown) {
                            // error
                        }
                    });
                },
                generateReference: function(){
                    $.ajax({
                        url    : "{{{ route('projects.generateContractNumber') }}}",
                        method : 'POST',
                        data   : {
                            _token   : '{{{ csrf_token() }}}',
                            subsidiary_id   : getSubsidiaryId(),
                            reference_suffix: vue.referenceSuffix,
                            running_number: vue.runningNumber,
                            work_category_id : getWorkCategoryId()
                        },
                        success: function(resp) {
                            vue.reference = resp['contract_number'];
                            vue.validateContractNumber(false);
                        },
                        error  : function(jqXHR, textStatus, errorThrown) {
                            // error
                        }
                    });
                }
            }
        });

        vue.generateReference();

    });
</script>