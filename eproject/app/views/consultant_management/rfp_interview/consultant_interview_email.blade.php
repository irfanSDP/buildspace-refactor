<div style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
{{ trans('email.toRecipientOfCompany', ['recipientName' => $recipientName, 'companyName' => $companyName], 'messages', $recipientLocale) }}
</div>

<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
    {{ $companyName }} has been invited for a Consultant Interview. Details of the RFP are as below.
</p>

<table>
    <tr>
        <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;text-align: left;vertical-align:top;"><strong>RFP Title :</strong></td>
        <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;text-align: left;">{{{ $vendorCategoryName }}}</td>
    </tr>
    <tr>
        <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;text-align: left;vertical-align:top;"><strong>Interview Title :</strong></td>
        <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;text-align: left;">{{{ $rfpInterview['title'] }}}</td>
    </tr>
    @if($rfpInterview['details'])
    <tr>
        <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;text-align: left;vertical-align:top;"><strong>{{ trans('general.details') }} :</strong></td>
        <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;text-align: left;">{{ $rfpInterview['details'] }}</td>
    </tr>
    @endif
    <tr>
        <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;text-align: left;vertical-align:top;"><strong>{{ trans('general.time') }} :</strong></td>
        <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;text-align: left;">{{{ $interviewTimestamp }}}</td>
    </tr>
    @if($rfpInterviewConsultant['remarks'])
    <tr>
        <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;text-align: left;vertical-align:top;"><strong>{{ trans('general.remarks') }} :</strong></td>
        <td style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;text-align: left;vertical-align:top;">{{ $rfpInterviewConsultant['remarks'] }}</td>
    </tr>
    @endif
</table>

<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
    <strong>Please click the link below to respond to the Consultant Interview invitation</strong>
</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
    {{ HTML::link($link, 'Reply Consultant Interview Invitation') }}
</p>

@include('notifications.email.partials.company_logo')

@include('notifications.email.partials.disclaimer')