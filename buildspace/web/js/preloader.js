var buildspace = {};
buildspace.intervals = {};
buildspace.timers = {};
(function() {
    require(
        [],
        function() {
            // display preloader
            var preloaderContainer = null;
            var frame = 0;
            var frameCounter = 0;
            var TOTAL_FRAMES = 12;
            var INITIAL_DELAY = 8;

            initCanvas();

            buildspace.timers.preloadTime = (new Date()).getTime();

            // create canvas
            function initCanvas(){
                preloaderContainer = document.getElementById('preloaderContainer');

                this.spinner = new Spinner({
                    lines: 9, // The number of lines to draw
                    length: 0, // The length of each line
                    width: 2, // The line thickness
                    radius: 4, // The radius of the inner circle
                    corners: 1, // Corner roundness (0..1)
                    rotate: 0, // The rotation offset
                    direction: 1, // 1: clockwise, -1: counterclockwise
                    color: '#fff', // #rgb or #rrggbb or array of colors
                    speed: 1, // Rounds per second
                    trail: 60, // Afterglow percentage
                    shadow: false, // Whether to render a shadow
                    hwaccel: false, // Whether to use hardware acceleration
                    className: 'spinner', // The CSS class to assign to the spinner
                    zIndex: 2e9, // The z-index (defaults to 2000000000)
                    top: 'auto', // Top position relative to parent in px
                    left: 'auto' // Left position relative to parent in px
                }).spin(preloaderContainer);

                var frameDelay = 1000/50;

                buildspace.intervals.siteLoader = setInterval(animatePreloader, frameDelay);

                animatePreloader();

                // load main library
                require(['buildspace/buildspace']);
            }

            function animatePreloader(){

                if(frameCounter > INITIAL_DELAY){

                    if(this.spinner && frame < TOTAL_FRAMES){
                        this.spinner.stop();
                        delete this.spinner;

                        this.spinner = new Spinner({
                            lines: 9, // The number of lines to draw
                            length: 0, // The length of each line
                            width: 8, // The line thickness
                            radius: frame < TOTAL_FRAMES ? frame : TOTAL_FRAMES, // The radius of the inner circle
                            corners: 1, // Corner roundness (0..1)
                            rotate: 0, // The rotation offset
                            direction: 1, // 1: clockwise, -1: counterclockwise
                            color: '#fff', // #rgb or #rrggbb or array of colors
                            speed: 1, // Rounds per second
                            trail: frame == TOTAL_FRAMES ? 60 : 0, // Afterglow percentage
                            shadow: false, // Whether to render a shadow
                            hwaccel: false, // Whether to use hardware acceleration
                            className: 'spinner', // The CSS class to assign to the spinner
                            zIndex: 2e9, // The z-index (defaults to 2000000000)
                            top: 'auto', // Top position relative to parent in px
                            left: 'auto' // Left position relative to parent in px
                        }).spin(preloaderContainer);
                    }

                    if(frame >= TOTAL_FRAMES){ frame = TOTAL_FRAMES; }
                    else { frame++; }
                }

                frameCounter++;
            }

        });

}).call(this);