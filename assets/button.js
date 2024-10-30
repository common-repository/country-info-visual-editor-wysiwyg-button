(function () {
	tinymce.create('tinymce.plugins.country_info_visual_editor_button', {
		init   : function (ed, url) {
			ed.addButton('country_code', {
				title: 'Enter 2 Letter Country Code',
				cmd  : 'country_info_command',
				image: url + '/globe.png'
			});
			ed.addCommand('country_info_command', function () {
				// TODO: Create better UI for the country input
				var country_code = prompt('Enter 2 Letter Country Code: ');
				// Simple validation. More validation in the back-end code
				if (2 !== country_code.length) {
					alert('Invalid country code');
					return;
				}
				var data = {
					'action'      : 'ci_get_country_info',
					'country_code': country_code
				};
				jQuery.post(ajaxurl, data, function (response) {
					ed.execCommand('mceInsertContent', 0, response);
				});
			});
		},
		getInfo: function () {
			return {
				longname: 'Country Info Visual Editor (WYSIWYG) Button',
				author  : 'Behzod Saidov',
				version : '1.0.1'
			};
		}
	});

	tinymce.PluginManager.add('country_info_visual_editor_button', tinymce.plugins.country_info_visual_editor_button);
})();