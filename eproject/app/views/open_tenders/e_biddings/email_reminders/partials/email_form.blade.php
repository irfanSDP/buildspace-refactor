<div class="smart-form">
    <fieldset class="padding-top-0">
        <section>
            <h3>{{ trans('eBiddingReminder.previewReminder') }}</h3>
        </section>
        <section>
            <label class="label">{{ trans('messaging.subject') }}<span class="required">*</span>:</label>
            <label class="input">
                {{ Form::text('subject', ! empty($emailReminder->subject) ? $emailReminder->subject : trans('eBiddingReminder.subjectPreview')) }}
            </label>
            <em class="invalid" id="email_reminder-input-subject" style="display:none;font-size:11px;color:#D56161;">&nbsp;</em>
        </section>
        <section>
            <label class="label">{{ trans('messaging.message') }}<span class="required">*</span>:</label>
            <label class="textarea">
                {{ Form::textarea('message', ! empty($emailReminder->message) ? $emailReminder->message : trans('eBiddingReminder.messagePreview'), array('rows' => 8)) }}
            </label>
            <em class="invalid" id="email_reminder-input-message" style="display:none;font-size:11px;color:#D56161;">&nbsp;</em>
        </section>
    </fieldset>

    <fieldset class="padding-bottom-0">
        <section>
            <h3>{{ trans('eBiddingReminder.biddingReminder') }}</h3>
        </section>
        <section>
            <label class="label">{{ trans('messaging.subject') }}<span class="required">*</span>:</label>
            <label class="input">
                {{ Form::text('subject2', ! empty($emailReminder->subject2) ? $emailReminder->subject2 : trans('eBiddingReminder.subjectBidding')) }}
            </label>
            <em class="invalid" id="email_reminder-input-subject2" style="display:none;font-size:11px;color:#D56161;">&nbsp;</em>
        </section>
        <section>
            <label class="label">{{ trans('messaging.message') }}<span class="required">*</span>:</label>
            <label class="textarea">
                {{ Form::textarea('message2', ! empty($emailReminder->message2) ? $emailReminder->message2 : trans('eBiddingReminder.messageBidding'), array('rows' => 8)) }}
            </label>
            <em class="invalid" id="email_reminder-input-message2" style="display:none;font-size:11px;color:#D56161;">&nbsp;</em>
        </section>
    </fieldset>
</div>