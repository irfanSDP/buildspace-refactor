define('buildspace/apps/StockOut/StockOutDialog/StockOutFormDialog', [
"dojo/_base/declare",
'dojo/_base/lang',
"dojo/keys",
"dojo/parser",
"dijit/_WidgetBase",
"dijit/_OnDijitClickMixin",
"dijit/_TemplatedMixin",
"dijit/_WidgetsInTemplateMixin",
'dijit/Toolbar',
'dijit/form/Button',
"dijit/layout/BorderContainer",
"dijit/form/ValidationTextBox",
"dijit/form/DateTextBox",
"dojo/dom-style",
"dojo/dom-form",
"dojo/request",
"dojox/validate/web",
"dojox/form/Manager",
"dojo/text!./templates/stockOutForm.html",
'dojo/i18n!../../../nls/StockOut'],
function(declare, lang, keys, parser, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, Toolbar, Button, BorderContainer, ValidationTextBox, DateTextBox, domStyle, domForm, request, web, Manager, template, nls) {
    var stockOutForm = declare("buildspace.apps.StockOut.StockOutForm", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        baseClass: "buildspace-form",
        templateString: template,
        region: 'center',
        style: "overflow: auto;",
        project: null,
        stockOutGrid: null,
        formInfo: null,
        nls: nls,
        dialogContainer: null,
        stockOutUsedQuantityId: -1,
        newStockOutUsedQuantityId: -1,
        startup: function() {
            this.inherited(arguments);
            this.stockOutForm.setFormValues(this.formInfo);
        },
        clearInputErrorMsgs: function() {
            var errorBlock, i, presetErrorBlocks, self;
            self = this;
            presetErrorBlocks = {
                project_structure_id: null,
                stock_out_date: null
            };
            for (i in presetErrorBlocks) {
                errorBlock = self["error-" + i];
                if (errorBlock == null) {
                    return false;
                }
                errorBlock.innerHTML = presetErrorBlocks[i];
                domStyle.set(errorBlock, "display", "none");
            }
        },
        save: function() {
            var form, formValues, pb, self;
            self = this;
            this.clearInputErrorMsgs();
            form = this.stockOutForm;
            if (!form.validate()) {
                return false;
            }
            formValues = dojo.formToObject(this.stockOutForm.id);
            if (parseInt(self.newStockOutUsedQuantityId) > 0) {
                lang.mixin(formValues, {
                    stockOutUsedQuantityId: self.newStockOutUsedQuantityId
                });
            } else {
                lang.mixin(formValues, {
                    stockOutUsedQuantityId: self.stockOutUsedQuantityId
                });
            }

            pb = buildspace.dialog.indeterminateProgressBar({
                title: nls.pleaseWait + "..."
            });

            pb.show().then(function(){
                request.post("stockOut/saveStockOutFormInformation/projectId/" + parseInt(self.project.id), {
                    data: formValues,
                    handleAs: 'json'
                }).then(function(response) {
                    var errorBlock, i, _results;
                    pb.hide();
                    if (response.success && parseInt(response.id) > 0) {
                        self.stockOutGrid.refreshGrid();
                        self.newStockOutUsedQuantityId = response.id;
                        self.dialogContainer.hide();
                    } else {
                        _results = [];
                        for (i in response.errorMsgs) {
                            errorBlock = self["error-" + i];
                            errorBlock.innerHTML = response.errorMsgs[i];
                            _results.push(domStyle.set(errorBlock, "display", "block"));
                        }
                        return _results;
                    }
                }, function(error) {
                    pb.hide();
                });
            });
        }
    });

    return declare('buildspace.apps.StockOut.StockOutFormDialog', dijit.Dialog, {
        title: nls.addNewStockOut,
        style: "padding:0px;margin:0px;",
        project: null,
        stockOutGrid: null,
        formInfo: null,
        buildRendering: function() {
            var content = this.createContent();
            this.content = content;
            content.startup();
            this.inherited(arguments);
        },
        postCreate: function() {
            domStyle.set(this.containerNode, {
                padding: "0px",
                margin: "0px"
            });
            this.closeButtonNode.style.display = "none";
            this.inherited(arguments);
        },
        createContent: function() {
            var borderContainer, form, self, toolbar;
            self = this;
            borderContainer = new BorderContainer({
                style: "width:480px;height:130px;padding:0;margin:0;",
                gutters: false
            });
            form = new stockOutForm({
                dialogContainer: self,
                project: self.project,
                stockOutGrid: self.stockOutGrid,
                formInfo: this.formInfo
            });
            toolbar = new Toolbar({
                region: 'top',
                style: "outline:none!important;padding:2px;overflow:hidden;"
            });
            toolbar.addChild(new Button({
                label: nls.save,
                iconClass: "icon-16-container icon-16-save",
                onClick: function() {
                    form.save();
                }
            }));
            toolbar.addChild(new dijit.ToolbarSeparator());
            toolbar.addChild(new dijit.form.Button({
                label: nls.close,
                iconClass: "icon-16-container icon-16-close",
                onClick: function() {
                    self.hide();
                }
            }));
            borderContainer.addChild(toolbar);
            borderContainer.addChild(form);

            return borderContainer;
        },
        _onKey: function(e) {
            var key = e.keyCode;
            if (key === keys.ESCAPE) {
                dojo.stopEvent(e);
            }
        },
        onHide: function() {
            this.destroyRecursive();
        }
    });
});
