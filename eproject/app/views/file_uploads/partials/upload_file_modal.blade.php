<?php $id = $id ?? 'fileupload'; ?>
<?php $tableId = $tableId ?? 'uploadFileTable'; ?>
<div id="{{{ $id }}}">
    <!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
    <div class="row fileupload-buttonbar" style="margin: 0;">
        <div>
            <!-- The fileinput-button span is used to style the file input field as button -->
			<span class="btn btn-sm btn-success fileinput-button">
				<i class="glyphicon glyphicon-plus"></i>
				<span>{{ trans('files.addFiles') }}</span>
				<input type="file" name="file" multiple>
			</span>
            <button type="submit" class="btn btn-sm btn-primary start">
                <i class="glyphicon glyphicon-upload"></i>
                <span>{{ trans('files.startUpload') }}</span>
            </button>
            <button type="reset" class="btn btn-sm btn-warning cancel">
                <i class="glyphicon glyphicon-ban-circle"></i>
                <span>{{ trans('files.cancelUpload') }}</span>
            </button>

            <!-- The global file processing state -->
            <span class="fileupload-process"></span>
        </div>
    </div>
    <!-- The global progress state -->
    <div class="fileupload-progress fade">
        <!-- The global progress bar -->
        <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
            <div class="progress-bar progress-bar-success" style="width:0;"></div>
        </div>
        <!-- The extended global progress state -->
        <div class="progress-extended">&nbsp;</div>
    </div>
    <!-- The table listing the files available for upload/download -->
    <div class="table-responsive">
        <table role="presentation" class="table  table-bordered table-hover" id="{{ $tableId }}">
            <thead>
            <tr>
                <th class="text-center" style="width:32px;">{{ trans('documentManagementFolders.preview') }}</th>
                <th style="width:auto;min-width:180px;">{{ trans('documentManagementFolders.filename') }}</th>
                <th class="text-center" style="width:64px;">{{ trans('documentManagementFolders.size') }}</th>
                <th class="text-center" style="width:180px;">{{ trans('documentManagementFolders.actions') }}</th>
                <th class="text-center" style="width:120px;">{{ trans('documentManagementFolders.uploaded') }}</th>
            </tr>
            </thead>
            <tbody class="files" style="font-size:11px!important;">
            @if (isset($uploadedFiles))
                @foreach ( $uploadedFiles as $uploadedFile )
                    @if (isset($project))
                        @include('file_uploads.partials.uploaded_file_row', ['file' => $uploadedFile, 'projectId' => $project->id])
                    @else
                        @include('file_uploads.partials.uploaded_file_row', ['file' => $uploadedFile])
                    @endif
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
</div>

<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
    {% for (var i=0, file; file=o.files[i]; i++) { %}
        <tr class="template-upload fade">
            <td class="text-center">
                <span class="tpreview"></span>
            </td>
            <td>
                <p class="name">{%=file.name%}</p>
                <strong class="error text-danger"></strong>
            </td>
            <td class="text-center">
                <p class="size">{{ trans('files.processing') }}...</p>
                <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
            </td>
            <td class="text-center">
                {% if (!i && !o.options.autoUpload) { %}
                    <button class="btn btn-xs btn-primary start" disabled>
                        <i class="glyphicon glyphicon-upload"></i>
                        <span>{{ trans('files.start') }}</span>
                    </button>
                {% } %}
                {% if (!i) { %}
                    <button class="btn btn-xs btn-warning cancel">
                        <i class="glyphicon glyphicon-ban-circle"></i>
                        <span>{{ trans('files.cancel') }}</span>
                    </button>
                {% } %}
            </td>
            <td class="text-center">
                -
            </td>
        </tr>
    {% } %}
</script>

<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
    {% for (var i=0, file; file=o.files[i]; i++) { %}
        <tr class="template-download fade">
            <td class="text-center">
                <span class="text-center preview">
                    {% if (file.thumbnailUrl) { %}
                        <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}"></a>
                    {% } %}
                </span>
            </td>
            <td>
                <p class="name">
                    {% if (file.url) { %}
                        <a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
                    {% } else { %}
                        <span>{%=file.name%}</span>
                    {% } %}
                    {% if (file.fileID) { %}
                        <input class="upload-field-ids" type="hidden" name="uploaded_files[]" value="{%=file.fileID%}">
                    {% } %}
                </p>
                {% if (file.error) { %}
                    <div><span class="label label-danger">{{ trans('files.error') }}</span> {%=file.error%}</div>
                {% } %}
            </td>
            <td class="text-center">
                <span class="size">{%=o.formatFileSize(file.size)%}</span>
            </td>
            <td class="text-center">
                {% if (file.deleteUrl) { %}
                    <button class="btn btn-xs btn-danger delete" onclick="deleteUpload('{%=file.deleteUrl%}')">
                        <i class="glyphicon glyphicon-trash"></i>
                        <span>{{ trans('files.delete') }}</span>
                    </button>
                {% } else { %}
                    <button class="btn btn-xs btn-warning cancel">
                        <i class="glyphicon glyphicon-ban-circle"></i>
                        <span>{{ trans('files.cancel') }}</span>
                    </button>
                {% } %}
            </td>
            <td class="text-center">
                {%=file.created_at%}
            </td>
        </tr>
    {% } %}
</script>
<script>
    $(document).ready(function() {
        pageSetUp();

        var token = $('meta[name=_token]').attr("content");

        // Initialize the jQuery File Upload widget:
        $('#{{{ $id }}}').fileupload({
            url: '{{ (isset($project)) ? route("moduleUploads.upload", array($project->id)) : route("generalUploads.upload")}}',
            formData: {_token :token},
            maxFileSize: '{{{ Config::get('uploader.max_file_size') }}}',
            // Enable image resizing, except for Android and Opera,
            // which actually support image resizing, but fail to
            // send Blob objects via XHR requests:
            disableImageResize: /Android(?!.*Chrome)|Opera/
                    .test(window.navigator.userAgent)
        });
    });

    function deleteUpload(url){
        $.post(url, {
            '_token': $('meta[name=_token]').attr("content")
        })
        .done(function(data) {
            // code for result
        });
    }
    $("#{{{ $id }}} button[data-action=delete]").on('click', function(){
        deleteUpload($(this).data('route'));
    });
</script>
