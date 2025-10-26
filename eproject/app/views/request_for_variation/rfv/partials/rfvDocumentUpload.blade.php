@if ($requestForVariation)
    <div class="row">
        <section class="col col-xs-12 col-md-12 col-lg-12">
            <table class="table table-bordered table-condensed table-striped table-hover smallFont" id="rfvUploadedFilesTable">
                <thead>
                    <tr>
                        <th>{{ trans('requestForVariation.filename') }}</th>
                        <th>{{ trans('forms.uploader') }}</th>
                        <th style="width:24px;">{{ trans('forms.actions') }}</th>
                    </tr>
                </thead>
            </table>
        </section>
    </div>
@endif
