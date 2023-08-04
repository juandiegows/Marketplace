<?php
/**
 * Pro version of the library page.
 *
 * @package WPCode
 */

/**
 * Class WPCode_Admin_Page_Library_Pro.
 */
class WPCode_Admin_Page_Library_Pro extends WPCode_Admin_Page_Library {
	/**
	 * Page-specific hooks.
	 *
	 * @return void
	 */
	public function page_hooks() {
		parent::page_hooks();
		add_action( 'admin_init', array( $this, 'maybe_add_from_my_cloud' ) );
		add_filter( 'wpcode_admin_js_data', array( $this, 'maybe_add_my_cloud_data' ) );
	}

	/**
	 * Get the data handler specific to the view.
	 *
	 * @return WPCode_Library|WPCode_My_Favorites|WPCode_My_Library
	 */
	public function get_data_handler() {
		if ( ! isset( $this->data_handler ) ) {
			switch ( $this->view ) {
				case 'my_library':
					$this->data_handler = wpcode()->my_library;
					break;
				case 'my_favorites':
					$this->data_handler = new WPCode_My_Favorites();
					break;
				default:
					$this->data_handler = wpcode()->library;
			}
		}

		return $this->data_handler;
	}

	/**
	 * Handler for adding snippets from the my_cloud snippets.
	 *
	 * @return void
	 */
	public function maybe_add_from_my_cloud() {
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'wpcode_add_from_cloud' ) ) {
			return;
		}
		$cloud_id = isset( $_GET['cloud_id'] ) ? sanitize_key( $_GET['cloud_id'] ) : 0;

		if ( empty( $cloud_id ) ) {
			return;
		}

		$snippet = $this->get_data_handler()->create_new_snippet( $cloud_id );

		if ( $snippet ) {
			$url = add_query_arg(
				array(
					'page'       => 'wpcode-snippet-manager',
					'snippet_id' => $snippet->get_id(),
				),
				admin_url( 'admin.php' )
			);
		} else {
			$url = add_query_arg(
				array(
					'message' => 1,
				),
				remove_query_arg(
					array(
						'_wpnonce',
						'cloud_id',
					)
				)
			);
		}

		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Add my-cloud snippet data.
	 *
	 * @param array $data The data localised.
	 *
	 * @return array
	 */
	public function maybe_add_my_cloud_data( $data ) {
		if ( 'library' === $this->view ) {
			return $data;
		}

		$snippets = $this->get_data_handler()->get_data();

		if ( empty( $snippets['snippets'] ) ) {
			return $data;
		}

		foreach ( $snippets['snippets'] as $key => $snippet ) {
			$snippets['snippets'][ $key ]['library_id'] = $snippet['cloud_id'];

			$updated_date_time_string = sprintf(
			// Translators: Used for displaying the updated date, in the format Date at time, eg: August 15, 2022 at 10:32.
				__( '%1$s at %2$s', 'wpcode-premium' ),
				date_i18n( 'F d, Y', $snippet['updated'] ),
				date_i18n( 'g:i a', $snippet['updated'] )
			);

			$snippets['snippets'][ $key ]['updated_text'] = sprintf(
			// Translators: the date at which a cloud snippet has been updated.
				esc_html__( 'Updated on %s', 'wpcode-premium' ),
				$updated_date_time_string
			);
		}
		$data['library']['snippets']   = $snippets['snippets'];
		$data['cloud_edit_url']        = WPCode()->my_library->get_editor_url();
		$data['confirm_delete_title']  = __( 'Are you sure you want to delete this snippet?', 'wpcode-premium' );
		$data['confirm_delete_text']   = __( 'This will delete the snippet from your library, if the snippet is used locally that snippet will not be affected.', 'wpcode-premium' );
		$data['confirm_delete_button'] = __( 'Delete snippet', 'wpcode-premium' );

		return $data;
	}

	/**
	 * Output specific for the "my cloud" items output.
	 *
	 * @param array  $snippet The snippet object.
	 * @param string $category The category of this snippet (for filtering).
	 *
	 * @return void
	 */
	public function get_my_cloud_snippet_item( $snippet = array(), $category = '*' ) {
		$title         = '';
		$url           = '';
		$description   = '';
		$button_text   = __( 'Use snippet', 'wpcode-premium' );
		$used_snippets = $this->get_data_handler()->get_used_library_snippets();
		$pill_text     = '';
		$snippet_id    = 0;
		if ( ! empty( $snippet ) ) {
			$url = add_query_arg(
				array(
					'page'   => 'wpcode-snippet-manager',
					'custom' => true,
				),
				admin_url( 'admin.php' )
			);
			if ( 0 !== $snippet['cloud_id'] ) {

				if ( ! empty( $used_snippets[ $snippet['cloud_id'] ] ) ) {
					$snippet_id  = absint( $used_snippets[ $snippet['cloud_id'] ] );
					$url         = add_query_arg(
						array(
							'page'       => 'wpcode-snippet-manager',
							'snippet_id' => $snippet_id,
						),
						admin_url( 'admin.php' )
					);
					$button_text = __( 'Edit snippet', 'wpcode-premium' );
					$pill_text   = __( 'Used', 'wpcode-premium' );
				} else {
					$url = wp_nonce_url(
						add_query_arg(
							array(
								'cloud_id' => $snippet['cloud_id'],
								'page'     => 'wpcode-library',
								'view'     => $this->view,
							),
							admin_url( 'admin.php' )
						),
						'wpcode_add_from_cloud'
					);
				}
			}
			$title       = $snippet['title'];
			$description = $snippet['note'];
		}
		if ( empty( $description ) ) {
			$description = wp_trim_words( $snippet['code'], 15 );
		}
		$id            = $snippet['cloud_id'];
		$button_2_text = __( 'Preview', 'wpcode-premium' );
		$categories    = isset( $snippet['categories'] ) ? $snippet['categories'] : array();

		$button_1 = array(
			'url'        => $url,
			'text'       => $button_text,
			'attributes' => array(
				'data-snippet-id' => $snippet_id,
			),
		);
		$button_2 = array(
			'text'  => $button_2_text,
			'class' => 'wpcode-button-secondary wpcode-button wpcode-library-preview-button',
		);

		$this->get_list_item( $id, $title, $description, $button_1, $button_2, $categories, $pill_text, 'blue', $category );
	}

	/**
	 * Item actions specific to this class.
	 *
	 * @param string|int $id The id of the element passed for generating action urls.
	 *
	 * @return void
	 */
	public function get_list_item_top_actions( $id ) {
		if ( 'my_library' !== $this->view ) {
			return;
		}
		?>
		<div class="wpcode-list-item-top-actions">
			<button
					type="button"
					class="wpcode-my-library-trash wpcode-button-just-icon"
					data-id="<?php echo esc_attr( $id ); ?>"
					title="<?php esc_attr_e( 'Delete snippet from library', 'wpcode-premium' ); ?>"
			>
				<?php wpcode_icon( 'trash', 13, 16, '0 0 13 16' ); ?>
			</button>
		</div>
		<?php
	}

	/**
	 * Markup for the "My Library" page.
	 *
	 * @return void
	 */
	public function output_view_my_library() {
		if ( wpcode()->library_auth->has_auth() ) {
			?>
			<?php
			$snippets = WPCode()->my_library->get_data();

			if ( empty( $snippets['snippets'] ) ) {
				?>
				<div class="wpcode-alert wpcode-alert-success">
					<?php printf( '<h4>%s</h4>', esc_html__( 'Your library is currently empty, as you create and save snippets to your personal library, they will show up here.', 'wpcode-premium' ) ); ?>
				</div>
				<?php
			} else {
				$this->get_library_markup( $snippets['categories'], $snippets['snippets'], 'get_my_cloud_snippet_item' );
				$this->my_cloud_preview_modal_content();
			}
			?>
			<?php
		} else {
			$this->blurred_placeholder_items();
			// Show message that you need to connect.
			echo WPCode_Admin_Page::get_upsell_box(
				__( 'Your site is not connected to the WPCode Library', 'wpcode-premium' ),
				'<p>' . __( 'Connect your WPCode account and access your Library snippets in the plugin.', 'wpcode-premium' ) . '</p>',
				array(
					'text'  => __( 'Connect to the WPCode Library', 'wpcode-premium' ),
					'tag'   => 'button',
					'class' => 'wpcode-button wpcode-button-large wpcode-start-auth',
				)
			);
		}
	}

	/**
	 * Get the preview modal markup.
	 *
	 * @return void
	 */
	public function my_cloud_preview_modal_content() {
		?>
		<div class="wpcode-library-preview wpcode-modal wpcode-my-library-modal" id="wpcode-library-preview">
			<div class="wpcode-library-preview-header">
				<button type="button" class="wpcode-just-icon-button wpcode-close-modal"><?php wpcode_icon( 'close', 15, 14 ); ?></button>
				<h2><?php esc_html_e( 'Preview Snippet', 'wpcode-premium' ); ?></h2>
			</div>
			<div class="wpcode-library-preview-content">
				<h3>
					<label for="wpcode-code-preview" id="wpcode-preview-title"><?php esc_html_e( 'Code Preview', 'wpcode-premium' ); ?></label>
				</h3>
				<textarea id="wpcode-code-preview"></textarea>
			</div>
			<div class="wpcode-library-preview-buttons">
				<a class="wpcode-button wpcode-button-wide" id="wpcode-preview-use-code"><?php esc_html_e( 'Use Snippet', 'wpcode-premium' ); ?></a>
				<?php if ( 'my_library' === $this->view ) { ?>
					<a class="wpcode-button wpcode-button-secondary" id="wpcode-preview-edit-snippet" target="_blank"><?php esc_html_e( 'Edit in Library', 'wpcode-premium' ); ?></a>
					<div class="wpcode-preview-updated" id="wpcode-preview-updated"></div>
				<?php } ?>
			</div>
		</div>
		<?php
		$editor = new WPCode_Code_Editor( 'text' );
		$editor->set_setting( 'readOnly', 'nocursor' );
		$editor->set_setting( 'gutters', array() );
		$editor->register_editor( 'wpcode-code-preview' );
		$editor->init_editor();
	}

	/**
	 * Override the view for the pro version to load favorites.
	 *
	 * @return void
	 */
	public function output_view_my_favorites() {

		$my_favorites = new WPCode_My_Favorites();
		if ( wpcode()->library_auth->has_auth() ) {
			$snippets = $my_favorites->get_data();

			if ( empty( $snippets['snippets'] ) ) {
				?>
				<div class="wpcode-alert wpcode-alert-success">
					<?php printf( '<h4>%s</h4>', esc_html__( 'You don\'t have any favorites yet, as you add snippets to your favorites list, they will show up here.', 'wpcode-premium' ) ); ?>
				</div>
				<?php
			} else {
				$this->get_library_markup( $snippets['categories'], $snippets['snippets'], 'get_my_cloud_snippet_item' );
				$this->my_cloud_preview_modal_content();
			}
			?>
			<?php
		} else {
			$this->blurred_placeholder_items();
			// Show message that you need to connect.
			echo WPCode_Admin_Page::get_upsell_box(
				__( 'Your site is not connected to the WPCode Library', 'wpcode-premium' ),
				'<p>' . __( 'Connect your WPCode account and access your favourite snippets in the plugin.', 'wpcode-premium' ) . '</p>',
				array(
					'text'  => __( 'Connect to the WPCode Library', 'wpcode-premium' ),
					'tag'   => 'button',
					'class' => 'wpcode-button wpcode-button-large wpcode-start-auth',
				)
			);
		}
	}
}
