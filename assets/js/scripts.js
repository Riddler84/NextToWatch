jQuery(function ($) {

	var gridItemsCount = $('.grid-item').length;
	var successCount   = 0;

	$('.grid-item .show-episode').each(function () {

		var element = $(this);
		var gridItem = $(this).closest('.grid-item');
		var data_url = element.attr('data-show-url');

		$.ajax({
			url: 'index.php',
			data: {
				ajax_action: 'get_episode',
				ajax_data: {
					show_url: data_url
				}
			},
			type: 'post',
			dataType: 'json',
			beforeSend: function () {
				element.html('<img src="images/loader.gif">');
			},
			success: function (output) {
				successCount++;
				if (output.info == 'nothing') {
					// gridItemsCount--;
					gridItem.remove();
				} else {
					gridItem.fadeIn('slow', function() {
						updateProgressBar();
					});

					if (output.info.title_german) {
						var title = '<strong>' + output.info.title_german + '</strong>&nbsp;-&nbsp;' + output.info.title_original;
					} else {
						var title = output.info.title_original;
					}

					var lang = '';
					$(output.lang).each(function () {
						lang = lang + '<img src="https://s.to' + this.flag + '" title="' + this.name + '">&nbsp;';
					});

					element.html(
						'<div class="show-meta">' + '<span class="badge">' + output.info.season + '</span>' + '&nbsp;' + '<span class="badge">' + output.info.episode + '</span>' + '</div>' +
						'<div class="show-title"><a href="https://s.to' + output.info.url + '" target="_blank">' + title + '</a></div>' +
						'<div class="show-lang">' + lang + '</div>'
					);

					gridItem.attr('data-episode-id', output.info.episode_id);

					sortGrid();
				}
			},
			error: function (xhr, text_status) {
				console.log(xhr);
				console.log(text_status);
			}
		});

	});


	function sortGrid(attribute = 'data-episode-id') {
		var $grid 	   = $('.grid-container'),
			$gridItems = $grid.children('.grid-item');

		$gridItems.sort(function (a, b) {
			return b.getAttribute(attribute) - a.getAttribute(attribute);
		});

		$gridItems.detach().appendTo($grid);

		// last seen at first
		$('.grid-item.lastseen').detach().prependTo($grid);
	}


	function updateProgressBar(value) {
		if (value) {
			var percent = Math.round( value );
		} else {
			var percent = Math.round( ((successCount / gridItemsCount) * 100) );
		}

		var bar = $('.cssProgress-bar');

		bar.css( 'width', percent + '%' );
		bar.attr( 'data-percent', percent );
		bar.find('.cssProgress-label').html( successCount + ' / ' + gridItemsCount + ' verarbeitet' );
	}


	$(document).ajaxStop(function () {
		updateProgressBar(100);
		$('.cssProgress').delay(800).fadeOut('slow');
		$('.show-filter > .search > input').prop('disabled', false);
		$('.show-filter > .lang input').prop('disabled', false);
	});


	// store last seen show when clicking an episode
	$('.grid-container').on('click', '.grid-item .show-title', function() {
		localStorage.setItem("last_seen_show", $(this).closest('.grid-item').find('h2').html());
	});


	// add class to last seen show
	$('.grid-container > .grid-item h2[data-title*="' + localStorage['last_seen_show'] + '"]').closest('.grid-item').addClass('lastseen');


	// filter title
	$('.show-filter .search').on('keyup', 'input', function() {
		var searchTerm = $(this).val();

		// uncheck language inputs
		$('.show-filter .lang input').prop('checked', false);
		$('.show-filter .lang label').removeClass('active');

		$('.grid-container .grid-item').each(function() {
			if ( $(this).find('h2').text().search( new RegExp( searchTerm, "i" ) ) < 0 ) {
				$(this).fadeOut();
			} else {
				$(this).fadeIn();
			}
		});
	});


	// language checkboxes
	$('.show-filter .lang').on('change', 'label input', function() {
		if( $(this).is(':checked') ) {
			$(this).closest('label').addClass('active');
		} else {
			$(this).closest('label').removeClass('active');
		};
	});


	// filter lang
	$('.show-filter .lang').on('change', 'input', function() {
		var langs = $(this).closest('.lang');
		var gridItems = $('.grid-container .grid-item');
		var gridItemsDEEN = gridItems.find('.show-lang img[src*="deen.png"]');
		var gridItemsDE = gridItems.find('.show-lang img[src*="de.png"]');
		var gridItemsEN = gridItems.find('.show-lang img[src*="en.png"]');

		// clear search input
		$('.show-filter .search input').val('');

		gridItems.hide();

		if ( langs.find('#lang-deen').prop('checked') ) {
			gridItemsDEEN.each(function() {
				$(this).closest('.grid-item').show();
			});
		}

		if ( langs.find('#lang-de').prop('checked') ) {
			gridItemsDE.each(function() {
				$(this).closest('.grid-item').show();
			});
		}

		if ( langs.find('#lang-en').prop('checked') ) {
			gridItemsEN.each(function() {
				$(this).closest('.grid-item').show();
			});
		}

		if ( ! langs.find('#lang-deen').prop('checked') && ! langs.find('#lang-de').prop('checked') && ! langs.find('#lang-en').prop('checked') ) {
			gridItems.show();
		}
	});

});