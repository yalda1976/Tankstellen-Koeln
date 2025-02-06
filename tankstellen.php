<?php
/*
Plugin Name: Tankstellen Köln
Plugin URI: https://github.com/yalda1976/Tankstellen-Koeln
Description: WordPress-Plugin zur Darstellung von Tankstellen-Daten mit Bootstrap Cards
Version: 1.1
Author: Yalda
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Update URI: https://github.com/yalda1976/Tankstellen
*/

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch gas station data with caching.
 */
function get_tankstellen_data() {

	$cache_key   = 'tankstellen_data_cache';
	$cached_data = get_transient( $cache_key );

	if ( $cached_data ) {
		return $cached_data; // Return cached data if available.
	}

	// API endpoint containing data.
	$url = 'https://geoportal.stadt-koeln.de/arcgis/rest/services/verkehr/gefahrgutstrecken/MapServer/0/query?where=objectid%20is%20not%20null&text=&objectIds=&time=&geometry=&geometryType=esriGeometryEnvelope&inSR=&spatialRel=esriSpatialRelIntersects&distance=&units=esriSRUnit_Foot&relationParam=&outFields=%2A&returnGeometry=true&returnTrueCurves=false&maxAllowableOffset=&geometryPrecision=&outSR=4326&havingClause=&returnIdsOnly=false&returnCountOnly=false&orderByFields=&groupByFieldsForStatistics=&outStatistics=&returnZ=false&returnM=false&gdbVersion=&historicMoment=&returnDistinctValues=false&resultOffset=&resultRecordCount=&returnExtentOnly=false&datumTransformation=&parameterValues=&rangeValues=&quantizationParameters=&featureEncoding=esriDefault&f=pjson';

	$response = wp_remote_get( $url );

	// Handle API request errors.
	if ( is_wp_error( $response ) ) {
		error_log( 'Failed to fetch data: ' . $response->get_error_message() );
		return [ 'error' => 'Failed to fetch data: ' . $response->get_error_message() ];
	}

	// Retrieve and decode JSON response.
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	// Validate JSON structure.
	if ( json_last_error() !== JSON_ERROR_NONE || ! isset( $data['features'] ) ) {
		return [ 'error' => 'Invalid JSON structure' ];
	}

	// Extract relevant gas station data.
	$stations = [];
	foreach ( $data['features'] as $feature ) {
		$adresse = $feature['attributes']['adresse'] ?? 'Unbekannte Adresse';
		$x       = $feature['geometry']['x'] ?? 'Nicht verfügbar';
		$y       = $feature['geometry']['y'] ?? 'Nicht verfügbar';

		$stations[] = [
			'adresse' => $adresse,
			'x'       => $x,
			'y'       => $y,
		];
	}

	// Cache API response for 15 minutes.
	set_transient( $cache_key, $stations, 15 * MINUTE_IN_SECONDS );

	return $stations;
}

/**
 * Register Gutenberg Block for displaying gas station data.
 */
function tankstellen_register_block() {
	register_block_type( 'tankstellen/block', array(
		'editor_script'   => 'tankstellen-block-editor',
		'render_callback' => 'tankstellen_render_callback',
		'attributes' => array(
            'columns' => array('type' => 'number', 'default' => 3),
            'cardColor' => array('type' => 'string', 'default' => '#ffffff'),
            'textColor' => array('type' => 'string', 'default' => '#000000'),
            'fontSize' => array('type' => 'number', 'default' => 14),
            'padding' => array('type' => 'number', 'default' => 10),
            'borderRadius' => array('type' => 'number', 'default' => 5)
        )
	) );
}
add_action( 'init', 'tankstellen_register_block' );

/**
 * Enqueue block editor assets (JS for Gutenberg).
 */
function tankstellen_enqueue_editor_assets() {
	wp_register_script(
		'tankstellen-block-editor',
		plugins_url( 'block-editor.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
		filemtime( plugin_dir_path( __FILE__ ) . 'block-editor.js' )
	);
	wp_enqueue_script( 'tankstellen-block-editor' );
}
add_action( 'enqueue_block_editor_assets', 'tankstellen_enqueue_editor_assets' );

/**
 * Enqueue front‑end assets: Bootstrap CSS, plugin styles, and front‑end JS.
 */
function tankstellen_enqueue_frontend_assets() {
	// Enqueue Bootstrap CSS.
	wp_enqueue_style( 'bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' );

	// Enqueue Bootstrap Blocks Plugin styles.
	wp_enqueue_style( 'bbp-styles', plugins_url( 'wp-bootstrap-blocks/build/style-index.css', dirname( __FILE__ ) ) );

	// Enqueue our front‑end JavaScript (no jQuery dependency).
	wp_enqueue_script(
		'tankstellen-frontend',
		plugins_url( 'frontend.js', __FILE__ ),
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'frontend.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'tankstellen_enqueue_frontend_assets' );

/**
 * Render Callback for the Block.
 */
function tankstellen_render_callback( $attributes ) {
	$stations = get_tankstellen_data();
	if ( empty( $stations ) || isset( $stations['error'] ) ) {
		return '<p>No data available</p>';
	}

	// Retrieve attributes from the block, with defaults if not provided.
	$columns      = isset( $attributes['columns'] ) ? (int) $attributes['columns'] : 3;
	$cardColor    = isset( $attributes['cardColor'] ) ? esc_attr( $attributes['cardColor'] ) : '#ffffff';
	$textColor    = isset( $attributes['textColor'] ) ? esc_attr( $attributes['textColor'] ) : '#000000';
	$fontSize     = isset( $attributes['fontSize'] ) ? (int) $attributes['fontSize'] : 14;
	$padding      = isset( $attributes['padding'] ) ? (int) $attributes['padding'] : 10;
	$borderRadius = isset( $attributes['borderRadius'] ) ? (int) $attributes['borderRadius'] : 5;

	// Adjust column class based on number of columns.
	//$columnClass = ( $columns === 1 ) ? 'col-12' : ( ( $columns === 2 ) ? 'col-md-6' : 'col-md-4' );
$columnClass = ($columns == 1) ? 'col-12' : 
              (($columns == 2) ? 'col-md-6' : 
              (($columns == 3) ? 'col-md-4' : 'col-lg-3'));


	// Begin building the output HTML.
	$output = '<div class="tankstellen-block container mt-4">';

	// Add search and sort input.
	$output .= '<div class="row mb-3">';
	$output .= '<div class="col-md-8"><input type="text" id="station-search" class="form-control" placeholder="Suche nach Straßennamen..."></div>';
	$output .= '<div class="col-md-4"><select id="station-sort" class="form-select">
					<option value="default">Sortieren nach</option>
					<option value="asc">Straßennamen (A-Z)</option>
					<option value="desc">Straßennamen (Z-A)</option>
				</select></div>';
	$output .= '</div>';

	$output .= '<div data-type="wp-bootstrap-blocks/row" class="wp-bootstrap-blocks-row row alignfull" id="station-list">';

	// Loop through each station and create a card.
	foreach ( $stations as $station ) {
    $output .= sprintf(
    '<div data-type="wp-bootstrap-blocks/column" class="%s station-card mb-4" data-name="%s">
        <div class="card h-100" style="background-color: %s; color: %s; padding: %dpx; border-radius: %dpx;">
            <div class="card-body p-4">
                <h5 class="card-title mb-3" style="color: %s; font-size: %dpx;">%s</h5>
                <p class="card-text" style="color: %s; font-size: %dpx;">x: (%s, y: %s)</p>
            </div>
        </div>
    </div>',
        esc_attr( $columnClass ),
        esc_attr( $station['adresse'] ),
        esc_attr( $cardColor ),
        esc_attr( $textColor ),
        $padding,
        $borderRadius,
        esc_attr( $textColor ),
        $fontSize + 2,
        esc_html( $station['adresse'] ),
        esc_attr( $textColor ),
        $fontSize,
        esc_html( $station['x'] ),
        esc_html( $station['y'] )
    );
}

$output .= '</div></div>';


	// Note: The inline JavaScript for sorting has been removed.
	return $output;
}
