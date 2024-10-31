function scwriterWrapper( $ ) {
	let scwriter = {

		presets: {},
		message_timeout: null,
		editor: null,

		init: function () {
			scwriter.initListeners();
			scwriter.initPresets();
			scwriter.initSelect();
			scwriter.initLimitInputWords();
			scwriter.initEditor();

		},
		
		initPresets() {

			if ( $('.scwriter-presets').length ) {

				if ( $('#preset_id option').length == 1 ) {
					$('.scwriter-presets-btn-delete').prop('disabled', true);
				}
				$('.scwriter-presets-btn-save').prop('disabled', true);

				if ( typeof scwriter_presets !== 'undefined' && scwriter_presets.length > 0 ) {
					scwriter.presets = scwriter_presets;
					if ( scwriter.presets.length > 0 ){
						for (let key in scwriter.presets) {
							let preset = scwriter.presets[key];
							let isSelected = scwriter_default_preset == preset.preset_id;
							let selected = isSelected ? 'selected' : '';
							$('#preset_id').append(`<option value="${preset.preset_id}" ${selected}>${preset.preset_name}</option>`);
							if ( isSelected ) {
								scwriter.usePreset(preset);
							}
						}
					}
				} else {
					scwriter.usePreset(scwriter_default_preset_data, true);
				}

				scwriter.checkDeleteAvailabe();
			}

		},

		setSelectValue(selectId, value) {
			const selectElement = document.getElementById(selectId);
			const normalizedValue = value.toLowerCase();
			let found = false;
			for (let option of selectElement.options) {
				if (option.value.toLowerCase() === normalizedValue) {
					selectElement.value = option.value;
					found = true; 
					break;
				}
			}
			if (!found) {
				selectElement.value = '';
			}
		},

		usePreset( preset, useDefaultValues = false ) {
			for (var key in preset) {
				if (preset.hasOwnProperty(key) && key != 'preset_id') {
					if ($('#' + key).length) {
						let $element = $('#' + key);
						let newValue = preset[key];
						if ( useDefaultValues && ['table_contents_css_class', 'table_contents_title'].includes(key) ){
							let table_contents_title = scwriter_table_of_contents[Math.floor(Math.random() * scwriter_table_of_contents.length)];
							let table_contents_css_class = table_contents_title.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
							$('#table_contents_title').val(table_contents_title).trigger('change');
							$('#table_contents_css_class').val(table_contents_css_class).trigger('change');
						} else {
							if ( $element.is('input[type="checkbox"]') ) {
								let checked = newValue == '1';
								$element.prop('checked', checked).trigger('change');
							} else if ( $element.is('select') ) {
								scwriter.setSelectValue(key, newValue);
							} else {
								$element.val(newValue).trigger('change');
							}
						}
					}
				}
			}

		},

		initLimitInputWords() {
    
			if ( $('.scwriter-limit-words-input').length ) {
				$('.scwriter-limit-words-input').each(function(){
					scwriter.limitInputWords($(this));
				});

				$('.scwriter-limit-words-input').on('input', function(){
					scwriter.limitInputWords($(this));
				});
			}
    
  		},

		limitInputWords( $input ) {

			let wordsLimit = $input.data('limit') ? $input.data('limit') : 100;
			
			let text = $input.val();
			let words = text.split(/\s+/);
			let count = text ? words.length : 0;

			if (count > wordsLimit) {
				const limitedText = words.slice(0, wordsLimit).join(' ');
				count = wordsLimit;
				$input.val(limitedText);
			}

			let $count = $input.closest('.scwriter-limit-words').find('.scwriter-limit-words-count');
			$count.empty();
			$count.append(`${count} / ${wordsLimit}`);

		},

		checkDeleteAvailabe: function() {

			let isDeleteAvailabe = false;

			if ( $('#preset_id option').length > 1 ) {
				isDeleteAvailabe = true;
			}

			$('.scwriter-presets-btn-delete').prop('disabled', !isDeleteAvailabe);

		},

		initSelect: function() {

			if ( $('.scwriter-select2').length ) {
				$('.scwriter-select2').select2();
			}

		},

		validateInput(inputVal, isRequired = true, onlyRequired = false, allowDots = true) {
			if (!inputVal.trim() && isRequired) {
				return false;
			}
		
			if (!isRequired && inputVal.trim().length === 0) {
				return true;
			}
		
			if (onlyRequired && isRequired && inputVal.trim().length > 0) {
				return true;
			}
		
			// Define the base character set including Unicode support
			let charSet = "\\p{L}\\p{N}\\s,\\-\'\’\"";
		
			if (allowDots) {
				charSet += "\\.";
			}
		
			// Construct the regular expression dynamically with Unicode support
			const alphanumericRegex = new RegExp(`^[${charSet}]+$`, 'u');
		
			// Validate the input
			return alphanumericRegex.test(inputVal);
		},

		togglePopup: function( action, popupName = '', popupContent = '' ) {

			if ( action == 'close' ){
				$('.scwriter-popup').removeClass('--show');
				$('.scwriter-popup-content-inner').empty();
			} else {
				$('.scwriter-popup').addClass('--show');
				if ( popupName ) {
					let html = $('.' + popupName).html();
					$('.scwriter-popup-content-inner').append( html );
				} else if ( popupContent ) {
					$('.scwriter-popup-content-inner').append( popupContent );
				}
			}

		},

		initListeners: function() {

			$('.scwriter-post-action').on('click', function(e){
				e.preventDefault();

				if ( confirm($(this).data('confirm')) ) {
					let post_id = $(this).data('postid');
					let type = $(this).data('type');
					let $this = $(this);
					let $allBtns = $('.scwriter-post-action');

					$allBtns.addClass('--is-doing');
					$this.addClass('--is-loading');

					jQuery.ajax({
						type: "post",
						dataType: "json",
						url: scwriter_ajax.url,
						data: {
							action: 'scwriter_post_action',
							type: type,
							post_id: post_id
						},
						success: function (response) {
							$allBtns.removeClass('--is-doing');
							$this.removeClass('--is-loading');
							if ( response.error ) {
								scwriter.showMessage('error', response.error_message);
							} else {
								scwriter.showMessage('success', response.message);

								setTimeout(() => {
									window.location.reload();
								}, 800);
							}
						},
						error: function () {
							$allBtns.removeClass('--is-doing');
							$this.removeClass('--is-loading');
							scwriter.showMessage('error', 'Something went wrong, try it again');
						}
					});
				}

			});

			$('.enable_preview_outline').on('change', function(){
				let isChecked = $(this).prop('checked');
				let type = isChecked ? 'draft' : 'article';
				$('.scwriter-post-create').attr('data-type', type);

				if ( $('.scwriter-post-create').data('article_id') ) {
					$('.scwriter-outline-holder').toggleClass('--hidden', isChecked);
				} else {
					$('.scwriter-outline-holder').addClass('--hidden');
				}
			});

			$('.scwriter-wizard-open').on('click', function(e){
				e.preventDefault();
				$(this).closest('.scwriter-presets-row').slideUp(800, function(){
					$('.scwriter-form-wizard[data-step=scwriter]').fadeIn();
				});
			});

			$('.scwriter-wizard-connect').on('click', function(e){
				e.preventDefault();
				let $step = $(this).closest('.scwriter-wizard-step');
				$step.addClass('--is-connecting');
				let nonce = $(this).data('nonce');
				let action = 'scwriter_connect';
				
				jQuery.ajax({
					type: "post",
					dataType: "json",
					url: scwriter_ajax.url,
					data: {
						nonce: nonce,
						action: action
					},
					success: function (response) {
						$step.removeClass('--is-connecting');
						if ( response.error ) {
							scwriter.showMessage('error', response.error_message);
						} else {
							scwriter.showMessage('success', response.message);
							$step.slideUp(1000, function(){
								$step.next().fadeIn();
							});
						}
					},
					error: function () {
						$step.removeClass('--is-connecting');
						scwriter.showMessage('error', 'Something went wrong, try it again');
					}
				});
			});

			$('.scwriter-tabs-heading-btn').on('click', function(e){
				e.preventDefault();
				
				let tab = $(this).data('tab');

				$('.scwriter-tabs-heading-btn').removeClass('active');
				$(this).addClass('active');

				$('.scwriter-tabs-content').removeClass('active');
				$('.scwriter-tabs-content[data-tab="' + tab + '"]').addClass('active');

			});

			$('#preset_id').on('change', function(){
				if ( scwriter.presets.length > 0 ) {
					let preset_id = $(this).val();
					let neededPreset = scwriter.presets.find(item => item.preset_id === preset_id);
					if ( neededPreset ) {
						scwriter.usePreset( neededPreset );

						scwriter.cancelDraft();
						$('.scwriter-enable-outline-holder').removeClass('--hidden');
					}
				} 
			});

			$('.scwriter-presets-settings, .scwriter-form').find('input, textarea, select').on('change', function(){
				$('.scwriter-presets-btn-save').prop('disabled', false);
			});

			$('.scwriter-validate-it').on('change', function(){

				let $error = $(this).closest('.scwriter-presets-row').find('.scwriter-input-errors');

				let currentValue = $(this).val();

				let inputType = '';
				if ( $(this).is('select') ) {
					inputType = 'select';
				} else if ( $(this).is('textarea') ) {
					inputType = 'textarea';
				} else {
					inputType = $(this).attr('type');
				}

				let errors = [];
				if ( inputType == 'number' ){
					currentValue = parseInt(currentValue);
					let min = parseInt($(this).attr('min'));
					let max = parseInt($(this).attr('max'));
					let isRequired = $(this).prop('required');
					if ( isRequired && !currentValue ){
						errors.push('Please fill in this field');
					} else if ( currentValue < min || currentValue > max ) {
						errors.push(`Value should be between ${min} and ${max}`);
					}
				} else if ( inputType == 'textarea' || inputType == 'text' ) {
					let isRequired = $(this).prop('required');
					if ( isRequired && !currentValue ){
						errors.push('Please fill in this field');
					}
				} else {
					let isRequired = $(this).prop('required');
					if ( isRequired && !currentValue ){
						errors.push('Please fill in this field');
					}
				}

				$error.empty();
				if ( errors.length ) {
					$error.append( errors.join('<br>') );
				}

			});

			$('.scwriter-presets-btn-save').on('click', function(e){
				e.preventDefault();
				let $form = $('.scwriter-presets-settings').length ? $('.scwriter-presets-settings') : $('.scwriter-form');
				$('.form-messages').empty();

				if ( $('#primary_keyword').length ) {
					$('#primary_keyword').prop('required', false);
				}

				$('.scwriter-validate-it').trigger('change');

				let errorElement = $('.scwriter-input-errors').filter(function() {
					return $(this).text().trim() !== '';
				}).first(); 
			
				if ( errorElement.length > 0 ) {
					$('html, body').animate({
						scrollTop: errorElement.closest('.scwriter-presets-row').offset().top - 50
					}, 800);
				}

				if ( !$form.is(':valid') && errorElement.length == 0 ) {
					$form.get(0).reportValidity();
				} else if ( errorElement.length == 0 ) {
					let formValues = {};

					$form.find('input, select, textarea').each(function() {
						if ( ['primary_keyword'].includes($(this).attr('name')) ) {
							return;
						}
						if ($(this).is(':visible') || $(this).attr('type') === 'hidden') {
							let newValue = $(this).val();
							if ( $(this).attr('type') == 'checkbox' ) {
								newValue = $(this).prop('checked') ? '1' : '0';
							}
							formValues[$(this).attr('name')] = newValue;
						}
					});
					
					$(this).addClass('--is-loading');
					$('.scwriter-presets').addClass('--is-loading');

					let data = {
						action: 'scwriter_save_preset',
						data: formValues
					};

					jQuery.ajax({
						type: "post",
						dataType: "json",
						url: scwriter_ajax.url,
						data: data,
						success: function (response) {
							$('.--is-loading').removeClass('--is-loading');
							if ( response.error ) {
								scwriter.showMessage('error', response.error_message);
							} else {
								scwriter.showMessage('success', response.message);
								if ( response.preset_id !== '' ) {

									$('#preset_id :selected').val(response.preset_id);
									$('#preset_id').select2('destroy');
									$('#preset_id').select2();

									let newFormValues = formValues;
									newFormValues.preset_id = response.preset_id;
									delete newFormValues['_wp_http_referer'];
									delete newFormValues.scwriter_update_preset_nonce
									scwriter.presets.push(newFormValues);

								} else {

									let preset_id = $('#preset_id').val();
									const index = scwriter.presets.findIndex(item => item.preset_id === preset_id);
									if (index !== -1) {
										scwriter.presets[index] = { ...scwriter.presets[index], ...formValues };
									}


								}
							}
	
						},
						error: function () {
							$('.--is-loading').removeClass('--is-loading');
							scwriter.showMessage('error', 'Something went wrong, try it again');
						}
					});
					
				}

				if ( $('#primary_keyword').length ) {
					$('#primary_keyword').prop('required', true);
				}

			});

			$('.scwriter-presets-btn-edit').on('click', function(e){
				e.preventDefault();
				scwriter.togglePopup('open', 'scwriter-edit-preset');
				let presetName = $('#preset_name').val();
				$('.scwriter-popup .scwriter-current-preset-name').val(presetName);
			});

			$('.scwriter-presets-btn-delete').on('click', function(e){
				e.preventDefault();
				let userConfirmed = confirm($(this).data('message'));

				if ( userConfirmed ) {
					let preset_id = $('#preset_id').val();
					let data = {
						action: 'scwriter_delete_preset',
						preset_id: preset_id,
						data: {
							scwriter_nonce_field: $('#scwriter_nonce_field').val()
						}
					};

					$(this).addClass('--is-loading');
					$('.scwriter-presets').addClass('--is-loading');

					jQuery.ajax({
						type: "post",
						dataType: "json",
						url: scwriter_ajax.url,
						data: data,
						success: function (response) {
							$('.--is-loading').removeClass('--is-loading');
							if ( response.error ) {
								scwriter.showMessage('error', response.error_message);
							} else {
								scwriter.showMessage('success', response.message);
								if ( response.preset_id !== '' ) {
									$('#preset_id :selected').remove();
									$('#preset_id').select2('destroy');
									$('#preset_id').val(response.preset_id).trigger('change');
									$('#preset_id').select2();
								} else {
									delete scwriter.presets[preset_id];
								}
							}
							scwriter.checkDeleteAvailabe();
	
						},
						error: function () {
							$('.--is-loading').removeClass('--is-loading');
							scwriter.showMessage('error', 'Something went wrong, try it again');
						}
					});
				}
			});
			
			$('.scwriter-presets-create').on('click', function(e){
				e.preventDefault();
				let userConfirmed = confirm($(this).data('message'));
				if ( userConfirmed ) {
					scwriter.togglePopup('open', 'scwriter-add-preset');
				}
			});

			$('.scwriter-popup-close, .scwriter-popup-overlay').on('click', function(e){
				e.preventDefault();
				scwriter.togglePopup('close');
			});

			$('.scwriter-popup').on('click', '.scwriter-add-new-preset', function(e){
				e.preventDefault();
				let inputVal = $('.scwriter-popup .scwriter-new-preset-name').val();
				if ( !scwriter.validateInput( inputVal ) ) {
					$('.scwriter-popup-content-input-errors').addClass('--show');
				} else {
					$('.scwriter-popup-content-input-errors').removeClass('--show');
					var data = {
						id: 'new',
						text: inputVal
					};
					var newOption = new Option(data.text, data.id, true, true);
					$('#preset_id').append(newOption).trigger('change');
					$('#preset_name').val(inputVal);
					scwriter.togglePopup('close');
					scwriter.usePreset(scwriter_default_preset_data, true);
					$('.scwriter-presets-btn-save').trigger('click');
				}
			});

			$('.scwriter-popup').on('click', '.scwriter-popup-edit-preset', function(e){
				e.preventDefault();
				let inputVal = $('.scwriter-popup .scwriter-current-preset-name').val();
				if ( !scwriter.validateInput( inputVal ) ) {
					$('.scwriter-popup-content-input-errors').addClass('--show');
				} else {
					$('.scwriter-popup-content-input-errors').removeClass('--show');
					let $option = jQuery('#preset_id option:selected');
					$option.text( inputVal );
					$('#preset_id').select2('destroy');
					$('#preset_id').select2();
					$('#preset_name').val(inputVal);
					scwriter.togglePopup('close');
				}
			});

			if ( $('.scwriter-form-hint-opener').length ) {
				$('.scwriter-form-hint-opener').on('click', function(e){
					scwriter.togglePopup('open', '', $(this).siblings('.scwriter-form-hint-content').html());
				});
			}

			if ( $('.scwriter-form-row-toc-show-hide').length ) {
				scwriter.showHideBlockTOC();
			}

			if ( $('.scwriter-form-row-cat-show-hide').length ) {
				scwriter.showHideBlockCat();
			}

			$('.scwriter-condition').on('change', function(){
				let inputVal = $(this).val();
				let targetValue = $(this).data('value');
				let target = $(this).data('target');
				let targetRequired = $(this).data('target-required');

				if ( inputVal == targetValue || $(this).is(':checked') ) {
					$('.'+target).addClass('--show');
					if ( targetRequired ) {
						$('.'+target).find('textarea').prop('required', true);
						$('.'+target).find('input').prop('required', true);
						$('.'+target).find('select').prop('required', true);
					}
					$('.'+target).find('.scwriter-input-errors').empty();
				} else {
					$('.'+target).removeClass('--show');
					$('.'+target).find('input').prop('required', false);
					$('.'+target).find('select').prop('required', false);
				}
				
			});

			$('.scwriter-post-create').on('click', async function(e){
				e.preventDefault();
				let $form = $('.scwriter-form');
				
				let isDraft = $(this).data('article_id') ? true : false;

				$('.scwriter-validate-it').trigger('change');

				let errorElement = $('.scwriter-input-errors').filter(function() {
					return $(this).text().trim() !== '';
				}).first(); 
			
				if ( errorElement.length > 0 ) {
					$('html, body').animate({
						scrollTop: errorElement.closest('.scwriter-presets-row').offset().top - 50
					}, 800);
				}

				if ( $form.is(':valid') && errorElement.length == 0 ) {
					$form.find('[name]').each(function(){
						if ( !$(this).is(':visible') && $(this).attr('type') !== 'hidden' ){
							let tempName = $(this).attr('name');
							$(this).removeAttr('name');
							$(this).addClass('scwriter-changed-name');
							$(this).data('name', tempName);
						}
					});

					// $form.trigger('submit');
					let data = $('.scwriter-form').serializeArray().reduce(function(obj, item) {
						// Check if the key already exists in the object
						if (obj.hasOwnProperty(item.name)) {
							// If it's not an array, convert it to an array
							if (!Array.isArray(obj[item.name])) {
								obj[item.name] = [obj[item.name]];
							}
							// Push the current value to the array
							obj[item.name].push(item.value);
						} else {
							// If the key doesn't exist, add it
							obj[item.name] = item.value;
						}
						return obj;
					}, {});

					$form.find('.scwriter-changed-name').each(function(){
						let tempName = $(this).data('name');
						$(this).attr('name', tempName);
						$(this).removeClass('scwriter-changed-name');
					});

					if (isDraft) {
						try {
							const savedData = await scwriter.editor.save();
							const edjsParser = edjsHTML();
							let html = edjsParser.parse(savedData);
							data['outlines'] = html.join('');
						} catch (error) {
							$form.removeClass('--is-loading');
							scwriter.showMessage('error', 'Something went wrong while sending data');
							return; 
						}
						data['article_id'] = $(this).data('article_id');
					}

					jQuery.ajax({
						type: "post",
						dataType: "json",
						url: scwriter_ajax.url,
						data: data,
						beforeSend: function () {
							$form.addClass('--is-loading');
						},
						success: function (response) {
							if ( response.error ) {
								$form.removeClass('--is-loading');
								scwriter.showMessage('error', response.error_message);
							} else if ( 'redirect_to' in response ) {
								$form.removeClass('--is-loading');
								window.location.replace(response.redirect_to);
							} else if ( 'article_id' in response ) {
								scwriter.getArticleInfo(response.article_id);
								$('.scwriter-post-create').data('article_id', response.article_id)
							}

						},
						error: function () {
							$form.removeClass('--is-loading');
							scwriter.showMessage('error', 'Something went wrong, please check URL and try it again');
						}
					});

				}

			});

			$('.scwriter-form-saver').on('click', function(e){
				e.preventDefault();
				var error = false;
				
				// Iterate through each tab
				$('.scwriter-tabs-content').each(function() {
					var $inputs = $(this).find('[required]');
					// Iterate through required inputs within each tab
					$inputs.each(function() {
						if (!$(this).val()) {
							// If any required input is empty, set error flag to true
							error = true;
							// Display the tab containing the empty input
							let tab = $(this).closest('.scwriter-tabs-content').data('tab');
							$('.scwriter-tabs-heading-btn[data-tab="'+tab+'"]').trigger('click');
							// Focus on the first empty required input
							
							let $this = $(this);
							setTimeout(() => {
								$this.focus().get(0).reportValidity();
							}, 100);
							// Exit the loop
							return false;
						}
					});
					if (error) return false;
				});

				if (!error){
					let $form = $('.scwriter-form-settings');
					
					let data = $form.serializeArray().reduce(function(obj, item) {
						// Check if the key already exists in the object
						if (obj.hasOwnProperty(item.name)) {
							// If it's not an array, convert it to an array
							if (!Array.isArray(obj[item.name])) {
								obj[item.name] = [obj[item.name]];
							}
							// Push the current value to the array
							obj[item.name].push(item.value);
						} else {
							// If the key doesn't exist, add it
							obj[item.name] = item.value;
						}
						return obj;
					}, {});

					jQuery.ajax({
						type: "post",
						dataType: "json",
						url: scwriter_ajax.url,
						data: data,
						beforeSend: function () {
							$form.addClass('--is-loading');
						},
						success: function (response) {
							$form.removeClass('--is-loading');
							if ( response.error ) {
								scwriter.showMessage('error', response.error_message);
							} else {
								scwriter.showMessage('success', response.message);
							}

						},
						error: function () {
							$form.removeClass('--is-loading');
							scwriter.showMessage('error', 'Something went wrong, please check URL and try it again');
						}
					});
				}
			});

			$('.scwriter-form-saver-scwriter_apikey').on('click', function(e){
				e.preventDefault();
				let error = false;
				
				if ( !$('#api_key').is(':valid') ) {
					error = true;
					$('#api_key').trigger('change');
				}

				if (!error){
					let $form = $(this).closest('form');
					let $step = $(this).closest('.scwriter-wizard-step');
					
					let data = $form.serializeArray().reduce(function(obj, item) {
						// Check if the key already exists in the object
						if (obj.hasOwnProperty(item.name)) {
							// If it's not an array, convert it to an array
							if (!Array.isArray(obj[item.name])) {
								obj[item.name] = [obj[item.name]];
							}
							// Push the current value to the array
							obj[item.name].push(item.value);
						} else {
							// If the key doesn't exist, add it
							obj[item.name] = item.value;
						}
						return obj;
					}, {});

					jQuery.ajax({
						type: "post",
						dataType: "json",
						url: scwriter_ajax.url,
						data: data,
						beforeSend: function () {
							$step.addClass('--is-loading');
						},
						success: function (response) {
							$step.removeClass('--is-loading');
							if ( response.error ) {
								scwriter.showMessage('error', response.error_message);
							} else {
								scwriter.showMessage('success', response.message);
								$step.slideUp(1000, function(){
									$step.next().fadeIn();
								});
							}

						},
						error: function () {
							$step.removeClass('--is-loading');
							scwriter.showMessage('error', 'Something went wrong, please check URL and try it again');
						}
					});
				}
			});
			
			$('.scwriter-form-saver-scwriter_openai').on('click', function(e){
				e.preventDefault();
				let error = false;
				
				if ( !$('#openai_api_key').is(':valid') ) {
					error = true;
					$('#openai_api_key').trigger('change');
				}

				if (!error){
					let $form = $(this).closest('form');
					let $step = $(this).closest('.scwriter-wizard-step');
					
					let data = $form.serializeArray().reduce(function(obj, item) {
						// Check if the key already exists in the object
						if (obj.hasOwnProperty(item.name)) {
							// If it's not an array, convert it to an array
							if (!Array.isArray(obj[item.name])) {
								obj[item.name] = [obj[item.name]];
							}
							// Push the current value to the array
							obj[item.name].push(item.value);
						} else {
							// If the key doesn't exist, add it
							obj[item.name] = item.value;
						}
						return obj;
					}, {});

					jQuery.ajax({
						type: "post",
						dataType: "json",
						url: scwriter_ajax.url,
						data: data,
						beforeSend: function () {
							$step.addClass('--is-loading');
						},
						success: function (response) {
							$step.removeClass('--is-loading');
							if ( response.error ) {
								scwriter.showMessage('error', response.error_message);
							} else {
								scwriter.showMessage('success', response.message);
								$step.slideUp(1000, function(){
									$step.next().fadeIn();
								});
							}

						},
						error: function () {
							$step.removeClass('--is-loading');
							scwriter.showMessage('error', 'Something went wrong, please check URL and try it again');
						}
					});
				}
			});
			
			$('.scwriter-form-saver-scwriter_blog_topic').on('click', function(e){
				e.preventDefault();
				let error = false;
				let blog_topic = $('#blog_topic').val()

				if ( !$('#blog_topic').is(':valid') ) {
					error = true;
					$('#blog_topic').trigger('change');
				}
				if (!error){
					let $form = $(this).closest('form');
					let $step = $(this).closest('.scwriter-wizard-step');
					
					let data = $form.serializeArray().reduce(function(obj, item) {
						// Check if the key already exists in the object
						if (obj.hasOwnProperty(item.name)) {
							// If it's not an array, convert it to an array
							if (!Array.isArray(obj[item.name])) {
								obj[item.name] = [obj[item.name]];
							}
							// Push the current value to the array
							obj[item.name].push(item.value);
						} else {
							// If the key doesn't exist, add it
							obj[item.name] = item.value;
						}
						return obj;
					}, {});

					jQuery.ajax({
						type: "post",
						dataType: "json",
						url: scwriter_ajax.url,
						data: data,
						beforeSend: function () {
							$step.addClass('--is-loading');
						},
						success: function (response) {
							$step.removeClass('--is-loading');
							if ( response.error ) {
								scwriter.showMessage('error', response.error_message);
							} else {
								scwriter.showMessage('success', response.message);
								if ( 'redirect_to' in response ) {
									setTimeout(() => {
										window.location.replace(response.redirect_to);
									}, 1000);
								}
							}

						},
						error: function () {
							$step.removeClass('--is-loading');
							scwriter.showMessage('error', 'Something went wrong, please check URL and try it again');
						}
					});
				}
			});

		},

		getArticleInfo: function( article_id, retry = 1 ){

			let $form = $('.scwriter-form');
			let data = {
				action: 'scwriter_get_article_info',
				article_id: article_id,
				scwriter_nonce_field: $('#scwriter_nonce_field').val(),
				retry: retry
			}

			$form.addClass('--is-loading');

			jQuery.ajax({
				type: "post",
				dataType: "json",
				url: scwriter_ajax.url,
				data: data,
				success: function (response) {
					if ( response.error ) {
						$form.removeClass('--is-loading');
						scwriter.showMessage('error', response.error_message);
					} else if ( 'data' in response ) {
						$form.removeClass('--is-loading');

						let blocks = scwriter.htmlToEditorJSBlocks(response.data.outlines);
						scwriter.editor.render({
							blocks: blocks
						});
						
						if ( response.data.title ) {
							$('#title').val(response.data.title);
						}
						
						if ( response.data.primary_keyword ) {
							$('#primary_keyword').val(response.data.primary_keyword);
						}
						
						if ( response.data.secondary_keywords ) {
							$('#secondary_keywords').val(response.data.secondary_keywords);
						}
						$('.scwriter-outline-holder').removeClass('--hidden');

						$('.scwriter-post-create').attr('data-type', 'article');
						
						$('.scwriter-enable-outline-holder').addClass('--hidden');

						scwriter.showMessage('success', response.message);
					} else {
						retry = parseInt(retry);
						retry++;
						scwriter.getArticleInfo(article_id, retry);
					}
				},
				error: function () {
					$form.removeClass('--is-loading');
					scwriter.showMessage('error', 'Something went wrong, please check URL and try it again');
				}
			});

		},

		cancelDraft: function() {

			if ( scwriter.editor ) {
				scwriter.editor.clear();
				$('.scwriter-outline').closest('.scwriter-presets-row').addClass('--hidden');
				$('.scwriter-post-create').attr('data-type', 'article');
				$('.scwriter-post-create').data('article_id', null);
			}

		},
		
		initEditor: function() {
			
			if ( $('#scwriter-outline').length ) {
				scwriter.editor = new EditorJS({
					holder: 'scwriter-outline',
					tools: {
					  header: {
						class: Header,
						inlineToolbar: ['link'],
						config: {
						  placeholder: 'Header',
						  levels: [2,3],
						  defaultLevel: 2,
						},
						shortcut: 'CMD+SHIFT+H'
					  },
					  list: {
						class: List,
						inlineToolbar: true,
						shortcut: 'CMD+SHIFT+L'
					  },
					},
				});
			}
			  
		},

		htmlToEditorJSBlocks: function(html) {
			
			const parser = new DOMParser();
			const doc = parser.parseFromString(html, 'text/html');
			
			const blocks = [];
		
			doc.body.childNodes.forEach((node) => {
			  if (node.nodeName === 'H2') {
				blocks.push({
				  type: 'header',
				  data: {
					text: node.textContent,
					level: 2
				  }
				});
			} else if (node.nodeName === 'H3') {
				blocks.push({
				  type: 'header',
				  data: {
					text: node.textContent,
					level: 3
				  }
				});
			  } else if (node.nodeName === 'P') {
				blocks.push({
				  type: 'paragraph',
				  data: {
					text: node.textContent
				  }
				});
			  } else if (node.nodeName === 'UL' || node.nodeName === 'OL') {
				const listItems = [];
				node.querySelectorAll('li').forEach((li) => {
				  listItems.push(li.textContent);
				});
				blocks.push({
				  type: 'list',
				  data: {
					items: listItems,
					style: node.nodeName === 'UL' ? 'unordered' : 'ordered'
				  }
				});
			  }
			});
		
			return blocks;
		},

		showMessage: function(type, message) {
			
			clearTimeout(scwriter.message_timeout);

			let needAdd = true;
			if ( $('.form-messages').length == 0 ) {
			
				if ( $('#wpbody-content h1').length ) {
					$('<div class="form-messages"></div>').insertAfter('#wpbody-content h1');
					needAdd = true;
				}
				
			} else {
				needAdd = true;
			}

			if ( !needAdd ){
				return;
			}

			let html = `<div class="notice notice-${type}"><p>${message}</p></div>`;
			$('.form-messages')
				.empty()
				.append(html)
				.fadeIn();

			$("html, body").stop().animate({scrollTop:0}, 800);

			let timeoutTime = type == 'error' ? 10000 : 5000;
			scwriter.message_timeout = setTimeout(function() {
				$('.form-messages').fadeOut(800, function(){
					$('.form-messages').empty()
				});
			}, timeoutTime);

		},

	};

	$( document ).ready( scwriter.init );
	window.scwriter = scwriter
}

scwriterWrapper( jQuery );