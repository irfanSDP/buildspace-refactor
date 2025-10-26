<?php $object = $object ?? null; ?>

@if ($object && $object->submitted_by)
    <div class="well" style="margin-top:12px;margin-bottom:12px;">
        <?php $user = PCK\Users\User::find($object->submitted_by); ?>
        <?php
            $updatedAt = $object->updated_at;
            if(isset($project)) $updatedAt = $project->getProjectTimeZoneTime($object->updated_at);
            $requested_at = Carbon\Carbon::parse($updatedAt)->format(\Config::get('dates.created_at'));
        ?>
        <strong>{{ trans('general.verificationRequestedBy') }} <span class="text-primary">{{{ $user->name }}}</span> {{ trans('general.at') }} <span class="text-danger">{{{ $requested_at }}}</span></strong>
    </div>
@endif