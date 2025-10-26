define(['dojo/_base/declare',
    'buildspace/ui/Applet'], function(declare, Applet){
    return declare("buildspace.ui.applets.Separator", Applet, {
        dispName: "Separator",
        postCreate: function(){
            dojo.addClass(this.containerNode, "seperator");
            dojo.style(this.handleNode, "background", "transparent none");
            dojo.style(this.handleNode, "zIndex", "100");
            dojo.style(this.containerNode, "zIndex", "1");
            this.inherited("postCreate", arguments);
        }
    });
});