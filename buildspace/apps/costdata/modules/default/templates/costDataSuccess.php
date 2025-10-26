<style>
.win {top: 0!important;}
</style>
<script type="application/javascript">
    require(["dojo/ready", "buildspace/apps/CostData"], function(ready){
        ready(function(){
            buildspace.startLoading();
            buildspace.startup('CostData', {
                id: <?php echo json_encode($costData)?>['id'],
                costData: {
                    <?php foreach($costData as $key => $costDataInfo): ?>
                        <?php echo json_encode($key)?>: <?php echo json_encode($costDataInfo)?>,
                    <?php endforeach ?>
                },
                isEditor: <?php echo $isEditor ? 'true' : 'false' ?>
            });
        });
    });
</script>
