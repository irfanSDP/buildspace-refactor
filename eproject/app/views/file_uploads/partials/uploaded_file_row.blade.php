<tr class="template-download">
	<td class="text-center">
		<span class="preview">
			<a href="{{{ $file->download_url }}}" title="{{{ $file->filename }}}" download="{{{ $file->filename }}}" data-gallery><img src="{{{ $file->generateThumbnailURL() }}}"></a>
		</span>
	</td>
	<td>
		<p class="name">
			<span>{{{ $file->filename }}}</span>

			<input class="upload-field-ids" type="hidden" name="uploaded_files[]" value="{{{ $file->id }}}">
		</p>
	</td>
	<td class="text-center">
		<span class="size">
			{{{ PCK\Base\Helpers::formatBytes($file->size) }}}
		</span>
	</td>
	<td class="text-center">
		<button class="btn btn-xs btn-danger delete" data-action="delete" data-route='{{{ (isset($projectId) ? $file->generateDeleteURL($projectId) : $file->generateGeneralDeleteURL() ) }}}'>
			<i class="glyphicon glyphicon-trash"></i>
			<span>{{ trans('files.delete') }}</span>
		</button>
	</td>
    <td class="text-center">
        <p>
			@if(isset($projectId))
            	{{{ \PCK\Projects\Project::find($projectId)->getProjectTimeZoneTime($file->created_at) }}}
			@else
				{{{ $file->created_at }}}
			@endif
        </p>
    </td>
</tr>