<?php
/**
 * Seed script for local development.
 *
 * Usage: npm run seed
 * (runs via wp-env: wp eval-file wp-content/plugins/wp-graphql-cloudflare-cache/bin/seed.php)
 */

// Bail if not running in WP-CLI context.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script must be run via WP-CLI.' );
}

echo "Seeding test data...\n";

// --- Categories ---
$categories = [ 'Technology', 'Gaming', 'Entertainment', 'Sports', 'Science' ];
$cat_ids    = [];

foreach ( $categories as $name ) {
	$slug = sanitize_title( $name );
	$term = term_exists( $slug, 'category' );
	if ( $term ) {
		$cat_ids[ $name ] = (int) $term['term_id'];
		echo "  Category '$name' already exists (ID {$cat_ids[$name]})\n";
	} else {
		$result = wp_insert_term( $name, 'category' );
		if ( is_wp_error( $result ) ) {
			echo "  Error creating category '$name': " . $result->get_error_message() . "\n";
			continue;
		}
		$cat_ids[ $name ] = $result['term_id'];
		echo "  Created category '$name' (ID {$cat_ids[$name]})\n";
	}
}

// --- Tags ---
$tags    = [ 'breaking', 'review', 'opinion', 'guide', 'update' ];
$tag_ids = [];

foreach ( $tags as $name ) {
	$slug = sanitize_title( $name );
	$term = term_exists( $slug, 'post_tag' );
	if ( $term ) {
		$tag_ids[ $name ] = (int) $term['term_id'];
		echo "  Tag '$name' already exists (ID {$tag_ids[$name]})\n";
	} else {
		$result = wp_insert_term( $name, 'post_tag' );
		if ( is_wp_error( $result ) ) {
			echo "  Error creating tag '$name': " . $result->get_error_message() . "\n";
			continue;
		}
		$tag_ids[ $name ] = $result['term_id'];
		echo "  Created tag '$name' (ID {$tag_ids[$name]})\n";
	}
}

// --- Posts ---
$posts = [
	[
		'title'      => 'New GPU Lineup Announced for 2025',
		'category'   => 'Technology',
		'tags'       => [ 'breaking', 'update' ],
	],
	[
		'title'      => 'Top 10 Games of the Year',
		'category'   => 'Gaming',
		'tags'       => [ 'review', 'guide' ],
	],
	[
		'title'      => 'Streaming Wars: Who Is Winning?',
		'category'   => 'Entertainment',
		'tags'       => [ 'opinion' ],
	],
	[
		'title'      => 'Championship Finals Recap',
		'category'   => 'Sports',
		'tags'       => [ 'breaking', 'update' ],
	],
	[
		'title'      => 'James Webb Telescope Latest Discoveries',
		'category'   => 'Science',
		'tags'       => [ 'breaking' ],
	],
	[
		'title'      => 'Best Budget Keyboards for Developers',
		'category'   => 'Technology',
		'tags'       => [ 'review', 'guide' ],
	],
	[
		'title'      => 'Esports League Spring Split Preview',
		'category'   => 'Gaming',
		'tags'       => [ 'guide', 'update' ],
	],
	[
		'title'      => 'AI in Everyday Life: A Practical Guide',
		'category'   => 'Science',
		'tags'       => [ 'guide', 'opinion' ],
	],
];

$created = 0;
foreach ( $posts as $i => $post_data ) {
	// Check if post already exists by title.
	$existing = new WP_Query( [
		'post_type'      => 'post',
		'title'          => $post_data['title'],
		'posts_per_page' => 1,
		'fields'         => 'ids',
	] );
	if ( $existing->have_posts() ) {
		echo "  Post '{$post_data['title']}' already exists (ID {$existing->posts[0]})\n";
		continue;
	}

	$post_args = [
		'post_title'   => $post_data['title'],
		'post_content' => "<!-- wp:paragraph --><p>This is sample content for \"{$post_data['title']}\". "
			. 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p><!-- /wp:paragraph -->',
		'post_status'  => 'publish',
		'post_type'    => 'post',
		'post_author'  => 1,
	];

	if ( isset( $cat_ids[ $post_data['category'] ] ) ) {
		$post_args['post_category'] = [ $cat_ids[ $post_data['category'] ] ];
	}

	$post_id = wp_insert_post( $post_args, true );
	if ( is_wp_error( $post_id ) ) {
		echo "  Error creating post '{$post_data['title']}': " . $post_id->get_error_message() . "\n";
		continue;
	}

	// Assign tags.
	if ( ! empty( $post_data['tags'] ) ) {
		$tag_term_ids = array_filter( array_map( function ( $tag ) use ( $tag_ids ) {
			return $tag_ids[ $tag ] ?? null;
		}, $post_data['tags'] ) );
		if ( $tag_term_ids ) {
			wp_set_post_terms( $post_id, array_values( $tag_term_ids ), 'post_tag' );
		}
	}

	echo "  Created post '{$post_data['title']}' (ID $post_id)\n";
	++$created;
}

// --- Pages ---
$pages = [
	'About Us'    => 'This is the about page for the test site.',
	'Contact'     => 'Get in touch with us through this contact page.',
];

foreach ( $pages as $title => $content ) {
	$existing = new WP_Query( [
		'post_type'      => 'page',
		'title'          => $title,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	] );
	if ( $existing->have_posts() ) {
		echo "  Page '$title' already exists (ID {$existing->posts[0]})\n";
		continue;
	}

	$page_id = wp_insert_post( [
		'post_title'   => $title,
		'post_content' => "<!-- wp:paragraph --><p>$content</p><!-- /wp:paragraph -->",
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_author'  => 1,
	], true );

	if ( is_wp_error( $page_id ) ) {
		echo "  Error creating page '$title': " . $page_id->get_error_message() . "\n";
		continue;
	}

	echo "  Created page '$title' (ID $page_id)\n";
	++$created;
}

// --- Enable the plugin settings (without real CF credentials) ---
$option = get_option( 'graphql_general_settings' );
echo "\nDone! Created $created items total.\n";
echo "Site URL: " . home_url() . "\n";
echo "GraphQL endpoint: " . home_url( '/graphql' ) . "\n";
echo "WP Admin: " . admin_url() . " (admin / password)\n";
