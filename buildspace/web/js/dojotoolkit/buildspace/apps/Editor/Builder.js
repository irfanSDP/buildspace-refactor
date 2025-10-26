define('buildspace/apps/Editor/Builder',[
    'dojo/_base/declare',
    "dijit/form/Form",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    'dojo/_base/lang',
    "dojo/_base/array",
    'dojo/_base/event',
    'dojo/keys',
    "dojo/dom-style",
    'dojo/on',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    './WorkArea',
    'dojo/i18n!buildspace/nls/Tendering'
], function(declare, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, lang, array, evt, keys, domStyle, on, DropDownButton, DropDownMenu, MenuItem, WorkArea, nls){

    return declare('buildspace.apps.Editor.Builder', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        pid: null,
        workarea: null,
        postCreate: function(){
            this.inherited(arguments);

            var self = this,
                projectInfoXhr = dojo.xhrGet({
                    url: "project/"+this.pid,
                    handleAs: "json"
                });

            projectInfoXhr.then(function(project){
                workarea = self.workarea = WorkArea({
                    project: project
                });

                workarea.startup();

                self.addChild(workarea);
            });
        }
    });
});
