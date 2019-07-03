<?php 
// define root path
define( 'ROOT_PATH', dirname( __FILE__ ) );


// include neccessary functions
include ROOT_PATH . '/includes/functions.php';


// delete cache cookie if needed
if ( isset( $_GET['action'] ) && $_GET['action'] == 'clear_cache' ) 
{
	if ( isset( $_COOKIE['ntw_unseen_ids'] ) ) 
	{
		unset( $_COOKIE['ntw_unseen_ids'] );
		setcookie( 'ntw_unseen_ids', '', time() - 3600, '/' );
	}
}


// handle ajax request and exit script
if ( isset( $_POST['ajax_action'] ) && $_POST['ajax_action'] == 'get_episode' ) 
{
	echo get_first_unseen_episode( $_POST['ajax_data']['show_url'] );
	exit;
}


// logout action
if ( isset( $_GET['logout'] ) ) :
	do_logout();
endif;


// login action
if ( isset( $_POST['submit'] ) && $_POST['submit'] == 'login' ) :
	do_login( $_POST['sstosession'] );
endif;
?>


<!DOCTYPE html>
<html>

	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Next To Watch</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" type="text/css" media="screen" href="assets/css/style.css" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="assets/js/scripts.js"></script>
	</head>

	<body>

		<?php 
		$site_data = get_site_data();

		if ( $site_data->loggedin === 1 ) :
		?>

			<?php
			$shows = get_subscribed_shows();
			?>

			<header>
				<div class="container">
					<div class="logo">NextToWatch</div>
					<div class="stats" style="text-align: right;">
						<div><small style="font-size:small;">Eingeloggt als <b><?php echo $site_data->username ?></b> (<a href="?logout" class="link-contrast">Ausloggen</a>)</small></div>
						<div><small>Abonnierte Serien:</small> <span class="badge"><?php echo count( $shows ); ?></span></div>
					</div>
				</div>
			</header>

			<main class="container">

				<div class="stats">
					<div><small style="font-size:small;">Eingeloggt als <b><?php echo $site_data->username ?></b> (<a href="?logout">Ausloggen</a>)</small></div>
					<div><small>Abonnierte Serien:</small> <span class="badge"><?php echo count( $shows ); ?></span></div>
				</div>

				<div class="show-filter">

					<div class="search">
						<input type="text" placeholder="Titel eingeben" disabled>
					</div>

					<b>&nbsp;|&nbsp;</b>

					<div class="lang">
						<label for="lang-deen">
							<input type="checkbox" id="lang-deen" data-lang="deen" disabled hidden>
							<img src="images/deen.png" alt="">
						</label>

						<label for="lang-de">
							<input type="checkbox" id="lang-de" data-lang="de" disabled hidden>
							<img src="images/de.png" alt="">
						</label>

						<label for="lang-en">
							<input type="checkbox" id="lang-en" data-lang="en" disabled hidden>
							<img src="images/en.png" alt="">
						</label>
					</div>
				</div>

				<div class="cssProgress">
					<div class="progress3">
						<div class="cssProgress-bar" data-percent="0" style="width: 0%;">
						<span class="cssProgress-label">0 / 0 verarbeitet</span>
						</div>
					</div>
				</div>

				<div class="grid-container small">

					<?php
					foreach ( $shows as $show ) :
					
						// include( ROOT_PATH . '/views/grid-item-big.php' );
						include( ROOT_PATH . '/views/grid-item-small.php' );

					endforeach
					?>

				</div>

			</main>

		<?php
		else :
		?>

			<div id="login-form">
				<p style="text-align:center; margin-top:0; font-weight:bold;">Deine s.to Session ID</p>

				<form action="" method="post">
					<!-- <input type="email" name="email" placeholder="E-Mail">
					<input type="password" name="pwd" placeholder="Passwort">
					<div style="text-align:center; font-weight:bold; font-size:10px; margin-bottom:10px;">- oder -</div> -->
					<input type="text" name="sstosession" placeholder="SSTOSESSION">
					<button type="submit" name="submit" value="login">Einloggen</button>
				</form>

				<p style="font-size: 12px; margin-top:50px;">
					Um die Session ID zu erhalten, auf s.to einloggen und anschließend in der Adressleiste auf das Schloss neben der URL klicken. Dann den Menüpunkt Cookies auswählen. Im sich öffnenden Fenster s.to -> Cookies -> SSTOSESSION wählen und die Zeichenfolge aus der Spalte Inhalt kopieren.
				</p>
			</div>
		
		<?php
		endif;
		?>

	</body>

</html>