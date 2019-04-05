<?php
if ( ! defined( 'ROOT_PATH' ) )	die( 'No direct access allowed!' );


/**
 * Include the simple html dom library
 */
include ROOT_PATH . '/lib/simple_html_dom.php';


/**
 * Do remote login to s.to and saves a cookie file
 */
function do_login( string $email, string $pwd ) 
{
	setcookie( "ntw_user", sha1( $email ), strtotime( '+365 days' ) );

	$post_fields = http_build_query([
		'email'    => strip_tags( trim( $email ) ),
		'password' => strip_tags( trim( $pwd ) )
	]);

	$ch = curl_init();

	curl_setopt( $ch, CURLOPT_COOKIEJAR, ROOT_PATH . "/tmp/cookies_" . $_COOKIE["ntw_user"] . ".txt" );
	curl_setopt( $ch, CURLOPT_URL, "https://s.to/login" );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
	curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"] );

	ob_start();
	curl_exec( $ch );
	ob_end_clean();

	curl_close( $ch );
	unset( $ch );

	$base_url = sprintf(
		"%s://%s%s",
		isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
		$_SERVER['SERVER_NAME'],
		explode( '?', $_SERVER['REQUEST_URI'], 2 )[0]
	);

	header( 'Location: ' . $base_url );
	exit;
}


/**
 * Do logout by deleting the cookie file
 */
function do_logout() 
{
	unlink( ROOT_PATH . "/tmp/cookies_" . $_COOKIE["ntw_user"] . ".txt" );

	$base_url = sprintf(
		"%s://%s%s",
		isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
		$_SERVER['SERVER_NAME'],
		explode( '?', $_SERVER['REQUEST_URI'], 2 )[0]
	);

	header( 'Location: ' . $base_url );
	exit;
}


/**
 * Get the html source of a specific external site
 */
function get_site_html( string $url = 'https://s.to/' ) 
{
	$url = filter_var( $url, FILTER_SANITIZE_URL, FILTER_FLAG_SCHEME_REQUIRED );

	$ch = curl_init();

	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_COOKIEFILE, ROOT_PATH . "/tmp/cookies_" . $_COOKIE["ntw_user"] . ".txt" );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"] );

	$html = curl_exec( $ch );

	curl_close( $ch );

	return $html;
}


/**
 * Get the general data of all subscribed shows
 */
function get_subscribed_shows() 
{
	$shows = str_get_html( get_site_html( 'https://s.to/account/subscribed' ) );

	$show_data = [];

	foreach ( $shows->find( 'div[class*=seriesListContainer] div' ) as $show ) 
	{
		$show_data[] = [
			'title' => strip_tags( trim( $show->find( 'h3', 0 )->plaintext ) ),
			'url'   => strip_tags( $show->find( 'a', 0 )->href ),
			'image' => strip_tags( $show->find( 'img', 0 )->src ),
		];
	}

	return ( ! empty( $show_data ) ) ? $show_data : false;
}


/**
 * Get the first unseen episode of a show
 */
function get_first_unseen_episode( $url ) 
{
	$show_page = str_get_html( get_site_html( 'https://s.to' . $url ) );
	$first_season_episode = get_episode_data( $show_page );

	if ( ! empty( $first_season_episode ) ) 
	{
		return json_encode( $first_season_episode );
	}
	else 
	{
		$first_incomplete_season_url = $show_page->find( '#stream', 0 )->find( 'ul', 0 )->find( 'a[!class]', 0 );
		
		if ( isset( $first_incomplete_season_url->href ) ) 
		{
			$show_page = str_get_html( get_site_html( 'https://s.to' . $first_incomplete_season_url->href ) );
			$other_season_episode = get_episode_data( $show_page );
			
			if ( ! empty( $other_season_episode ) ) 
			{
				return json_encode( $other_season_episode );
			}
		}
	}

	return json_encode( ['info' => 'nothing'] );
}


/**
 * Get all relevant data of a episode
 */
function get_episode_data( $season_page ) 
{
	$first_unseen = $season_page->find( 'table.seasonEpisodesList tbody', 0 )->find( 'tr[class!=seen]', 0 );

	$episode_data = [];

	if ( is_object( $first_unseen ) ) 
	{
		$episode_data['info'] = [
			'season' 		 => strip_tags( trim( $season_page->find( '#stream ul', 0 )->find( 'a.active', 0 )->title ) ),
			'episode_id'	 => intval( $first_unseen->{'data-episode-id'} ),
			'episode' 		 => strip_tags( trim( $first_unseen->find( 'td', 0 )->find( 'a', 0 )->plaintext ) ),
			'title_german'   => isset( $first_unseen->find( '.seasonEpisodeTitle a strong', 0 )->plaintext ) ? strip_tags( trim( $first_unseen->find( '.seasonEpisodeTitle a strong', 0 )->plaintext ) ) : '',
			'title_original' => isset( $first_unseen->find( '.seasonEpisodeTitle a span', 0 )->plaintext ) ? strip_tags( trim( $first_unseen->find( '.seasonEpisodeTitle a span', 0 )->plaintext ) ) : '',
			'url' 	  		 => strip_tags( $first_unseen->find( '.seasonEpisodeTitle a', 0 )->href ),
		];

		foreach ( $first_unseen->find( '.editFunctions img' ) as $lang ) 
		{
			$episode_data['lang'][] = [
				'flag' => $lang->src,
				'name' => $lang->title,
			];
		}

		return $episode_data;
	}
	else
	{
		return false;
	}
}


/**
 * Checks if you're logged in and retrieve your username
 */
function get_site_data()
{
	$html = str_get_html( get_site_html( 'https://s.to' ) );

	$data = new stdClass;

	// logged in?
	$element = $html->find('.primary-navigation .user', 0);

	if ( is_object( $element ) && ! empty( $element ) ) 
	{
		$data->loggedin = 1;
	}
	else 
	{
		$data->loggedin = 0;
	}

	// username
	if ( $data->loggedin === 1 ) 
	{
		$element = $html->find('.primary-navigation .user .name', 0)->plaintext;
		$data->username = strip_tags( trim( $element ) );
	}

	return $data;
}