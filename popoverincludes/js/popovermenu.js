function po_updateMovePopover() {
	alert('moved');
}

function po_setupReOrder() {

	alert('here');

	jQuery('tr.draghandle a.draganchor').click(function() {alert('click'); return false;});

	jQuery("#dragtable tbody.dragbody").sortable({	items: "tr.draghandle",
											revert: true,
											handle: 'tr.draghandle a.draganchor',
											scroll:true,
											smooth:true,
											revert:true,
											//containment:'table.dragtable',
											opacity: 0.75,
											cursor:'move',
											tolerance: 'pointer',
											update: po_updateMovePopover
								});
}

function po_MenuReady() {

	po_setupReOrder();

}

jQuery(document).ready(po_MenuReady);