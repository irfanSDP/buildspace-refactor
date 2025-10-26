<?php $isLocked = isset($isLocked) ? $isLocked : false; ?>
<?php $classes = isset($classes) ? $classes : false; ?>
<?php $styles = isset($styles) ? $styles : false; ?>
<?php $allTags = isset($allTags) ? $allTags : []; ?>
<select id="{{ $id }}" name="tags" class="form-control {{ $classes }}" style="{{ $styles }}" multiple="multiple" @if($isLocked) disabled @endif>
    @foreach($allTags as $tag)
        @if(isset($objectTagIds) && in_array($tag->id, $objectTagIds))
            <option name="{{ $tag->id }}" selected="selected">{{{ $tag->name }}}</option>
        @else
            <option name="{{ $tag->id }}">{{{ $tag->name }}}</option>
        @endif
    @endforeach
</select>