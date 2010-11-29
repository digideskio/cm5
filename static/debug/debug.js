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
