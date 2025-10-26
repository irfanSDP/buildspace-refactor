<div class="row">
    <section class="col col-xs-12 col-md-12 col-lg-12">
        <label class="label">{{ trans('tenders.verificationLogs') }} :</label>
        <ol style="margin: 0 0 0 18px;">
            @foreach ( $model->verifierLogs as $log )
                <li>{{ $log->present()->log_text_format() }}</li>
            @endforeach
        </ol>
    </section>
</div>