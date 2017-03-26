/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.skin = 'v2';
	config.toolbar = 'Cms';
	config.toolbar_Cms = [
		{ name: 'document', items : ['Source','-','NewPage'] },
		{ name: 'tools', items : [ 'Maximize', 'ShowBlocks','-'] },
		{ name: 'clipboard', items : ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
		{ name: 'editing', items : ['Find','Replace'] },
		'/',
		{ name: 'basicstyles', items : ['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
		{ name: 'paragraph', items : ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
		{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
		{ name: 'colors', items : [ 'TextColor','BGColor' ] }
	];
	config.pasteFromWordIgnoreFontFace = false;
	config.pasteFromWordRemoveStyle = true;
};

CKEDITOR.on('instanceReady', function(p){
	with(p.editor.dataProcessor.writer)
	{
		setRules('p', {
			indent:false,
			breakBeforeOpen : false,
			breakAfterOpen : false,
			breakBeforeClose : false,
			breakAfterClose : true
		});
		setRules('li', {
			indent:false,
			breakBeforeOpen : false,
			breakAfterOpen : false,
			breakBeforeClose : false,
			breakAfterClose : true
		});
		setRules('td', {
			indent:false,
			breakBeforeOpen : false,
			breakAfterOpen : false,
			breakBeforeClose : false,
			breakAfterClose : true
		});
	}
});
