define('buildspace/apps/ProjectSummary/FooterForm',[
    'dojo/_base/declare',
    "dijit/Editor",
    "dijit/_editor/plugins/FontChoice",
    "dijit/_editor/plugins/AlwaysShowToolbar",
    "dojo/text!./templates/FooterForm.html",
    "dojo/i18n!buildspace/nls/ProjectSummary"
], function(declare, Editor, FontChoice, AlwaysShowToolbar, template, nls){

    return declare('buildspace.apps.ProjectSummary.FooterForm', dijit.layout.BorderContainer, {
        project: null,
        region: 'center',
        style: "outline:none;padding:0px;margin:0px;",
        formValues: {},
        postCreate: function(){
            this.inherited(arguments);

            var editorStyle, editorDisabled;
            if(this.project.status_id == buildspace.apps.ProjectSummary.ProjectStatus.STATUS_PRETENDER){

                var toolbar = new dijit.Toolbar({region:"top", style:"outline:none!important;border-left:0px;border-right:0px;border-top:0px;padding:2px;overflow:hidden;"});
                toolbar.addChild(
                    new dijit.form.Button({
                        label: nls.saveFooter,
                        iconClass: "icon-16-container icon-16-save",
                        style:"outline:none!important;",
                        onClick: dojo.hitch(this, 'submit')
                    })
                );

                this.addChild(toolbar);

                editorStyle = "width:40%;font:81.25% arial,helvetica,sans-serif;";
                editorDisabled = false;
            }else{
                editorStyle = "width:40%;border-top:0px;font:81.25% arial,helvetica,sans-serif;";
                editorDisabled = true;
            }

            this.leftEditor = new Editor({
                region:"left",
                disabled: editorDisabled,
                plugins:['fontSize', '|', 'bold','italic','underline','|', 'indent', 'outdent', 'justifyLeft', 'justifyCenter', 'justifyRight'],
                style: editorStyle,
                value: this.formValues.left_text
            });

            this.rightEditor = new Editor({
                region:"right",
                disabled: editorDisabled,
                plugins:['fontSize', '|', 'bold','italic','underline','|', 'indent', 'outdent', 'justifyLeft', 'justifyCenter', 'justifyRight'],
                style: editorStyle,
                value: this.formValues.right_text
            });

            this.addChild(this.leftEditor);

            this.addChild(this.rightEditor);
        },
        submit: function(){
            var pb = buildspace.dialog.indeterminateProgressBar({
                title:nls.saving+'. '+nls.pleaseWait+'...'
            });

            pb.show();

            dojo.xhrPost({
                url: 'projectSummary/footerUpdate',
                content: { id: this.project.id, 'project_summary_footer[project_structure_id]': this.project.id, 'project_summary_footer[left_text]': this.leftEditor.value, 'project_summary_footer[right_text]': this.rightEditor.value, 'project_summary_footer[_csrf_token]': this.formValues._csrf_token },
                handleAs: 'json',
                load: function(resp) {
                    pb.hide();
                },
                error: function(error){
                    pb.hide();
                }
            });
        }
    });
});