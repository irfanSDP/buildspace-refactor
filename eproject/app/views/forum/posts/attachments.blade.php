@if(!$post->getAttachmentDetails()->isEmpty())
    <div style="margin-top:8px;">
        <div class="well {{{ isset($unreadPosts) ? ($unreadPosts->find($post->id) ? 'forum-unread' : '') : '' }}}">
            <table class="table table-hover" style="font-size: smaller;">
                <thead>
                    <tr>
                        <th>
                            {{ trans('general.attachments') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                @foreach($post->getAttachmentDetails() as $file)
                    <tr>
                        <td>
                            <a href="{{{ $file->download_url }}}" title="{{{ $file->filename }}}" download="{{{ $file->filename }}}">{{{ $file->filename }}}</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif