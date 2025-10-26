/*
Usage
-------
Trigger element:
Add
    [data-action=expandToggle]
    [data-target=<someId>]

Toggleable element:
Add
    [data-type=expandable]
    [data-id=<someId>]
    [data-default=hide] Optional
*/
var app_expandable = {
    toggleExpand: function(content, callback){
        //open up the content needed - toggle the slide- if visible, slide up, if not slidedown.
        content.slideToggle(200, callback);
    }
};

// Todo: change data-type to data-expandable
$(document).on('click', '[data-action=expandToggle]', function(){
    var content = $('[data-type=expandable][data-id=' + $(this).data('target') + ']');

    app_expandable.toggleExpand(content);
});

$('[data-type=expandable][data-default=hide]').each(function(){
    app_expandable.toggleExpand($(this));
});