<?php 
define( 'ROOT_PATH', dirname( __FILE__ ) );

include ROOT_PATH . '/includes/functions.php';


if ( isset( $_POST['ajax_action'] ) && $_POST['ajax_action'] == 'get_episode' ) 
{
	echo get_first_unseen_episode( $_POST['ajax_data']['show_url'] );
	exit;
}


if ( isset( $_GET['logout'] ) ) :
	do_logout();
endif;

if ( isset( $_POST['submit'] ) && $_POST['submit'] == 'login' ) :
	do_login( $_POST['email'], $_POST['pwd'] );
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

				<div class="show-filter">

					<div class="search">
						<input type="text" placeholder="Titel eingeben">
					</div>

					<!-- <b>&nbsp;|&nbsp;</b>

					<div class="lang">
						<label for="lang-deen">
							<input type="checkbox" id="lang-deen" data-lang="deen" hidden>
							<img src="images/deen.png" alt="">
						</label>

						<label for="lang-de" class="active">
							<input type="checkbox" id="lang-de" data-lang="de" checked hidden>
							<img src="images/de.png" alt="">
						</label>

						<label for="lang-en">
							<input type="checkbox" id="lang-en" data-lang="en" hidden>
							<img src="images/en.png" alt="">
						</label>
					</div> -->
				</div>

				<div class="cssProgress">
					<div class="progress3">
						<div class="cssProgress-bar" data-percent="0" style="width: 0%;">
						<span class="cssProgress-label">0 / 0 verarbeitet</span>
						</div>
					</div>
				</div>

				<div class="grid-container">

					<?php
					foreach ( $shows as $show ) :
					?>

						<div class="grid-item" style="display:none;">

							<div class="show-cover">
								<a href="http://s.to<?php echo $show['url']; ?>" target="_blank">
									<img src="http://s.to<?php echo $show['image']; ?>" alt="<?php echo $show['title']; ?> - Cover">
								</a>
							</div>
							
							<div>
								<h2><?php echo $show['title']; ?></h2>
								<div class="show-episode" data-show-url="<?php echo $show['url'] ?>"></div>
							</div>

						</div>

					<?php
					endforeach
					?>

				</div>

			</main>

		<?php
		else :
		?>

			<div id="login-form">
				<p style="text-align:center; margin-top:0; font-weight:bold;">Deine s.to Zugangsdaten</p>

				<form action="" method="post">
					<input type="email" name="email" placeholder="E-Mail" required>
					<input type="password" name="pwd" placeholder="Passwort" required>
					<button type="submit" name="submit" value="login">Einloggen</button>
				</form>
			</div>
		
		<?php
		endif;
		?>

	</body>

</html>