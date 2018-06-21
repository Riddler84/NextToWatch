<?php
include 'lib/simple_html_dom.php';


function do_login( string $email, string $pwd ) 
{
	$email = strip_tags( trim( $email ) );
	$pwd   = strip_tags( trim( $pwd ) );

	$ch = curl_init();

	curl_setopt( $ch, CURLOPT_COOKIEJAR, dirname(__FILE__) . "/tmp/cookies.txt" );
	curl_setopt( $ch, CURLOPT_URL, "https://s.to/login" );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_POST, 1 );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, "email={$email}&password={$pwd}" );
	curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"] );

	ob_start();
	curl_exec( $ch );
	ob_end_clean();

	curl_close( $ch );
	unset( $ch );

	$base_url = sprintf(
		"%s://%s",
		isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
		$_SERVER['SERVER_NAME']
	);

	header( 'Location: ' . $base_url );
	exit;
}


function do_logout() 
{
	unlink( dirname(__FILE__) . "/tmp/cookies.txt" );

	$base_url = sprintf(
		"%s://%s",
		isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
		$_SERVER['SERVER_NAME']
	);

	header( 'Location: ' . $base_url );
	exit;
}


function get_site_html( string $url = 'https://s.to/' ) 
{
	$url = filter_var( $url, FILTER_SANITIZE_URL, FILTER_FLAG_SCHEME_REQUIRED );

	$ch = curl_init();

	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_COOKIEFILE, dirname(__FILE__) . "/tmp/cookies.txt" );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"] );

	$html = curl_exec( $ch );

	curl_close( $ch );

	return $html;
}


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


function get_first_unseen_episode( $url ) 
{
	$show_page = str_get_html( get_site_html( 'https://s.to' . $url ) );
	$first_season_episode = get_first_unseen_episode_data( $show_page );

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
			$other_season_episode = get_first_unseen_episode_data( $show_page );
			
			if ( ! empty( $other_season_episode ) ) 
			{
				return json_encode( $other_season_episode );
			}
		}
	}

	return json_encode( ['info' => 'nothing'] );
}


function get_first_unseen_episode_data( $season_page ) 
{
	$first_unseen = $season_page->find( 'table.seasonEpisodesList tbody', 0 )->find( 'tr[class!=seen]', 0 );

	$episode_data = [];

	if ( ! empty( $first_unseen ) ) 
	{
		$episode_data['info'] = [
			'season' 		 => strip_tags( trim( $season_page->find( '#stream ul', 0 )->find( 'a.active', 0 )->title ) ),
			'episode_id'	 => intval( $first_unseen->{'data-episode-id'} ),
			'episode' 		 => strip_tags( trim( $first_unseen->find( 'td', 0 )->find( 'a', 0 )->plaintext ) ),
			'title_german'   => strip_tags( trim( $first_unseen->find( '.seasonEpisodeTitle a strong', 0 )->plaintext ) ),
			'title_original' => strip_tags( trim( $first_unseen->find( '.seasonEpisodeTitle a span', 0 )->plaintext ) ),
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