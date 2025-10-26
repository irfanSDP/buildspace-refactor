define(['dojo/_base/declare',
    'dojo/dom',
    'dojo/dom-style',
    'dojo/window',
    'dojo/i18n!buildspace/nls/Window',
    'dijit/_TemplatedMixin',
    'dijit/layout/BorderContainer',
    "dojo/text!./templates/Window.html"
], function(declare, dom, domStyle, win, nls, _TemplatedMixin, BorderContainer, template){
    return declare("buildspace.widget.Window", [BorderContainer, _TemplatedMixin], {
        templateString: template,
        _started: false,
        //  closed: Boolean
        //      Is the window closed?
        closed: false,
        //  shown: Boolean
        //      Is the window shown?
        shown: false,
        //  main: Boolean?
        //      Is this the main window for this application?
        main: false,
        //  iconClass: String?
        //      the class to give the icon node
        iconClass: "",
        //  liveSplitters: Boolean
        //      specifies whether splitters resize as you drag (true) or only upon mouseup (false)
        liveSplitters: false,
        fullscreen: false,
        onClose: function(){
            //  summary:
            //      What to do on destroying of the window
        },
        onResize: function(){
            //  summary:
            //      What to do on the resizing of the window
        },
        gutters: false,
        //  showMaximize: Boolean
        //      Whether or not to show the maximize button
        height: "480px",
        //  width: String
        //      The window's width in px, or %.
        width: "600px",
        //  title: String
        //      The window's title
        title: "(untitled)",
        id:'buildspace-main_window',
        constructor: function(){
            var today = new Date();
            this.copyrightYear = today.getFullYear();
            var win = dijit.byId('buildspace-main_window');
            if(win){ win.close(); }
            this.inherited(arguments);
        },
        postCreate: function(){
            dojo.setSelectable(this.titleNode, false);
            this.domNode.title="";
            this.shown = false;
            this.closed = false;

            if(dojo.isIE){
                dojo.connect(this.domNode,'onresize',this,"_onResize");
            }
            dojo.connect(window,'onresize', this, "_onResize");
            dojo.style(this.domNode, "position", "absolute"); //override /all/ css values for this one

            if(this._drag) this._drag.destroy();
            this.inherited(arguments);
        },
        show: function(){
            //  summary:
            //      Shows the window
            if(this.shown)
                return;
            this.shown = true;
            document.body.appendChild(this.domNode);
            this.titleNode.innerHTML = this.title;
            dojo.style(this.domNode, "display", "block");
            this._onResize();

            if(!this._started){
                this.startup();
            }
        },
        _setTitleAttr: function(/*String*/title){
            //  summary:
            //      Sets window title after window creation
            //  title:
            //      The new title
            this.titleNode.innerHTML = title;
            this.title = title;
        },
        setTitle: function(/*String*/title){
            dojo.deprecated("builspace.widget.Window.setTitle", "setTitle is deprecated. Please use Window.attr(\"title\", \"value\");", "1.1");
            return this._setTitleAttr(title);
        },
        close: function(){
            //      closes the window
            if (!this.closed){
                this.closed = true;
                this.onClose();
            }
            this.destroyRecursive();
        },
        layout: function(){
            //hack so we don't have to deal with BorderContainer's method using this.domNode
            var oldNode = this.domNode;
            this.domNode = this.containerNode;

            try{
                this.inherited(arguments);
            }
            finally{
                this.domNode = oldNode;
            }
        },
        resize: function(){
            // resize the window
            //hack so we don't have to deal with BorderContainer's method using this.domNode
            var oldNode = this.domNode;
            this.domNode = this.containerNode;
            try{
                //this.inherited(arguments);
            }
            finally{
                this.domNode = oldNode;
            }
            dojo.forEach(this.getChildren(), function(wid){
                if(typeof wid != "undefined" && typeof wid.resize == "function")
                    wid.resize();
            });
            this.onResize();
        },
        _onResize: function(e){
            //  summary:
            //      Event handler. Resizes the window when the screen is resized.
            var max = win.getBox();
            if(this.domNode){
                var height = (this.fullscreen) ? parseInt(max.h) : (parseInt(max.h)-40);
                dojo.style(this.domNode, {
                    width: parseInt(max.w)+"px",
                    height: height+"px"
                });
            }
            this.resize();
        }
    });
});
