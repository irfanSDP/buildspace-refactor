<tr>
    <td>
        <a href="#"
           class="update-button"
           data-id="{{{ $subsidiary->id }}}"
           data-name="{{{ $subsidiary->name }}}"
           data-identifier="{{{ $subsidiary->identifier }}}"
           data-parent-id="{{{ $subsidiary->parent_id }}}">
            {{{ $subsidiary->fullName }}}
        </a>
    </td>
    <td>
        <div class="d-flex">
            <a href="#"
            style="margin-right:2px;"
            class="update-button btn btn-xs btn-default"
            data-id="{{{ $subsidiary->id }}}"
            data-name="{{{ $subsidiary->name }}}"
            data-identifier="{{{ $subsidiary->identifier }}}"
            data-parent-id="{{{ $subsidiary->parent_id }}}">
                <i class="fa fa-pencil-alt"></i>
            </a>
            <a href="{{{ route('subsidiaries.delete', array($subsidiary->id)) }}}"
            class="pull-right btn btn-xs btn-danger delete-button"
            data-id="{{{ $subsidiary->id }}}"
            data-method="delete"
            data-csrf_token="{{{ csrf_token() }}}">
                <i class="fa fa-trash"></i>
            </a>
        </div>
        
    </td>
    <td class="text-center">{{{ $subsidiary->identifier }}}</td>
</tr>
@foreach($subsidiary->children as $child)
    @include('subsidiaries.partials.tableRow', array('subsidiary' => $child, 'level' => ($level+1)))
@endforeach