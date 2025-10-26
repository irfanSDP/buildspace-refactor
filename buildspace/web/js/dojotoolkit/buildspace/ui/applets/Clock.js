define(['dojo/_base/declare',
    'dijit/Calendar',
    'dojo/date',
    'dojo/dom-attr',
    'buildspace/ui/Applet'], function(declare, Calendar, date, domAttr, Applet){
    return declare("buildspace.ui.applets.Clock", Applet, {
        //  summary:
        //      A clock applet with a drop-down calendar
        dispName: "Clock",
        postCreate: function(){
            domAttr.set(this.containerNode, "aria-live", "off");
            var calendar = new Calendar({});
            this.button = new dijit.form.DropDownButton({
                label: "loading...",
                dropDown: calendar
            }, this.containerNode);
            dojo.addClass(this.button._buttonNode, "noArrowButtonInner");
            var old = "";
            this.clockInterval = setInterval(dojo.hitch(this, function(){
                var p = dojo.date.locale.format(new Date(), {selector:"date", datePattern:"EEE hh:mm a" });
                if(p != old){
                    old=p;
                    this.button.set('label', p);
                }
            }), 1000);
            this.inherited("postCreate", arguments);
        },
        uninitialize: function(){
            clearInterval(this.clockInterval);
            this.inherited("uninitialize", arguments);
        }
    });
});