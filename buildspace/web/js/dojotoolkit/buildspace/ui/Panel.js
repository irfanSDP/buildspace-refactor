define(['dojo/_base/lang',
    'dojo/_base/declare',
    'dijit/_Widget',
    'dijit/_TemplatedMixin',
    'dijit/_Container',
    'dijit/Menu',
    'dijit/MenuSeparator'], function(lang, declare, _Widget, _TemplatedMixin, _Container, Menu){
    return declare("buildspace.ui.Panel", [_Widget, _TemplatedMixin, _Container], {
        templateString: "<div class=\"desktopPanel\" dojoAttachEvent=\"onmousedown:_onClick, oncontextmenu:_onRightClick\"><div class=\"desktopPanel-start\"><div class=\"desktopPanel-end\"><div class=\"desktopPanel-middle\" dojoAttachPoint=\"containerNode\"></div></div></div></div>",
        //  span: Float
        //  a number between 0 and 1 indicating how far the panel should span accross (1 being the whole screen, 0 being none)
        span: 1,
        //  opacity: Float
        //  a number between 0 and 1 indicating how opaque the panel should be (1 being visible, 0 being completely transparent)
        opacity: 1,
        //  thickness: Integer
        //  how thick the panel should be in pixels
        thickness: 24,
        //  locked: Boolean
        //  are the applets and the panel itself be repositionable?
        locked: false,
        //  placement: String
        //      where the panel should be placed on the screen.
        //      acceptible values are "BL", "BR", "BC", "TL", "TR", "TC", "LT", "LB", "LC", "RT", "RB", or "RC".
        //      The first character indicates the edge, the second character indicates the placement.
        //      R = right, L = left, T = top, and B = bottom.
        //      So LT would be on the left edge on the top corner.
        placement: "BL",
        getOrientation: function(){
            //  summary:
            //      Gets the orientation of the panel
            //  returns:
            //      "horizontal" or "vertical"
            var s = this.placement.charAt(0);
            return (s == "B" || s == "T") ? "horizontal" : "vertical";
        },
        _onClick: function(e){
            dojo.stopEvent(e);
        },
        _onRightClick: function(e){
            dojo.stopEvent(e);
        },
        uninitialize: function(){
            dojo.forEach(this.getChildren(), function(item){
                item.destroy();
            });
            if(this.window) this.window.close();
        },
        _place: function(){
            //  summary:
            //      Updates the position and size of the panel
            var viewport = dijit.getViewport();
            var s = {};
            if(this.placement.charAt(0) == "T" || this.placement.charAt(0) == "B"){
                this._makeHorizontal();
                if(this.placement.charAt(1) == "R")
                    s.left = (viewport.w - this.domNode.offsetWidth);
                if(this.placement.charAt(1) == "L")
                    s.left = viewport.l;
                if(this.placement.charAt(1) == "C"){
                    if(this.span != 1){
                        s.left = (viewport.w - (this.span*viewport.w)) / 2;
                    }
                    else
                        s.left = viewport.l;
                }

                if(this.placement.charAt(0) == "B")
                    s.top = (viewport.h + viewport.t) - this.domNode.offsetHeight;
                else
                if(this.placement.charAt(0) == "T")
                    s.top = viewport.t;
            } else {
                //we need a completely different layout algorithm :D
                this._makeVertical();
                if(this.placement.charAt(1) == "C"){
                    if(this.span != 1){
                        var span = dojo.style(this.domNode, "height");
                        s.top = (viewport.h - span)/2;
                    }
                }
                else if(this.placement.charAt(1) == "B"){
                    s.top = (viewport.h + viewport.t) - this.domNode.offsetHeight;
                }
                else {
                    s.top = viewport.t;
                }
                if(this.placement.charAt(0) == "L"){
                    s.left = viewport.l;
                }
                else {
                    s.left = (viewport.w + viewport.l) - this.domNode.offsetWidth;
                }
            }
            var sides = {
                T: "Top",
                L: "Left",
                R: "Right",
                B: "Bottom"
            };
            for(var sk in sides){
                dojo.removeClass(this.domNode, "desktopPanel"+sides[sk]);
            }
            dojo.addClass(this.domNode, "desktopPanel"+sides[this.placement.charAt(0)]);

            var count = 0;
            //check for other panels in the same slot as us
            dojo.query(".desktopPanel").forEach(dojo.hitch(this, function(panel){
                var panel = dijit.byNode(panel);
                if(panel.id != this.id){
                    if(this.placement.charAt(0) == panel.placement.charAt(0) && (panel.span==1 || this.span==1)) count += panel.thickness;
                    else if(panel.placement == this.placement)
                        count += panel.thickness;
                }
            }));
            if(this.placement.charAt(0) == "L" || this.placement.charAt(0) == "T") s[this.getOrientation() == "horizontal" ? "top" : "left"] += count;
            else s[this.getOrientation() == "horizontal" ? "top" : "left"] -= count;

            var props = {};
            for(var key in s){
                props[key] = {end: s[key], unit: "px"};
            }
            dojo.animateProperty({
                node: this.domNode,
                properties: props,
                duration: 750
            }).play();

            dojo.forEach(this.getChildren(), function(item){
                item.resize();
            });
        },
        resize: function(){
            //  summary:
            //      Called when the window is resized. Resizes the panel to the new window height
            var viewport = dijit.getViewport();
            dojo.style(this.domNode, (this.getOrientation() == "horizontal" ? "width" : "height"), (this.span*viewport[(this.getOrientation() == "horizontal" ? "w" : "h")])+"px");
            dojo.style(this.domNode, (this.getOrientation() == "vertical" ? "width" : "height"), this.thickness+"px");
            dojo.forEach(this.getChildren(), function(item){
                item.resize();
            });
        },
        _makeVertical: function(){
            //  summary:
            //      Orients the panel's applets vertically
            dojo.removeClass(this.domNode, "desktopPanelHorizontal");
            dojo.addClass(this.domNode, "desktopPanelVertical");
            this.resize();
        },
        _makeHorizontal: function(){
            //  summary:
            //      Orients the panel's applets horizontally
            dojo.removeClass(this.domNode, "desktopPanelVertical");
            dojo.addClass(this.domNode, "desktopPanelHorizontal");
            this.resize();
        },
        lock: function(){
            //  summary:
            //      Locks the panel
            this.locked = true;
            dojo.forEach(this.getChildren(), function(item){
                item.lock();
            });
        },
        unlock: function(){
            //  summary:
            //      Unlocks the panel
            this.locked = false;
            dojo.forEach(this.getChildren(), function(item){
                item.unlock();
            });
        },
        dump: function(){
            //  summary:
            //      Returns a javascript object that can be used to restore the panel using the restore method
            var applets = [];
            var myw = dojo.style(this.domNode, "width"), myh = dojo.style(this.domNode, "height");
            dojo.forEach(this.getChildren(), dojo.hitch(this, function(item){
                var left=dojo.style(item.domNode, "left"), top=dojo.style(item.domNode, "top");
                var pos = (this.getOrientation() == "horizontal" ? left : top);
                pos = pos / (this.getOrientation() == "horizontal" ? myw : myh);
                var applet = {
                    settings: item.settings,
                    pos: pos,
                    declaredClass: item.declaredClass
                };
                applets.push(applet);
            }));
            return applets;
        },
        restore: function(/*Array*/applets){
            //  summary:
            //      Restores the panel's applets
            //  applets:
            //      an array of applets to restore (generated by the dump method)
            var self = this;
            var size = dojo.style(this.domNode, this.getOrientation() == "horizontal" ? "width" : "height");
            dojo.forEach(applets, dojo.hitch(this, function(applet){
                require([applet.declaredClass], function(obj){
                    var a = new obj({settings: applet.settings, pos: applet.pos});
                    if(self.locked) a.lock();
                    else a.unlock();
                    self.addChild(a);
                    a.startup();
                });
            }));
        },
        startup: function(){
            dojo.setSelectable(this.domNode, false);

            dojo.style(this.domNode, "top", (-(this.thickness))+"px");

            //dojo.style(this.domNode, "zIndex", 9999*9999);
            dojo.style(this.domNode, "opacity", this.opacity);
            if(dojo.isIE){
                dojo.connect(this.domNode,'onresize', this,"_place");
            }
            dojo.connect(window,'onresize',this, "_place");
            this._place();
        }
    });
});