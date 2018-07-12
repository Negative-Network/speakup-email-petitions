<?php

// plugin installation routine
function dk_speakup_install() {

	global $wpdb, $db_petitions, $db_signatures, $dk_speakup_version;

	// $db_petitions = 'wp_3_dk_speakup_petitions';

	dk_speakup_translate();

	$sql_create_tables = "
	
        CREATE TABLE `$db_petitions` (
        `id` bigint(20) unsigned NOT NULL,
          `title` text NOT NULL,
          `target_email` varchar(300) NOT NULL,
          `email_subject` varchar(200) NOT NULL,
          `greeting` varchar(200) NOT NULL,
          `petition_message` longtext NOT NULL,
          `address_fields` varchar(200) NOT NULL,
          `expires` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          `expiration_date` datetime NOT NULL,
          `created_date` datetime NOT NULL,
          `goal` int(11) NOT NULL,
          `goal_start` int(11) DEFAULT NULL,
          `sends_email` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          `twitter_message` varchar(120) NOT NULL,
          `requires_confirmation` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          `return_url` varchar(200) NOT NULL,
          `displays_custom_field` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          `custom_field_label` varchar(200) NOT NULL,
          `displays_optin` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          `optin_label` varchar(200) NOT NULL,
          `is_editable` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
          `petition_before_form` text,
          `petition_after_form` text,
          `user_send_email` tinyint(1) DEFAULT '0',
          `user_sender_email` varchar(300) DEFAULT NULL,
          `user_subject` tinytext,
          `user_text` longtext,
          `user_html` longtext,
          `share_fb_img` text,
          `share_fb_title` tinytext,
          `share_fb_desc` text,
          `share_twitter` tinytext,
          `share_email_subject` tinytext,
          `share_email_body` text
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

        ALTER TABLE `$db_petitions`
         ADD UNIQUE KEY `id` (`id`), ADD UNIQUE KEY `id_2` (`id`), ADD UNIQUE KEY `id_3` (`id`), ADD UNIQUE KEY `id_4` (`id`);

		CREATE TABLE `$db_signatures` (
		`id` bigint(20) unsigned NOT NULL,
		  `petitions_id` bigint(20) NOT NULL,
		  `first_name` varchar(200) NOT NULL,
		  `last_name` varchar(200) NOT NULL,
		  `email` varchar(200) NOT NULL,
		  `street_address` varchar(200) NOT NULL,
		  `city` varchar(200) NOT NULL,
		  `state` varchar(200) NOT NULL,
		  `postcode` varchar(200) NOT NULL,
		  `country` varchar(200) NOT NULL,
		  `custom_field` varchar(400) NOT NULL,
		  `optin` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  `date` datetime NOT NULL,
		  `confirmation_code` varchar(32) NOT NULL,
		  `is_confirmed` char(1) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		  `custom_message` longtext NOT NULL,
		  `language` varchar(10) NOT NULL
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

		ALTER TABLE `$db_signatures`
		 ADD UNIQUE KEY `id` (`id`), ADD UNIQUE KEY `id_2` (`id`), ADD UNIQUE KEY `id_3` (`id`), ADD UNIQUE KEY `id_4` (`id`);

	";

	// create database tables
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql_create_tables );

	// set default options
	$options = array(
		"petitions_rows"         => "20",
		"signatures_rows"        => "50",
		"petition_theme"         => "default",
		"button_text"            => __( "Sign Now", "dk_speakup" ),
		"expiration_message"     => __( "This petition is now closed.", "dk_speakup" ),
		"success_message"        => "<strong>" . __( "Thank you", "dk_speakup" ) . ", %first_name%.</strong>\r\n<p>" . __( "Your signature has been added.", "dk_speakup" ) . "</p>",
		"already_signed_message" => __( "This petition has already been signed using your email address.", "dk_speakup"),
		"share_message"          => __( "Share this with your friends:", "dk_speakup" ),
		"confirm_subject"        => __( "Please confirm your email address", "dk_speakup" ),
		"confirm_message"        => __( "Hello", "dk_speakup" ) . " %first_name%\r\n\r\n" . __( "Thank you for singing our petition", "dk_speakup" ) . ". " . __( "Please confirm your email address by clicking the link below:", "dk_speakup" ) . "\r\n%confirmation_link%\r\n\r\n" . get_bloginfo( "name" ),
		"confirm_email"          => get_bloginfo( "name" ) . " <" . get_bloginfo( "admin_email" ) . ">",
		"optin_default"          => "unchecked",
		"display_count"          => "1",
		"signaturelist_theme"    => "default",
		"signaturelist_header"   => __( "Latest Signatures", "dk_speakup" ),
		"signaturelist_rows"     => "50",
		"signaturelist_columns"  => serialize( array( "sig_date" ) ),
		"widget_theme"           => "default",
		"csv_signatures"         => "all"
	);
	// add plugin options to wp_options table
	add_option( 'dk_speakup_options', $options );
	add_option( 'dk_speakup_version', $dk_speakup_version );

	// register options for translation in WPML
	include_once( 'class.wpml.php' );
	$wpml = new dk_speakup_WPML();
	$wpml->register_options( $options );
}

// run plugin update script if needed
add_action( 'plugins_loaded', 'dk_speakup_update' );
function dk_speakup_update() {

	global $wpdb, $db_petitions, $db_signatures, $dk_speakup_version;
	$installed_version = get_option( 'dk_speakup_version' );
	$options           = get_option( 'dk_speakup_options' );

	// Update to version 1.6
	if ( version_compare( $installed_version, '1.6', '<' ) == 1 ) {
		error_log( 'updating to 1.6' );

		// change old 'address' column name to 'street_address' in signatures table
		$sql_update = "
			ALTER TABLE $db_signatures
			CHANGE address street_address VARCHAR(200) CHARACTER SET utf8 NOT NULL
		;";
		$wpdb->query( $sql_update );

		// drop display_address column in petitions table
		$sql_update = "
			ALTER TABLE $db_petitions
			CHANGE display_address address_fields VARCHAR(200) CHARACTER SET utf8 NOT NULL
		;";
		$wpdb->query( $sql_update );

		// make existing petitions that were set to display the address field now display the new street field
		$address_fields = serialize( array( 'street' ) );
		$sql_update = "
			UPDATE $db_petitions
			SET `address_fields` = '%s'
			WHERE `address_fields` = '1'
		;";
		$wpdb->query( $wpdb->prepare( $sql_update, $address_fields ) );
	}

	// Update to version 1.7
	if ( version_compare( $installed_version, '1.7', '<' ) == 1 ) {
		error_log( 'updating to 1.7' );

		// make existing petitions that were NOT set to display the address field contain a serialized array
		$address_fields = serialize( array( 'none' ) );
		$sql_update = "
			UPDATE $db_petitions
			SET `address_fields` = '%s'
			WHERE `address_fields` = '0'
		;";
		$wpdb->query( $wpdb->prepare( $sql_update, $address_fields ) );

		$sql_update = "
			UPDATE $db_petitions
			SET `address_fields` = '%s'
			WHERE `address_fields` = 'Array'
		;";
		$wpdb->query( $wpdb->prepare( $sql_update, $address_fields ) );
	}


	// Update to version 2.0
	if ( version_compare( $installed_version, '2.0', '<' ) == 1 ) {
		error_log( 'updating to 2.0' );

		$sql_update = "
			ALTER TABLE $db_petitions
			DROP COLUMN has_signature_goal,
			CHANGE petition_title title TEXT CHARACTER SET utf8 NOT NULL,
			CHANGE petition_email target_email VARCHAR(300) CHARACTER SET utf8 NOT NULL,
			CHANGE petition_subject email_subject VARCHAR(200) CHARACTER SET utf8 NOT NULL,
			CHANGE petition_greeting greeting VARCHAR(200) CHARACTER SET utf8 NOT NULL,
			CHANGE signature_goal goal INT(11) NOT NULL,
			CHANGE send_email sends_email CHAR(1) BINARY NOT NULL,
			CHANGE confirm requires_confirmation CHAR(1) BINARY NOT NULL,
			CHANGE display_custom_field displays_custom_field CHAR(1) BINARY NOT NULL,
			CHANGE display_email_optin displays_optin CHAR(1) BINARY NOT NULL,
			CHANGE email_optin_label optin_label VARCHAR(200) CHARACTER SET utf8 NOT NULL
		;";
		$wpdb->query( $sql_update );

		$sql_update = "
			ALTER TABLE $db_signatures
			CHANGE email_optin optin CHAR(1) BINARY NOT NULL,
			CHANGE confirmed is_confirmed CHAR(1) BINARY NOT NULL
		;";
		$wpdb->query( $sql_update );

		if ( $options['petition_theme'] == 'standard' ) {
			$options['petition_theme'] = 'default';
		}
	}

	// Update to version 3.0
	if ( version_compare( $installed_version, '3.0', '<' ) == 1 ) {
		error_log( 'updating to 3.0' );

		$results = $wpdb->get_results("SHOW COLUMNS FROM `".$wpdb->prefix."dk_speakup_petitions` LIKE 'petition_before_form'", OBJECT );
		if(empty($results))
		{
			$wpdb->query("SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0");
			$wpdb->query("SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0");
			$wpdb->query("SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES'");

			$wpdb->query("ALTER TABLE `".$wpdb->prefix."dk_speakup_petitions` 
							CHANGE COLUMN `expiration_date` `expiration_date` DATETIME NULL DEFAULT '1900-01-01 00:00:00';");

			$wpdb->query("UPDATE TABLE `".$wpdb->prefix."dk_speakup_petitions` SET 'expiration_date' = '1900-01-01 00:00:00' WHERE 'expiration_date' = '0000-00-00 00:00:00';");

			$wpdb->query("ALTER TABLE `".$wpdb->prefix."dk_speakup_petitions` 
							ADD COLUMN `petition_before_form` TEXT NULL DEFAULT NULL AFTER `is_editable`,
							ADD COLUMN `petition_after_form` TEXT NULL DEFAULT NULL AFTER `petition_before_form`,
							ADD COLUMN `user_send_email` TINYINT(1) NULL DEFAULT 0 AFTER `petition_after_form`,
							ADD COLUMN `user_subject` TINYTEXT NULL DEFAULT NULL AFTER `user_send_email`,
							ADD COLUMN `user_text` LONGTEXT NULL DEFAULT NULL AFTER `user_subject`,
							ADD COLUMN `user_html` LONGTEXT NULL DEFAULT NULL AFTER `user_text`;
							");

			$wpdb->query("ALTER TABLE `".$wpdb->prefix."dk_speakup_petitions` 
							ADD COLUMN `user_sender_email` VARCHAR(300) NULL DEFAULT NULL AFTER `user_send_email`;");

			$wpdb->query("ALTER TABLE `".$wpdb->prefix."dk_speakup_petitions` 
							ADD COLUMN `share_fb_img` TEXT NULL DEFAULT NULL AFTER `user_html`,
							ADD COLUMN `share_fb_desc` TEXT NULL DEFAULT NULL AFTER `share_fb_img`,
							ADD COLUMN `share_twitter` TINYTEXT NULL DEFAULT NULL AFTER `share_fb_desc`,
							ADD COLUMN `share_email_subject` TINYTEXT NULL DEFAULT NULL AFTER `share_twitter`,
							ADD COLUMN `share_email_body` TEXT NULL DEFAULT NULL AFTER `share_email_subject`;");

			$wpdb->query("ALTER TABLE `".$wpdb->prefix."dk_speakup_petitions` 
							ADD COLUMN `share_fb_title` TINYTEXT NULL DEFAULT NULL AFTER `share_fb_img`;");
			
			$wpdb->query("ALTER TABLE `".$wpdb->prefix."dk_speakup_petitions` 
							ADD COLUMN `goal_start` INT(11) NULL AFTER `goal`;
							");

			$wpdb->query("SET SQL_MODE=@OLD_SQL_MODE");
			$wpdb->query("SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS");
			$wpdb->query("SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS");
		}
	}
	
	// Update to version 3.1
	if ( version_compare( $installed_version, '3.1', '<' ) == 1 ) {
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."dk_speakup_petitions` 
						ADD COLUMN `goal_start` INT(11) NULL AFTER `goal`;
						");
	}

	if ( $installed_version != $dk_speakup_version ) {

		// create database tables and options
		dk_speakup_install();

		// options added after initial release
		if ( ! array_key_exists( 'confirm_subject', $options ) ) {
			$options['confirm_subject'] = __( 'Please confirm your email address', 'dk_speakup' );
		}
		if ( ! array_key_exists( 'confirm_message', $options ) ) {
			$options['confirm_message'] = __( "Hello", "dk_speakup" ) . " %first_name%\r\n\r\n" . __( "Thank you for singing our petition", "dk_speakup" ) . ". " . __( "Please confirm your email address by clicking the link below:", "dk_speakup" ) . "\r\n%confirmation_link%\r\n\r\n" . get_bloginfo( "name" );
		}
		if ( ! array_key_exists( 'confirm_email', $options ) ) {
			$options['confirm_email'] = get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>';
		}
		if ( ! array_key_exists( 'signaturelist_header', $options ) ) {
			$options['signaturelist_header'] = __( 'Latest Signatures', 'dk_speakup' );
		}
		if ( ! array_key_exists( 'signaturelist_rows', $options ) ) {
			$options['signaturelist_rows'] = '50';
		}
		if ( ! array_key_exists( 'optin_default', $options ) ) {
			$options['optin_default'] = 'unchecked';
		}
		if ( ! array_key_exists( 'display_count', $options ) ) {
			$options['display_count'] = '1';
		}
		if ( ! array_key_exists( 'signaturelist_columns', $options ) ) {
			$options['signaturelist_columns'] = serialize( array( 'sig_date' ) );
		}
		if ( ! array_key_exists( 'signaturelist_theme', $options ) ) {
			$options['signaturelist_theme'] = "default";
		}
		if ( ! array_key_exists( 'widget_theme', $options ) ) {
			$options['widget_theme'] = "default";
		}
		if ( ! array_key_exists( 'csv_signatures', $options ) ) {
			$options['csv_signatures'] = "all";
		}
		update_option( 'dk_speakup_options', $options );
	}

	// update plugin version tag in db
	if ( $installed_version != $dk_speakup_version ) {
		update_option( 'dk_speakup_version', $dk_speakup_version );
	}
}

?>