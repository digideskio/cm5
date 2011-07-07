
$(document).ready(function(){
	
	/* Make fieldset collapsible */
	$('fieldset.collapsable legend').live('click', function(){
		$(this).parent().toggleClass('expand');
	});
	
	/* Revertable entryies in history */
	$('fieldset[name=revisions] .history .edit:not(.disabled)').live('click', function(){
		var li = $(this).parent();
		$('fieldset[name=revisions] .history .edit').addClass('disabled');
		
		
		$.get($(this).attr('data-url'), function(data){
			if ($('#page_editor').cm5_editor('isdirty'))
				if (!confirm('There are unsaved changes that will be lost. Are you sure?')){
					$('fieldset[name=revisions] .history .edit').removeClass('disabled');
					return false;
				}
			
			$('fieldset[name=revisions] ul.history li').removeClass('current');
			li.addClass('current');
			$('#page_editor input[name=title]').val(data.title);
			$('#page_editor input[name=slug]').val(data.slug);
			$('#page_editor').data().cm5_editor.editor.setData(data.body, function(){
				$('#page_editor').cm5_editor('resetdirty');
				$('fieldset[name=revisions] .history .edit').removeClass('disabled');
			});			
		});
		return false;
	});
	
	/* Convert "VIEW" button to "preview" */
	$('#page_editor li[data-name=preview] .view').live('click', function(e){
		var view = $(this);
		var form  = $(this).parents('form');
		
		// Mangle form
		form.attr('target', '_blank');
		form.find('input[name*=revisions\\[type]').val('preview');
		var events = form.data('events');
		form.data('events', null);
		
		// Submit and open new window
		form.submit();
		
		// Bring things to previous state
		form.data('events', events);
		form.attr('target', null);
		form.find('input[name*=revisions\\[type]').val('user');
		
		return false;
	});
});