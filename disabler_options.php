<?php
/*

Copyright 2010-12 Mika Epstein (email: ipstenu@ipstenu.org)

    This file is part of Disabler, a plugin for WordPress.

    Disabler is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    Disabler is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with WordPress.  If not, see <http://www.gnu.org/licenses/>.

*/

?>
<div class="wrap">

<h2><?php _e("Disabler", 'ippy_dis'); ?></h2>

<p><?php _e("Here's where you can disable whatever you want.", 'ippy_dis'); ?></p>

<?php
global $wpdb;

        if (isset($_POST['update']))
        {
                if ($new_smartquotes = $_POST['new_smartquotes'])       // Texturization
					{ update_option('disabler_smartquotes', $new_smartquotes); }
                else { update_option('disabler_smartquotes', '0'); }           
                if ($new_capitalp = $_POST['new_capitalp'])             // Capital P
					{ update_option('disabler_capitalp', $new_capitalp); }
                else { update_option('disabler_capitalp', '0'); }
                if ($new_autop = $_POST['new_autop'])                	// AutoP
					{ update_option('disabler_autop', $new_autop); }
                else { update_option('disabler_autop', '0'); }
                
                if ($new_selfping = $_POST['new_selfping'])             // SelfPing
					{ update_option('disabler_selfping', $new_selfping); }
                else { update_option('disabler_selfping', '0'); }
                if ($new_norss = $_POST['new_norss'])             // RSS
					{ update_option('disabler_norss', $new_norss); }
                else { update_option('disabler_norss', '0'); }
				if ($new_xmlrpc = $_POST['new_xmlrpc'])                // AutoSaves
					{ update_option('disabler_xmlrpc', $new_xmlrpc); }                				
                else { update_option('disabler_xmlrpc', '0'); }
                if ($new_autosave = $_POST['new_autosave'])                // AutoSaves
					{ update_option('disabler_autosave', $new_autosave); }
                else { update_option('disabler_autosave', '0'); }
				if ($new_revisions = $_POST['new_revisions'])                // Post Revisions
					{ update_option('disabler_revisions', $new_revisions); }
                else { update_option('disabler_revisions', '0'); }

				if ($new_version = $_POST['new_version'])               // Version
					{ update_option('disabler_version', $new_version); }
                else { update_option('disabler_version', '0'); }
				if ($new_nourl = $_POST['new_nourl'])                	// Phone Home URL
					{ update_option('disabler_nourl', $new_nourl); }
                else { update_option('disabler_nourl', '0'); }


?>
        <div id="message" class="updated fade"><p><strong><?php _e("Options Updated!", 'ippy_dis'); ?></strong></p></div>
<?php
        }

        if (get_option('disabler_smartquotes') != '0' )
			{ $smartquotes = ' checked="checked"'; } 
			else { $smartquotes = ''; }
		if (get_option('disabler_capitalp') != '0' )
			{ $capitalp = ' checked="checked"'; } 
			else { $capitalp = ''; }
		if (get_option('disabler_autop') != '0' )
			{ $autop = ' checked="checked"'; } 
			else { $autop = ''; }	

		if (get_option('disabler_selfping') != '0' )
			{ $selfping = ' checked="checked"'; } 
			else { $selfping = ''; }
		if (get_option('disabler_norss') != '0' )
			{ $norss = ' checked="checked"'; } 
			else { $norss = ''; }
		if (get_option('disabler_xmlrpc') != '0' )
			{ $xmlrpc = ' checked="checked"'; } 
			else { $xmlrpc = ''; }		 
		if (get_option('disabler_autosave') != '0' )
			{ $autosave = ' checked="checked"'; } 
			else { $autosave = ''; }		
		if (get_option('disabler_revisions') != '0' )
			{ $revisions = ' checked="checked"'; } 
			else { $revisions = ''; }		
		
		if (get_option('disabler_version') != '0' )
			{ $version = ' checked="checked"'; } 
			else { $version = ''; }		
		if (get_option('disabler_nourl') != '0' )
			{ $nourl = ' checked="checked"'; } 
			else { $nourl = ''; }		
?>

<form method="post" width='1'>

<h3><?php _e("Front End Settings", 'ippy_dis'); ?></h3>

<p><?php _e("These are settings are changes on the front end. These are the things that affect what your site looks like when other people visit. What THEY see.  While these are actually things that annoy <strong>you</strong>, it all comes back to being things on the forward facing part of your site.", 'ippy_dis'); ?></p>

<fieldset class="options">
<p> <input type="checkbox" id="new_smartquotes" name="new_smartquotes" value="1" <?php echo $smartquotes ?> /> <?php _e("Disable Texturization -- smart quotes (a.k.a. curly quotes), em dash, en dash and ellipsis.", 'ippy_dis'); ?></p>
</fieldset>

<fieldset class="options">
<p> <input type="checkbox" id="new_capitalp" name="new_capitalp" value="1" <?php echo $capitalp ?> /> <?php _e("Disable auto-correction of WordPress capitalization.", 'ippy_dis'); ?></p>
</fieldset>


<fieldset class="options">
<p> <input type="checkbox" id="new_autop" name="new_autop" value="1" <?php echo $autop ?> /> <?php _e("Disable paragraphs (i.e. &lt;p&gt;  tags) from being automatically inserted in your posts.", 'ippy_dis'); ?></p>
</fieldset>

<h3><?php _e("Back End Settings", 'ippy_dis'); ?></h3>

<p><?php _e("Back End settings affect how WordPress runs. Nothing here will <em>break</em> your install, but some turn off 'desired' functions.", 'ippy_dis'); ?></p>

<fieldset class="options">
<p> <input type="checkbox" id="new_selfping" name="new_selfping" value="1" <?php echo $selfping ?> /> <?php _e("Disable self pings (i.e. trackbacks/pings from your own domain).", 'ippy_dis'); ?></p>
</fieldset>

<fieldset class="options">
<p> <input type="checkbox" id="new_norss" name="new_norss" value="1" <?php echo $norss ?> /> <?php _e("Disable all RSS feeds.", 'ippy_dis'); ?></p>
</fieldset>

<fieldset class="options">
<p> <input type="checkbox" id="new_xmlrpc" name="new_xmlrpc" value="1" <?php echo $xmlrpc ?> /> <?php _e("Disable XMP-RPC.", 'ippy_dis'); ?></p>
</fieldset>

<fieldset class="options">
<p> <input type="checkbox" id="new_autosave" name="new_autosave" value="1" <?php echo $autosave ?> /> <?php _e("Disable auto-saving of posts.", 'ippy_dis'); ?></p>
</fieldset>

<fieldset class="options">
<p> <input type="checkbox" id="new_revisions" name="new_revisions" value="1" <?php echo $revisions ?> /> <?php _e("Disable post revisions.", 'ippy_dis'); ?></p>
</fieldset>

<h3><?php _e("Privacy Settings", 'ippy_dis'); ?></h3>

<p><?php _e("These settings help obfuscate information about your blog to the world (inclyding to Wordpress.org). While they don't protect you from anything, they do make it a little harder for people to get information about you and your site.", 'ippy_dis'); ?></p>

<fieldset class="options">
<p> <input type="checkbox" id="new_version" name="new_version" value="1" <?php echo $version ?> /> <?php _e("Disable WordPress from printing it's version in your headers (only seen via View Source).", 'ippy_dis'); ?></p>
</fieldset>

<fieldset class="options">
<p> <input type="checkbox" id="new_nourl" name="new_nourl" value="1" <?php echo $nourl ?> /> <?php _e("Disable WordPress from sending your URL information when checking for updates.", 'ippy_dis'); ?></p>
</fieldset>


</fieldset>
        <p class="submit"><input type="submit" name="update" value="<?php _e("Update Options", 'ippy_dis'); ?>" /></p>
</form>

</div>