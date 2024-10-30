<?php

namespace Bulk\Manager\Admin;

/**
 * Handle settings
 */
class Settings
{
    private static $settings = false;

    /**
     * Tab classes.
     */
    public static function get_pages() {
        if ( self::$settings === false ) {
            new Settings\Posts();
            new Settings\Taxonomies();
            if(function_exists('WC')){
                new Settings\Woocommerce();
            }

            self::$settings = true;
        }

		return self::$settings;
    }

    /**
     * Set post types.
     */
    public static function set_all_post_types() {
        bulk_manager_set_all_post_types();
    }

    /**
     * Setting page template handle
     *
     * @return void
     */
    public static function output()
    {
        global $active_tab;

		$tabs = apply_filters( 'admin_settings_tabs', [] );

        $template = __DIR__ . '/views/settings.php';

        if (file_exists($template)) {
            include $template;
        }
    }

    /**
     * Setting save button
     *
     * @return void
     */
    public static function save_button()
    {
        ?>
        <p class="submit">
            <button name="save" class="button-primary bulk-manager-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'bulk-manager' ); ?>"><?php esc_html_e( 'Save changes', 'bulk-manager' ); ?></button>
			<?php wp_nonce_field( 'bulk-manager-save-settings' ); ?>
		</p>
        <?php
    }

    /**
     * Setting page template handle
     *
     * @return void
     */
    public static function save()
    {
        global $active_tab;

        check_admin_referer( 'bulk-manager-save-settings' );

        do_action( 'bulk_manager_settings_save_' . $active_tab );
    }

    /**
     * Get option value from settings.
     */
    public static function get_option( $option_name, $default = '' ) {
        if ( ! $option_name ) {
            return $default;
        }

        $option_value = get_option( $option_name, null );

        if ( is_array( $option_value ) ) {
            $option_value = wp_unslash( $option_value );
        } elseif ( ! is_null( $option_value ) ) {
            $option_value = stripslashes( $option_value );
        }

        return ( null === $option_value ) ? $default : $option_value;
    }

    /**
     * Output form fields
     *
     * @return void
     */
    public static function output_fields( $options ) {
        foreach ( $options as $value ) {
            if ( ! isset( $value['type'] ) ) {
                continue;
            }
            if ( ! isset( $value['id'] ) ) {
                $value['id'] = '';
            }

            if ( ! isset( $value['field_name'] ) ) {
                $value['field_name'] = $value['id'];
            }
            if ( ! isset( $value['title'] ) ) {
                $value['title'] = isset( $value['name'] ) ? $value['name'] : '';
            }
            if ( ! isset( $value['class'] ) ) {
                $value['class'] = '';
            }
            if ( ! isset( $value['wrap_class'] ) ) {
                $value['wrap_class'] = '';
            }
            if ( ! isset( $value['css'] ) ) {
                $value['css'] = '';
            }
            if ( ! isset( $value['default'] ) ) {
                $value['default'] = '';
            }
            if ( ! isset( $value['desc'] ) ) {
                $value['desc'] = '';
            }
            if ( ! isset( $value['desc_tip'] ) ) {
                $value['desc_tip'] = false;
            }
            if ( ! isset( $value['placeholder'] ) ) {
                $value['placeholder'] = '';
            }
            if ( ! isset( $value['suffix'] ) ) {
                $value['suffix'] = '';
            }
            if ( ! isset( $value['value'] ) ) {
                $value['value'] = self::get_option( $value['id'], $value['default'] );
            }

            // Custom attribute handling.
            $custom_attributes = [];

            if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
                foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
                    $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
                }
            }

            // Description handling.
            $field_description = self::get_field_description( $value );
            $description       = $field_description['description'];
            $tooltip_html      = $field_description['tooltip_html'];

            // Switch based on type.
            switch ( $value['type'] ) {

                // Section Titles.
                case 'title':
                    if ( ! empty( $value['title'] ) ) {
                        echo '<h1>' . esc_html( $value['title'] ) . '</h1>';
                    }
                    if ( ! empty( $value['desc'] ) ) {
                        echo '<div id="' . esc_attr( sanitize_title( $value['id'] ) ) . '-description">';
                        echo wp_kses_post( wpautop( wptexturize( $value['desc'] ) ) );
                        echo '</div>';
                    }
                    echo '<table class="form-table">' . "\n\n";
                    break;

                case 'info':
                    echo '<tr><th scope="row" class="titledesc"/><td style="' . esc_attr( $value['css'] ) . '">';
                    echo wp_kses_post( wpautop( wptexturize( $value['text'] ) ) );
                    echo '</td></tr>';
                    break;

                // Section Ends.
                case 'sectionend':
                    echo '</table>';
                    break;

                // Standard text inputs and subtypes like 'number'.
                case 'text':
                case 'password':
                case 'datetime':
                case 'datetime-local':
                case 'date':
                case 'month':
                case 'time':
                case 'week':
                case 'number':
                case 'email':
                case 'url':
                case 'tel':
                    $option_value = $value['value'];

                    $visibility_class = [];

                    if ( ! isset( $value['show_if_checked'] ) ) {
                        $value['show_if_checked'] = false;
                    }
                    if ( 'yes' === $value['show_if_checked'] ) {
                        $visibility_class[] = 'hidden_option';
                    }
                    if ( 'option' === $value['show_if_checked'] ) {
                        $visibility_class[] = 'show_options_if_checked';
                    }

                    if ( ! isset( $value['show_if_matched'] ) ) {
                        $value['show_if_matched'] = false;
                    }
                    if ( 'yes' === $value['show_if_matched'] ) {
                        $visibility_class[] = 'hidden_option '.$value['field_name'];
                    }
                    if ( 'option' === $value['show_if_matched'] ) {
                        $visibility_class[] = 'show_match_if_checked';
                    }

                    ?>
                    <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( sanitize_title($value['id']) ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
                        </th>
                        <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
                            <input
                                name="<?php echo esc_attr( $value['field_name'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                type="<?php echo esc_attr( $value['type'] ); ?>"
                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                value="<?php echo esc_attr( $option_value ); ?>"
                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
                                <?php echo esc_attr(implode( ' ', $custom_attributes )); ?>
                                /> <span><?php echo esc_html( $value['suffix'] ); ?></span> <?php echo wp_kses_post($description); ?>
                        </td>
                    </tr>
                    <?php
                    break;

                // Color picker.
                case 'color':
                    $option_value = $value['value'];

                    ?>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
                        </th>
                        <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">&lrm;
                            <span class="colorpickpreview" style="background: <?php echo esc_attr( $option_value ); ?>">&nbsp;</span>
                            <input
                                name="<?php echo esc_attr( $value['field_name'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                type="text"
                                dir="ltr"
                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                value="<?php echo esc_attr( $option_value ); ?>"
                                class="<?php echo esc_attr( $value['class'] ); ?> colorpick"
                                placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
                                <?php echo esc_attr(implode( ' ', $custom_attributes )); ?>
                                />&lrm; <?php echo wp_kses_post($description); ?>
                                <div id="colorPickerDiv_<?php echo esc_attr( $value['id'] ); ?>" class="colorpickdiv" style="z-index: 100;background:#eee;border:1px solid #ccc;position:absolute;display:none;"></div>
                        </td>
                    </tr>
                    <?php
                    break;

                // Textarea.
                case 'textarea':
                    $option_value = $value['value'];

                    $visibility_class = [];

                    if ( ! isset( $value['show_if_matched'] ) ) {
                        $value['show_if_matched'] = false;
                    }
                    if ( 'yes' === $value['show_if_matched'] ) {
                        $visibility_class[] = 'hidden_option '.$value['field_name'];
                    }
                    if ( 'option' === $value['show_if_matched'] ) {
                        $visibility_class[] = 'show_match_if_checked';
                    }

                    ?>
                    <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
                        </th>
                        <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
                            <?php echo wp_kses_post($description); ?>

                            <textarea
                                name="<?php echo esc_attr( $value['field_name'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
                                <?php echo esc_attr(implode( ' ', $custom_attributes )); ?>
                                ><?php echo esc_textarea( $option_value ); ?></textarea>
                        </td>
                    </tr>
                    <?php
                    break;

                // Select boxes.
                case 'select':
                case 'multiselect':
                    $option_value = $value['value'];

                    $visibility_class = [$value['wrap_class']];

                    if ( ! isset( $value['show_if_matched'] ) ) {
                        $value['show_if_matched'] = false;
                    }
                    if ( 'yes' === $value['show_if_matched'] ) {
                        $visibility_class[] = 'hidden_option '.$value['field_name'];
                    }
                    if ( 'option' === $value['show_if_matched'] ) {
                        $visibility_class[] = 'show_match_if_checked';
                    }

                    if ( ! isset( $value['show_if_checked'] ) ) {
                        $value['show_if_checked'] = false;
                    }
                    if ( 'yes' === $value['show_if_checked'] ) {
                        $visibility_class[] = 'hidden_option';
                    }
                    if ( 'option' === $value['show_if_checked'] ) {
                        $visibility_class[] = 'show_options_if_checked';
                    }
                    ?>
                    <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
                        </th>
                        <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
                            <select
                                name="<?php echo esc_attr( $value['field_name'] ); ?><?php echo esc_attr( 'multiselect' === $value['type'] ? '[]' : '' ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                style="<?php echo esc_attr( $value['css'] ); ?>"
                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                <?php echo esc_attr(implode( ' ', $custom_attributes )); ?>
                                <?php echo esc_attr('multiselect' === $value['type'] ? 'multiple="multiple"' : ''); ?>
                                >
                                <?php
                                foreach ( $value['options'] as $key => $val ) {
                                    ?>
                                    <option value="<?php echo esc_attr( $key ); ?>"
                                        <?php

                                        if ( is_array( $option_value ) ) {
                                            selected( in_array( (string) $key, $option_value, true ), true );
                                        } else {
                                            selected( $option_value, (string) $key );
                                        }

                                        ?>
                                    ><?php echo esc_html( $val ); ?></option>
                                    <?php
                                }
                                ?>
                            </select> <?php echo wp_kses_post($description); ?>
                        </td>
                    </tr>
                    <?php
                    break;

                // Radio inputs.
                case 'radio':
                    $option_value = $value['value'];
                    $disabled_values = $value['disabled'] ?? [];

                    ?>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
                        </th>
                        <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
                            <fieldset>
                                <?php echo wp_kses_post($description); ?>
                                <ul>
                                <?php
                                foreach ( $value['options'] as $key => $val ) {
                                    ?>
                                    <li>
                                        <label><input
                                            name="<?php echo esc_attr( $value['field_name'] ); ?>"
                                            value="<?php echo esc_attr( $key ); ?>"
                                            type="radio"
                                            <?php if( in_array( $key, $disabled_values ) ) { echo 'disabled'; } ?>
                                            style="<?php echo esc_attr( $value['css'] ); ?>"
                                            class="<?php echo esc_attr( $value['class'] ); ?>"
                                            <?php echo esc_attr(implode( ' ', $custom_attributes )); ?>
                                            <?php checked( $key, $option_value ); ?>
                                            /> <?php echo esc_html( $val ); ?>
                                        </label>
                                    </li>
                                    <?php
                                }
                                ?>
                                </ul>
                            </fieldset>
                        </td>
                    </tr>
                    <?php
                    break;

                // Checkbox input.
                case 'checkbox':
                    $option_value     = $value['value'];
                    $visibility_class = [];

                    if ( ! isset( $value['hide_if_checked'] ) ) {
                        $value['hide_if_checked'] = false;
                    }
                    if ( ! isset( $value['show_if_checked'] ) ) {
                        $value['show_if_checked'] = false;
                    }
                    if ( 'yes' === $value['hide_if_checked'] || 'yes' === $value['show_if_checked'] ) {
                        $visibility_class[] = 'hidden_option';
                    }
                    if ( 'option' === $value['hide_if_checked'] ) {
                        $visibility_class[] = 'hide_options_if_checked';
                    }
                    if ( 'option' === $value['show_if_checked'] ) {
                        $visibility_class[] = 'show_options_if_checked';
                    }

                    $must_disable = $value['disabled'] ?? false;

                    if ( ! isset( $value['checkboxgroup'] ) || 'start' === $value['checkboxgroup'] ) {
                        $has_tooltip             = isset( $value['tooltip'] ) && '' !== $value['tooltip'];
                        $tooltip_container_class = $has_tooltip ? 'with-tooltip' : '';
                        ?>
                            <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
                                <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ); ?></th>
                                <td class="forminp forminp-checkbox <?php echo esc_html( $tooltip_container_class ); ?>">
                                    <?php if ( $has_tooltip ) : ?>
                                        <span class="help-tooltip"><?php echo esc_html( bulk_manager_help_tip( $value['tooltip'] ) ); ?></span>
                                    <?php endif; ?>
                                    <fieldset>
                        <?php
                    } else {
                        ?>
                            <fieldset class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
                        <?php
                    }

                    if ( ! empty( $value['title'] ) ) {
                        ?>
                            <legend class="screen-reader-text"><span><?php echo esc_html( $value['title'] ); ?></span></legend>
                        <?php
                    }

                    ?>
                        <label for="<?php echo esc_attr( $value['id'] ); ?>">
                            <input
                                <?php echo esc_attr($must_disable ? 'disabled' : ''); ?>
                                name="<?php echo esc_attr( $value['field_name'] ); ?>"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                type="checkbox"
                                class="<?php echo esc_attr( isset( $value['class'] ) ? $value['class'] : '' ); ?>"
                                value="1"
                                <?php disabled( $value['disabled'] ?? false ); ?>
                                <?php checked( $option_value, 'yes' ); ?>
                                <?php echo esc_attr(implode( ' ', $custom_attributes )); ?>
                            /> <?php echo wp_kses_post($description); ?>
                        </label> <?php echo wp_kses_post($tooltip_html); ?>
                    <?php

                    if ( ! isset( $value['checkboxgroup'] ) || 'end' === $value['checkboxgroup'] ) {
                        ?>
                                    </fieldset>
                                </td>
                            </tr>
                        <?php
                    } else {
                        ?>
                            </fieldset>
                        <?php
                    }
                    break;

                // Single page selects.
                case 'single_select_page':
                    $args = array(
                        'name'             => $value['field_name'],
                        'id'               => $value['id'],
                        'sort_column'      => 'menu_order',
                        'sort_order'       => 'ASC',
                        'show_option_none' => ' ',
                        'class'            => $value['class'],
                        'echo'             => false,
                        'selected'         => absint( $value['value'] ),
                        'post_status'      => 'publish,private,draft',
                    );

                    if ( isset( $value['args'] ) ) {
                        $args = wp_parse_args( $value['args'], $args );
                    }

                    $formatted_field = str_replace( ' id=', " data-placeholder='" . esc_attr__( 'Select a page&hellip;', 'bulk-manager' ) . "' style='" . esc_attr($value['css']) . "' class='" . esc_attr($value['class']) . "' id=", wp_dropdown_pages( $args ) );

                    ?>
                    <tr valign="top" class="single_select_page">
                        <th scope="row" class="titledesc">
                            <label><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
                        </th>
                        <td class="forminp">
                            <?php echo wp_kses(
                                    $formatted_field,
                                    array(
                                        'select'      => array(
                                            'name'              => array(),
                                            'data-placeholder'  => array(),
                                            'style'             => array(),
                                            'class'             => array(),
                                            'id'                => array(),
                                        ),
                                        'option'     => array(
                                            'class'  => array(),
                                            'value'  => array(),
                                        )
                                    )
                                );
                            ?> 
                            <?php echo wp_kses_post($description); ?>
                        </td>
                    </tr>
                    <?php
                    break;

                // Days/months/years selector.
                case 'relative_date_selector':
                    $periods      = array(
                        'days'   => __( 'Day(s)', 'bulk-manager' ),
                        'weeks'  => __( 'Week(s)', 'bulk-manager' ),
                        'months' => __( 'Month(s)', 'bulk-manager' ),
                        'years'  => __( 'Year(s)', 'bulk-manager' ),
                    );
                    $option_value = bulk_manager_parse_relative_date_option( $value['value'] );
                    ?>
                    <tr valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
                        </th>
                        <td class="forminp">
                        <input
                                name="<?php echo esc_attr( $value['field_name'] ); ?>[number]"
                                id="<?php echo esc_attr( $value['id'] ); ?>"
                                type="number"
                                style="width: 80px;"
                                value="<?php echo esc_attr( $option_value['number'] ); ?>"
                                class="<?php echo esc_attr( $value['class'] ); ?>"
                                placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
                                step="1"
                                min="1"
                                <?php echo esc_attr(implode( ' ', $custom_attributes )); ?>
                            />&nbsp;
                            <select name="<?php echo esc_attr( $value['field_name'] ); ?>[unit]" style="width: auto;">
                                <?php
                                foreach ( $periods as $value => $label ) {
                                    echo '<option value="' . esc_attr( $value ) . '"' . selected( $option_value['unit'], $value, false ) . '>' . esc_html( $label ) . '</option>';
                                }
                                ?>
                            </select> <?php echo wp_kses_post( $description ? $description : ''); ?>
                        </td>
                    </tr>
                    <?php
                    break;
                
                case 'image_uploader':
                case 'multi_image_uploader':

                    $multi_image = ('multi_image_uploader' == $value['type']) ? 'multi-image' : '';

                    $visibility_class = [];

                    if ( ! isset( $value['show_if_matched'] ) ) {
                        $value['show_if_matched'] = false;
                    }
                    if ( 'yes' === $value['show_if_matched'] ) {
                        $visibility_class[] = 'hidden_option '.$value['field_name'];
                    }
                    if ( 'option' === $value['show_if_matched'] ) {
                        $visibility_class[] = 'show_match_if_checked';
                    }

                    ?>
                    <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                        </th>
                        <td>
                            <p class="bulk-gallery-uploader">
                                <input id="bulk-browse-button" type="button" value="<?php echo esc_attr('Upload/ Edit Images', 'bulk-manager') ?>" class="button button-primary <?php echo esc_attr($multi_image); ?> <?php echo esc_attr( $value['class'] ); ?>" style="position: relative; z-index: 1;">
                                <input name="gallery-image-ids" class="gallery-image-ids" type="hidden"  id="gallery-image-ids" value="">
                            </p>
                            <div class="bulk-uploaded-gallery-images"></div>
                            <script type="text/html" id="bulk-render-gallery-images">
                                <% if(items.length){ %>
                                    <ul id="bulk-sortable-list" class="ui-sortable">
                                        <% _.each(items, function(item, index) { %>
                                            <li class="ui-sortable-handle">
                                                <img src="<%= item.url %>" alt="<%= item.alt ? item.alt : item.caption ? item.caption : item.title %>">
                                                <span data-id="<%= item.id %>">&times;</span>
                                            </li>
                                        <% }) %>
                                    </ul>
                                <% } %>
                            </script>
                        </td>
                    </tr>
                    <?php
                    break;
                
                case 'wp_editor':
                    $visibility_class = [];

                    if ( ! isset( $value['show_if_matched'] ) ) {
                        $value['show_if_matched'] = false;
                    }
                    if ( 'yes' === $value['show_if_matched'] ) {
                        $visibility_class[] = 'hidden_option '.$value['field_name'];
                    }
                    if ( 'option' === $value['show_if_matched'] ) {
                        $visibility_class[] = 'show_match_if_checked';
                    }

                    ?>
                    <tr valign="top" class="<?php echo esc_attr( implode( ' ', $visibility_class ) ); ?>">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo wp_kses_post($tooltip_html); ?></label>
                        </th>
                        <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
                            <?php echo wp_kses_post($description); ?>

                            <?php
                                $content            = $value['value'];
                                $custom_editor_id   = $value['id'];
                                $custom_editor_name = $value['field_name'];
                                $args = array(
                                        'media_buttons' => true,
                                        'textarea_name' => $custom_editor_name,
                                        'textarea_rows' => 10,
                                        'quicktags'     => true,
                                    );
                                wp_editor( $content, $custom_editor_id, $args );
                            ?>
                        </td>
                    </tr>
                    <?php
                    break;

                // Default: run an action.
                default:
                    do_action( 'bulk_manager_custom_admin_field_' . $value['type'], $value );
                    break;
            }
        }
    }

    /**
     * Get description
     */
    public static function get_field_description( $value ) {
        $description  = '';
        $tooltip_html = '';

        if ( true === $value['desc_tip'] ) {
            $tooltip_html = $value['desc'];
        } elseif ( ! empty( $value['desc_tip'] ) ) {
            $description  = $value['desc'];
            $tooltip_html = $value['desc_tip'];
        } elseif ( ! empty( $value['desc'] ) ) {
            $description = $value['desc'];
        }

        if ( $description && in_array( $value['type'], array( 'textarea', 'radio' ), true ) ) {
            $description = '<p style="margin-top:0">' . wp_kses_post( $description ) . '</p>';
        } elseif ( $description && in_array( $value['type'], array( 'checkbox' ), true ) ) {
            $description = wp_kses_post( $description );
        } elseif ( $description ) {
            $description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
        }

        if ( $tooltip_html && in_array( $value['type'], array( 'checkbox' ), true ) ) {
            $tooltip_html = '<p class="description">' . wp_kses_post($tooltip_html) . '</p>';
        } elseif ( $tooltip_html ) {
            $tooltip_html = bulk_manager_help_tip( $tooltip_html );
        }

        return array(
            'description'  => $description,
            'tooltip_html' => $tooltip_html,
        );
    }

    /**
     * Save settings fields
     *
     * @return void
     */
    public static function save_fields( $options )
    {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( bulk_manager_clean( wp_unslash ( $_POST['_wpnonce'] ) ) , 'bulk-manager-save-settings' ) ) return;

        if ( empty( $_POST ) ) {
            return false;
        }

        // Options to update will be stored here and saved later.
        $update_options   = [];
        $autoload_options = [];

        // Loop options and get values to save.
        foreach ( $options as $option ) {
            if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) || ( isset( $option['is_option'] ) && false === $option['is_option'] ) ) {
                continue;
            }

            $option_name = $option['field_name'] ?? $option['id'];

            // Get posted value.
            if ( strstr( $option_name, '[' ) ) {
                parse_str( $option_name, $option_name_array );
                $option_name  = current( array_keys( $option_name_array ) );
                $setting_name = key( $option_name_array[ $option_name ] );
                $raw_value    = isset( $_POST[ $option_name ][ $setting_name ] ) ? bulk_manager_clean( wp_unslash( $_POST[ $option_name ][ $setting_name ] ) ) : null;
            } else {
                $setting_name = '';
                $raw_value    = isset( $_POST[ $option_name ] ) ? bulk_manager_clean( wp_unslash( $_POST[ $option_name ] ) ) : null;
            }

            // Format the value based on option type.
            switch ( $option['type'] ) {
                case 'checkbox':
                    $value = '1' === $raw_value || 'yes' === $raw_value ? 'yes' : 'no';
                    break;
                case 'textarea':
                    $value = wp_kses_post( trim( $raw_value ) );
                    break;
                case 'multiselect':
                    $value = array_filter( array_map( 'bulk_manager_clean', (array) $raw_value ) );
                    break;
                case 'image_width':
                    $value = [];
                    if ( isset( $raw_value['width'] ) ) {
                        $value['width']  = bulk_manager_clean( $raw_value['width'] );
                        $value['height'] = bulk_manager_clean( $raw_value['height'] );
                        $value['crop']   = isset( $raw_value['crop'] ) ? 1 : 0;
                    } else {
                        $value['width']  = $option['default']['width'];
                        $value['height'] = $option['default']['height'];
                        $value['crop']   = $option['default']['crop'];
                    }
                    break;
                case 'select':
                    $allowed_values = empty( $option['options'] ) ? array() : array_map( 'strval', array_keys( $option['options'] ) );
                    if ( empty( $option['default'] ) && empty( $allowed_values ) ) {
                        $value = null;
                        break;
                    }
                    $default = ( empty( $option['default'] ) ? $allowed_values[0] : $option['default'] );
                    $value   = in_array( $raw_value, $allowed_values, true ) ? $raw_value : $default;
                    break;
                case 'relative_date_selector':
                    $value = bulk_manager_parse_relative_date_option( $raw_value );
                    break;
                default:
                    $value = bulk_manager_clean( $raw_value );
                    break;
            }

            if ( is_null( $value ) ) {
                continue;
            }

            // Check if option is an array and handle that differently to single values.
            if ( $option_name && $setting_name ) {
                if ( ! isset( $update_options[ $option_name ] ) ) {
                    $update_options[ $option_name ] = get_option( $option_name, array() );
                }
                if ( ! is_array( $update_options[ $option_name ] ) ) {
                    $update_options[ $option_name ] = [];
                }
                $update_options[ $option_name ][ $setting_name ] = $value;
            } else {
                $update_options[ $option_name ] = $value;
            }

            $autoload_options[ $option_name ] = isset( $option['autoload'] ) ? (bool) $option['autoload'] : true;
        }

        // Save all options in our array.
        foreach ( $update_options as $name => $value ) {
            update_option( $name, $value, $autoload_options[ $name ] ? 'yes' : 'no' );
        }

        return true;
    }

}
