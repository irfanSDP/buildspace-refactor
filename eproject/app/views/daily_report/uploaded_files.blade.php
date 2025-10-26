 @if(count($uploadedItems) > 0)
    <div class="table-responsive" style="overflow:hidden;">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ trans('siteManagement.filename') }}</th>
                    
                    <th style="text-align: center">{{"Action"}}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($uploadedItems as $uploadedItem)
                <tr>
                    <td style="width:50%">
                        <a href="{{{$uploadedItem->getDownloadUrlAttribute()}}}" download>{{{$uploadedItem->filename}}}</a>
                    </td>
                    <td style="width:50%" align="center">
                        <a href="{{ route('daily-report.attachements.delete',[$project->id,$uploadedItem->id, $record->id])}}"
                            class="btn btn-xs btn-danger"
                            data-method="delete"
                            data-csrf_token="{{ csrf_token() }}">
                            <i class="fa fa-trash"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    {{ trans('siteManagement.no_file_uploaded') }}
@endif


