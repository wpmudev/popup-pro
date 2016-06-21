<?php

// This file offers a convenient way to setup/reset test-data in the database.
class TData {
	protected static $ids = array();

	/**
	 * Resets the database.
	 *
	 * @since  1.0.0
	 */
	public static function reset() {
		global $wpdb;

		// wipe all existing data.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->users};" );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->usermeta};" );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->posts};" );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->postmeta};" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%transient_%';" );
		self::$ids = array(
			'user' => array(),
			'post' => array(),
			'membership' => array(),
		);

		// create demo users
		$users = array(
			'admin' => array(
				'role' => 'administrator',
			),
			'editor' => array(
				'role' => 'editor',
			)
		);
		foreach ( $users as $login => $data ) {
			$defaults = array(
				'user_login' => $login,
				'user_pass' => $login . '-password',
				'user_email' => $login . '@local.dev',
				'role' => 'subscriber',
				'user_nicename' => '',
				'user_url' => '',
				'display_name' => 'User ' . $login,
				'nickname' => '',
				'first_name' => '',
				'last_name' => '',
				'description' => '',
				'user_registered' => '',
			);
			$data = shortcode_atts( $defaults, $data );

			$id = wp_insert_user( $data );
			if ( ! empty( $data['meta'] ) ) {
				foreach ( $data['meta'] as $key => $val ) {
					$val = maybe_serialize( $val );
					update_user_meta( $id, $key, $val );
				}
			}

			self::$ids['user'][$login] = $id;
		}

		// create demo posts
		$posts = array(
			'sample-post' => array(
				'post_content' => 'Just a very simple sample post...',
			),
			'sample-page' => array(
				'post_type' => 'page',
				'post_content' => 'Just a very simple sample page...',
			),
		);
		foreach ( $posts as $slug => $data ) {
			$defaults = array(
				'post_type' => 'post',
				'post_author' => self::id( 'user', 'admin' ),
				'post_title' => $slug,
				'post_name' => $slug,
			);
			$data = shortcode_atts( $defaults, $data );

			$id = wp_insert_post( $data );
			if ( ! empty( $data['meta'] ) ) {
				foreach ( $data['meta'] as $key => $val ) {
					$val = maybe_serialize( $val );
					update_post_meta( $id, $key, $val );
				}
			}

			self::$ids['post'][$slug] = $id;
		}

	}

	/**
	 * Returns a single item ID.
	 *
	 * Example:
	 * $user = id( 'user', 'admin' );
	 *
	 * @since  1.0.0
	 * @return int
	 */
	public static function id( $type, $key ) {
		if ( ! isset( self::$ids[ $type ] ) ) {
			$err = '[UNKNOWN TYPE] ' . $type;
			error_log( $err );
			throw new Exception( $err, 1 );
		}

		if ( ! isset( self::$ids[ $type ][ $key ] ) ) {
			$err = '[UNKNOWN ELEMENT] ' . $type . '.' . $key;
			error_log( $err );
			throw new Exception( $err, 1 );
		}

		return self::$ids[ $type ][ $key ];
	}


};