<?php $modalId         = $modalId ?? 'verifierModal'; ?>
<?php $showDesignation = isset($showDesignation) ? $showDesignation : false ?>
@if(count($verifiers) < 1)
    <div class="well padded txt-color-orangeDark">
        {{{ trans('verifiers.noVerifiers') }}}
    </div>
@else
    <div data-id="selectVerifiers" data-modal="{{{ $modalId }}}">
        <fieldset class="bg-transparent">
            <div class="verifiers">
                <label class="label">{{{ trans('verifiers.selectVerifiers') }}}:</label>
                <div class="add-ons">
                </div>
                <div class="row verifier">
                    <section class="form-group col col-md-10 col-lg-10">
                        <label class="select">
                            <select class="input verifier_select" name="verifiers[]" data-type="original">
                                <option value="">{{ trans('forms.none') }}</option>
                                @foreach($verifiers as $verifier)
                                    <?php $verifierName = $showDesignation ? $verifier->name_with_designation : $verifier->name; ?>
                                    <option value="{{{ $verifier->id }}}">
                                        {{{ $verifierName }}}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </section>
                    <section class="col col-2 add-entry-div">
                        <label class="input">
                            <button type="button" data-action="add-entry" class="btn btn-success rounded add-entry"><i class="fa fa-plus"></i></button>
                        </label>
                    </section>
                </div>
                <div class="template">
                    <div class="row verifier" style="display: none;">
                        <section class="form-group col col-md-10 col-lg-10">
                            <label class="select">
                                <select class="input verifier_select" name="verifiers[]" data-type="select-input">
                                    <option value="">{{ trans('forms.none') }}</option>
                                    @foreach($verifiers as $verifier)
                                        <?php $verifierName = $showDesignation ? $verifier->name_with_designation : $verifier->name; ?>
                                        <option value="{{{ $verifier->id }}}">
                                            {{{ $verifierName }}}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        </section>
                        <section class="col col-2">
                            <label class="input remove-entry-div">
                                <button type="button" class="btn btn-danger rounded" data-action="remove-entry"><i class="fa fa-minus"></i></button>
                            </label>
                        </section>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
@endif

<script>
    var {{{ $modalId }}}_object = {
        addVerifierEntry: function(verifierId)
        {
            var entryValue = verifierId ? verifierId : this.getVerifierInput();

            var newEntry = $('[data-id=selectVerifiers][data-modal={{{ $modalId }}}] .template').children('.verifier').clone();
            newEntry.appendTo('[data-id=selectVerifiers][data-modal={{{ $modalId }}}] .add-ons');
            newEntry.find('[name="verifiers[]"]').val(entryValue);

            newEntry.show();

            this.refreshVerifierInput();
        },
        getVerifierInput: function()
        {
            return $('[data-id=selectVerifiers][data-modal={{{ $modalId }}}] .input[name="verifiers[]"][data-type=original]').val();
        },
        refreshVerifierInput: function()
        {
            var input = $('[data-id=selectVerifiers][data-modal={{{ $modalId }}}] .input[name="verifiers[]"][data-type=original' );
            input.val($('[data-id=selectVerifiers][data-modal={{{ $modalId }}}] .input[name="verifiers[]"] option:first').val());
        },
        removeVerifierEntry: function(button){
            $(button).parents('.verifier').remove();
        }

    };

    $(document).on('click', '[data-id=selectVerifiers][data-modal={{{ $modalId }}}] [data-action=add-entry]', function(){
        {{{ $modalId }}}_object.addVerifierEntry();
    });

    $(document).on('click', '[data-id=selectVerifiers][data-modal={{{ $modalId }}}] [data-action=remove-entry]', function(){
        {{{ $modalId }}}_object.removeVerifierEntry(this);
    });

    // Set selected verifiers.
    @if(isset($selectedVerifiers))
        @foreach($selectedVerifiers as $selectedVerifier)
            {{{ $modalId }}}_object.addVerifierEntry('{{{ $selectedVerifier->id }}}');
        @endforeach
    @endif
</script>