
function po_updateMovePopover() {
	jQuery.post(
	   popover.ajaxurl,
	   {
			'action':'popover_update_order',
			'_ajax_nonce': popover.ordernonce,
	      	'data': jQuery('#dragbody').tableDnDSerialize()
	   },
	   function(response){
		if(response != 'fail') {
			popover.ordernonce = response;
		} else {
			alert(popover.dragerror);
		}
	   }
	);
}

function po_setupReOrder() {

	//alert('here');

	//jQuery('tr.draghandle a.draganchor').click(function() {alert('click'); return false;});

	jQuery('#dragbody').tableDnD({
		onDragClass: 'dragging',
		dragHandle: 'check-drag',
		onDragStart: function( table, row ) {},
		onDrop: function( table, row ) {
			po_updateMovePopover();
		}
	});

}

function po_confirmDelete() {
	if(confirm(popover.deletepopover)) {
		return true;
	} else {
		return false;
	}
}

function po_MenuReady() {

	po_setupReOrder();

	jQuery('span.delete a').click(po_confirmDelete);

}

jQuery(document).ready(po_MenuReady);