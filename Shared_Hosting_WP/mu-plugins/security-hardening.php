<?php
/**
 * Plugin Name: Security Hardening (MU)
 * Description: ปิด XML-RPC, กัน user enumeration, ซ่อนเวอร์ชัน, ข้อความ login แบบ generic
 * Version:     1.0.0
 *
 * อัปโหลดเป็น wp-content/mu-plugins/security-hardening.php
 * Must-use plugin โหลดอัตโนมัติและปิดจากหน้า admin ไม่ได้
 */

defined( 'ABSPATH' ) || exit;

/* --- XML-RPC ------------------------------------------------------------- */
// ลบส่วนนี้ถ้าใช้ Jetpack หรือแอปมือถือ WordPress
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );
add_filter( 'wp_headers', static function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );
remove_action( 'wp_head', 'rsd_link' );

/* --- User enumeration ---------------------------------------------------- */
// /?author=N -> redirect เปิดเผย username ใน URL
add_action( 'template_redirect', static function () {
	if ( ! is_user_logged_in() && ( isset( $_GET['author'] ) || is_author() ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );

// REST API /wp/v2/users เปิดเผย username (slug) ให้คนที่ไม่ได้ login
add_filter( 'rest_endpoints', static function ( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( 0 === strpos( $route, '/wp/v2/users' ) ) {
			unset( $endpoints[ $route ] );
		}
	}
	return $endpoints;
} );

// oEmbed ใส่ชื่อผู้เขียนลงใน response
add_filter( 'oembed_response_data', static function ( $data ) {
	unset( $data['author_name'], $data['author_url'] );
	return $data;
} );

// ไม่ใส่ users ใน wp-sitemap.xml
add_filter( 'wp_sitemaps_add_provider', static function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

/* --- Login --------------------------------------------------------------- */
// ไม่บอกว่า username ถูกหรือผิด
add_filter( 'login_errors', static function () {
	return __( 'Login failed. Please check your credentials and try again.' );
} );

/* --- ซ่อนเวอร์ชัน ---------------------------------------------------------- */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

/* --- Application Passwords ----------------------------------------------- */
// ปิดถ้าไม่ได้เชื่อมต่อระบบภายนอกผ่าน REST API (ลด credential ที่ข้าม 2FA ได้)
add_filter( 'wp_is_application_passwords_available', '__return_false' );
