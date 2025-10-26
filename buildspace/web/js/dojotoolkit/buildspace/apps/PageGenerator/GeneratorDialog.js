define('buildspace/apps/PageGenerator/GeneratorDialog',[
    'dojo/_base/declare',
    "dojo/html",
    "dojo/on",
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/promise/all",
    'dojo/keys',
    "dojo/mouse",
    "dojo/aspect",
    "dojo/dom-style",
    "dijit/_WidgetBase",
    "dijit/_OnDijitClickMixin",
    "dijit/_TemplatedMixin",
    "dijit/_WidgetsInTemplateMixin",
    'dijit/layout/BorderContainer',
    'dijit/Dialog',
    "dojo/text!./templates/generatorForm.html",
    'dojo/i18n!buildspace/nls/PageGenerator'
], function(declare, html, on, dom, domConstruct, all, keys, mouse, aspect, domStyle, _WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin, BorderContainer, Dialog, template, nls){

    var GeneratorFormWidget = declare("buildspace.apps.PageGenerator.GeneratorFormWidget", [_WidgetBase, _OnDijitClickMixin, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        baseClass: "buildspace-form",
        region: 'center',
        dialogObj: null,
        project : null,
        bills: [],
        nls: nls,
        style: "padding:5px;overflow:auto;",
        postCreate: function(){
            this.inherited(arguments);

            var self = this;
            var promises = [];

            var validateUrl = (this.validateUrl) ? this.validateUrl : 'pageGenerator/validateBill';

            dojo.forEach(this.bills, function(bill, i){

                domConstruct.create("li", { id:"pageGeneratorBillList-"+bill.id, innerHTML: buildspace.truncateString(bill.title, 78)+' <span style="float:right;line-height:normal!important;" class="dojoxGridLoading" id="loader-'+bill.id+'"> '+nls.validating+'...</span>' }, self.generatorBillListNode);

                var promise = dojo.xhrPost({
                    url: validateUrl,
                    content: {id: bill.id},
                    handleAs: 'json'
                }).then(function(ret){

                    var pageGeneratorListNode = dom.byId("pageGeneratorBillList-"+bill.id);
                    var pageGeneratorLoaderNode = dom.byId("loader-"+bill.id);

                    dojo.removeClass(pageGeneratorLoaderNode, "dojoxGridLoading");

                    var className = ret.success ? "alert-text-success" : "alert-text-error";
                    var pointerClassName = ret.success ? "" : "pointer";
                    var txt = ret.success ? nls.passed : nls.error;
                    
                    dojo.addClass(pageGeneratorLoaderNode, className);
                    dojo.addClass(pageGeneratorListNode, pointerClassName);

                    html.set(pageGeneratorLoaderNode, txt);

                    if(!ret.success){
                        on(pageGeneratorListNode, "click", function(evt){
                            self.dialogObj.onClickErrorNode(bill, evt);
                        });
                        on(pageGeneratorListNode, mouse.enter, function(evt){
                            domStyle.set(pageGeneratorListNode, "backgroundColor", "#f7f9fc");
                        });
                        on(pageGeneratorListNode, mouse.leave, function(evt){
                            domStyle.set(pageGeneratorListNode, "backgroundColor", "#ffffff");
                        });
                    }

                    return ret.success;
                });

                promises.push(promise);
            });

            all(promises).then(function(results) {
                var hasError = false;
                dojo.forEach(results, function(result, i){
                    if(!result){
                        hasError = true;
                        return false;
                    }
                });

                if(hasError){
                    var closeBtn = dijit.byId("pageGeneratorCloseBtn-"+self.project.id);
                    if(closeBtn)
                        closeBtn._setDisabledAttr(false);
                }else{
                    if(self.dialogObj){
                        self.dialogObj.onSuccess();
                        self.dialogObj.hide();
                    }
                }
            });
        }
    });

    return declare('buildspace.apps.PageGenerator.GeneratorDialog', dijit.Dialog, {
        style:"padding:0px;margin:0px;",
        title: nls.billPageGenerator,
        project: null,
        bill: null,
        validateUrl: null,
        buildRendering: function(){
            var content = this.createContent();
            content.startup();
            this.content = content;
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
        onSuccess: function(){
            this.hide();
        },
        onClickErrorNode: function(bill, evt){
        },
        createContent: function(){
            var self = this;
            var borderContainer = this.borderContainer = new BorderContainer({
                style:"padding:0;margin:0;width:520px;height:320px;background-color:#F5F1F1;",
                gutters: false
            });

            var toolbar = new dijit.Toolbar({
                region: "top",
                style: "outline:none!important;padding:2px;overflow:hidden;border-right:0px;border-left:0px;border-top:0px;"
            });

            toolbar.addChild(
                new dijit.form.Button({
                    id: "pageGeneratorCloseBtn-"+this.project.id,
                    label: nls.close,
                    iconClass: "icon-16-container icon-16-close",
                    style:"outline:none!important;",
                    disabled: true,
                    onClick: dojo.hitch(this, 'hide')
                })
            );

            borderContainer.addChild(toolbar);

            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.gatheringInformation+'...'
            });

            var content = (this.bill) ? {pid:this.project.id, bid:this.bill.id} : {pid:this.project.id};

            pb.show().then(function(){
                dojo.xhrGet({
                    url: 'pageGenerator/getBills',
                    content: content,
                    handleAs: 'json',
                    load: function(bills) {
                        pb.hide();

                        var formWidget = new GeneratorFormWidget({
                            dialogObj: self,
                            project: self.project,
                            bills: bills,
                            validateUrl: self.validateUrl
                        });

                        borderContainer.addChild(formWidget);
                    },
                    error: function(error) {
                        pb.hide();
                    }
                });
            });

            return borderContainer;
        }
    });
});