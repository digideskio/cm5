/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	config.language = 'en';
//	config.uiColor = '#DCAA6E';
    config.height = '350px';
    config.width = '98%';
    config.toolbar = [
    ['Source',],
    ['Preview', 'Maximize', 'ShowBlocks'],
    ['Paste','PasteText','PasteFromWord','-', 'SpellChecker', 'Scayt'],
    ['Undo','Redo','-','Find','Replace'],
    ['Image', 'Table','HorizontalRule','Smiley','SpecialChar'],
    ['Link','Unlink','Anchor'],
    '/',

    '/',
    ['Format','Font','FontSize'],
    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
    ['TextColor','BGColor', 'RemoveFormat'],
    ];
    config.scayt_autoStartup = false;
    config.startupOutlineBlocks = true;
    config.entities_greek = false;
};
