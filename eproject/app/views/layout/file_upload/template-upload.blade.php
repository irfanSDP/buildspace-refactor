<!-- The template to display files available for upload -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td>
            <span class="preview"></span>
        </td>
        <td>
            <p class="name">{%=file.name%}</p>
            <strong class="error text-danger"></strong>
        </td>
        <td>
            <p class="size">{{ trans('files.processing') }}...</p>
            <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
        </td>
        <td>
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
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        <td>
            <span class="preview">
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
                    <input class="upload-field-ids" type="hidden" name="fileid[]" value="{%=file.fileID%}">
                {% } %}
            </p>
            {% if (file.error) { %}
                <div><span class="label label-danger">{{ trans('files.error') }}</span> {%=file.error%}</div>
            {% } %}
        </td>
        <td>
            <span class="size">{%=o.formatFileSize(file.size)%}</span>
        </td>
        <td>
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
    </tr>
{% } %}
</script>