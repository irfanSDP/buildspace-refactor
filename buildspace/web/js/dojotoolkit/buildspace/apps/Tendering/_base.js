define(["dojo/_base/declare", 'dojo/_base/lang', './Builder', "buildspace/widget/grid/cells/Formatter", 'dojo/i18n!buildspace/nls/Tendering'], function(declare, lang, Builder, GridFormatter, nls){
    return declare('buildspace.apps.Tendering', buildspace.apps._App, {
        win: null,
        init: function(args){
            this.win = new buildspace.widget.Window({
                title: nls.tendering + ' > ' + nls.projectList,
                onClose: dojo.hitch(this, "kill")
            });

            var formatter = GridFormatter(),
                store = new dojo.data.ItemFileWriteStore({
                    clearOnClose:true,
                    url:'tendering/getProjects'
                });

            this.projectListing = new buildspace.apps.Tendering.ProjectListing({
                region: 'center',
                gridOpts: {
                    structure: [
                        {name: '&nbsp;', field: 'id', width:'30px', styles:'text-align:center;',formatter: formatter.rowCountCellFormatter, filterable: false},
                        {name: nls.title, field: 'title', width:'auto', cellType:'buildspace.widget.grid.cells.Textarea', autoComplete: true},
                        {name: nls.reference, field: 'reference', width:'120px', styles:'text-align: center;', autoComplete: true},
                        {name: nls.country, field: 'country', width:'100px', styles:'text-align: center;', autoComplete: true},
                        {name: nls.state, field: 'state', width:'120px', styles:'text-align: center;', autoComplete: true},
                        {name: nls.status, field: 'status', width:'100px', styles:'text-align: center;', autoComplete: true},
                        {name: nls.created_at, field: 'created_at', width:'120px', styles:'text-align: center;', filterable: false}
                    ],
                    store: store,
                    deleteUrl: 'projectBuilder/projectListingDelete',
                    onRowDblClick: dojo.hitch(this, 'projectListingDblClick')
                }
            });

            this.win.addChild(this.projectListing);
            this.win.show();
            this.win.startup();
        },
        projectListingDblClick: function(e){
            var project = this.projectListing.grid.getItem(e.rowIndex);
            if(project && !isNaN(parseInt(String(project.id)))){

                if (parseInt(String(project.tender_type_id)) === buildspace.constants.TENDER_TYPE_PARTICIPATED) {
                    var pb = new dijit.ProgressBar({
                        value: 0,
                        title: "Importing Bills",
                        layoutAlign:"center"
                    });

                    var box = new dijit.Dialog({
                        content: pb,
                        style: "background:#fff;padding:5px;height:78px;width:280px;",
                        splitter: false
                    });
                    box.closeButtonNode.style.display = "none";
                    box._onKey = function(evt){
                        var key = evt.keyCode;
                        if (key == keys.ESCAPE) {
                            dojo.stopEvent(evt);
                        }
                    };
                    box.onHide = function() {
                        box.destroyRecursive();
                    };

                    this.importBillProgress(project, box, pb);

                } else {
                    return this.createBuilderWin(project);
                }
            }
        },
        importBillProgress: function(project, box, pb){
            var self = this;
            dojo.xhrPost({
                url: 'tendering/getImportTenderBillProgress',
                content: {
                    id: parseInt(String(project.id))
                },
                handleAs: 'json',
                sync: false,
                load: function(data) {
                    var totalImportedBills = parseInt(data.total_imported_bills);
                    var totalBills = parseInt(data.total_bills);

                    if(data.exists && totalBills > 0 && totalImportedBills != totalBills){
                        if(!box.open){
                            box.show();
                        }

                        box.set({title:"Importing "+totalImportedBills+"/"+totalBills+" Bills"});

                        var i = totalImportedBills / totalBills * 100;
                        pb.set({value: i});

                        setTimeout(function(){self.importBillProgress(project, box, pb);}, 5000);
                    }else{
                        if(box.open){
                            box.hide();
                        }
                        
                        return self.createBuilderWin(project);
                    }
                },
                error: function(error) {
                    if(box.open){
                        box.hide();
                    }
                }
            });
        },
        createBuilderWin: function(project){
            this.kill();
            this.project = project;
            this.win = new buildspace.widget.Window({
                title: nls.tendering + ' > ' + buildspace.truncateString(project.title, 100)  + ' (' + nls.status + '::' + project.status[0].toUpperCase() + ')',
                onClose: dojo.hitch(this, "kill")
            });

            this.win.addChild(new Builder({
                project: project
            }));
            this.win.show();
            this.win.startup();
        },
        makeTab: function(appName, title, pane){
            this.tabArea.addChild(pane);
            this.tabArea.selectChild(pane);

            pane.mod_info = {
                title: title,
                appName: appName
            };
        },
        kill: function(){
            if (this.win && typeof(this.win) != "undefined"){
                this.win.close();
            }
        }
    });
});
