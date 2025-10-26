<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">Dear {{{ $recipientName }}}</p>

<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
    Details of the RFP are as below.
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
</table>

<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
    Email has been sent to Consultant(s) to respond to the Consultant Interview invitation
</p>
<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
    {{ HTML::link($interviewLink, 'Consultant Interview Details') }}
</p>

@include('notifications.email.partials.company_logo')

@include('notifications.email.partials.disclaimer')