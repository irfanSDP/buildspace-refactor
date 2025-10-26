@if ( $files->isEmpty() )
    <p class="required">{{ trans('files.noFilesAvailable') }}</p>
@else
    <ol style="margin: 0 0 0 18px;">
        @foreach ( $files as $file )
            <?php $attachment = $file->file; ?>

            @if(isset($projectId))
            <li>{{ link_to_route('moduleUploads.download', $attachment->filename, [$projectId, $attachment->id]) }}</li>
            @else
            <li>{{ link_to_route('generalUploads.download', $attachment->filename, [$attachment->id]) }}</li>
            @endif
        @endforeach
    </ol>
@endif