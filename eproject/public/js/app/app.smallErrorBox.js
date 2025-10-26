SmallErrorBox = {
    saved: function(title, message){
        $.smallBox({
            title : "<strong>" + title + "</strong>",
            content : "<i>" + message + "</i>",
            color : "#739E73",
            sound: false,
            iconSmall : "fa fa-save",
            timeout : 5000
        });
    },
    success:function(title, message){
        $.smallBox({
            title : "<strong>" + title + "</strong>",
            content : "<i>" + message + "</i>",
            color : "#739E73",
            sound: true,
            iconSmall : "fa fa-check",
            timeout : 5000
        });
    },
    formValidationError: function(title, message){
        $.smallBox({
            title : "<strong>" + title + "</strong>",
            content : "<i>" + message + "</i>",
            color : "#C46A69",
            sound: false,
            iconSmall : "fa fa-exclamation-triangle",
            timeout : 5000
        });
    },
    refreshAndRetry: function(){
        eproject.translate(function(translation){
            SmallErrorBox.formValidationError(translation["general.somethingWentWrong"], translation["general.refreshAndRetry"]);
        }, ["general.somethingWentWrong", "general.refreshAndRetry"]);
    }
}