<?php $formId            = $formId ?? 'verifierForm'; ?>
<?php $approveClass      = $approveClass ?? 'btn btn-sm btn-success'; ?>
<?php $rejectClass       = $rejectClass ?? 'btn btn-sm btn-danger'; ?>
<?php $showApproveButton = $showApproveButton ?? true; ?>
<?php $showRejectButton  = $showRejectButton ?? true; ?>
{{ Form::open(array('route' => array('verify', $object->id), 'id' => $formId, 'style'=>'display:inline;', 'class' => 'smart-form')) }}
    <input type="text" name="class" value="{{{ get_class($object) }}}" hidden/>
    @if($showApproveButton)
    {{ Form::button('<i class="fa fa-check"></i> '.trans('forms.approve'), ['type' => 'submit', 'name' => 'approve', 'class'=> $approveClass] ) }}
    @endif
    @if($showRejectButton)
    {{ Form::button('<i class="fa fa-times"></i> '.trans('forms.reject'), ['type' => 'submit', 'name' => 'reject', 'class'=> $rejectClass] ) }}
    @endif
{{ Form::close() }}