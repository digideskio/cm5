/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	config.language = 'en';
	config.uiColor = '#AADC6E';
    config.toolbar = [
    ['Source',],
    ['Preview', 'Maximize', 'ShowBlocks'],
    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
    ['Undo','Redo','-','Find','Replace'],
    '/',
    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    ['Link','Unlink','Anchor'],
    '/',
    ['Styles','Format','Font','FontSize'],
    ['TextColor','BGColor', 'RemoveFormat'],
    ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak'],
    ];
    config.scayt_autoStartup = false;
};
