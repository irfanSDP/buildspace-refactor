require(["dojo/_base/lang",
    "dojo/_base/declare",
    "dijit/dijit",
    "dijit/form/Button",
    "dijit/form/FilteringSelect",
    "dojo/data/ItemFileReadStore",
    "dojo/data/ItemFileWriteStore",
    "dijit/Menu",
    "buildspace/ui/Applet",
    "buildspace/ui/Panel",
    "buildspace/ui/applets/UserProfile",
    "buildspace/ui/applets/GlobalNotifier",
    "buildspace/ui/applets/Menubar",
    "buildspace/ui/applets/ButtonLinkEProject",
    "buildspace/ui/applets/Separator"], function(lang, declare,dijit,Button,FilteringSelect,ItemFileReadStore,ItemFileWriteStore,Menu){
        lang.mixin(buildspace.ui, {
            //  _drawn: Boolean
            //  true after the UI has been drawn
            _drawn: false,
            _draw: function(){
                if(this._drawn === true) return;
                this._drawn = true;
                var toolbar = new dijit.Toolbar({
                    id:'buildspace-toolbar',
                    baseClass: 'buildspace-toolbar'
                },'easingNode');
                this.makePanels();
            },
            init: function(){
                this._draw();
            },
            //  drawn: Boolean
            //      have the panels been drawn yet?
            drawn: false,
            makePanels: function(){
                //  summary:
                //      the first time it is called it draws each panel based on what's stored in the configuration,
                //      after that it cycles through each panel and calls it's _place(); method
                if(this.drawn){
                    dojo.query(".desktopPanel").forEach(function(panel){
                        var p = dijit.byNode(panel);
                        p._place();
                    }, this);
                    return;
                }
                this.drawn = true;
                var panels = [{
                        thickness: 40,
                        span: 1,
                        locked: true,
                        orientation: "horizontal",
                        placement: "TC",
                        applets: [
                            {"settings": {}, "pos": 0.01, "declaredClass": "buildspace/ui/applets/Menubar"},
                            //{"settings": {}, "pos": 0.90, "declaredClass": "buildspace/ui/applets/GlobalNotifier"},
                            {"settings": {}, "pos": 0.93, "declaredClass": "buildspace/ui/applets/ButtonLinkEProject"},
                            {"settings": {}, "pos": 0.96, "declaredClass": "buildspace/ui/applets/UserProfile"}
                        ]
                    }];
                dojo.forEach(panels, function(panel){
                    var p = new buildspace.ui.Panel({
                        thickness: panel.thickness,
                        span: panel.span,
                        placement: panel.placement,
                        opacity: panel.opacity
                    });
                    if(panel.locked) p.lock();
                    else p.unlock();
                    p.restore(panel.applets);
                    document.body.appendChild(p.domNode);
                    p.startup();
                });
            },
            textContent: function(/*DomNode|String*/node, /*String?*/text){
                //  summary:
                //      sets the textContent of a domNode if text is provided
                //      gets the textContent if a domNode if text is not provided
                //      if dojo adds this in the future, grep though
                //      the js code and replace it with dojo's method
                //  node:
                //      the node to set/get the text of
                //  text:
                //      the text to use
                node = dojo.byId(node);
                var attr = typeof node.textContent == "string" ? "textContent" : "innerText";
                if(arguments.length == 1)
                    return node[attr];
                else
                    node[attr] = text;
            }
        });
});
