
$(document).ready(function(){

    var editor; // The ckeditor instance

    // Pages tree
    $('#pages_tree .resort.button').toggle(
        function(){
	        $('#pages_tree .sortable').sortable({
	            connectWith: '.sortable',
	            cancel: '.system-page',
                helper: 'original',
                tolerance: 'pointer',
                opacity: 0.2,
                grid: [10,10],
                axis: 'y',
                update: function(event, ui) { 

                    if (ui.sender != null)
                        return;

                    var page_id = parseInt(ui.item.attr('id').replace(/page_/, ''));
                    var order = ui.item.parent().sortable('serialize');
                    var par = ui.item.parent().parent('li');

                    if (par.length == 0)
                        parent_id = '';
                    else
                        parent_id = parseInt(par.attr('id').replace(/page_/, ''));
                    
                    $.post(page_id + '/+move/?' + order, { parent_id: parent_id});
                }
	        }).disableSelection().sortable('enable');
	        $('#pages_tree').toggleClass('sort-mode');
	        $(this).toggleClass('pressed');
	        $('#pages_tree ul a.page').click(function(){return false;});
        },
        function(){
            $('#pages_tree .sortable').sortable('disable');
	        $('#pages_tree').toggleClass('sort-mode');
	        $(this).toggleClass('pressed');
	        $('#pages_tree ul a.page').unbind('click');
        }
    );
    
    // Page editor slug generator
    var request_translit = function(){
         $.get('../tools/transliterate', { 
            text: $('.ui-page-form input[name=title]').val()},
            function(data){
                $('.ui-page-form input[name=slug]').val(data);
            }
         );
    };

    if ($('.ui-page-form input[name=slug]').length == 0)
        ;
    else if ($('.ui-page-form input[name=slug]').val() == '')
    {
        $('.ui-page-form input[name=title]').change(request_translit);
        request_translit();
    }
	else
	{
	    $('.ui-page-form input[name=slug]')
	    .parent().append(
	        suggest = $('<span class="suggest button"/>')
	        .text('suggest')
	        .click(request_translit)
        );

	    function reposition_suggest_btn()
	    {
	        var title = $('.ui-page-form input[name=title]');
    	    var input = $('.ui-page-form input[name=slug]');
    	        input.width(title.offset().left + title.innerWidth() - input.offset().left);
    	        
	        var suggest = $(".suggest.button");
	        suggest.css({'position' : 'absolute'});
            var io = input.offset();
            var new_off = {
                left: io.left + input.innerWidth() - suggest.outerWidth() - 1,
                top: io.top + ((input.outerHeight() -  suggest.outerHeight()) /2)
            };
            suggest.offset(new_off);
        }
        $('#page_editor').bind('resize', reposition_suggest_btn);

	}
	
	// Page editor ckeditor
	if ($('#page_editor textarea').length)
    	editor = CKEDITOR.replace($('#page_editor textarea')[0]);
        
	//--- PAGE GUARD ---//
    var dirty = false;

    window.onbeforeunload = function(){
        if (dirty || editor.checkDirty())
            return 'You have not saved this article. Any changes you made will be lost!';
    };	
    
	// Guard page
	var page_set_dirty = function(){
	    dirty = true;	}
	    
    $('#page_editor input[type=submit]').click(function(){ window.onbeforeunload = null; });
    $('#page_editor textarea[name=body]').change(page_set_dirty);
    $('#page_editor input[type=text]').change(page_set_dirty);
    $('#page_editor input[type=text]').change(page_set_dirty);
    $('#page_editor select').change(page_set_dirty);

});
