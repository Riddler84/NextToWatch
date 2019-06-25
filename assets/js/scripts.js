jQuery(function ($) {

	var gridItemsCount = $('.grid-item').length;
	var successCount   = 0;

	$('.grid-item').each(function () {

		var item = $(this);
		var data_url = item.attr('data-show-url');

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
				// item.html('<img src="images/loader.gif">');
			},
			success: function (output) {
				successCount++;
				updateProgressBar();

				console.log(output);

				if (output.episode_error == 'no_unseen') {
					item.find('.description').html('<span style="color:red; font-weight: bold;">Keine ungesehene Episode</span>');
				}

				if (output.show_info) {
					if (output.show_info.header_background) {
						item.find('.grid-item-header').attr('style', output.show_info.header_background);
					}
				}

				if (output.episode_info) {
					if (output.episode_info.title_german) {
						item.find('.title-container > .episode-title').text(output.episode_info.title_german);
					} else if (output.episode_info.title_english) {
						item.find('.title-container > .episode-title').text(output.episode_info.title_english);
					}

					if (output.episode_info.description) {
						item.find('.description').text(output.episode_info.description);
					} else {
						item.find('.description').text('Keine Beschreibung vorhanden');
					}

					if (output.episode_info.current_season == 0) {
						item.find('.seasons-progress').show().prepend('<span>Filme</span>');
						item.find('.seasons-progress .progress-current').css('width', '100%');
					} else if (output.episode_info.current_season && output.episode_info.seasons_count) {
						item.find('.seasons-progress').show().prepend('<span>Staffel ' + output.episode_info.current_season + '/' + output.episode_info.seasons_count + '</span>');

						var percentage_finished = ((output.episode_info.current_season - 1) * 100) / output.episode_info.seasons_count;
						item.find('.seasons-progress .progress-finished').css('width', percentage_finished + '%');

						var remaining_seasons = (output.episode_info.seasons_count - (output.episode_info.current_season - 1));
						var percentage_current = (100 - percentage_finished ) / remaining_seasons;
						item.find('.seasons-progress .progress-current').css('width', percentage_current + '%');
					}

					if (output.episode_info.current_episode && output.episode_info.episodes_count) {
						item.find('.episodes-progress').show().prepend('<span>Episode ' + output.episode_info.current_episode + '/' + output.episode_info.episodes_count + '</span>');

						var percentage_finished = ((output.episode_info.current_episode - 1) * 100) / output.episode_info.episodes_count;
						item.find('.episodes-progress .progress-finished').css('width', percentage_finished + '%');

						var remaining_episodes = (output.episode_info.episodes_count - (output.episode_info.current_episode - 1));
						var percentage_current = (100 - percentage_finished ) / remaining_episodes;
						item.find('.episodes-progress .progress-current').css('width', percentage_current + '%');
					}

					if (output.episode_info.url) {
						item.find('.grid-item-header .overlay-link').attr('href', 'https://s.to' + output.episode_info.url);
					} else {
						item.find('.grid-item-header .overlay-link').removeAttr('href');
					}

					if (output.episode_info.current_episode_id) {
						item.attr('data-episode-id', output.episode_info.current_episode_id);
					}
				}

				if (output.episode_lang) {
					var lang = '';
					$(output.episode_lang).each(function () {
						lang = lang + '<img src="https://s.to' + this.flag + '" title="' + this.name + '">&nbsp;';
					});
					item.find('.languages').html(lang);
				}

				sortGrid();
				resizeAllGridItems();
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
	$('.grid-container').on('click', '.grid-item .overlay-link', function() {
		localStorage.setItem("last_seen_show", $(this).closest('.grid-item').find('.show-title').text());
	});


	// add class to last seen show
	$('.grid-container > .grid-item[data-title*="' + localStorage['last_seen_show'] + '"]').addClass('lastseen');


	// filter title
	$('.show-filter .search').on('keyup', 'input', function() {
		var searchTerm = $(this).val();

		// uncheck language inputs
		$('.show-filter .lang input').prop('checked', false);
		$('.show-filter .lang label').removeClass('active');

		$('.grid-container .grid-item').each(function() {
			if ( $(this).find('.show-title').text().search( new RegExp( searchTerm, "i" ) ) < 0 ) {
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
		var gridItemsDEEN = gridItems.find('.languages img[src*="deen.png"]');
		var gridItemsDE = gridItems.find('.languages img[src*="de.png"]');
		var gridItemsEN = gridItems.find('.languages img[src*="en.png"]');

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


	// masonry grid
	function resizeGridItem(item) {
		grid = document.getElementsByClassName("grid-container")[0];
		rowHeight = parseInt(window.getComputedStyle(grid).getPropertyValue('grid-auto-rows'));
		rowGap = parseInt(window.getComputedStyle(grid).getPropertyValue('grid-row-gap'));
		rowSpan = Math.ceil((item.querySelector('.content').getBoundingClientRect().height + rowGap) / (rowHeight + rowGap));
		item.style.gridRowEnd = "span " + rowSpan;
	}

	function resizeAllGridItems() {
		allItems = document.getElementsByClassName("grid-item");
		for (x = 0; x < allItems.length; x++) {
			resizeGridItem(allItems[x]);
		}
	}

	// function resizeInstance(instance) {
	// 	item = instance.elements[0];
	// 	resizeGridItem(item);
	// }

	// window.onload = resizeAllGridItems();
	window.addEventListener("resize", resizeAllGridItems);

	// allItems = document.getElementsByClassName("item");
	// for (x = 0; x < allItems.length; x++) {
	// 	imagesLoaded(allItems[x], resizeInstance);
	// }

});