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
                            <?php $emailRecipientNames = []; ?>
                            @foreach($email->recipients as $recipient)
                                <?php array_push($emailRecipientNames, $recipient->user->name); ?>
                            @endforeach

                            <strong>{{ trans('email.to') }}:</strong><br>
                            {{ implode(', ', $emailRecipientNames) }}
                        </section>

                        <section>
                            <strong>{{ trans('email.dateIssued') }}:</strong><br>
                            <span class="dateSubmitted">{{{ $email->project->getProjectTimeZoneTime($email->created_at) }}}</span>
                        </section>

                        <section>
                            <strong>{{ trans('messaging.author') }}:</strong><br>
                            <span class="color-blue">
                                {{{ $email->createdBy->name }}}
                                ({{{ $email->createdBy->getProjectCompanyName($email->project, $email->created_at) }}})
                            </span>
                        </section>

                        <section>
                            <strong>{{ trans('messaging.message') }}:</strong><br>
                            {{ nl2br($email->message) }}
                        </section>

                        @if (!$email->attachments->isEmpty())
                            <p>
                                <strong>{{ trans('forms.attachments') }}:</strong><br>
                                @include('file_uploads.partials.uploaded_file_show_only', ['files' => $email->attachments, 'projectId' => $email->project_id])
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </article>
    </div>
</div>