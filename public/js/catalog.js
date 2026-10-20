/**
 * Frontend JavaScript for Rejaton Product Catalog
 */
(function($) {
	'use strict';

	$(document).ready(function() {

		// 1. Mobile Filter Drawer Toggle
		$('#rj-open-mobile-filter').on('click', function() {
			$('#rj-catalog-sidebar').addClass('rj-drawer-open');
			$('body').css('overflow', 'hidden');
		});

		$('#rj-close-mobile-filter').on('click', function() {
			$('#rj-catalog-sidebar').removeClass('rj-drawer-open');
			$('body').css('overflow', '');
		});

		// 2. Single Product Gallery Thumbnail Click
		$('.rj-thumb-item').on('click', function() {
			var largeUrl = $(this).data('large');
			$('.rj-thumb-item').removeClass('active');
			$(this).addClass('active');

			var mainPhoto = $('#rj-main-photo');
			mainPhoto.fadeOut(150, function() {
				mainPhoto.attr('src', largeUrl).fadeIn(150);
			});
		});

		// 3. AJAX Filter & Sorting Handling
		function executeFilter(page) {
			page = page || 1;
			var formData = $('#rj-filter-form').serializeArray();

			formData.push({ name: 'action', value: 'rejaton_filter_products' });
			formData.push({ name: 'nonce', value: (typeof rejaton_ajax_vars !== 'undefined') ? rejaton_ajax_vars.nonce : '' });
			formData.push({ name: 'sort', value: $('#rj-sort-select').val() });
			formData.push({ name: 'paged', value: page });

			$('#rj-loading-spinner').show();

			$.ajax({
				url: (typeof rejaton_ajax_vars !== 'undefined') ? rejaton_ajax_vars.ajax_url : '/wp-admin/admin-ajax.php',
				type: 'POST',
				data: formData,
				dataType: 'json',
				success: function(res) {
					$('#rj-loading-spinner').hide();
					if (res.success && res.data) {
						$('#rj-products-grid').html(res.data.grid_html);
						$('#rj-pagination-wrapper').html(res.data.pagination_html);
						$('#rj-result-count').text(res.data.total_found);
						$('.rj-count-num').text(res.data.total_found);

						// Close mobile drawer if open
						$('#rj-catalog-sidebar').removeClass('rj-drawer-open');
						$('body').css('overflow', '');

						// Smooth scroll to top of grid
						$('html, body').animate({
							scrollTop: $('#rj-catalog-root').offset().top - 80
						}, 300);
					}
				},
				error: function() {
					$('#rj-loading-spinner').hide();
					// Fallback to normal form submit if AJAX fails
					$('#rj-filter-form').off('submit').submit();
				}
			});
		}

		// Trigger filter on form submit
		$('#rj-filter-form').on('submit', function(e) {
			e.preventDefault();
			executeFilter(1);
		});

		// Trigger on sorting change
		$('#rj-sort-select').on('change', function() {
			executeFilter(1);
		});

		// Pagination click
		$(document).on('click', '.rj-page-btn', function(e) {
			e.preventDefault();
			var pageNum = $(this).data('page');
			executeFilter(pageNum);
		});

		// Reset filter
		$('#rj-reset-filter').on('click', function(e) {
			e.preventDefault();
			$('#rj-filter-form')[0].reset();
			executeFilter(1);
		});

	});
})(jQuery);
