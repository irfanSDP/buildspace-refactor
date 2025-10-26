<div class="modal fade" id="progressBarModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="progressBarLabel" aria-hidden="true" xmlns="http://www.w3.org/1999/html">
    <div class="modal-dialog modal-dialog-centered modal-transparent" role="document">
        <div class="modal-content">

            <div class="modal-header bg-grey-e">
                <h6 class="modal-title text-white">
                    {{{ trans('forms.processing') }}}
                </h6>
            </div>

            <div class="modal-body">
                <div class="bar-holder">
                    <div class="progress progress-sm progress-striped active">
                        <div data-id="progressbar" class="progress-bar bg-color-green"  role="progressbar"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <strong class="text-white">
                    Please wait<span class="blink">...</span>
                </strong>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->