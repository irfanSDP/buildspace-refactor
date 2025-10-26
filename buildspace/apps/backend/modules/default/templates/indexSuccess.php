<script type="application/javascript">
    require(["dojo/ready", "buildspace/ui", "buildspace/dialog"<?php if($data['bs_app_name']):?>, "buildspace/apps/<?php echo $data['bs_app_name']?>"<?php endif;?>], function(ready){
        ready(function(){
            dojo.xhrGet({
                url: 'myProfile',
                handleAs: 'json',
                load: function(data) {
                    buildspace.user                 = data;
                    buildspace.currencyAbbreviation = data.curr_abb;
                },
                error: function(error) {
                    //something is wrong somewhere
                }
            }).then(function(){
                buildspace.startLoading();
                buildspace.startup('BuildSpace', <?php echo json_encode($data)?>);
            });
        });
    });
</script>
