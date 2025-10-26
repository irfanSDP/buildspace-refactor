var app_progressBar = {
    updateRate: 200,
    currentWidth: 0,
    toggle: function(){
        $('#progressBarModal').modal('toggle');
    },
    show: function(){
        $('#progressBarModal').modal('show');
    },
    hide: function(){
        $('#progressBarModal').modal('hide');
    },
    updateValue: function(value, milliseconds, callback){

        if(!callback) callback = function(){};

        var numberOfIntervals = (milliseconds < this.updateRate) ? 1 : (milliseconds/this.updateRate);

        var widthIncreasePerInterval = value/numberOfIntervals;

        this.currentWidth = 0;

        this.doUpdateValue(widthIncreasePerInterval, callback);
    },
    doUpdateValue:function(widthIncreasePerInterval, callback){
        var self = this;

        this.currentWidth += widthIncreasePerInterval;

        setTimeout(function(){
            $('[data-id=progressbar]').css('width', self.currentWidth + '%');

            if((self.currentWidth >= 100) || (self.currentWidth <= 0)) {
                callback();
                return;
            }

            self.doUpdateValue(widthIncreasePerInterval, callback);
        }, this.updateRate);
    },
    maxOut: function(milliseconds, callback){
        if( !milliseconds ) milliseconds = 0;
        this.updateValue(100, milliseconds, callback);
    },
    reset: function(){
        this.updateValue(0, 0);
    }
};

$('#progressBarModal').on('hide.bs.modal', function(){
    app_progressBar.reset();
});

$('#progressBarModal').on('show.bs.modal', function(){
    app_progressBar.reset();
});