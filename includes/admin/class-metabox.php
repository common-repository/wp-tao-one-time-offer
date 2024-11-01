<?php

class WPTAO_OTO_Metabox {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_oto_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_oto_meta_box' ), 10, 3 );
	}

	public function add_oto_meta_box() {
		add_meta_box( 'wptao-oto-meta-box', __( 'WP Tao OTO', WPTAO_ONETIMEOFFER_DOMAIN ), array( $this, 'oto_meta_box_markup' ), 'page', 'normal', 'high', null );
	}

	public function oto_meta_box_markup( $object ) {
		wp_nonce_field( basename( __FILE__ ), 'oto-meta-box-nonce' );
		?>
		<div>
			<label for="wptao-oto-url"><?php _e( 'URL', WPTAO_ONETIMEOFFER_DOMAIN ); ?></label>
			<input name="wptao-oto-url" type="text" value="<?php echo get_post_meta( $object->ID, 'wptao-oto-url', true ); ?>" class="regular-text">
			<small><?php _e( 'Eneter URL wher user should be redirected when try to see page again. Leave empty for OTO disabled', WPTAO_ONETIMEOFFER_DOMAIN ); ?></small>
		</div>
		<?php
	}

	public function save_oto_meta_box( $post_id, $post, $update ) {
		if ( !isset( $_POST[ 'oto-meta-box-nonce' ] ) || !wp_verify_nonce( $_POST[ 'oto-meta-box-nonce' ], basename( __FILE__ ) ) )
			return $post_id;

		if ( !current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		if ( 'page' != $post->post_type )
			return $post_id;

		$wptao_oto_url_value = '';

		if ( isset( $_POST[ 'wptao-oto-url' ] ) ) {
			$wptao_oto_url_value = trim( filter_var( $_POST[ 'wptao-oto-url' ], FILTER_SANITIZE_URL ) );
			if ( !filter_var( $wptao_oto_url_value, FILTER_VALIDATE_URL ) ) {
				$wptao_oto_url_value = '';
			}
		}
		update_post_meta( $post_id, 'wptao-oto-url', $wptao_oto_url_value );
	}

}

new WPTAO_OTO_Metabox();
