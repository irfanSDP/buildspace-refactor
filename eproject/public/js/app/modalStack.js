/**
 * Stack for Bootstrap Modals.
 * Used to avoid stacking Bootstrap Modal overlays on top of each other
 * which can be ugly and may require messing around with the z-index.
 * Simplifies showing and hiding different modals that lead to one another.
 * 
 * ==============================
 * BASIC USAGE:
 * ------------
 * var stack = new ModalStack();
 * 
 * stack.push('#modal1');
 * 
 * ==============================
 * ADDITIONAL METHODS:
 * -------------------
 * stack.remove('#modal1');
 * 
 * stack.add('#modal2', 1);
 * 
 * stack.addBefore('#modal1', '#modal0');
 * 
 * stack.addAfter('#modal1', '#modal1-5');
 */
class ModalStack {
    constructor() {
        this.stack = [];
    }
    push(modalSelector){
        var stackTop = this.stack[this.stack.length-1];

        this.add(modalSelector, this.stack.length);

        $(stackTop).modal('hide');

        $(modalSelector).modal('show');
    }
    _pop(){
        this.stack.pop();

        $(this.stack[this.stack.length-1]).modal('show');
    }
    remove(modalSelector){
        if(this.stack[this.stack.length-1] === modalSelector){
            $(modalSelector).modal('hide');
            return;
        }

        var index = this.stack.lastIndexOf(modalSelector);

        if(index > -1)
        {
            this.stack.splice(index, 1);
        }
    }
    add(modalSelector, index){
        var self = this;

        this.stack.splice(index, 0, modalSelector);

        $(modalSelector).on('hide.bs.modal', function (event) {
            if(self.stack[self.stack.length-1] === modalSelector){
                self._pop();
                // Unbind this event handler to prevent duplicates.
                $(this).off(event);
            }
        });
    }
    addAfter(modalSelector, target){
        var index = this.stack.lastIndexOf(target);
        if(index > -1){
            // If target is the last item.
            if(index === this.stack.length-1){
                this.push(modalSelector);
            }
            else{
                this.add(modalSelector, index+1);
            }
        }
    }
    addBefore(modalSelector, target){
        var index = this.stack.lastIndexOf(target);
        if(index > -1){
            this.add(modalSelector, index);
        }
    }
}