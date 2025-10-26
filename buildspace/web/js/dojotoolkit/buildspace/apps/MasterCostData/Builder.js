define('buildspace/apps/MasterCostData/Builder',[
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
    'dijit/layout/TabContainer',
    'dijit/form/DropDownButton',
    "dijit/DropDownMenu",
    "dijit/MenuItem",
    "./WorkArea",
    'dojo/i18n!buildspace/nls/CostData'
], function(declare, Form, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, lang, array, evt, keys, domStyle, on, TabContainer, DropDownButton, DropDownMenu, MenuItem, WorkArea, nls){

    return declare('buildspace.apps.MasterCostData.Builder', dijit.layout.BorderContainer, {
        style: "padding:0px;width:100%;height:100%;",
        gutters: false,
        id: null,
        data: {},
        workarea: null,
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var workarea = self.workarea = WorkArea({
                masterCostData: {id:self.id}
            });

            workarea.startup();

            self.addChild(workarea);
        }
    });
});
