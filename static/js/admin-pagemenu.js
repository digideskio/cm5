
$(document).ready(function(){

    // Pages tree
    $('#pages_tree .resort-button').toggle(
        function(){
	        $('#pages_tree .sortable').sortable({
		        connectWith: '.sortable',
                helper: 'original',
                tolerance: 'pointer',
                opacity: 0.6,
                grid: [10, 10],
                distance: 5,
                update: function(event, ui) { 
                    if (ui.sender != null)
                        return;
                    var page_id = parseInt(ui.item.attr('id').replace(/page_/, ''));
                    var par = ui.item.parent().parent('li');
                    
                    if (par.length == 0)
                        parent_id = '';
                    else
                        parent_id = parseInt(par.attr('id').replace(/page_/, ''));
                    
                    $.post(page_id + '/+move/', { parent_id: parent_id});
                }
	        }).disableSelection();
        },
        function(){
            $('#pages_tree .sortable').sortable('disable');
        }
    ).toggleClass('pressed');
    
    // Page editor slug generator
    var request_translit = function(){
         $.get('../tools/transliterate', { 
            text: $('#page_editor input[name=title]').val()},
            function(data){
                $('#page_editor input[name=slug]').val(data);
            }
         );
    };

    if ($('#page_editor input[name=slug]').val() == '')
        $('#page_editor input[name=title]').change(request_translit);
	else
	{
	    $('#page_editor input[name=slug]').parent().append(
	        $('<span class="suggest button"/>')
	        .text('suggest')
	        .click(request_translit)
        );
	}
	
	// Page editor ckeditor
	CKEDITOR.replace($('#page_editor textarea')[0]);
	
	// Guard page
	var enable_page_guard = function(){
	    if (window.onbeforeunload != null)
	        return;
        window.onbeforeunload = function(){
            return 'You have not saved this article. Any changes you made will be lost!';
        };
	}
    $('#page_editor input[type=submit]').click(function(){ window.onbeforeunload = null; });
    $('#page_editor textarea[name=body]').change(enable_page_guard);
    $('#page_editor input[type=text]').change(enable_page_guard);
    $('#page_editor input[type=text]').change(enable_page_guard);
    $('#page_editor select').change(enable_page_guard);

});
