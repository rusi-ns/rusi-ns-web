jQuery(document).ready( function($) {
	// ========== Begin "Add Exceptions" UI scripts ==========
	var item_path = new Object;
	var all_exceptiondata = [];
	var xid = -1;
	
	$('ul.categorychecklist ul.children li[style="display:none"]').parent().prevAll('input.menu-item-checkbox').next('span').html(' + ');
	
	$('input.menu-item-checkbox').nextAll('span').click( function(e) {
		$(this).parent().children('ul.children').children('li').toggle();
		
		if ( $(this).nextAll('ul.children').length ) {
			if ( $(this).html() == ' + ' ) {
				$(this).html(' &ndash; ');
			} else {
				$(this).html(' + ');
			}
		}
		e.preventDefault();
	});
	
	$(document).on('click', 'ul.categorychecklist li label', function(e) {
		$(this).prevAll('input.menu-item-checkbox').trigger('click');
	});
	
	$('.add-to-menu .waiting').hide();
	
	$("#pp_save_exceptions input.button-primary").click( function() {
		$('input[name="member_csv"]').val( $("input#member_csv").val() );
		$('input[name="group_name"]').val( $("input#group_name").val() );
		$('input[name="description"]').val( $("input#description").val() );
		$("#pp_new_x_submission_msg").html(ppRestrict.submissionMsg);
		$("#pp_new_x_submission_msg").show();
	});
	
	$('#agent-profile #submit').click( function(e) {
		// no need to submit selection inputs
		$('#pp_review_exceptions').hide();
		$('#pp_add_exception').remove();
	});
	
	$(document).on( 'click', "#pp_tbl_exception_selections .pp_clear", function(e) {
		var xid = $(this).closest('tr').find('input[name="pp_xid[]"]').val();

		if ( typeof all_exceptiondata[xid] != 'undefined' ) {
			delete all_exceptiondata[xid];
		}

		$(this).closest('tr').remove();
		e.stopPropagation();
	});
	
	$('.pp_clear_all').click( function() {
		$('.pp_clear').trigger('click');
	});
	
	$(".menu-item-checkbox").click( function() {
		item_checkbox_click( 'menu-item', $(this) );
	});
	
	// for group management exceptions
	$(".pp_group-item-checkbox").click( function() {
		item_checkbox_click( 'pp_group-item', $(this) );
	});
	
	// for net group management exceptions
	$(".pp_net_group-item-checkbox").click( function() {
		item_checkbox_click( 'pp_net_group-item', $(this) );
	});
	
	var item_checkbox_click = function( data_var, t ) {
		var expr = data_var + '\\[(\[^\\]\]*)';
		var re = new RegExp(expr);

		itemdata = t.closest('li').getItemData();
		
		if( t.closest('div.tabs-panel').parent().hasClass('hierarchical') ) {
			x_ajax_ui('get_item_path',x_update_item_path,itemdata['menu-item-object-id']);
		}
	}
	
	var timer;
	
	var x_update_item_path  = function(data,txtStatus) {
		var item_info = data.split('\r');
		item_path[ item_info[0] ] = item_info[1];
		$('input.menu-item-checkbox[value="' + item_info[0] + '"]').nextAll('label').attr('title', item_info[1]);
		clearTimeout(timer);
	}
	
	$(document).on('mouseenter', 'div.hierarchical ul.categorychecklist li label', function() {
		if ( $(this).attr('title') == undefined || $(this).attr('title') == '' ) {
			var that = this;
			timer = setTimeout(function(){
				itemdata = $(that).closest('li').getItemData();
				x_ajax_ui('get_item_path',x_update_item_path,itemdata['menu-item-object-id']);
			}, 500);
		}
	});
	
	$(document).on('mouseleave', 'ul.categorychecklist li label', function() {
		clearTimeout(timer);
	});
	
	//$('.submit-add-item-exception').live('click', function(e) {
	$(document).on('click', '.submit-add-item-exception', function(e) {
		x_add_item_exception('menu-item');
		return false;
	});
	
	// group management exceptions
	//$('.submit-add-pp_group-exception').live('click', function(e) {
	$(document).on('click', '.submit-add-pp_group-exception', function(e) {
		x_add_item_exception('pp_group-item');
		return false;
	});
	
	// net group management exceptions
	$(document).on('click', '.submit-add-pp_net_group-exception', function(e) {
		x_add_item_exception('pp_net_group-item');
		return false;
	});
	
	var x_add_item_exception = function(data_var) {
		$('div.pp-ext-promo').hide();
		
		var items = $('#menu-settings-column').find('.tabs-panel-active .categorychecklist li input:checked');
		
		if ( ! $('select[name="pp_select_x_operation"]').val() ) {
			$('#pp_item_selection_msg').html( ppRestrict.noOp );
			$('#pp_item_selection_msg').addClass('pp-error-note');
			$('#pp_item_selection_msg').show();
			return false;
		}
		
		if ( items.length == 0 ) {
			$('#pp_item_selection_msg').html( ppRestrict.noItems );
			$('#pp_item_selection_msg').addClass('pp-error-note');
			$('#pp_item_selection_msg').show();
			return false;
		}
		
		var newrow = '', trackdata = '', hier_type = false, assign_mode_inputs = '', item_caption = '', any_added = false, duplicate = false, child_assign = 0, item_assign = 1;

		if ( $('#pp_select_x_assign_for div').children().length > 1 )
			hier_type = true;

		if ( hier_type ) {
			if ( ! $('#pp_select_x_item_assign').is(':checked') )
				item_assign = 0;
			
			if ( $('#pp_select_x_child_assign').is(':checked') )
				child_assign = 1;
		}

		if ( child_assign ) { 
			if ( item_assign ) {
				var item_lbl = jQuery.trim( $('#pp_x_item_assign_label').html() );
				item_lbl = item_lbl.replace( ':', '' );
				scope_caption = item_lbl + ', ' + jQuery.trim( $('#pp_x_child_assign_label').html() );
			} else
				scope_caption = jQuery.trim( $('#pp_x_child_assign_label').html() );
		} else {
			if ( item_assign ) {
				scope_caption = jQuery.trim( $('#pp_x_item_assign_label').html() );
			} else {
				$('#pp_item_selection_msg').html( ppRestrict.noMode );
				$('#pp_item_selection_msg').addClass('pp-error-note');
				$('#pp_item_selection_msg').show();
				return false;
			}
		}
		
		var for_type = $('select[name="pp_select_x_for_type"]').val();
		var op = $('select[name="pp_select_x_operation"]').val();
		var via_type = $('select[name="pp_select_x_via_type"]').val();
		var mod_type = $('select[name="pp_select_x_mod_type"]').val();

		var for_type_caption = $('select[name="pp_select_x_for_type"] option:selected').html()
		var op_caption = $('select[name="pp_select_x_operation"] option:selected').html()
		var via_type_caption = $('select[name="pp_select_x_via_type"] option:selected').html()
		var mod_type_caption = $('select[name="pp_select_x_mod_type"] option:selected').html();
		var assign_for_captions = $('select[name="pp_select_x_mod_type"] option:selected').html()
		
		var conds = $('td.pp-select-x-status').find('input[name="pp_select_x_cond[]"]:checked');

		if ( conds.length == 0 ) {
			$('#pp_item_selection_msg').html( ppCred.noConditions );
			$('#pp_item_selection_msg').addClass('pp-error-note');
			$('#pp_item_selection_msg').show();
			return false;
		}
		$('.pp-save-exceptions').show();
		
		$(items).each(function(item_index){
			var t = $(this);
			var expr = data_var + '\\[(\[^\\]\]*)';
			var re = new RegExp(expr);
			
			// menu-item-title, menu-item-object-id
			itemdata = t.closest('li').getItemData();
			
			if ( typeof( itemdata['menu-item-object-id'] != 'undefined' ) ) {
				item_caption = itemdata['menu-item-title'];

				if ( hier_type ) {
					if ( typeof(item_path[ itemdata['menu-item-object-id'] ]) != 'undefined' )
						item_caption = item_path[ itemdata['menu-item-object-id'] ];
				}
				
				if ( child_assign ) { 
					if ( item_assign ) {
						var item_lbl = jQuery.trim( $('#pp_x_item_assign_label').html() );
						item_lbl = item_lbl.replace( ':', '' );
						selected_caption = item_lbl + ', ' + jQuery.trim( $('#pp_x_child_assign_label').html() );
					} else
						selected_caption = jQuery.trim( $('#pp_x_child_assign_label').html() );
				} else {
					if ( item_assign ) {
						selected_caption = jQuery.trim( $('#pp_x_item_assign_label').html() );
					} else {
						$('#pp_item_selection_msg').html( ppRestrict.noMode );
						$('#pp_item_selection_msg').addClass('pp-error-note');
						$('#pp_item_selection_msg').show();
						return false;
					}
				}
				
				$(conds).each(function(){
					id = agp_escape_id(this.id);
					var lbl = $('#pp_add_exception label[for="' + id + '"]');

					trackdata = for_type
						+ '|' + op
						+ '|' + via_type
						+ '|' + mod_type
						+ '|' + $('#' + id).val()
						+ '|' + itemdata['menu-item-object-id'];

					if ( $.inArray( trackdata, all_exceptiondata ) != -1 ) {
						duplicate = true;
					} else {
						xid++;
						all_exceptiondata[xid]=trackdata;
						
						if ( hier_type ) {
							assign_mode_inputs = '<input type="hidden" name="pp_add_exception[' + xid + '][for_item]" value="' + item_assign + '" />'
												+ '<input type="hidden" name="pp_add_exception[' + xid + '][for_children]" value="' + child_assign + '" />';
						} else
							assign_mode_inputs = '';
							
						newrow = '<tr>' 
							+ '<td>' + for_type_caption + '</td>'
							+ '<td>' + op_caption + '</td>'
							+ '<td>' + mod_type_caption + '</td>'
							+ '<td>' + selected_caption + '</td>'
							+ '<td>' + item_caption + '</td>'
							+ '<td>' + lbl.html() + '</td>'
							+ '<td><div class="pp_clear">' + ' <a href="javascript:void(0)" class="pp_clear">' + ppRestrict.clearException + '</a></div>'
							+ '<input type="hidden" name="pp_xid[]" value="' + xid + '" />'
							+ '<input type="hidden" name="pp_add_exception[' + xid + '][for_type]" value="' + for_type + '" />' 
							+ '<input type="hidden" name="pp_add_exception[' + xid + '][operation]" value="' + op + '" />' 
							+ '<input type="hidden" name="pp_add_exception[' + xid + '][via_type]" value="' + via_type + '" />' 
							+ '<input type="hidden" name="pp_add_exception[' + xid + '][mod_type]" value="' + mod_type + '" />'
							+ '<input type="hidden" name="pp_add_exception[' + xid + '][attrib_cond]" value="' + $('#' + id).val() + '" />'
							+ '<input type="hidden" name="pp_add_exception[' + xid + '][item_id]" value="' + itemdata['menu-item-object-id'] + '" />'
							+ assign_mode_inputs
							+ '</td>'
							+ '</tr>';

						$('#pp_tbl_exception_selections tbody').append(newrow);
						
						any_added = true;
					}
				});
			}
		});
		
		$("#pp_add_exception .menu-item-checkbox").prop('checked',false);
		
		if ( duplicate && ! any_added ) {
			$('#pp_item_selection_msg').html( ppRestrict.alreadyException );
			$('#pp_item_selection_msg').addClass('pp-error-note');
			$('#pp_item_selection_msg').show();
		} else {
			$('#pp_item_selection_msg').html(ppRestrict.pleaseReview);
			$('#pp_item_selection_msg').removeClass('pp-error-note');
			$('#pp_item_selection_msg').show();
		}

		return false;
	}
	
	var reload_operation = function() {
		if ( $('select[name="pp_select_x_for_type"]').val() ) {
			$('select[name="pp_select_x_for_type"] option.pp-opt-none').remove();  // @todo: review this
			x_ajax_ui('get_operation_options',draw_operations);
		} else
			$('.pp-select-x-operation').hide();
	}
	
	var reload_via_type = function() {
		if ( $('select[name="pp_select_x_operation"]').val() )
			x_ajax_ui('get_via_type_options',draw_via_types);
		else
			$('.pp-select-x-via-type').hide();
	}
	
	var reload_mod_type = function() {
		if ( $('select[name="pp_select_x_operation"]').val() ) {
			x_ajax_ui('get_mod_options',draw_mod_types);
		} else
			$('.pp-select-x-mod-type').hide();
	}
	
	var reload_assign_for = function() {
		if ( $('select[name="pp_select_x_for_type"]').val() )
			x_ajax_ui('get_assign_for_ui',draw_assign_for);
		else
			$('.pp-select-x-assign-for').hide();
	}
	
	var reload_status = function() {
		var op = $('select[name="pp_select_x_operation"]').val();
		var mod_type = $('select[name="pp_select_x_mod_type"]').val();
		if ( mod_type && op ) {
			x_ajax_ui('get_status_ui',draw_status);
			
			if ( 'include' == mod_type ) {
				$('input.add-to-top').show();
				$('input.add-to-top').parent().show();
			} else {
				$('input.add-to-top').hide();
				$('input.add-to-top').parent().hide();
			}
		} else
			$('.pp-select-x-status').hide();
		
		if ( 'include' == mod_type || ( ( 'exclude' == mod_type ) && ( 'associate' == op ) ) ) {
			$('td.pp-select-items input.menu-item-checkbox[value="0"]').closest('li').show();
		} else {
			$('td.pp-select-items input.menu-item-checkbox[value="0"]').closest('li').hide();
		}
	}
	
	$('select[name="pp_select_x_for_type"]').bind( 'change', reload_operation );
	
	$('select[name="pp_select_x_for_type"]').change( function() {
		$('.pp-select-items').hide();
		$('.pp-select-x-mod-type').hide();
		$('.pp-select-x-via-type').hide();
		$('.pp-select-x-status').hide();
		$('#pp_add_exception').css('width','auto');
	});
	
	$('select[name="pp_select_x_operation"]').bind( 'change', reload_via_type );
	$('select[name="pp_select_x_operation"]').bind( 'change', reload_mod_type );
	$('select[name="pp_select_x_operation"]').bind( 'change', reload_status );
	
	$('select[name="pp_select_x_mod_type"]').bind( 'change', reload_status );
	
	$('select[name="pp_select_x_via_type"]').bind( 'change', reload_status );
	$('select[name="pp_select_x_via_type"]').bind( 'change', reload_assign_for );
	
	$('select[name="pp_select_x_via_type"]').change( function() {
		$('#pp_add_exception .postbox').hide();	// @todo: review this

		if ( $(this).val() ) {
			var pp_via_type = $(this).val();

			$('#select-exception-' + pp_via_type).show();
			$('.pp-select-items').show();
		} else
			$('.pp-select-items').hide();

		$('#pp_add_exception').css('width', '100%');
		
		$('input.menu-item-checkbox').prop('checked', false);
	});
	
	var draw_operations = function(data,txtStatus) {
		sel = $('select[name="pp_select_x_operation"]');
		sel.html(data);
		sel.triggerHandler('change');
		$('.pp-select-x-operation').show();
		x_ajax_ui_done();
	}
	
	var draw_via_types = function(data,txtStatus) {
		sel = $('select[name="pp_select_x_via_type"]');
		sel.html(data);
		sel.triggerHandler('change');
		$('.pp-select-x-via-type').show();
		x_ajax_ui_done();
	}
	
	var draw_mod_types = function(data,txtStatus) {
		sel = $('select[name="pp_select_x_mod_type"]');
		sel.html(data);
		sel.triggerHandler('change');
		$('.pp-select-x-mod-type').show();
		x_ajax_ui_done();
	}
	
	var draw_assign_for = function(data,txtStatus) {
		dv = $('#pp_select_x_assign_for');
		dv.html(data);
		
		if ( dv.children().length > 1 )
			$('.pp-select-x-assign-for').show();
		else
			$('.pp-select-x-assign-for').hide();
			
		x_ajax_ui_done();
	}
	
	var draw_status = function(data,txtStatus) {
		dv = $('td.pp-select-x-status');
		dv.html(data);

		if ( dv.children().length > 1 )
			$('.pp-select-x-status').show();
		else
			$('.pp-select-x-status').hide();
			
		if ( $('.pp-select-x-status input:checkbox').length == 1 ) {
			$('.pp-select-x-status input:checkbox').prop('checked', true);
		}

		x_ajax_ui_done();
	}
	
	var x_ajax_ui = function(op,handler,item_id) {
		if ( 'get_item_path' != op ) {
			$('#pp_add_exception select').prop('disabled',true);
			$('#pp_add_exception_waiting').show();
		}
	
		if ( typeof item_id == 'undefined' )
			item_id = 0;
	
		var data = { 'pp_ajax_exceptions_ui': op, 'pp_for_type' : $('select[name="pp_select_x_for_type"]').val(), 'pp_operation': $('select[name="pp_select_x_operation"]').val(), 'pp_via_type': $('select[name="pp_select_x_via_type"]').val(), 'pp_mod_type': $('select[name="pp_select_x_mod_type"]').val(), 'pp_agent_id' : ppRestrict.agentID, 'pp_agent_type' : ppRestrict.agentType, 'pp_item_id' : item_id };
		$.ajax({url:ppRestrict.ajaxurl, data:data, dataType:"html", success:handler, error:x_ajax_ui_failure});
	}
	
	var x_ajax_ui_done = function() {
		$('#pp_add_exception select').prop('disabled',false);
		$('#pp_add_exception_waiting').hide();
		
		$.event.trigger({type: "pp_exceptions_ui"});
	}
	
	var x_ajax_ui_failure = function(data,txtStatus) {
		$('#pp_add_exception .waiting').hide();
		return;
	}
	
	var PPsearchTimer;
	$('.pp-quick-search').keypress(function(e){
		var t = $(this);

		if( 13 == e.which ) {
			PPupdateQuickSearchResults( t );
			return false;
		}

		if( PPsearchTimer ) clearTimeout(PPsearchTimer);

		PPsearchTimer = setTimeout(function(){
			PPupdateQuickSearchResults( t );
		}, 400);
	}).attr('autocomplete','off');

	var PPupdateQuickSearchResults = function(input) {
		var panel, params,
		minSearchLength = 2,
		q = input.val();

		if( q.length < minSearchLength ) return;

		panel = input.parents('.tabs-panel');
		params = {
			'action': 'pp-menu-quick-search',
			'response-format': 'markup',
			'menu': $('#menu').val(),
			'menu-settings-column-nonce': $('#menu-settings-column-nonce').val(),
			'q': q,
			'type': input.attr('name')
		};
		
		$('img.waiting', panel).show();

		$.post( ajaxurl, params, function(menuMarkup) {
			PPprocessQuickSearchQueryResponse(menuMarkup, params, panel);
		});
	}
	
	/**
	 * Process the quick search response into a search result
	 *
	 * @param string resp The server response to the query.
	 * @param object req The request arguments.
	 * @param jQuery panel The tabs panel we're searching in.
	 */
	var PPprocessQuickSearchQueryResponse = function(resp, req, panel) {
		var matched, newID,
		takenIDs = {},
		form = document.getElementById('nav-menu-meta'),
		pattern = new RegExp('menu-item\\[(\[^\\]\]*)', 'g'),
		$items = $('<div>').html(resp).find('li'),
		$item;

		if( ! $items.length ) {
			$('.categorychecklist', panel).html( '<li><p>' + ppItems.noResultsFound + '</p></li>' );
			$('img.waiting', panel).hide();
			return;
		}

		$items.each(function(){
			$item = $(this);

			// make a unique DB ID number
			matched = pattern.exec($item.html());

			if ( matched && matched[1] ) {
				newID = matched[1];
				while( form.elements['menu-item[' + newID + '][menu-item-type]'] || takenIDs[ newID ] ) {
					newID--;
				}

				takenIDs[newID] = true;
				if ( newID != matched[1] ) {
					$item.html( $item.html().replace(new RegExp(
						'menu-item\\[' + matched[1] + '\\]', 'g'),
						'menu-item[' + newID + ']'
					) );
				}
			}
		});

		$('.categorychecklist', panel).html( $items );
		$('img.waiting', panel).hide();
	}
	// ========== End "Add Exceptions" UI scripts ==========
	

	// ========== Begin "Edit Exception" Submission scripts ==========
	$('#pp_current_exceptions input').click( function(e) {
		$(this).closest('div.pp-current-type-roles').find('div.pp-exception-bulk-edit').show();
	});
	
	$('#pp_current_exceptions .pp_check_all').click( function(e) {
		$(this).closest('td').find('input[name="pp_edit_exception[]"][disabled!="true"]').prop('checked', $(this).is(':checked') );
	});
	
	var current_exceptions_ajax_done = function() {
		$('#pp_current_exceptions input.submit-edit-item-exception').prop('disabled',false);
		$('#pp_current_exceptions .waiting').hide();
	}
	
	var remove_exceptions_done = function(data,txtStatus) {
		current_exceptions_ajax_done();
		
		if ( ! data )
			return;
		
		var startpos = data.indexOf('<!--ppResponse-->');
		var endpos = data.indexOf('<--ppResponse-->');
		
		if ( ( startpos == -1 ) || ( endpos <= startpos ) )
			return;
		
		data = data.substr(startpos+17,endpos-startpos-17);

		var deleted_ass_ids = data.split('|');
		
		$.each(deleted_ass_ids, function(index, value) {
			cbid = $('#pp_current_exceptions input[name="pp_edit_exception[]"][value="' + value + '"]').attr('id');
			$('#' + cbid).closest('label').parent().remove();
			
			var ass_ids = value.split(','); // some checkboxes represent both an item and child exception_item
			for (i = 0; i < ass_ids.length; ++i) {
				$('#pp_current_exceptions label[class~="from_' + ass_ids[i] + '"]').parent().remove();
			}
		});
	}
	
	var edit_exceptions_done = function(data,txtStatus) {
		current_exceptions_ajax_done();
		
		if ( ! data )
			return;
			
		var startpos = data.indexOf('<!--ppResponse-->');
		var endpos = data.indexOf('<--ppResponse-->');
		
		if ( ( startpos == -1 ) || ( endpos <= startpos ) )
			return;
		
		data = data.substr(startpos+17,endpos-startpos-17);
		
		var edit_data = data.split('~');
		var operation = edit_data[0];
		var set_class = '';
		
		switch(operation) {
			case 'exceptions_propagate':
				set_class = 'role_both';
				break;
			case 'exceptions_unpropagate':
				set_class = '';
				break;
			case 'exceptions_children_only':
				set_class = 'role_ch';
				break;
			default:
				return;
		}

		var edited_eitem_ids = edit_data[1].split('|');

		$.each(edited_eitem_ids, function(index, value) {
			cbid = $('#pp_current_exceptions input[name="pp_edit_exception[]"][value="' + value + '"]').attr('id');
			$('#' + cbid).closest('div').find('label').attr('class',set_class);
			
			// temp workaround for Ajax UI limitation
			if ( ( 'exceptions_children_only' == operation ) || ( 'exceptions_unpropagate' == operation ) ) {
				$('#' + cbid).closest('div').find('input').prop('checked',false);
				$('#' + cbid).closest('div').find('input').prop('disabled',true);
				$('#' + cbid).closest('div').find('label').attr('title',ppRestrict.reloadRequired);
			}
		});
	}
	
	$('#pp_current_exceptions input.submit-edit-item-exception').click( function(e) {
		var action = $(this).closest('div.pp-current-type-roles').find('div.pp-exception-bulk-edit select').first().val();
	
		if ( ! action ) {
			alert(ppRestrict.noAction);
			return false;
		}
		
		var selected_ids = new Array();
		$(this).closest('div.pp-current-exceptions').find('input[type="checkbox"]:checked').each(function(){
			selected_ids.push( $(this).attr('value') );
		});

		$(this).prop('disabled',true);
		$(this).closest('div').find('.waiting').show();

		switch( action ) {
			case 'remove':
				ajax_submit('exceptions_remove',remove_exceptions_done,selected_ids.join('|'));
				break
			default:
				ajax_submit('exceptions_' + action,edit_exceptions_done,selected_ids.join('|'));
				break
		}

		return false;
	});
	
	var ajax_submit = function(op,handler,rids) {
		if ( ! rids )
			return;
	
		var data = { 'pp_ajax_submission': op, 'agent_type': ppRestrict.agentType, 'agent_id': ppRestrict.agentID, 'pp_eitem_ids' : rids };
		$.ajax({url:ppRestrict.ajaxurl, data:data, dataType:"html", success:handler, error:ajax_submit_failure});
	}
	
	var ajax_submit_failure = function(data,txtStatus) {
		return;
	}
	
	$(document).on('mouseenter', 'div.pp-current-type-roles label', function() {
		var func = function(lbl) {
			$(lbl).parent().find('a').show();
		}
		window.setTimeout( func, 300, $(this) );
	});
	
	// ========== End "Edit Exception" Submission scripts ==========
});

