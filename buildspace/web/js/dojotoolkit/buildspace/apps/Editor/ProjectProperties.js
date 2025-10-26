define('buildspace/apps/Editor/ProjectProperties',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/dom",
    "dijit/form/Form",
    "dijit/focus",
    'dojox/grid/EnhancedGrid',
    "dijit/form/ValidationTextBox",
    "dijit/form/Textarea",
    "dijit/form/DateTextBox",
    "dijit/form/Select",
    "dijit/form/FilteringSelect",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/projectProperties.html",
    'dojo/i18n!buildspace/nls/Tendering'
], function(declare, html, dom, Form, focusUtil, EnhancedGrid, ValidateTextBox, Textarea, DateTextBox, Select, FilteringSelect, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls){

    var MainInfoForm = declare("buildspace.apps.Editor.MainInfoFormWidget", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        style: "border:none;padding:5px;overflow:auto;",
        project: null,
        nls: nls,
        close: function(){
        },
        onCancel: function(){
        }
    });

    var PrintRevisionGrid = declare('buildspace.apps.Editor.PrintRevisionGrid', EnhancedGrid, {
        project: null,
        workArea: null,
        style: "border-top:none;",
        canSort: function(inSortInfo){
            return false;
        },
        postCreate: function(){
            this.inherited(arguments);

            this.on('CellClick', function(e) {
                var colField = e.cell.field,
                    item = this.getItem(e.rowIndex);

                if (colField == 'current_print' && item && !isNaN(parseInt(String(item.id))) && !item.current_print[0]){
                    this.changeCurrentViewingRevision(item);
                }
            });
        },
        changeCurrentViewingRevision: function(item){
            var self = this,
                pb = buildspace.dialog.indeterminateProgressBar({
                    title:nls.pleaseWait+'...'
                });
            focusUtil.curNode.blur();//unfocus clicked button
            focusUtil.curNode = null;

            pb.show().then(function(){
                dojo.xhrPost({
                    url: "changeCurrentPrintRevision",
                    content: { id: item.id, pid: self.project.id, _csrf_token: item._csrf_token },
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            var store = self.store;

                            dojo.forEach(resp.items, function(node){
                                store.fetchItemByIdentity({ 'identity' : node.id,  onItem : function(item){
                                    for(var property in node){
                                        if(item.hasOwnProperty(property) && property != store._getIdentifierAttribute()){
                                            store.setValue(item, property, node[property]);
                                        }
                                    }
                                }});
                            });
                            store.save();

                            self.workArea.removeBillTab();
                        }
                        pb.hide();
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });
        },
    });

    return declare('buildspace.apps.Editor.ProjectProperties', dijit.layout.BorderContainer, {
        region: "center",
        style:"padding:0;margin:0;width:100%;height:100%;",
        gutters: false,
        project: null,
        workArea: null,
        postCreate: function(){
            this.inherited(arguments);

            var projectMainInfoForm = this.projectMainInfoForm = new MainInfoForm({
                project: this.project,
                region: 'top'
            });

            var customFormatter = {
                printRevision: function(cellValue, rowIdx, cell){
                    var item = this.grid.getItem(rowIdx);
                    if(item && !isNaN(parseInt(item.id.toString()))){
                        return item.current_print[0] ? '<span class="icon-16-container icon-16-checkmark2" style="margin: 0 auto;display: block;"></span>' : '<a href="javascript:void(0);">'+nls.printThisRevision+'</a>';
                    }else{
                        return "&nbsp;"
                    }
                }
            };

            var printRevisionGrid = new PrintRevisionGrid({
                project: this.project,
                workArea: this.workArea,
                region: 'center',
                structure: [
                    {name: 'No.', field: 'count', width:'30px', styles:'text-align:center;' },
                    {name: nls.billRevisionDescription, field: 'revision', width:'auto'},
                    {name: nls.currentPrintingRevision, field: 'current_print', width:'150px', styles:'text-align:center;vertical-align:middle;padding:5px;', formatter: customFormatter.printRevision}
                ],
                store: new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:"projectRevisions/"+this.project.id
                })
            });

            this.addChild(projectMainInfoForm);
            this.addChild(printRevisionGrid);
        }
    });
});
