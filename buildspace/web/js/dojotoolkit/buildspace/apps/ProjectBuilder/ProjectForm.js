define('buildspace/apps/ProjectBuilder/ProjectForm',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/dom",
    'dojo/keys',
    "dojo/dom-style",
    "dijit/form/Form",
    "dijit/form/ValidationTextBox",
    "dijit/form/Textarea",
    "dijit/form/Select",
    "dijit/form/FilteringSelect",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/text!./templates/projectForm.html",
    'dojo/i18n!buildspace/nls/ProjectBuilder',
    "dojo/on"
], function(declare, html, dom, keys, domStyle, dijitForm, ValidateTextBox, Textarea, Select, FilteringSelect, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, template, nls, on){

    var Form = declare("buildspace.apps.ProjectBuilder.ProjectFormWidget", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        projectId: -1,
        nls: nls,
        style: "padding:5px;overflow:auto;",
        postCreate: function(){
            this.inherited(arguments);
            var self = this;

            var countrySelectStore = new dojo.data.ItemFileReadStore({
                url:"projectBuilder/getCountry"
            });

            var currencySelectStore = new dojo.data.ItemFileReadStore({
                url:"projectBuilder/getCurrency"
            });

            this.stateSelectStore = stateSelectStore = new dojo.data.ItemFileReadStore({
                url:"projectBuilder/getStateByCountry/regionId/"+null
            });

            self.countrySelect = new FilteringSelect({
                name: "project_main_information[region_id]",
                store: countrySelectStore,
                style: "padding:2px;",
                searchAttr: "name",
                onChange: function(country){
                    if(country){
                        self.stateSelect.set('readOnly' , false);
                    }else{
                        self.stateSelect.set('readOnly' , true);
                    }

                    self.updateStateSelectStore(country);
                }
            }).placeAt(self.countrySelectDivNode);

            self.stateSelect = new FilteringSelect({
                name: "project_main_information[subregion_id]",
                store: stateSelectStore,
                style: "padding:2px;",
                searchAttr: "name",
                readOnly: true
            }).placeAt(self.stateSelectDivNode);

            self.currencySelect = new FilteringSelect({
                name: "project_main_information[currency_id]",
                store: currencySelectStore,
                style: "padding:2px;",
                searchAttr: "name"
            }).placeAt(self.currencySelectDivNode);

        },
        refreshStateSelectStore: function(){
            this.stateSelect.store.close();
            this.stateSelect.set('store', this.stateSelectStore);
            this.stateSelect.set('value', '');
        },
        updateStateSelectStore: function(country){
            var countryId = (country) ? country : 0,
                values = {regionId : countryId},
                self = this;

            dojo.xhrPost({
                url: 'projectBuilder/getCurrencyValueByCountry',
                content: values,
                handleAs: 'json',
                load: function(resp) {

                    self.stateSelectStore = new dojo.data.ItemFileReadStore({
                        url:"projectBuilder/getStateByCountry/regionId/"+countryId,
                        clearOnClose: true
                    });

                    self.currencySelect.set('value', resp.id);

                    self.refreshStateSelectStore();
                },
                error: function(error) {
                }
            });
        },
        startup: function(){
            this.inherited(arguments);
            var self = this;

            dojo.xhrGet({
                url: 'projectBuilder/projectForm',
                handleAs: 'json',
                content: { id: this.projectId },
                load: function(data){
                    new Select({
                        name: "project_main_information[work_category_id]",
                        style: "width:180px;padding:2px!important;",
                        options: data.workCategoryOptions
                    }, "project_main_information_work_category_id");

                    self.projectForm.setFormValues(data.projectForm);
                },
                error: function(error) {
                    //something is wrong somewhere
                }
            });
        },
        save: function(){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.savingData+'...'
            });

            var self = this,
                values = dojo.formToObject(self.projectForm.id);

            if(this.projectForm.validate()){
                pb.show().then(function(){
                    dojo.xhrPost({
                        url: 'projectBuilder/projectUpdate',
                        content: values,
                        handleAs: 'json',
                        load: function(resp) {
                            dojo.query('[id^="error-project_main_information_"]').forEach(function(node){
                                node.innerHTML = '';
                            });

                            if(resp.success) {
                                var rowsToMove = [];

                                //Refresh or Update Grid Store Operation
                                var defaultRowIndex = 0;
                                var grid            = self.dialogObj.projectListingGrid;
                                var selectedIndex   = grid.selection.selectedIndex;
                                var store           = grid.store;
                                var item            = resp.items;
                                var saved           = store.newItem(item);
                                var itemIdx         = grid.getItemIndex(saved);

                                rowsToMove.push(itemIdx);

                                // If there is a currently selected row, deselect it now
                                if (selectedIndex != -1 && selectedIndex >= 0){
                                    grid.selection.setSelected(selectedIndex, false);
                                }

                                if(rowsToMove.length > 0) {
                                    grid.rearranger.moveRows(rowsToMove, defaultRowIndex);
                                    grid.selection.setSelected(defaultRowIndex, true);
                                    grid.render();
                                }

                                store.save();

                                if(self.dialogObj) {
                                    self.dialogObj.hide();
                                }
                            } else {
                                for(var key in resp.errorMsg){
                                    var msg = resp.errorMsg[key];
                                    html.set(dom.byId("error-project_main_information_"+key), msg);
                                }
                            }

                            pb.hide();
                        },
                        error: function(error) {
                            pb.hide();
                        }
                    });
                });
            }
        }
    });

    return declare('buildspace.apps.ProjectBuilder.ProjectFormDialog', dijit.Dialog, {
        style:"padding:0;margin:0;",
        projectId: -1,
        title: null,
        projectListingGrid: null,
        buildRendering: function(){
            var form = this.createForm();
            form.startup();
            this.content = form;

            this.inherited(arguments);
        },
        postCreate: function(){
            domStyle.set(this.containerNode, {
                padding:"0px",
                margin:"0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        _onKey: function(e){
            var key = e.keyCode;
            if (key == keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        },
        createForm: function(){
            var borderContainer = new dijit.layout.BorderContainer({
                style:"padding:0px;width:645px;height:420px;",
                gutters: false
            });

            var form = new Form({
                dialogObj: this,
                projectId: this.projectId
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.save,
                    iconClass: "icon-16-container icon-16-save",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(form, 'save')
                })
            );
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(
                new dijit.form.Button({
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        }
    });
});