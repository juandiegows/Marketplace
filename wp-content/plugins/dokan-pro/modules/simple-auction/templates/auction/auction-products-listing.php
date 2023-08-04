<?php do_action( 'dokan_dashboard_wrap_start' ); ?>

<div class="dokan-dashboard-wrap">
    <?php
    do_action( 'dokan_dashboard_content_before' );
    do_action( 'dokan_auction_product_listing_content_before' );
    ?>

    <div class="dokan-dashboard-content dokan-product-listing">
        <?php

            /**
             *  dokan_auction_product_listing_inside_before hook
             *
             *  @since 2.4
             */
            do_action( 'dokan_auction_product_listing_inside_before' );
            do_action( 'dokan_before_listing_product' );
        ?>

        <?php do_action( 'dokan_before_listing_auction_product' ); ?>

            <article class="dokan-product-listing-area">

                <div class="product-listing-top dokan-clearfix" style="display: flex;">
                    <?php dokan_auction_product_listing_status_filter(); ?>

                    <?php if ( current_user_can( 'dokan_add_auction_product' ) ) { ?>
                        <span class="dokan-add-product-link">
                            <a href="<?php echo dokan_get_navigation_url( 'new-auction-product' ); ?>" class="dokan-btn dokan-btn-theme dokan-right"><i class="fas fa-briefcase">&nbsp;</i> <?php esc_html_e( 'Add New Auction Product', 'dokan' ); ?></a>
                        </span>
                        <span class="button-ml">
                            <a href="<?php echo esc_url( dokan_get_navigation_url( 'auction-activity' ) ); ?>" class="dokan-btn dokan-right"><i class="fa fa-gavel">&nbsp;</i> <?php esc_html_e( 'Auctions Activity', 'dokan' ); ?></a>
                        </span>
                    <?php } ?>
                </div>

                <?php dokan_product_dashboard_errors(); ?>

                <?php
                $post_statuses = [
                    'publish' => __( 'Publish', 'dokan' ),
                    'draft'   => __( 'Draft', 'dokan' ),
                    'pending' => __( 'Pending', 'dokan' ),
                    'vacation' => __( 'Vacation', 'dokan' ),
                ];

                $search         = empty( $_GET['search'] ) ? '' : esc_sql( sanitize_text_field( wp_unslash( $_GET['search'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
                $start_date     = empty( $_GET['start_date'] ) ? '' : sanitize_text_field( wp_unslash( $_GET['start_date'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
                $end_date       = empty( $_GET['end_date'] ) ? '' : sanitize_text_field( wp_unslash( $_GET['end_date'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
                $localized_date = $start_date && $end_date ? dokan_format_date( $start_date ) . ' - ' . dokan_format_date( $end_date ) : '';
                ?>
                <form method="GET" action="" class="dokan-form-inline" id="dokan-auction-products-filter">
                    <div class="dokan-form-group">
                        <input name="search" type="text" class="dokan-form-control" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search Here', 'dokan' ); ?>">
                    </div>

                    <div class="dokan-form-group">
                        <input autocomplete="off" id="auction_date_range" type="text" class="dokan-form-control" placeholder="<?php esc_attr_e( 'Select Date Range', 'dokan' ); ?>" value="<?php echo esc_attr( $localized_date ); ?>">
                        <input autocomplete="off" name="start_date" type="hidden" class="dokan-form-input" value="<?php echo esc_attr( $start_date ); ?>">
                        <input autocomplete="off" name="end_date" type="hidden" class="dokan-form-input" value="<?php echo esc_attr( $end_date ); ?>">
                    </div>

                    <div class="dokan-form-group">
                        <button class="dokan-btn"><span class="fa fa-filter"></span> <?php esc_html_e( 'Filter', 'dokan' ); ?></button>
                        <a id="auction-clear-filter-button" class="dokan-btn"><span class="fa fa-undo"></span> <?php esc_html_e( 'Reset', 'dokan' ); ?></a>
                    </div>
                </form>

                <table class="dokan-table table-striped product-listing-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Image', 'dokan' ); ?></th>
                            <th><?php esc_html_e( 'Name', 'dokan' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'dokan' ); ?></th>
                            <th><?php esc_html_e( 'SKU', 'dokan' ); ?></th>
                            <th><?php esc_html_e( 'Stock', 'dokan' ); ?></th>
                            <th><?php esc_html_e( 'Price', 'dokan' ); ?></th>
                            <th><?php esc_html_e( 'Type', 'dokan' ); ?></th>
                            <th><?php esc_html_e( 'Views', 'dokan' ); ?></th>
                            <th><?php esc_html_e( 'Date', 'dokan' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        global $wpdb;

                        $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
                        $limit   = 20;
                        $args    = [ // $args not for query but for cache
                            'post_status'         => array_keys( $post_statuses ),
                            'ignore_sticky_posts' => 1,
                            'orderby'             => 'post_date',
                            'author'              => dokan_get_current_user_id(),
                            'order'               => 'DESC',
                            'posts_per_page'      => $limit,
                            'tax_query'           => [
                                [
                                    'taxonomy' => 'product_type',
                                    'field' => 'slug',
                                    'terms' => 'auction',
                                ],
                            ],
                            'auction_archive'     => true,
                            'show_past_auctions'  => true,
                            'paged'               => $pagenum,
                        ];

                        $auction_taxonomy_ids = $wpdb->get_col(
                            "SELECT t.term_id
                                    FROM {$wpdb->prefix}terms AS t
                                             INNER JOIN {$wpdb->prefix}term_taxonomy AS tt
                                                        ON t.term_id = tt.term_id
                                    WHERE tt.taxonomy IN ('product_type') AND t.slug IN ('auction')"
                        );

                        $sql = "SELECT SQL_CALC_FOUND_ROWS posts.ID
                                FROM {$wpdb->prefix}posts AS posts
                                         LEFT JOIN {$wpdb->prefix}term_relationships AS term_rel
                                                   ON (posts.ID = term_rel.object_id)
                                         LEFT JOIN {$wpdb->prefix}posts AS p2
                                                   ON (posts.post_parent = p2.ID)
                                         LEFT JOIN {$wpdb->prefix}wc_product_meta_lookup wc_product_meta_lookup
                                                   ON posts.ID = wc_product_meta_lookup.product_id
                                         LEFT JOIN {$wpdb->prefix}wc_product_meta_lookup parent_wc_product_meta_lookup
                                                   ON posts.post_type = 'product_variation' AND parent_wc_product_meta_lookup.product_id = posts.post_parent
                                WHERE posts.post_type IN ('product','product_variation')\n";

                        $sql .= ' AND term_rel.term_taxonomy_id IN ( ';

                        $auction_taxonomy_ids = join(
                            ', ',
                            array_map(
                                function ( $id ) use ( $wpdb ) {
                                    return $wpdb->prepare( '%d', $id );
                                },
                                $auction_taxonomy_ids
                            )
                        );

                        $sql .= $auction_taxonomy_ids . " )\n";

                        $sql .= $wpdb->prepare( " AND posts.post_author IN ( %d )\n", get_current_user_id() );

                        if ( isset( $_GET['post_status'] ) && in_array( $_GET['post_status'], array_keys( $post_statuses ) ) ) {
                            $status = sanitize_text_field( wp_unslash( $_GET['post_status'] ) );

                            $args['post_status'] = $status;

                            $sql .= $wpdb->prepare(
                                'AND ( posts.post_status = "%s" OR ( posts.post_status = \'inherit\' AND p2.post_status = "%s" ) )',
                                $status, $status
                            );
                        } else {
                            $sql .= ' AND ( posts.post_status IN ( ';

                            $statuses = join(
                                ', ',
                                array_map(
                                    function( $status_key ) use ( $wpdb ) {
                                        return $wpdb->prepare( '"%s"', $status_key );
                                    },
                                    array_keys( $post_statuses )
                                )
                            );

                            $sql .= $statuses . ' ) OR ( posts.post_status = \'inherit\' AND ( p2.post_status IN ( ';
                            $sql .= $statuses . ") ) ) )\n";
                        }

                        if ( ! empty( $_GET['search'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                            $keyword = '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_GET['search'] ) ) ) . '%'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

                            $args['s'] = $keyword;

                            $sql .= $wpdb->prepare(
                                " AND ( ( posts.post_title LIKE \"%s\" )
                                        OR ( posts.post_excerpt LIKE \"%s\" )
                                        OR ( posts.post_content LIKE \"%s\" )
                                        OR ( wc_product_meta_lookup.sku LIKE \"%s\" )
                                        OR ( wc_product_meta_lookup.sku = '' AND parent_wc_product_meta_lookup.sku LIKE \"%s\" ) )\n",
                                $keyword, $keyword, $keyword, $keyword, $keyword
                            );
                        }

                        $date_regex = '/^[\d]{4}-(([0]\d)|([1][1|2]))-[0-3]{1}\d{1}$/m';

                        if ( ! empty( $start_date ) && preg_match( $date_regex, $start_date ) ) {
                            $sql .= $wpdb->prepare( " AND ( posts.post_date >= \"%s\" )\n", $start_date . ' 00.00.00' );

                            $args['start_date'] = $start_date;
                        }

                        if ( ! empty( $end_date ) ) {
                            $sql .= $wpdb->prepare( " AND ( posts.post_date <= \"%s\" )\n", $end_date .  ' 23:59:59' );

                            $args['end_date'] = $end_date;
                        }

                        $sql .= $wpdb->prepare( "\nLIMIT %d, %d\n", ( $pagenum - 1 ) * $limit, $limit );

                        $cache_group   = "auction_products_{$args['author']}";
                        $cache_key     = 'products_' . md5( wp_json_encode( $args ) );
                        $product_query = WeDevs\Dokan\Cache::get( $cache_key, $cache_group );

                        if ( false === $product_query ) {
                            $product_query = $wpdb->get_results( $sql );
                            $total_rows    = $wpdb->get_var( 'SELECT FOUND_ROWS();' );
                            WeDevs\Dokan\Cache::set( $cache_key, $product_query, $cache_group );
                        }

                        if ( count( $product_query ) > 0 ) {
                            foreach ( $product_query as $product_id ) {
                                $product_post = get_post( $product_id );
                                $product  = dokan_wc_get_product( $product_id );

                                $tr_class = ( 'pending' === $product->get_status() ) ? ' class="danger"' : '';
                                $edit_url = add_query_arg(
                                    [
                                        'product_id' => $product->get_id(),
                                        'action' => 'edit',
                                    ],
                                    dokan_get_navigation_url( 'auction' )
                                );
                                ?>
                                <tr<?php echo $tr_class; ?>>
                                    <td data-title="<?php esc_attr_e( 'Image', 'dokan' ); ?>" class="column-thumb">
                                        <?php if ( current_user_can( 'dokan_edit_auction_product' ) ) { ?>
                                            <a href="<?php echo $edit_url; ?>"><?php echo $product->get_image(); ?></a>
                                        <?php } else { ?>
                                            <a href="#"><?php echo $product->get_image(); ?></a>
                                        <?php } ?>
                                    </td>

                                    <td class="column-primary">
                                        <?php if ( current_user_can( 'dokan_edit_auction_product' ) ) { ?>
                                            <p><a href="<?php echo $edit_url; ?>"><?php echo $product->get_title(); ?></a></p>
                                        <?php } else { ?>
                                            <p><a href=""><?php echo $product->get_title(); ?></a></p>
                                        <?php } ?>

                                        <div class="row-actions">
                                            <?php if ( current_user_can( 'dokan_edit_auction_product' ) ) { ?>
                                                <span class="edit"><a href="<?php echo $edit_url; ?>"><?php esc_html_e( 'Edit', 'dokan' ); ?></a> | </span>
                                            <?php } ?>

                                            <?php if ( current_user_can( 'dokan_delete_auction_product' ) ) { ?>
                                                <span class="delete"><a onclick="dokan_show_delete_prompt( event, dokan.delete_confirm );"
                                                                        href="
                                                                        <?php
                                                                        echo wp_nonce_url(
                                                                            add_query_arg(
                                                                                [
                                                                                    'action' => 'dokan-delete-auction-product',
                                                                                    'product_id' => $product->get_id(),
                                                                                ],
                                                                                dokan_get_navigation_url( 'auction' )
                                                                            ), 'dokan-delete-auction-product'
                                                                        );
                                                                        ?>
                                                                        "><?php esc_html_e( 'Delete Permanently', 'dokan' ); ?></a> | </span>
                                            <?php } ?>

                                            <span class="view"><a href="<?php echo get_permalink( $product->get_id() ); ?>" rel="permalink"><?php esc_html_e( 'View', 'dokan' ); ?></a></span>
                                        </div>

                                        <button type="button" class="toggle-row"></button>
                                    </td>

                                    <td class="post-status" data-title="<?php esc_attr_e( 'Status', 'dokan' ); ?>">
                                    <label class="dokan-label <?php echo esc_attr( dokan_get_post_status_label_class( $product->get_status() ) ); ?>"><?php echo esc_html( dokan_get_post_status( $product->get_status() ) ); ?></label>
                                    </td>

                                    <td data-title="<?php esc_attr_e( 'SKU', 'dokan' ); ?>">
                                        <?php
                                        if ( $product->get_sku() ) {
                                            echo $product->get_sku();
                                        } else {
                                            echo '<span class="na">&ndash;</span>';
                                        }
                                        ?>
                                    </td>

                                    <td data-title="<?php esc_attr_e( 'Stock', 'dokan' ); ?>">
                                        <?php
                                        if ( $product->is_in_stock() ) {
                                            echo '<mark class="instock">' . __( 'In stock', 'dokan' ) . '</mark>';
                                        } else {
                                            echo '<mark class="outofstock">' . __( 'Out of stock', 'dokan' ) . '</mark>';
                                        }

                                        if ( $product->managing_stock() ) {
                                            if ( version_compare( WC_VERSION, '2.7', '>' ) ) {
                                                echo ' &times; ' . $product->get_stock_quantity();
                                            } else {
                                                echo ' &times; ' . $product->get_total_stock();
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td data-title="<?php esc_attr_e( 'Price', 'dokan' ); ?>">
                                        <?php
                                        if ( $product->get_price_html() ) {
                                            echo $product->get_price_html();
                                        } else {
                                            echo '<span class="na">&ndash;</span>';
                                        }
                                        ?>
                                    </td>

                                    <td data-title="<?php esc_attr_e( 'Type', 'dokan' ); ?>">
                                        <?php
                                        $class = '';

                                        if ( 'auction' === $product->get_type() ) {
                                            if ( $product->is_closed() ) {
                                                $class .= ' finished ';
                                            }

                                            if ( $product->get_auction_fail_reason() === '1' ) {
                                                $class .= ' no_bid fail ';
                                            }

                                            if ( $product->get_auction_fail_reason() === '2' ) {
                                                $class .= ' no_reserve fail';
                                            }

                                            if ( $product->get_auction_closed() === '3' ) {
                                                $class .= ' sold ';
                                            }

                                            if ( $product->get_auction_payed() ) {
                                                $class .= ' payed ';
                                            }
                                            echo "<span class='tips' title='Auction'><i class='fa fa-gavel " . $class . "'></i></span>";
                                        }

                                        if ( get_post_meta( $product->get_id(), '_auction', true ) ) {
                                            echo "<span class='product-type tips auction' title='Auction'><i class='fa fa-gavel order'></i><span>";
                                        }
                                        ?>
                                    </td>

                                    <td data-title="<?php esc_attr_e( 'Views', 'dokan' ); ?>">
                                        <?php echo (int) get_post_meta( $product->get_id(), 'pageview', true ); ?>
                                    </td>

                                    <td class="post-date" data-title="<?php esc_attr_e( 'Date', 'dokan' ); ?>">
                                        <?php
                                        if ( '0000-00-00 00:00:00' === $product->get_date_created() ) {
                                            $t_time    = __( 'Unpublished', 'dokan' );
                                            $h_time    = $t_time;
                                            $time_diff = 0;
                                        } else {
                                            $t_time = get_the_time( __( 'Y/m/d g:i:s A', 'dokan' ) );
                                            $m_time = $product->get_date_modified();
                                            $time   = get_post_time( 'G', true, $product_post );

                                            $time_diff = time() - $time;

                                            if ( $time_diff > 0 && $time_diff < 24 * 60 * 60 ) {
                                                /* translators: %s is human readable time difference */
                                                $h_time = sprintf( __( '%s ago', 'dokan' ), human_time_diff( $time ) );
                                            } else {
                                                $h_time = mysql2date( __( 'Y/m/d', 'dokan' ), $m_time );
                                            }
                                        }

                                        echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $product_post, 'date', 'all' ) . '</abbr>';
                                        echo '<br />';

                                        if ( 'publish' === $product->get_status() ) {
                                            esc_html_e( 'Published', 'dokan' );
                                        } elseif ( 'future' === $product->get_status() ) {
                                            if ( $time_diff > 0 ) {
                                                echo '<strong class="attention">' . __( 'Missed schedule', 'dokan' ) . '</strong>';
                                            } else {
                                                esc_html_e( 'Scheduled', 'dokan' );
                                            }
                                        } else {
                                            esc_html_e( 'Last Modified', 'dokan' );
                                        }
                                        ?>
                                    </td>
                                </tr>

                            <?php } ?>

                            <?php } else { ?>
                            <tr>
                                <td colspan="9"><?php esc_html_e( 'No product found', 'dokan' ); ?></td>
                            </tr>
                        <?php } ?>

                    </tbody>

                </table>

                <?php
                $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;

                $max_num_pages = ceil( $total_rows / $limit );

                if ( $max_num_pages > 1 ) {
                    echo '<div class="pagination-wrap">';
                    $page_links = paginate_links(
                        [
                            'current'   => $pagenum,
                            'total'     => $max_num_pages,
                            'base'      => add_query_arg( 'pagenum', '%#%' ),
                            'format'    => '',
                            'type'      => 'array',
                            'prev_text' => __( '&laquo; Previous', 'dokan' ),
                            'next_text' => __( 'Next &raquo;', 'dokan' ),
                        ]
                    );

                    echo '<ul class="pagination"><li>';
                    echo join( "</li>\n\t<li>", $page_links );
                    echo "</li>\n</ul>\n";
                    echo '</div>';
                }
                ?>
            </article>

        <?php do_action( 'dokan_after_listing_auction_product' ); ?>
        <?php

            /**
             *  dokan_auction_product_listing_inside_after hook
             *
             *  @since 2.4
             */
            do_action( 'dokan_auction_product_listing_inside_after' );
        ?>
    </div><!-- #primary .content-area -->

     <?php
        /**
         *  dokan_dashboard_content_after hook
         *  dokan_withdraw_content_after hook
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_after' );
        do_action( 'dokan_auction_product_listing_content_after' );
        ?>
</div><!-- .dokan-dashboard-wrap -->

<?php do_action( 'dokan_dashboard_wrap_end' ); ?>

<script>
    ;(function($) {
        $( document ).ready( function() {
            $( '#auction-clear-filter-button' ).on( 'click', function () {
                window.location = window.location.href.split("?")[0];
            } );

            let localeData = {
                format : dokan_get_daterange_picker_format(),
                ...dokan_helper.daterange_picker_local
            };

            const auction_date_range = $('#auction_date_range');

            auction_date_range.daterangepicker({
                autoUpdateInput : false,
                locale          : localeData,
            });

            // Set the value for date range field to show frontend.
            auction_date_range.on( 'apply.daterangepicker', function( ev, picker ) {
                $( this ).val( picker.startDate.format( localeData.format ) + ' - ' + picker.endDate.format( localeData.format ) );
                // Set the value for date range fields to send backend
                $('input[name="start_date"]').val(picker.startDate.format('YYYY-MM-DD'));
                $('input[name="end_date"]').val(picker.endDate.format('YYYY-MM-DD'));
            });

            // Clear the data
            auction_date_range.on( 'cancel.daterangepicker', function( ev, picker ) {
                $( this ).val( '' );
                $('input[name="start_date"]').val('');
                $('input[name="end_date"]').val('');
            });
        });
    })(jQuery)
</script>
