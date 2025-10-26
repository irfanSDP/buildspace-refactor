<div id="showEmailContainer">
    <div class="row">
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false" data-widget-sortable="false">
                <header>
                    <span class="widget-icon"> <i class="fa fa-paper-plane"></i> </span>
                    <h2 class="hidden-mobile">{{{ \PCK\Helpers\StringOperations::shorten($email->subject, 50) }}}</h2>
                </header>
                <div>
                    <div class="widget-body smart-form">
                        <section>
                            <strong>{{ trans('messaging.subject') }}:</strong><br>
                            {{{ $email->subject }}}
                        </section>

                        <section>
                            <strong>{{ trans('email.to') }}:</strong><br>
                            {{ implode(', ', $emailRecipientNames) }}
                            @if($displayShowUsersButton)
                            {{ trans('general.and') . ' ' . $remainderUsersCount . ' ' . trans('general.others') }}
                            @endif
                        </section>

                        <section>
                            <strong>{{ trans('email.dateIssued') }}:</strong><br>
                            <span class="dateSubmitted">{{ $email->created_at }}</span>
                        </section>

                        <section>
                            <strong>{{ trans('messaging.author') }}:</strong><br>
                            <span class="color-blue">
                                {{ $email->createdBy->name }}
                            </span>
                        </section>

                        <section>
                            <strong>{{ trans('messaging.message') }}:</strong><br>
                            {{ nl2br($email->message) }}
                        </section>

                        @if (!$email->attachments->isEmpty())
                            <p>
                                <strong>{{ trans('forms.attachments') }}:</strong><br>
                                @include('file_uploads.partials.uploaded_file_show_only', ['files' => $email->attachments])
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </article>
    </div>
</div>