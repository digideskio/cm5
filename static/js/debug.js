$(document).ready(function(){

    // Backtrace
    $('ul.backtrace .source .file').click(function(){
        $(this).parent().find('.code').toggle();
        console.log($(this).parent().find('.code'));
    });
});
