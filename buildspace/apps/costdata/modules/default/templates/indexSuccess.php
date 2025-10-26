<style>
.win {top: 0!important;}
</style>
<script type="application/javascript">
    require(["dojo/ready", "buildspace/apps/MasterCostData"], function(ready){
        ready(function(){
            buildspace.startLoading();
            buildspace.startup('MasterCostData', <?php echo json_encode($masterCostData)?>);
        });
    });
</script>
