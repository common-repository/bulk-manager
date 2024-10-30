<?php
/**
 * Admin View: Settings
 *
 * @package BulkManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tab_title = isset( $tabs[ $active_tab ]['tab_label'] ) ? $tabs[ $active_tab ]['tab_label'] : '';
?>
<div class="wrap bulk-manager-settings">
	<form method="post" id="mainform" action="" enctype="multipart/form-data">
		<nav class="nav-tab-wrapper">
			<?php
			if(!empty($tabs) && count($tabs) > 0){
				foreach ( $tabs as $slug => $label ) {
					echo '<a href="' . esc_html( admin_url( 'admin.php?page=bulk-manager-dashboard&tab=' . esc_attr( $slug ) ) ) . '" class="nav-tab ' . esc_attr( $active_tab === $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label['label'] ) . '</a>';
				};
			}
			?>
		</nav>

		<?php if($tab_title) :?>
			<h1><?php echo esc_html($tab_title); ?></h1>
		<?php endif; ?>

		<?php
			do_action( 'bulk_manager_settings_' . $active_tab );
			do_action( 'bulk_manager_settings_save_button_' . $active_tab );
		?>
	</form>
</div>
