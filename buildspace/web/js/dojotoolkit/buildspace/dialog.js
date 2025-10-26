require(['dojo/_base/declare',
    'dojo/dom-style',
    'dojo/i18n!buildspace/nls/Common',
    'dojo/keys',
    'dojo/_base/lang',
    'dijit/layout/ContentPane',
    "dijit/form/Button",
    "dijit/Toolbar",
    "dijit/ToolbarSeparator",
    "dijit/layout/BorderContainer",
    'dijit/form/FilteringSelect',
    'dijit/ProgressBar',
    'dojox/widget/Toaster',
    'dijit/Dialog'
    ], function(declare, domStyle, nls, keys, lang, ContentPane, Button, Toolbar, ToolbarSeparator, BorderContainer){
    buildspace.dialog = {
        alert: function(title,content,height,width, onOk){
            var dialogLayout = new BorderContainer({
                gutters:false,
                style: "width:"+parseInt(width)+"px; height:"+parseInt(height)+"px;"
            });

            var buttonsPane = new Toolbar({
                region: "bottom",
                baseClass:'confirm-dialog',
                style: "background-color:white;text-align:right;outline:none !important;border:0;height:24px;overflow:hidden;"
            });
            var okBtn = new Button({ label:nls.ok });

            buttonsPane.addChild(okBtn);

            dialogLayout.addChild(new ContentPane({
                region: "center",
                style: "padding:0;margin:0;border:0;height:100%;",
                content: content
            }));

            dialogLayout.addChild(new ContentPane({
                region: "left",
                class: 'icon-24-container icon-24-info',
                style: "padding:0;margin:0;border:0;height:100%;width:32px;overflow:hidden;"
            }));

            dialogLayout.addChild(buttonsPane);
            dialogLayout.startup();

            var dialog = new dijit.Dialog({
                title:title,
                content: dialogLayout,
                style:"padding:0px;margin:0px;"
            });
            dialog.closeButtonNode.style.display = "none";
            dialog._onKey = function(evt){
                var key = evt.keyCode;
                if (key == keys.ESCAPE) {
                    dojo.stopEvent(evt);
                }
            };

            var okConn = dojo.connect(okBtn, "onClick", function(){
                if(typeof onOk === 'function'){
                    onOk();
                }
                dialog.hide();
            });

            dialog.onHide = function() {
                dojo.disconnect(okConn);
                dialog.destroyRecursive();
            };

            dialog.startup();
            dialog.show();

            return dialog;
        },
        indeterminateProgressBar: function(args){
            //  summary:
            //      Shows a simple progressbar dialog
            var pb = new dijit.ProgressBar({
                indeterminate : true, layoutAlign:"center"
            });

            args = lang.mixin(args, {
                content: pb,
                style: "background:#fff;padding:5px;height:60px;width:250px;",
                splitter: false
            });

            var box = new dijit.Dialog(args);
            box.closeButtonNode.style.display = "none";
            box._onKey = function(evt){
                var key = evt.keyCode;
                if (key == keys.ESCAPE) {
                    dojo.stopEvent(evt);
                }
            };
            box.onHide = function() {
                box.destroyRecursive();
            };

            return box;
        },
        dijitDialog: function(args){
            var box = new dijit.Dialog(args);
            box.onHide = function() {
                box.destroyRecursive();
            };
            box.show();

            return box;
        },
        confirm: function(title,content,height,width, onYes, onNo){
            var dialogLayout = new BorderContainer({
                gutters :false,
                style: "width:"+parseInt(width)+"px; height:"+parseInt(height)+"px;"
            });

            var buttonsPane = new Toolbar({
                region: "bottom",
                baseClass:'confirm-dialog',
                style: "background-color:white;text-align:right;outline:none !important;border:0;height:24px;overflow:hidden;"
            });
            var yesBtn = new Button({ label:nls.yes });
            var noBtn = new Button({ label:nls.no });

            buttonsPane.addChild(yesBtn);
            buttonsPane.addChild(new dijit.ToolbarSeparator());
            buttonsPane.addChild(noBtn);

            dialogLayout.addChild(new ContentPane({
                region: "center",
                style: "padding:0;margin:0;border:0;height:100%;",
                content: content
            }));

            dialogLayout.addChild(buttonsPane);

            dialogLayout.startup();

            var dialog = new dijit.Dialog({
                title: title,
                content: dialogLayout,
                style: "padding:0px;margin:0px;"
            });
            dialog.closeButtonNode.style.display = "none";
            dialog._onKey = function(evt){
                var key = evt.keyCode;
                if (key == keys.ESCAPE) {
                    dojo.stopEvent(evt);
                }
            };

            var yesConn = dojo.connect(yesBtn, "onClick", function(){
                if(typeof onYes === 'function'){
                    onYes();
                }
                dialog.hide();
            });

            var noConn = dojo.connect(noBtn, "onClick", function(){
                if(typeof onNo === 'function'){
                    onNo();
                }
                dialog.hide();
            });

            dialog.onHide = function() {
                dojo.disconnect(yesConn);
                dojo.disconnect(noConn);
                dialog.destroyRecursive();
            };

            dialog.startup();
            dialog.show();

            return dialog;
        },
        confirmWithInput: function(title,content,height,width, onYes, onNo, placeholder){
            var dialogLayout = new BorderContainer({
                gutters :false,
                style: "background:#fff!important;padding:4px;margin:4px;width:"+parseInt(width)+"px; height:"+parseInt(height)+"px;"
            });

            var buttonsPane = new Toolbar({
                region: "bottom",
                baseClass:'confirm-dialog',
                style: "padding-top:8px;background:#fff!important;text-align:right;outline:none!important;border:0;height:24px;overflow:hidden;"
            });
            var yesBtn = new Button({ label:nls.yes });
            var noBtn = new Button({ label:nls.no });

            buttonsPane.addChild(yesBtn);
            buttonsPane.addChild(new dijit.ToolbarSeparator());
            buttonsPane.addChild(noBtn);

            var contentBorderContainer = new BorderContainer({
                region: "center",
                style: "padding:0;margin:0;border:none;height:100%;background:#fff!important;",
                gutters :true
            });

            contentBorderContainer.addChild(new ContentPane({
                region: "top",
                style: "padding:0;margin:0;border:none;padding-bottom:4px;background:#fff!important;",
                content: content
            }));

            var input = new dijit.form.Textarea({
                region: "center",
                placeholder: placeholder,
                style:"width:100%;min-height:60px;_height:60px;border:none;outline:none;background:#fff!important;"
            });

            contentBorderContainer.addChild(new ContentPane({
                region: "center",
                style: "padding:0;margin:0;border:1px #ccc solid;overflow-y:auto;background:#fff!important;",
                content: input
            }));
            
            dialogLayout.addChild(contentBorderContainer);
            dialogLayout.addChild(buttonsPane);
            dialogLayout.startup();

            var dialog = new dijit.Dialog({
                title: title,
                content: dialogLayout,
                style: "padding:0px;margin:0px;"
            });
            dialog.closeButtonNode.style.display = "none";
            dialog._onKey = function(evt){
                var key = evt.keyCode;
                if (key == keys.ESCAPE) {
                    dojo.stopEvent(evt);
                }
            };

            var yesConn = dojo.connect(yesBtn, "onClick", function(){
                if(typeof onYes === 'function'){
                    onYes(input.value);
                }
                dialog.hide();
            });

            var noConn = dojo.connect(noBtn, "onClick", function(){
                if(typeof onNo === 'function'){
                    onNo();
                }
                dialog.hide();
            });

            dialog.onHide = function() {
                dojo.disconnect(yesConn);
                dojo.disconnect(noConn);
                dialog.destroyRecursive();
            };

            dialog.startup();
            dialog.show();

            return dialog;
        }
    }
});