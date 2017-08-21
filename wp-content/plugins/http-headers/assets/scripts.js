(function ($, undefined) {
	$(function() {
		"use strict";

		$(document).on('change', 'select[name="hh_x_frame_options_value"]', function () {
			var $el = $('input[name="hh_x_frame_options_domain"]'),
				readOnly = $(this).find('option:selected').val() != 'allow-from';
			if ($el.length) {
				$el.prop('readOnly', readOnly).toggle(!readOnly);
			}
		}).on('change', 'select[name="hh_x_xxs_protection_value"]', function (e) {
			var $el = $('input[name="hh_x_xxs_protection_uri"]'),
				readOnly = $(this).find('option:selected').val() != '1; report=';
			if ($el.length) {
				$el.prop('readOnly', readOnly).toggle(!readOnly);
			}
		}).on('change', 'select[name="hh_x_powered_by_option"]', function () {
			var $el = $('input[name="hh_x_powered_by_value"]'),
				readOnly = $(this).find('option:selected').val() != 'set';
			if ($el.length) {
				$el.prop('readOnly', readOnly).toggle(!readOnly);
			}
		}).on('change', 'select[name="hh_access_control_allow_origin_value"]', function () {
			var $el = $('input[name="hh_access_control_allow_origin_url"]'),
				readOnly = $(this).find('option:selected').val() != 'origin';
			if ($el.length) {
				$el.prop('readOnly', readOnly).toggle(!readOnly);
			}
		}).on('change', '.http-header', function () {
			var $this = $(this),
				$el = $this.closest('tr').find('.http-header-value');
			
			if (!$el.length) {
				return;
			}
			
			if (Number($this.val()) === 1) {
				$el.prop('readOnly', false).removeAttr('readonly').removeClass('readonly');
			} else {
				$el.prop('readOnly', true).addClass('readonly');
			}
		}).on('change', 'input[name="hh_x_frame_options"]', function () {
			$('select[name="hh_x_frame_options_value"]').trigger('change');
		}).on('change', 'input[name="hh_x_powered_by"]', function () {
			$('select[name="hh_x_powered_by_option"]').trigger('change');
		}).on('change', 'input[name="hh_access_control_allow_origin"]', function () {
			$('select[name="hh_access_control_allow_origin_value"]').trigger('change');
		}).on('submit', '#frmIspect', function (e) {
			e.preventDefault();
			var $this = $(this);
			$.post($this.attr('action'), $this.serialize()).done(function (data) {
				$('#hh-result').html(data);
			});
			return false;
		}).on('change', '#authentication', function () {
			var $a = $('#box-authentication');
			if (this.checked) {
				$a.show();
			} else {
				$a.hide();
			}
		});
	});
})(jQuery);