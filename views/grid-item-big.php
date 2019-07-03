<?php
if ( ! defined( 'ROOT_PATH' ) )
	die( 'walk away!' );
?>


<div class="grid-item big" data-title="<?php echo $show['title']; ?>" data-show-url="<?php echo $show['url'] ?>">

	<div class="content">

		<div class="grid-item-header show-background" style="background-image:url('images/header-default-bg.png')">

			<div class="overlay-bg"></div>
			<a href="" class="overlay-link episode-url" target="_blank"></a>

			<div class="cover">
				<a href="http://s.to<?php echo $show['url']; ?>" target="_blank">
					<img src="http://s.to<?php echo $show['image']; ?>" alt="<?php echo $show['title']; ?> - Cover">
				</a>
			</div>

			<div class="title-container">
				<div class="show-title"><?php echo $show['title']; ?></div>
				<div class="episode-title"></div>
			</div>

		</div>

		<div class="grid-item-body">
			<div class="column">
				<div class="languages"></div>
				<div class="description-container">
					<span class="seasons-progress" style="display:none;">
						<div class="progress">
							<div class="progress-finished"></div>
							<div class="progress-current"></div>
						</div>
					</span>
					<span class="episodes-progress" style="display:none;">
						<div class="progress">
							<div class="progress-finished"></div>
							<div class="progress-current"></div>
						</div>
					</span>
					<p class="description full"></p>
				</div>
			</div>
		</div>

		<div class="grid-item-footer">
			
		</div>

	</div>

</div>