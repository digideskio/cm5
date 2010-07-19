$(document).ready(function(){

    // Backtrace
    $('ul.backtrace .source .file').click(function(){
        $(this).parent().find('.code').toggle();
    });
    
    // Var dump
    $('ul.backtrace .arguments .variable').click(function(){
        var varid = parseInt($(this).attr('var'));
        var dump = $(this).parent().parent().parent().find('.vardump[var=' + varid + ']');
        $('.vardump').hide();
        dump.show(); 
        $('.backtrace').addClass('two-columns');
    });
    
    // Var dump expander
    $('.vardump table .expander').click(function(){
        $(this).parent().parent().toggleClass('expanded');
    });
    
    $('.vardump > .dump.array > table').addClass('expanded');
});
