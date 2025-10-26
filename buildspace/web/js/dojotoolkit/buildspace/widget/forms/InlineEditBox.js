define('buildspace/widget/forms/InlineEditBox',[
    'dojo/_base/declare',
    'dojo/_base/lang',
    "dijit/InlineEditBox",
    "dojo/dom-construct",
    "dijit/_Widget",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin"
], function(declare, lang, InlineEditBox, domConstruct, _Widget, _TemplatedMixin, _WidgetsInTemplateMixin){
    return declare("buildspace.widget.forms.InlineEditBox", InlineEditBox, {
        
        edit: function(){
            this.inherited(arguments);

            domConstruct.destroy(this.wrapperWidget.buttonContainer);
        }

    });
});