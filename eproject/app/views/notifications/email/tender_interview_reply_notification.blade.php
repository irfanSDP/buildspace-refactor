<p>To: {{{ $recipientName }}}</p>
<p>of <span style="color: blue">{{{ $companyName }}}</span></p>

<p>
    Project: <strong>{{{ $projectTitle }}}</strong>
</p>

<hr/>

<p>
    Notice of Tender Interview Reply
</p>

<hr/>

<p>
    <span style="color: blue;">{{{ $tendererCompany }}}</span><br/>
    has given their reply for the tender interview<br/>
    (<span style="color: green;">{{{ $repliedAt }}}</span>):
</p>

<table>
    <tr>
        <th style="text-align: left">Status</th>
        <td style="text-align: left">: {{{ $status }}}</td>
    </tr>
</table>

@include('notifications.email.partials.company_logo')

@include('notifications.email.partials.disclaimer')