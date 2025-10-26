<style>
.win {top: 0!important;}
</style>
<script type="application/javascript">
    require(["dojo/ready", "buildspace/apps/Editor"], function(ready){
        ready(function(){
            buildspace.startLoading();
            buildspace.startup('Editor', {
                pid: <?php echo $eproject->id?>
            });
        });
    });
</script>
