define(['dojo/_base/declare',
    'dojo/dom-style',
    'dojo/i18n!buildspace/nls/Menu',
    'dijit/Toolbar',
    'dijit/ToolbarSeparator',
    'dijit/form/Button',
    'dijit/form/DropDownButton',
    "dojo/_base/fx",
    'dojo/dom-attr',
    "dojo/fx/easing",
    "dijit/DialogUnderlay",
    'buildspace/ui/Applet'], function(declare, domStyle, Menui18n, Toolbar, ToolbarSeparator, Button, DropDownButton, baseFx, domAttr, easing, DialogUnderlay, Applet){
    return declare("buildspace.ui.applets.Menubar", Applet, {
        dispName: "Menu Bar",
        _drawn: false,
        mainMenuToolbar: null,
        mainToolbar: null,
        underlayEvtConn:null,
        mainToolbarEvtConn:null,
        postCreate: function(){
            if (this._menu){
                this._menu.destroy();
            }
            this._drawButton();
            dojo.addClass(this.containerNode, "menuApplet");
            this.inherited("postCreate", arguments);
        },
        _drawButton: function(){
            //  summary:
            //      Draws the button for the applet
            if(this._drawn){
                this._appMenuButton.dropDown = this._menu;
                this._appMenuButton._started = false; //hackish....
                this._appMenuButton.startup();
                return;
            }
            else this._drawn = true;

            var self = this;
            var mainMenuToolbar = this.mainMenuToolbar = new Toolbar({
                id:'buildspace-main_menu-toolbar'
            });
            this.addChild(mainMenuToolbar);

            var b = new Button({
                id: 'buildspace-start_menu-btn',
                baseClass: 'buildspace-main_menu',
                iconClass: 'buildspace-start_menu',
                showLabel: false,
                onClick: function(){
                    self.showMainMenu();
                }
            });
            this.mainMenuToolbar.addChild(b);
            this.mainMenuToolbar.addChild(new dijit.ToolbarSeparator({id:'buildspace-main_menu-separator'}));
            b.domNode.style.height="100%";
            b.startup();
        },
        updateMenuBar: function(item){
            var self = this;
            var mainMenuToolbar = this.mainMenuToolbar,
                children = mainMenuToolbar.getChildren();
            dojo.forEach(children, function(child, index){
                if(index > 1){
                    mainMenuToolbar.removeChild(child);
                    child.destroyRecursive(true);
                }
            });
            if(item.__children.length > 0){
                dojo.forEach(item.__children, function(child){
                    var button = Button({
                        id: 'main-tool-bar-sub-child-menus_' + child.id,
                        buttonId: child.id,
                        label: child.title,
                        iconClass: child.icon ? "icon-14-"+child.icon : 'icon-24-container icon-24-application',
                        onClick: dojo.hitch(this, function(){
                            // if current child has sub item(s) with it, then recursively parse the sub item(s)
                            if(child.__children.length > 0){
                                return self.updateMenuBar(child);
                            }

                            if(child.is_app){
                                buildspace.app.launch(child);

                                self.clearLastSelectedSubModule();

                                var button = new dijit.byId('main-tool-bar-sub-child-menus_' + child.id);

                                if ( button ) {
                                    domAttr.set(button.domNode, "style", {backgroundColor: "#ffe284"});
                                }
                            }
                        })
                    });
                    mainMenuToolbar.addChild(button);
                });
            }
        },
        clearLastSelectedSubModule: function() {
            var mainMenuToolbar = this.mainMenuToolbar,
                children = mainMenuToolbar.getChildren();

            dojo.forEach(children, function(child, index){
                if(index > 1){
                    var button = new dijit.byId('main-tool-bar-sub-child-menus_' + child.params.buttonId);

                    if ( button ) {
                        domAttr.set(button.domNode, "style", '');
                    }
                }
            });
        },
        createMainToolbar: function(){
            var self = this;
            var mainToolbar = this.mainToolbar = dijit.byId('buildspace-toolbar');
            if(mainToolbar){
                var data = buildspace.app.appList;
                var children = mainToolbar.getChildren();
                dojo.forEach(children, function(child){
                    mainToolbar.removeChild(child);
                    child.destroyRecursive(true);
                });

                dojo.forEach(data[0], function(item){
                    var button = Button({
                        showLabel:true,
                        label: item.title,
                        iconClass: item.icon ? 'icon-64-container icon-64-'+item.icon : null,
                        style:"height:64px;width:96px;background:transparent;",
                        onClick: dojo.hitch(this, function(){
                            buildspace.app.launch(item);
                            self.updateMenuBar(item);

                            if ( item.is_app === true ) {
                                self.displayAppName(item);
                            }
                        })
                    });
                    mainToolbar.addChild(button);
                },this);
            }
        },
        showMainMenu: function(){
            if(!this.mainToolbar){
                this.createMainToolbar();
            }
            var underlay = DialogUnderlay._singleton;
            if(underlay){ underlay.destroyRecursive();}
            underlay = dijit._underlay = DialogUnderlay._singleton = new DialogUnderlay({
                dialogId: 'buildspace-toolbar',
                id:'buildspace-toolbar-underlay',
                "class": 'buildspace-toolbar-underlay'
            });
            baseFx.animateProperty({
                node: 'buildspace-toolbar',
                properties: {
                    top: {
                        start: -200,
                        end: 0,
                        unit: "px"
                    }
                },
                easing: easing.liner,
                duration: 150
            }).play();
            underlay.show();
            domStyle.set(DialogUnderlay._singleton.domNode, 'zIndex', 300000);
            this.underlayEvtConn = dojo.connect(underlay, "onClick", dojo.hitch(this, "hideMainMenu"));
            this.mainToolbarEvtConn = dojo.connect(this.mainToolbar, "onClick", dojo.hitch(this, "hideMainMenu"));
        },
        hideMainMenu: function(){
            dojo.disconnect(this.underlayEvtConn);
            dojo.disconnect(this.mainToolbarEvtConn);
            var underlay = DialogUnderlay._singleton;
            baseFx.animateProperty({
                node: 'buildspace-toolbar',
                properties: {
                    top: {
                        start: 0,
                        end: -200,
                        unit: "px"
                    }
                },
                easing: easing.liner,
                duration: 150
            }).play();
            if(underlay){ underlay.destroyRecursive();}
        },
        displayAppName: function(item){
            var mainMenuToolbar = this.mainMenuToolbar,
                children = mainMenuToolbar.getChildren();
            dojo.forEach(children, function(child, index){
                if(index > 1){
                    mainMenuToolbar.removeChild(child);
                    child.destroyRecursive(true);
                }
            });

            mainMenuToolbar.addChild(Button({
                label: '<h1 style=\"font-size:12px; color:#414841;\">'+item.title+'</h1>'
            }));
        }
    });
});