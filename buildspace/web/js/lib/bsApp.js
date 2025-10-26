define([
    "dojo/dom",
    "dojo/dom-style",
    "dojo/dom-class",
    "dojo/dom-construct",
    "dojo/dom-geometry",
    "dojo/string",
    "dojo/on",
    "dojo/aspect",
    "dojo/keys",
    "dojo/_base/lang",
    "dojo/_base/fx",
    "dijit/registry",
    "dojo/parser",
    "dijit/form/Button",
    "dojox/layout/ContentPane",
    "dojo/_base/window"
],
function(dom, domStyle, domClass, domConstruct, domGeometry, string, on, aspect, keys, lang, baseFx, registry, parser, Button, ContentPane, win) {

    var startup = function() {

            initUI();
        },

        initUI = function() {
            endLoading();
        },

        startLoading = function(targetNode) {
            var overlayNode = dom.byId("loadingOverlay");
            if("none" == domStyle.get(overlayNode, "display")) {
                var coords = domGeometry.getMarginBox(targetNode || win.body());
                domGeometry.setMarginBox(overlayNode, coords);
                domStyle.set(dom.byId("loadingOverlay"), {
                    display: "block",
                    opacity: 1
                });
            }
        },

        endLoading = function() {
            baseFx.fadeOut({
                node: dom.byId("loadingOverlay"),
                onEnd: function(node){
                    domStyle.set(node, "display", "none");
                }
            }).play();
        },

        confirmDialog = function(title, message, onYes) {
            var p = dijit.byId('defaultModalDialog');
            p.set( "title", title );
            dojo.byId('defaultModalDialogText').innerHTML = message;
            p.execute = dojo.hitch( p, function() {
                if( dojo.isObject( arguments ) ) {
                    onYes();
                }
            });
            p.show();
        },

        addTab = function(elId, title, tabId, url){
            var tc = registry.byId(elId);
            var cp = registry.byId(tabId);
            if(!cp){
                cp = new ContentPane({
                    id: tabId,
                    title: title,
                    closable: true,
                    href: url
                });
                tc.addChild(cp);
            }
            tc.selectChild(cp);
        },

        truncateString = function(str, len){
            if (str.length > len) {
                str = str.substring(0, len);
                str = str.replace(/\w+$/, '');
                str += '...';
            }
            return str;
        }

    return {
        init: function() {
            startLoading();
            // register callback for when dependencies have loaded
            startup();
        },

        addTab: function(elId, title, tabId, url){
            addTab(elId, title, tabId, url);
        },

        truncateString: function(str, len){
            return truncateString(str, len);
        }
    };

});
