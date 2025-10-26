var arrayFx = {
    push  : function(array, item) {
        array.push(item);
    },
    remove: function(array, item) {
        var index = array.indexOf(item);
        if( index > -1 )
        {
            array.splice(index, 1);
        }
    },
    hasKey: function(array, key) {
        return (key in array);
    },
    init  : function(array, key) {
        if( !this.hasKey(array, key) )
        {
            array[ key ] = [];
        }
    },
    inArray: function(array, item)
    {
        return (array.indexOf(item) > -1);
    }
};

var checkboxFx = {
    uncheckAll: function(selector){
        var checkboxes = $(selector);
        checkboxes.each(function()
        {
            $(this).prop('checked', false);
        });
    },
    checkSelected: function(selector, values){
        var checkboxes = $(selector);
        checkboxes.each(function()
        {
            if(arrayFx.inArray(values, $(this).val()))
            {
                $(this).prop('checked', true);
            }
        });
    },
    disable: function(selector){
        var checkboxes = $(selector);
        checkboxes.each(function()
        {
            $(this).prop('disabled', true);
        });
    },
    enable: function(selector){
        var checkboxes = $(selector);
        checkboxes.each(function()
        {
            $(this).prop('disabled', false);
        });
    }
};

var treeFx = {
    getBranch: function(tree, key)
    {
        var branch;

        for(i in tree)
        {
            if(key == i) return tree[i];

            branch = this.getBranch(tree[i], key);

            if(branch != false) return branch;
        }

        return false;
    },
    getBranchKeys: function(tree)
    {
        var keys = [];

        for(i in tree)
        {
            keys.push(i);

            keys = keys.concat(this.getBranchKeys(tree[i]));
        }

        return keys;
    }
};