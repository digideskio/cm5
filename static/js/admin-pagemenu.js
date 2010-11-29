/*
 *  This file is part of CM5 <http://code.0x0lab.org/p/cm5>.
 *  
 *  Copyright (c) 2010 Sque.
 *  
 *  CM5 is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published 
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  CM5 is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with CM5.  If not, see <http://www.gnu.org/licenses/>.
 *  
 *  Contributors:
 *      Sque - initial API and implementation
 */


(function($) {
	jQuery.fn.cm5_editor = function(cmd, id) {

		// Ajax request for translit
		var request_translit = function() {
			$.get('tools/transliterate', {
				text : $('.ui-page-form input[name=title]').val()
			}, function(data) {
				$('.ui-page-form input[name=slug]').val(data);
			});
		};

		// Position suggest button in proper state
		var reposition_suggest_btn = function() {
			var title = $('.ui-page-form input[name=title]');
			var input = $('.ui-page-form input[name=slug]');
			if (input.length == 0)
				return;
			input.width(title.offset().left + title.innerWidth()
					- input.offset().left);

			var suggest = $(".suggest.button");
			suggest.css( {
				'position' : 'absolute'
			});
			var io = input.offset();
			var new_off = {
				left : io.left + input.innerWidth() - suggest.outerWidth() - 1,
				top : io.top
						+ ((input.outerHeight() - suggest.outerHeight()) / 2)
			};
			suggest.offset(new_off);
		}

		// Check if it is dirty
		var is_editor_dirty = function(){
			var pdiv = $('#page_editor');
			var editor = pdiv.data('cm5_editor').editor;
			return (pdiv.hasClass('dirty') || ((editor) && (pdiv.data('cm5_editor').editor.checkDirty())));
		}
		
		// Reset page guard
		var reset_page_guard = function() {
			$('#page_editor').removeClass('dirty');
			window.onbeforeunload = function() {
				if (is_editor_dirty())
					return 'You have not saved this article. Any changes you made will be lost!';
			};			

			// Guard page
			var page_set_dirty = function() {
				$('#page_editor').addClass('dirty');
			}

			$('#page_editor input[type=submit]').click(function() {
				window.onbeforeunload = null;
			});
			$('#page_editor textarea[name=body]').change(page_set_dirty);
			$('#page_editor input[type=text]').change(page_set_dirty);
			$('#page_editor input[type=text]').change(page_set_dirty);
			$('#page_editor select').change(page_set_dirty);
		}

		// Automatify editor
		var automatify_editor = function() {
			var pdiv = $('#page_editor');
			
			$('.ui-page-form input[name=slug]').parent().append(
					suggest = $('<span class="suggest button"/>').text(
							'suggest').click(request_translit));
			pdiv.bind('resize', reposition_suggest_btn);
			

			// Submit button
			var form = pdiv.find('form');
			form.submit(function(){
				pdiv.data('cm5_editor').editor.updateElement()
				data = form.serialize();
				action = form.attr('action');
				page_id = action.split('/');
				page_id = page_id[page_id.length - 2];
				form.find('input').attr('disabled', 'disabled');
				pdiv.find('.ui-page-form').addClass('loading');
				$.post(form.attr('action'), data, function(){
					loadpage(pdiv, page_id);
				})
				//alert();
				return false;
			})
						
			reposition_suggest_btn();
			reset_page_guard();
		}

		// Automatification destroy
		var destroy_automatification = function() {
			$('#page_editor').unbind('resize');
		}

		var loadpage = function(pdiv, page_id) {
			var options = pdiv.data('cm5_editor');
			if (options.editor) {
				destroy_automatification();
				options.editor.destroy();
			}

			// Clear html and redraw form
			pdiv.html('<span class="loading"></span>');			
			pdiv.load('editor/' + page_id + '/+form', function() {
				options.editor = CKEDITOR.replace($('#page_editor textarea')[0]);
				pdiv.data('cm5_editor', options);
				document.title = document.title.replace(/^([^|]+).*$/, '$1') + ' | Edit: ' 
					+ (pdiv.find('input[name=title]').length?pdiv.find('input[name=title]').val():'Home');
				automatify_editor();
			});
		}
		
		// ENTRY POINT
		return this.each(function() {
			var pdiv = $(this);
			
			if (cmd == 'loadpage')
				window.location = 'editor#' + id;
			else {
				// initilization
				pdiv.data('cm5_editor', {
					page_id: null,
					editor: null
				});
				
				$(window).bind('hashchange', function(event) {
					var options = pdiv.data('cm5_editor');
					var page_id = parseInt(location.hash.substr(1));
					if (page_id == options.page_id)
						return;	// No change
					
					if (is_editor_dirty())
						if (!confirm('You have unsaved changed that will be lost. Are you sure?'))
						{
							window.location = 'editor#' + options.page_id;
							return;
						}
							
					
					options.page_id = page_id;
					pdiv.data('cm5_editor', options);
					loadpage(pdiv, page_id);
				});
			}

		});
	};
})(jQuery);

$(document).ready(
	function() {
		// Pages tree
		$('#pages_tree .resort.button').toggle(
				function() {
					$('#pages_tree .sortable')
					.sortable(
							{
								connectWith : '.sortable',
								cancel : '.system-page',
								helper : 'original',
								tolerance : 'pointer',
								opacity : 0.2,
								grid : [ 10, 10 ],
								axis : 'y',
								update : function(event, ui) {

									if (ui.sender != null)
										return;

									var page_id = parseInt(ui.item
											.attr('id').replace(
													/page_/, ''));
									var order = ui.item.parent()
											.sortable('serialize');
									var par = ui.item.parent()
											.parent('li');

									if (par.length == 0)
										parent_id = '';
									else
										parent_id = parseInt(par
												.attr('id')
												.replace(/page_/,
														''));

									$.post('editor/' + page_id + '/+move/?'
											+ order, {
										parent_id : parent_id
									});
								}
							}).disableSelection()
							.sortable('enable');
					$('#pages_tree').toggleClass('sort-mode');
					$(this).toggleClass('pressed');
				}, function() {
					$('#pages_tree .sortable').sortable('disable');
					$('#pages_tree').toggleClass('sort-mode');
					$(this).toggleClass('pressed');
				});

			$('#pages_tree ul a.page').click(
					function() {
						page_id = parseInt($(this).parent('li').attr('id')
								.replace(/page_/, ''));
						$('#page_editor').cm5_editor('loadpage', page_id);
						window.location = 'editor#' + page_id;
						return false;
					});

			$('#page_editor').cm5_editor();
			if (!window.location.hash.length)
				window.location.hash = '#1';
			else
				$(window).hashchange();

		});
