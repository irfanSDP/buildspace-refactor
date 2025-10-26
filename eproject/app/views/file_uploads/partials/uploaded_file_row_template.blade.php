<tr class="template-download">
	<td class="text-center">
		<span class="preview">
			<a data-category="link" data-gallery><img data-category="img"></a>
		</span>
	</td>
	<td>
		<p class="name">
			<span data-category="name"></span>
			<a data-category="link"></a>
			<input class="upload-field-ids" type="hidden" name="uploaded_files[]" value="">
		</p>
	</td>
	<td class="text-center">
		<span class="size" data-category="size">
		</span>
	</td>
	<td class="text-center">
		<button class="btn btn-xs btn-danger delete" data-action="delete">
			<i class="glyphicon glyphicon-trash"></i>
			<span>{{ trans('files.delete') }}</span>
		</button>
	</td>
    <td class="text-center">
        <p data-category="created-at">
        </p>
    </td>
</tr>