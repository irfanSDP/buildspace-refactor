<!DOCTYPE html>
<html lang="en-us">
<title>BuildSpace eProject</title>
<meta name="viewport" http-equiv="Content-Type" content="text/html; charset=UTF-8; width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<style type="text/css">
	table, th, td {
	    border: 1px solid black;
	    border-collapse: collapse;
	    padding:1%
	    width:100%;
    }

    tr.center{
    	text-align: center;
    }

    .left > div {
    	float:left;
    }

    h3 {
    	text-decoration: underline;
    }
}
</style>
<table style="width:100%">
    <thead>
        <tr>
        	<th>{{{ trans('siteManagementDefect.project') }}}&nbsp;&#58;</th>
        	<th>{{{ trans('siteManagementDefect.mcar-number') }}}&nbsp;&#58;</th>
        	<th>{{{ trans('siteManagementDefect.date-submitted') }}}&nbsp;&#58;</th>
        </tr>
    </thead>
    <tbody>
    	<tr class="center">
    		<td>{{{$MCARRecord->project->title}}}</td>
    		<td>{{{$MCARRecord->mcar_number}}}</td>
    		<td>{{{$MCARRecord->project->getProjectTimeZoneTime($MCARRecord->created_at)}}}</td>
    	</tr>
    </tbody>
</table>
<br><br>
<table style="width:100%">
    <thead>
        <tr>
        	<th>1.&nbsp;{{{ trans('siteManagementDefect.work-description') }}}</th>
        	<th>{{{ trans('siteManagementDefect.remark') }}}</th>
        </tr>
    </thead>
    <tbody>
    	<tr class="center">
    		<td>{{{$MCARRecord->work_description}}}</td>
    		<td>{{{$MCARRecord->remark}}}</td>
    	</tr>
    </tbody>
</table>
<br>
<p>{{{ trans('siteManagementDefect.prepared-site-representative') }}}&#58;</p>
<br>
<table style="width:100%">
    <tbody>
    	<tr>
    		<td colspan="3">
	    		<h3>2.&nbsp;{{{ trans('siteManagementDefect.corrective-action') }}}</h3> 
				<?php
					$satisfactory = PCK\SiteManagement\SiteManagementMCARFormResponse::getStatusText($MCARFormResponse->satisfactory);
				 	$applicable = PCK\SiteManagement\SiteManagementMCARFormResponse::getStatusText($MCARFormResponse->applicable);
				?>
				<section>
			        <label class="label">2.1&nbsp;&nbsp;{{{ trans('siteManagementDefect.cause') }}}&#58;</label>
			        <label class="input">
			             {{{$MCARFormResponse->cause}}}
			        </label>
			    </section>
			        <br>
			    <section>
			        <label class="label">2.2&nbsp;&nbsp;{{{ trans('siteManagementDefect.action') }}}&#58;</label>
			        <label class="input">
			            {{{$MCARFormResponse->action}}}
			        </label>
			    </section>
			        <br>
			    <section>
			        <label class="label">{{{ trans('siteManagementDefect.applicable') }}}&#58;</label>
			        <label class="input">
			            {{{$applicable}}}&nbsp;&nbsp;(If Yes, kindly answer section below)
			        </label>
			    </section>
			        <br>
			    <section>
			        <label class="label">2.3&nbsp;&nbsp;{{{ trans('siteManagementDefect.corrective') }}}&#58;</label>
			        <label class="input">
			            &nbsp;{{{$MCARFormResponse->corrective}}}
			        </label>
			    </section>
			</td> 
    	</tr>
    	<tr>
    		<td style="width: 25%">{{{ trans('siteManagementDefect.responsible-person') }}}&nbsp;&#58;&nbsp;<br><br>
    			{{{$MCARFormResponse->user->name}}}
    		</td>
			<td style="width: 50%">{{{ trans('siteManagementDefect.sign') }}}&nbsp;&#58;&nbsp;<br><br> Date&nbsp;&#58;&nbsp;</td>
			<td style="width: 25%">{{{ trans('siteManagementDefect.commit-date') }}}&nbsp;&#58;&nbsp;<br><br>
				{{{$MCARRecord->project->getProjectTimeZoneTime($MCARFormResponse->commitment_date)}}}
			</td>
    	</tr>
    	<tr>
    		<td colspan="3"><strong>{{{ trans('siteManagementDefect.reminder') }}}</strong></td>
    	</tr>
    </tbody>
</table>
<br><br>
<table style="width:100%">
    <tbody>
    	<tr>
    		<td colspan="2">
	    		<h3>3.&nbsp;{{{ trans('siteManagementDefect.effectiveness-verify') }}}</h3>  

	    		<div class="left">
	    			<div style="width:70%">
	                    {{{ trans('siteManagementDefect.received-by') }}}&#58;
	                    {{{$MCARFormResponse->verifier->name}}}
	                </div>
	                <div style="width:30%">
	                    {{{ trans('siteManagementDefect.date') }}}&#58;
	                    {{{$MCARRecord->project->getProjectTimeZoneTime($MCARFormResponse->verified_at)}}}
	                </div>
	            </div>
	            <br><br>
			</td> 
    	</tr>
    	<tr>
			<td>{{{ trans('siteManagementDefect.satisfactory') }}}&#58;&nbsp;{{{$satisfactory}}}</td>
			<td>
				<table width="100%" style="border:none;">
	        		<tr>
	        			<td style="border:none;">{{{ trans('siteManagementDefect.comment-site') }}}&#58;&nbsp;{{{$MCARFormResponse->comment}}}
	        			</td>
	        		</tr>
	        		<tr>
	            		<td style="border:none;">{{{ trans('siteManagementDefect.reinspection-date') }}}
				            &#58;&nbsp;{{{$MCARRecord->project->getProjectTimeZoneTime($MCARFormResponse->reinspection_date)}}}
			            </td>
			        </tr>
	    		</table>
			</td>
		</tr>
		<tr>
			<td>{{{ trans('siteManagementDefect.verification-personnel') }}}&nbsp;&#58;&nbsp;</td>
			<td>{{{ trans('siteManagementDefect.sign') }}}&nbsp;&#58;&nbsp;<br>{{{ trans('siteManagementDefect.date') }}}&nbsp;&#58;&nbsp;</td>
		</tr>
    </tbody>
</table>

</html>