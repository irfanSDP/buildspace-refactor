define(["dojo", "dijit", "dijit/Tree", "dojo/_base/lang", "dijit/form/Textarea", "dijit/InlineEditBox", "dijit/Tooltip"], function(dojo, dijit, Tree, lang, Textarea, InlineEditBox, Tooltip){

    dojo.declare("_TreeInlineEditor", dijit._InlineEditor, {
        // summary:
        //		Modified version of internal InlineEditor class, needed by EditableTreeNode below

        postCreate: function(){
            this.inherited(arguments);

            // Stop onmousedown/onmouseup/onclick events because otherwise the propagate to the Tree which calls stopEvent(),
            // and then I can't position the caret in the editor by clicking it.
            // Also, stop keystrokes, so that trying to edit a field
            // (typing a value like "hello") doesn't cause navigation to other tree nodes
            dojo.forEach(["onmousedown", "onmouseup", "onclick", "onkeypress"], function(name){
                this.connect(this.domNode, name, function(evt){
                    evt.stopPropagation();
                });
            }, this);
        }
    });

    dojo.declare("TreeInlineEditBox", InlineEditBox,{
        tree:null,
        item:null,
        save: function(focus){
            this.inherited(arguments);
            var tree = this.tree,
                model = tree.model || tree.store,
                store = model.store || model,
                ww = this.wrapperWidget,
                value = ww.getValue();
            if(tree.ajaxSubmitUrl){
                var self = this;
                var tooltip = dijit.byId('TreeInlineEditBox_error-tooltip');
                if(tooltip){
                    tooltip.destroyRecursive();
                }
                var xhrArgs = {
                    url: tree.ajaxSubmitUrl,
                    content: {id: self.item.id, _csrf_token:self.item._csrf_token, attr:tree.labelAttr || "name", val:value},
                    handleAs: 'json',
                    load: function(resp) {
                        if(resp.success){
                            store.setValue(self.item,tree.labelAttr, value);
                            store.save();
                            tree.onSuccess(self.item.id);
                        }else{
                            tree._itemNodesMap[ self.item.id ][0].labelWidget.edit();
                            var errorMsg="";
                            for(var error in resp.errors){
                                errorMsg += resp.errors[error]+"<br/>";
                            };
                            var tooltip = new dijit.Tooltip({
                                id:'TreeInlineEditBox_error-tooltip',
                                _onHover:function(){return;},
                                _onUnHover: function(){return;},
                                position: ['below'],
                                selector: "div",
                                connectId:[tree._itemNodesMap[ self.item.id ][0].iconNode.parentNode],
                                label:'<span class="alert-text-error">'+errorMsg+'</span>'
                            });
                            tooltip.open(tree._itemNodesMap[ self.item.id ][0].iconNode.parentNode);
                        }
                    },
                    error: function(error) {}
                }
                dojo.xhrPost(xhrArgs);
            }
        }
    });

    dojo.declare("EditableTreeNode",[dijit._TreeNode], {
        // summary:
        //		Extension of dijit.TreeNode to let user edit labels of TreeNodes
        buildRendering: function(){
            this.inherited(arguments);

            var _this = this;
            this.labelWidget = new TreeInlineEditBox({
                editorWrapper: "_TreeInlineEditor",
                width: this.tree.editorWidth,
                _onClick: function(){
                    return;
                },
                tree: _this.tree,
                item: _this.item
            }, this.labelNode);
            this.labelWidget._onDblClick = this.labelWidget.constructor.prototype._onClick;
        },

        _onDblClick: function(evt){
            // call to original _onClick() method, to start editor (from a setTimeout())
            this.labelWidget._onDblClick(evt);
        },

        _setLabelAttr: function(val){
            this.labelWidget.set("value", val, false);// false to not fire onChange() code above (which is for user input)
        }
    });

    dojo.declare("dijit.EditableTree", [dijit.Tree], {
        // summary:
        //		Extension of dijit.Tree to let user edit labels of TreeNodes

        // editorWidth:
        //		Width of textbox for editing tree node values
        editorWidth: "100px",
        ajaxSubmitUrl:null,
        onSuccess: function(){},
        labelAttr:'name',
        _createTreeNode: function(/*Object*/ args){
            return new EditableTreeNode(args);
        },
        _onKeyPress: function(evt){
           //return;
        },
        _onKeyDown: function(evt){
            return;
        }
        // TODO: setup Menu with "Edit" option, for a11y
    });

    return dijit.EditableTree;
});