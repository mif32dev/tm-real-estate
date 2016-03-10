<div class="wrap cherry-settings-page">
	<h2><?php echo $title ?></h2>
	<?php if ( ! empty( $sections ) && is_array( $sections ) ) : ?>
	<div class="cherry-settings-tabs">
		<ul>
			<?php foreach( $sections as $section_slug => $section ) : ?>
			<li><a href="#<?php echo $section_slug ?>"><?php echo $section['name'] ?></a></li>
			<?php endforeach; ?>
		</ul>

		<?php foreach( $sections as $section_slug => $section ) : ?>
		<div id="<?php echo $section_slug ?>">
			<form method="POST" action="options.php" id="form-<?php echo $section_slug ?>">
				<?php settings_fields( $section_slug ); ?>
				<?php do_settings_sections( $section_slug ); ?>
				<?php submit_button( __( 'Save', 'tm-real-estate' ), 'primary small', null, true, array( 'data-ajax' => true ) ); ?> 
			</form>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
</div>