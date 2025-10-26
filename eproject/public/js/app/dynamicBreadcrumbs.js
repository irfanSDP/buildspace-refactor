/*
USAGE
var breadcrumbs = new DynamicBreadcrumbs('#breadcrumbs-id');
breadcrumbs.addItem("label", 'labelId'[, onRemoveCallback[, onRemoveCallbackParams]]);

// onRemoveCallbackParams can be of any data type (e.g. [] or {}). onRemoveCallback should process it accordingly.
*/
class DynamicBreadcrumbs {
	constructor(selector) {
		this.breadcrumb = $(selector);
		this.breadcrumb.data('type', 'dynamic-breadcrumb');
		this.onRemoveCallbacks = [];
		this.onRemoveCallbackParams = [];
		this.crumbIds = [];

		var self = this;

		$(this.breadcrumb).on('click', '[data-crumb-id]', function(e){
			self.navigateTo(this.dataset['crumbId']);
		});
	}
	getLength(){ return this.crumbIds.length; }
	addItem(name, crumbId, onRemoveCallback, onRemoveCallbackParams){
		this.crumbIds.push(crumbId);
		this.onRemoveCallbacks.push(onRemoveCallback);
		this.onRemoveCallbackParams.push(onRemoveCallbackParams);

		$('<li class="breadcrumb-item text-info"><a href="javascript:void(0)" class="text-info" data-crumb-id="'+crumbId+'">'+name+'</a></li>')
			.appendTo(this.breadcrumb);
	}
	navigateTo(crumbId){
		var self = this;

		for(var i = (this.crumbIds.length-1); i>this.crumbIds.indexOf(crumbId);i--)
		{
			self.removeItem(this.crumbIds[i]);
		}
	}
	removeItem(crumbId){
		$('[data-crumb-id='+crumbId+']').parent().remove();

		var crumbIndex = this.crumbIds.indexOf(crumbId);

		if(typeof this.onRemoveCallbacks[crumbIndex] !== 'undefined'){
			if(typeof this.onRemoveCallbackParams[crumbIndex] !== 'undefined'){
				this.onRemoveCallbacks[crumbIndex](this.onRemoveCallbackParams[crumbIndex]);
			}
			else{
				this.onRemoveCallbacks[crumbIndex]();
			}
		}
		
		this.crumbIds.splice(crumbIndex, 1);
		this.onRemoveCallbacks.splice(crumbIndex, 1);
		this.onRemoveCallbackParams.splice(crumbIndex, 1);
	}
}