<div style="padding:5px;">
    <h1><?php echo $projectSchedule->title?> (
        <?php if($type==ProjectSchedule::PRINT_TYPE_PLAN): ?>
            Plan Progress
        <?php else: ?>
            Actual Progress
        <?php endif; ?>
        )
    </h1>
    <br clear="all">
    <h1><?php echo $projectSchedule->ProjectStructure->MainInformation->title?></h1>
</div>

<div style="relative;padding:5px;height:32px;border-top:1px solid #e5e5e5;" class="noprint">
    <div class="printOptions" style="background:#fff!important;">
        <button onclick="window.print();" class="button" title="Print"><span class="ganttInline icon-16-container icon-16-print"></span> Print</button>
    </div>
    <div class="printOptions" style="background:#fff!important;">
        <button onclick="$('#workSpace').trigger('zoomPlus.gantt');" class="button" title="Zoom In"><span class="ganttInline icon-16-container icon-16-zoom_in"></span> Zoom In</button>
    </div>
    <div class="printOptions" style="background:#fff!important;">
        <button onclick="$('#workSpace').trigger('zoomMinus.gantt');" class="button" title="Zoom Out"><span class="ganttInline icon-16-container icon-16-zoom_out"></span> Zoom Out</button>
    </div>
    <div class="printOptions" style="background:#fff!important;">
        <button onclick="ge.gantt.showCriticalPath=!ge.gantt.showCriticalPath; ge.redraw();" class="button" title="Critical Path"><span class="ganttInline icon-16-container icon-16-random"></span> Critical Path</button>
    </div>
    <div class="printOptions" style="background:#fff!important;width:128px!important;">
        <select name="gantt_table-columns" multiple id="gantt_table-columns">
            <option value="code">Code</option>
            <option value="name">Name</option>
            <option value="start">Start</option>
            <option value="end">End</option>
            <?php if($type==ProjectSchedule::PRINT_TYPE_PLAN) :?>
                <option value="plan">Plan</option>
                <option value="actual">Actual</option>
            <?php endif; ?>
            <option value="dur">Duration</option>
            <option value="dep">Depends</option>
        </select>
    </div>
</div>

<div id="workSpace" style="padding:0;overflow:hidden;;position:relative;margin:0;"></div>

<script type="text/javascript">
    var ge;  //this is the fugly but very friendly global var for the gantt editor
    $(function() {

        $('#gantt_table-columns').multiselect({
            columns: 1,
            texts: {
                placeholder: 'Toggle Columns'
            },
            onLoad: function(){
                var optionsList = $(".ms-options input:checkbox");
                optionsList.prop('checked', true);
            },
            onOptionClick: function(el, opt){
                var $tbl = $(".gdfTable");
                var colToHide = $tbl.find(".colname-" + $(opt).val());
                $(colToHide).toggle();
            }
        });

        $.getJSON("<?php echo url_for('projectManagement/getNonWorkingDays?id='.$projectSchedule->id) ?>", function(nonWorkingDays) {
            // here starts gantt initialization
            ge = new GanttMaster();
            ge.projectScheduleStartDate = new Date("<?php echo $projectSchedule->start_date?>");
            ge.calendarSettings = {
                'satIsHoly': nonWorkingDays ? nonWorkingDays.sat_is_holy : true,
                'sunIsHoly': nonWorkingDays ? nonWorkingDays.sun_is_holy : true,
                'holidays': nonWorkingDays ? nonWorkingDays.holidays : ""
            };
            ge.resourceUrl = '<?php echo sfContext::getInstance()->getRequest()->getUriPrefix()?>/<?php echo sfContext::getInstance()->getRequest()->getRelativeUrlRoot()?>/css/gantt/res/';

            Date.prototype.incrementDateByWorkingDays=function (days) {
                //console.debug("incrementDateByWorkingDays start ",d,days)
                var q = Math.abs(days);
                while (q > 0) {
                    this.setDate(this.getDate() + (days > 0 ? 1 : -1));
                    if (!ge.isHoliday(this))
                        q--;
                }
                return this;
            };

            Date.prototype.distanceInWorkingDays= function (toDate){
                var pos = new Date(this.getTime());
                pos.setHours(23, 59, 59, 999);
                var days = 0;
                var nd=new Date(toDate.getTime());
                nd.setHours(23, 59, 59, 999);
                var end=nd.getTime();
                while (pos.getTime() <= end) {
                    days = days + (ge.isHoliday(pos) ? 0 : 1);
                    pos.setDate(pos.getDate() + 1);
                }
                return days;
            };

            var workSpace = $("#workSpace");
            workSpace.css({width:$(window).width() - 20,height:$(window).height() - 100});
            ge.init(workSpace);

            //simulate a data load from a server.
            loadGantt();

            $(window).resize(function(){
                workSpace.css({width:$(window).width() - 1,height:$(window).height() - workSpace.position().top});
                workSpace.trigger("resize.gantt");
            }).oneTime(150,"resize",function(){$(this).trigger("resize")});
        });

    });

    function loadGantt() {
        $.getJSON("<?php echo ($type==ProjectSchedule::PRINT_TYPE_PLAN) ?  url_for('TaskItemListPrint/'.$projectSchedule->id) : url_for('ActualTaskItemList/'.$projectSchedule->id) ?>", function(data) {
            ge.loadProject(data);
            ge.checkpoint(); //empty the undo stack
        });
    }
</script>

<div id="gantEditorTemplates" style="display:none;">
    <div class="__template__" type="TASKSEDITHEAD"><!--
        <table class="gdfTable" cellspacing="0" cellpadding="0">
            <thead>
                <tr style="height:40px">
                    <th class="gdfColHeader" style="width:16px;"></th>
                    <th class="gdfColHeader gdfResizable colname-code" style="width:30px;">Code</th>
                    <th class="gdfColHeader gdfResizable colname-name" style="width:300px;">Name</th>
                    <th class="gdfColHeader gdfResizable colname-start" style="width:80px;">Start</th>
                    <th class="gdfColHeader gdfResizable colname-end" style="width:80px;">End</th>
                    <?php if($type==ProjectSchedule::PRINT_TYPE_PLAN): ?>
                    <th class="gdfColHeader gdfResizable colname-plan" style="width:50px;">% Plan</th>
                    <th class="gdfColHeader gdfResizable colname-actual" style="width:52px;">% Actual</th>
                    <?php endif; ?>
                    <th class="gdfColHeader gdfResizable colname-dur" style="width:45px;">Dur.</th>
                    <th class="gdfColHeader gdfResizable colname-dep" style="width:38px;">Dep.</th>
                </tr>
            </thead>
        </table>
--></div>

    <div class="__template__" type="TASKROW"><!--
    <tr taskId="(#=obj.id#)" class="(#=obj.isParent()?'isParent':''#)" level="(#=level#)">
        <th class="gdfCell" align="center"><span class="taskRowIndex">(#=obj.getRow()+1#)</span></th>
        <td class="gdfCell colname-code">(#=obj.code?obj.code:''#)</td>
        <td class="gdfCell colname-name indentCell" style="padding-left:(#=obj.level*10#)px;">
            <div class="(#=obj.isParent()?'exp-controller expcoll exp':'exp-controller'#)" align="center"></div>
            (#=obj.name#)
        </td>
        <td class="gdfCell colname-start">(#=new Date(obj.start).format()#)</td>
        <td class="gdfCell colname-end">(#=new Date(obj.end).format()#)</td>
        <?php if($type==ProjectSchedule::PRINT_TYPE_PLAN): ?>
        <td class="gdfCell colname-plan (#=parseFloat(obj.progress) < parseFloat(obj.actualProgress) ? 'delay-task' : ''#)">(#=obj.progress#) %</td>
        <td class="gdfCell colname-actual">(#=obj.actualProgress#) %</td>
        <?php endif; ?>
        <td class="gdfCell colname-dur">(#=obj.duration#)</td>
        <td class="gdfCell colname-dep">(#=obj.depends#)</td>
    </tr>
--></div>

    <div class="__template__" type="TASKEMPTYROW"><!--
        <tr class="emptyRow" >
            <th class="gdfCell" align="center"></th>
            <td class="gdfCell colname-code"></td>
            <td class="gdfCell colname-name"></td>
            <td class="gdfCell colname-start"></td>
            <td class="gdfCell colname-end"></td>
            <?php if($type==ProjectSchedule::PRINT_TYPE_PLAN): ?>
            <td class="gdfCell colname-plan"></td>
            <td class="gdfCell colname-actual"></td>
            <?php endif; ?>
            <td class="gdfCell colname-dur"></td>
            <td class="gdfCell colname-dep"></td>
        </tr>
 --></div>
</div>