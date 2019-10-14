/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';

      config.extraPlugins='inserthtml';

		config.language = 'it';

        config.toolbar = 'Basic';

        config.toolbar_Full =
        [
            ['Maximize', 'ShowBlocks', 'Source','-','Templates'],
            ['Cut','Copy','Paste','PasteText','PasteFromWord','-','SpellChecker', 'Scayt'],
            ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
            '/',
            ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
            ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
            ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
            ['Link','Unlink','Anchor'],
            '/',
            ['Image','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','CreateDiv','inserthtml'],
            ['Styles','Format','Font','FontSize'],
            ['TextColor','BGColor'],
            ['About']
        ];

        config.toolbar_Basic =
        [
            ['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink','-','About']
        ];
        
        config.toolbar_Frontend =
        [
            ['Bold', 'Italic', 'Underline','Strike'],
            ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
            ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo'],
            ['Find','Replace','-','SelectAll','RemoveFormat']
        ];
        
        config.toolbar_MyToolBar =
	        [
	            ['Cut','Copy','Paste','PasteText','-'],
	            ['Bold','Italic','Underline','Strike'],
	            ['NumberedList','BulletedList','-','Outdent','Indent'],
	            ['Link','Unlink','Anchor'],
	            ['Styles','Format']
	        ];        

};
