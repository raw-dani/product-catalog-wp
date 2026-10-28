/**
 * Admin JavaScript for Rejaton Product Catalog
 */
(function($) {
	'use strict';

	$(document).ready(function() {

		// 1. Token insertion into WordPress Editor
		$('.rj-token-insert-btn').on('click', function(e) {
			e.preventDefault();
			var token = $(this).data('token');

			if (typeof wp !== 'undefined' && wp.media && typeof tinymce !== 'undefined' && tinymce.activeEditor && !tinymce.activeEditor.isHidden()) {
				tinymce.activeEditor.execCommand('mceInsertContent', false, token);
			} else {
				// Classic text mode textarea
				var contentArea = document.getElementById('content');
				if (contentArea) {
					var startPos = contentArea.selectionStart;
					var endPos = contentArea.selectionEnd;
					contentArea.value = contentArea.value.substring(0, startPos) + token + contentArea.value.substring(endPos, contentArea.value.length);
					contentArea.focus();
					contentArea.selectionStart = startPos + token.length;
					contentArea.selectionEnd = startPos + token.length;
				}
			}
		});

		// 2. Media Library for Product Gallery
		var galleryFrame;
		$('#rj_add_gallery_btn').on('click', function(e) {
			e.preventDefault();

			if (galleryFrame) {
				galleryFrame.open();
				return;
			}

			galleryFrame = wp.media({
				title: 'Pilih Foto untuk Galeri Produk Rejaton',
				button: { text: 'Tambahkan ke Galeri' },
				multiple: true
			});

			galleryFrame.on('select', function() {
				var selection = galleryFrame.state().get('selection');
				var currentIds = $('#rj_product_gallery_input').val() ? $('#rj_product_gallery_input').val().split(',') : [];

				selection.each(function(attachment) {
					var attJson = attachment.toJSON();
					var idStr = String(attJson.id);

					if (currentIds.indexOf(idStr) === -1) {
						currentIds.push(idStr);
						var thumbUrl = (attJson.sizes && attJson.sizes.thumbnail) ? attJson.sizes.thumbnail.url : attJson.url;

						var itemHtml = '<div class="rj-gallery-item" data-id="' + idStr + '">' +
							'<img src="' + thumbUrl + '" alt="" />' +
							'<button type="button" class="rj-gallery-remove">&times;</button>' +
							'</div>';
						$('#rj_gallery_container').append(itemHtml);
					}
				});

				$('#rj_product_gallery_input').val(currentIds.join(','));
			});

			galleryFrame.open();
		});

		// Remove image from gallery
		$('#rj_gallery_container').on('click', '.rj-gallery-remove', function(e) {
			e.preventDefault();
			var item = $(this).closest('.rj-gallery-item');
			var removeId = String(item.data('id'));
			var currentIds = $('#rj_product_gallery_input').val() ? $('#rj_product_gallery_input').val().split(',') : [];

			currentIds = currentIds.filter(function(id) {
				return id !== removeId;
			});

			$('#rj_product_gallery_input').val(currentIds.join(','));
			item.remove();
		});

		// 3. Dynamic Template Preview Refresh
		$('#rj_preview_product_select').on('change', function() {
			var productId = $(this).val();
			var editorContent = '';

			if (typeof tinymce !== 'undefined' && tinymce.get('content') && !tinymce.get('content').isHidden()) {
				editorContent = tinymce.get('content').getContent();
			} else {
				var textarea = document.getElementById('content');
				if (textarea) {
					editorContent = textarea.value;
				}
			}

			if (typeof rejaton_admin_vars !== 'undefined') {
				$('#rj_template_preview_output').css('opacity', '0.5');
				$.ajax({
					url: rejaton_admin_vars.ajax_url,
					type: 'POST',
					data: {
						action: 'rejaton_preview_template',
						nonce: rejaton_admin_vars.nonce,
						product_id: productId,
						content: editorContent
					},
					success: function(res) {
						$('#rj_template_preview_output').css('opacity', '1');
						if (res && res.success && res.data) {
							$('#rj_template_preview_output').html(res.data.html);
						}
					},
					error: function() {
						$('#rj_template_preview_output').css('opacity', '1');
					}
				});
			}
		});

	});
})(jQuery);
