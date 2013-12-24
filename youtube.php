<?php

/*
 * Plugin Name:   YouTube for Types
 * Plugin URI:    http://alttypes.wordpress.com/youtube
 * Description:   Implements a Types custom field for YouTube videos and extends the Types shortcode to show them.
 * Documentation: http://alttypes.wordpress.com/
 * Version:       0.1
 * Author:        Magenta Cuda
 * Author URI:    http://magentacuda.wordpress.com
 * License:       GPL2
 */
 
/*  
    Copyright 2013  Magenta Cuda

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

add_filter( types_register_fields, function( $fields ) {
    #error_log( '##### filter:types_register_fields():backtrace=' . print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), true ) );
    $fields['youtube'] = __FILE__;
    #error_log( '##### filter:types_register_fields():$fields=' . print_r( $fields, TRUE ) );
    return $fields;
} );

function wpcf_fields_youtube() {
    #error_log( '##### wpcf_fields_youtube():backtrace=' . print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), true ) );
    return array(
        'id' => 'ttk-youtube',
        'title' => 'YouTube',
        'description' => 'YouTube',
        'validate' => array('required', 'url'),
        'path' => __FILE__
        #'inherited_field_type' => 'textfield'
    );
}

# Get the field definition form for a YouTube custom field

function wpcf_fields_youtube_insert_form() {
    $form['name'] = array(
        '#type' => 'textfield',
        '#title' => __( 'Name of custom field', 'wpcf' ),
        '#description' => __( 'Under this name field will be stored in DB (sanitized)', 'wpcf' ),
        '#name' => 'name',
        '#attributes' => array('class' => 'wpcf-forms-set-legend'),
        '#validate' => array('required' => array('value' => true)),
    );
    $form['description'] = array(
        '#type' => 'textarea',
        '#title' => __( 'Description', 'wpcf' ),
        '#description' => __( 'Text that describes function to user', 'wpcf' ),
        '#name' => 'description',
        '#attributes' => array('rows' => 5, 'cols' => 1),
    );
    # Let the designer set the video player width and height; This can be overwritten later by the content provider.
    $form['default-width'] = array(
        '#type' => 'textfield',
        '#title' => 'Default Width',
        '#description' => 'default video player width',
        '#name' => 'default_width',
        '#value' => 640
    );
    $form['default-height'] = array(
        '#type' => 'textfield',
        '#title' => 'Default Height',
        '#description' => 'default video player height',
        '#name' => 'default_height',
        '#value' => 360
    );
    return $form;
}

# Get the YouTube custom field data input form

function wpcf_fields_youtube_meta_box_form( $field, $field_object ) {
    #error_log( '##### wpcf_fields_youtube_meta_box_form():$field='        . print_r( $field,        true ) );
    #error_log( '##### wpcf_fields_youtube_meta_box_form():$field_object=' . print_r( $field_object, true ) );
    
    $meta_box_form['name'] = array(
        '#name' => 'wpcf[' . $field['slug'] . ']', // Set this to override default output
        '#type' => 'textfield',
        '#title' => 'YouTube Video URL',
        '#description' => __( 'Your input should look something like "http://www.youtube.com/watch?v=556cyHpG_Hs"',
                'wpcf' )
    );
    return $meta_box_form;
}

# Get the Types shortcode editor for this video

function wpcf_fields_youtube_editor_callback( $field, $settings ) {
    #error_log( '##### wpcf_fields_youtube_editor_callback():$field='    . print_r( $field,    true ) );
    #error_log( '##### wpcf_fields_youtube_editor_callback():$settings=' . print_r( $settings, true ) );
    
    $player_width  = !empty( $settings['player_width']  ) ? $settings['player_width']  : $field['data']['default_width'];
    $player_height = !empty( $settings['player_height'] ) ? $settings['player_height'] : $field['data']['default_height'];
    $start_time    = !empty( $settings['start_time'] )    ? $settings['start_time']    : 0;
    $auto_play     = !empty( $settings['auto_play'] )     ? ' checked'                 : '';
    $loop          = !empty( $settings['loop'] )          ? ' checked'                 : '';
    # the $settings code was derived from the example code in function wpcf_fields_google_map_editor_callback() of google_map.php
    # however $settings seems to always be the empty array - why?
   
    # Construct the form as a table of input parameters
    
    ob_start();
    ?>
    <table>
    <tr><td><?php _e( 'Video Player Width', 'wpcf' ); ?></td>
        <td><input type="text" name="player_width" value="<?php echo $player_width; ?>" /></td></tr>
    <tr><td><?php _e( 'Video Player Height', 'wpcf' ); ?></td>
        <td><input type="text" name="player_height" value="<?php echo $player_height; ?>" /></td></tr>
    <tr><td><?php _e( 'Video Start Time', 'wpcf' ); ?></td>
        <td><input type="text" name="start_time" value="<?php echo $start_time; ?>" /></td></tr>
    <tr><td><?php _e( 'Auto Play', 'wpcf' ); ?></td>
        <td><input type="checkbox" name="auto_play"<?php echo $auto_play; ?>" /></td></tr>
    <tr><td><?php _e( 'Loop', 'wpcf' ); ?></td>
        <td><input type="checkbox" name="loop"<?php echo $loop; ?>" /></td></tr>
    </table>
    <?php
    $form = ob_get_contents();
    ob_get_clean();
    
    return array(
        #'supports' => array('styling', 'style'),
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => $form,
            )
        )
    );
}

# Get the Types shortcode for this YouTube video

function wpcf_fields_youtube_editor_submit( $data, $field ) {
    #error_log( '##### wpcf_fields_youtube_editor_submit():$data='  . print_r( $data,  true ) );
    #error_log( '##### wpcf_fields_youtube_editor_submit():$field=' . print_r( $field, true ) );
    
    # Add parameters
    
    $add = '';
    if ( !empty( $data['player_width'] ) ) {
        $add .= ' player_width="'  . strval( $data['player_width'] ) . '"';
    }
    if ( !empty( $data['player_height'] ) ) {
        $add .= ' player_height="' . strval( $data['player_height'] ) . '"';
    }
    if ( !empty( $data['start_time'] ) ) {
        $add .= ' start_time="'    . strval( $data['start_time'] ) . '"';
    }
    if ( !empty( $data['auto_play'] ) ) {
        $add .= ' auto_play="on"';
    }
    if ( !empty( $data['loop'] ) ) {
        $add .= ' loop="on"';
    }
    
    # Generate and return shortcode
    
    $shortcode = wpcf_fields_get_shortcode( $field, $add );
    #error_log( '##### wpcf_fields_youtube_editor_submit():return=' . $shortcode );
    return $shortcode;
}

# Get the HTML for the YouTube iframe player for this video

function wpcf_fields_youtube_view( $params ) {
    #error_log( '##### wpcf_fields_related_view():backtrace=' . print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), true ) );
    #error_log( '##### wpcf_fields_related_view():$params=' . print_r( $params, true ) );
    
    # Format the parameters for use in youtube player url
    
    $url       = $params['field_value'];
    $width     = $params['player_width'];
    $height    = $params['player_height'];
    $start     = ( !empty( $params['start_time'] )                                 ) ? "&start=$params[start_time]" : '';
    $auto_play = ( !empty( $params['auto_play']  ) && $params['auto_play'] == 'on' ) ? '&autoplay=1'                : '';
    $loop      = ( !empty( $params['loop']       ) && $params['loop']      == 'on' ) ? '&loop=1'                    : '';
    
    # Extract the video id from the YouTube url
    
    $url = parse_url( $url );
    if ( !empty( $url['query'] ) ) {
        parse_str( $url['query'], $query );
        if ( !empty( $query['v'] ) ) {
            $videoId = $query['v'];
        }
    }
    if ( empty( $videoId ) ) {
        return <<< EOT
    <h1>{$params['field']['name']}: $params[field_value] is not a valid YouTube video URL</h1>
EOT;
    }
    
    # Return the HTML for YouTube iframe player
    
    $html = <<< EOT
    <iframe id="youtube-player" class="youtube-player" type="text/html" width="$width" height="$height"
        src="http://www.youtube.com/embed/{$videoId}?modestbranding=1&rel=0{$start}{$auto_play}{$loop}"
        frameborder="0" allowfullscreen></iframe>
EOT;
    #error_log( '##### wpcf_fields_youtube_view():return=' . $html );
    return $html;
}

