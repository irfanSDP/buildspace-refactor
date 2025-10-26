<!-- new flot lib -->
<!-- <script src="{{ asset('js/plugin/flot-new/jquery.flot.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.colorhelper.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.canvas.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.categories.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.crosshair.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.errorbars.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.fillbetween.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.image.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.navigate.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.pie.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.resize.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.selection.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.stack.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.symbol.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.threshold.js') }}"></script>
<script src="{{ asset('js/plugin/flot-new/jquery.flot.time.js') }}"></script> -->

<script src="{{ asset('js/plugin/flot/jquery.flot.pie.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.cust.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.resize.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.fillbetween.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.orderBar.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.pie.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.time.min.js') }}"></script>
<script src="{{ asset('js/plugin/flot/jquery.flot.tooltip.min.js') }}"></script>

<script type="text/javascript">
    $(document).ready(function() {

        var data = [
            { label: "Design", data: '{{{ $projectsData['design'] }}}', color: "#006699" },
            { label: "Calling Tender", data: '{{{ $projectsData['callingTender'] }}}', color: "#001a66" },
            { label: "Closed Tender", data: '{{{ $projectsData['closedTender'] }}}', color: "#4d0066" },
            { label: "Post Contract", data: '{{{ $projectsData['postContract'] }}}', color: "#006600" },
            { label: "Completed", data: '{{{ $projectsData['completed'] }}}', color: "#00664d" }
        ];

        $.plot($("#project-pie-chart"), data, {
            series : {
                pie : {
                    show : true,
                    radius : 1,
                    label : {
                        show : true,
                        radius : 2 / 3,
                        formatter : function(label, series) {
                            return '<div style="font-size:11px;text-align:center;padding:4px;color:white;">' + series.label + '<br/>' + Math.round(series.percent) + '%</div>';
                        },
                        threshold : 0.1
                    }
                }
            },
            legend : {
                show : true,
                noColumns : 1, // number of colums in legend table
                labelFormatter : null, // fn: string -> string
                labelBoxBorderColor : "#000", // border color for the little label boxes
                container : null, // container (as jQuery object) to put legend in, null means default on top of graph
                position : "ne", // position of default legend container within plot
                margin : [50, 0], // distance from grid edge to default legend container within plot
                backgroundColor : null, // null means auto-detect
                backgroundOpacity : 0.5 // set to 0 to avoid background
            }
        });
    });
</script>