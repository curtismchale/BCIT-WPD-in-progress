<?php

/**
 * Add metaboxes to products so we can restrict the purchase button
 *
 * @since 1.0
 * @author Curtis McHale
 */
class BCIT_WPD_Restrict_Purchase_Meta{

	function __construct(){

		add_action( 'load-post.php', array( $this, 'metaboxes_setup' ) );
		add_action( 'load-post-new.php', array( $this, 'metaboxes_setup' ) );

	} // __construct

	/**
	 * Adds our actions to start our metaboxes
	 *
	 * @since 1.0
	 * @author Curtis
	 */
	public function metaboxes_setup(){

		add_action( 'add_meta_boxes', array( $this, 'add_post_metaboxes' ) );

		add_action( 'save_post', array( $this, 'save_post_meta' ), 10, 2 );

	} // metaboxes_setup

	/**
	 * Adds the metabox with add_meta_box
	 *
	 * @since 1.0
	 * @author Curtis
	 *
	 * @uses add_meta_box()             Adds the metabox to our site with the args
	 */
	public function add_post_metaboxes(){

		add_meta_box(
			'bcit-wpd-restrict-content',         // $id - HTML 'id' attribute of the section
			'Restrict Content',                  // $title - Title that the user will see
			array( $this, 'display_metaboxes' ), // $callback - The function that will display metaboxes
			'product',                           // $posttype - The registered name of the post type we are adding to
			'side',                              // $content - where it shows on the page. normal/side/advanced
			'high'                               // $priority - How important is the display
			// '$callback_args'                  // any extra params that the callback should get
		);

	} // add_post_metaboxes

	public function display_metaboxes( $post_object, $box ){

		wp_nonce_field( basename( __FILE__ ), 'bcit_wpd_meta_nonce'.$post_object->ID );

		$check_value = get_post_meta( $post_object->ID, '_bcit_wpd_restrict_purchase', true ) ? 1 : 0;

	?>
		<p>
			<label for="bcit-wpd-restrict-content-check">Should we restrict the purchase</label><br />
			<input class="widefat" type="checkbox" id="bcit-wpd-restrict-content-check" name="bcit-wpd-restrict-content-check" value="1" <?php checked( $check_value, 1 ); ?> size="30" />
		</p>

		<p>
			<label for="bcit-wpd-restrict-content-message">Restrict Content Message</label>
			<input class="widefat" type="text" id="bcit-wpd-restrict-content-message" name="bcit-wpd-restrict-content-message" value="<?php echo esc_attr( get_post_meta( $post_object->ID, '_bcit_wpd_restrict_content_message', true ) ); ?>" size="30" />
			<span class="description">Add a message if we want a custom message</span>
		</p>
	<?php
	}

	public function save_post_meta( $post_id, $post ){

		// check that the nonce exists
		if ( ! isset( $_POST['bcit_wpd_meta_nonce'.$post_id] ) ) {
			return $post_id;
		}

		// verify that the nonce is correct
		if ( ! wp_verify_nonce( $_POST['bcit_wpd_meta_nonce'.$post_id], basename( __FILE__ ) ) ){
			return $post_id;
		}

		$post_type = get_post_type_object( $post->post_type );
		if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
			return $post_id;
		}

		if ( empty( $_POST['bcit-wpd-restrict-content-message'] ) ) {
			delete_post_meta( absint( $post_id ), '_bcit_wpd_restrict_content_message' );
		} else {
			$value = strip_tags( $_POST['bcit-wpd-restrict-content-message'] );
			update_post_meta( $post_id, '_bcit_wpd_restrict_content_message', esc_attr( $value ) );
		}

		if ( empty( $_POST['bcit-wpd-restrict-content-check'] ) ) {
			delete_post_meta( absint( $post_id ), '_bcit_wpd_restrict_purchase' );
		} else {
			$value = $_POST['bcit-wpd-restrict-content-check'];
			update_post_meta( $post_id, '_bcit_wpd_restrict_purchase', (bool) $value );
		}

	} // save_post_meta

} // BCIT_WPD_Restrict_Purchase_Meta

new BCIT_WPD_Restrict_Purchase_Meta();
