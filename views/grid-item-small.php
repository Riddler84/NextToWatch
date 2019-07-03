<?php
if ( ! defined( 'ROOT_PATH' ) )
	die( 'walk away!' );
?>


<div class="grid-item small" data-title="<?php echo $show['title']; ?>" data-show-url="<?php echo $show['url'] ?>">

	<div class="content">

		<div class="header" hidden>

		</div>

		<div class="body">

			<div class="column">
				<div class="cover">
					<a href="http://s.to<?php echo $show['url']; ?>" target="_blank">
						<img src="http://s.to<?php echo $show['image']; ?>" alt="<?php echo $show['title']; ?> - Cover">
					</a>
				</div>

				<div class="metadata">
					<div class="titles">
						<div class="show-title"><?php echo $show['title']; ?></div>
						<div class="episode-title"></div>
						<a href="" class="overlay-link episode-url" target="_blank"></a>
					</div>
					<p class="description cropped"></p>
				</div>
			</div>

		</div>

		<div class="footer">

			<div>
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
			</div>

			<div class="languages"></div>

		</div>

	</div>

</div>