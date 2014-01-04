<?php

// This is the uninstall script.

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
	exit();

	if (is_multisite()) {
	    global $wpdb;
	    $blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
	    if ($blogs) {
	        foreach($blogs as $blog) {
	            switch_to_blog($blog['blog_id']);
			        delete_option('disabler_smartquotes');
			        delete_option('disabler_capitalp');
			        delete_option('disabler_autop');

			        delete_option('disabler_selfping');
			        delete_option('disabler_norss');
			        delete_option('disabler_revisions');
			        delete_option('disabler_autosave');
			        delete_option('disabler_xmlrpc');

			        delete_option('disabler_version');
			        delete_option('disabler_nourl');
	        }
	        restore_current_blog();
	    }
	} else {

        delete_option('disabler_smartquotes');
        delete_option('disabler_capitalp');
        delete_option('disabler_autop');

        delete_option('disabler_selfping');
        delete_option('disabler_norss');
        delete_option('disabler_revisions');
        delete_option('disabler_autosave');
        delete_option('disabler_xmlrpc');

        delete_option('disabler_version');
        delete_option('disabler_nourl');
	}