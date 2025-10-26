 @if(count($uploadedItems) > 0)
    <div class="table-responsive" style="overflow:hidden;">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>{{ trans('siteManagement.filename') }}</th>
                    <th style="text-align: center">{{ trans('siteManagement.uploaded') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($uploadedItems as $uploadedItem)
                <tr>
                    <td style="width:50%">
                        <a href="{{{$uploadedItem->getDownloadUrlAttribute()}}}" download>{{{$uploadedItem->filename}}}</a>
                    </td>
                    <td style="width:50%" align="center">
                       <img id="uploaded-item" src="{{{$uploadedItem->getDownloadUrlAttribute()}}}" style="width:100px;height:100px;border:3px solid black;border-radius: 8px;"/>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@else
    {{ trans('siteManagement.no_photo_uploaded') }}
@endif
