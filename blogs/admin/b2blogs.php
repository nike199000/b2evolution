<?php
/*
 * b2evolution - http://b2evolution.net/
 *
 * Copyright (c) 2003-2004 by Francois PLANQUE - http://fplanque.net/
 * Released under GNU GPL License - http://b2evolution.net/about/license.html
 */
require_once (dirname(__FILE__).'/_header.php'); // this will actually load blog params for req blog
$title = T_('Blogs');
param( 'action', 'string' );

switch($action) 
{
	case 'new':
		require(dirname(__FILE__).'/_menutop.php');
		require(dirname(__FILE__).'/_menutop_end.php');
		if ($user_level < 9) 
		{
			die( '<p>'.T_('You have no right to edit the blogs.').'</p>' );
		}
		echo "<div class=\"panelblock\">\n";
		echo '<h2>', T_('New blog'), ":</h2>\n";
		// EDIT FORM:
		param( 'blog_name', 'string', 'new weblog' );
		param( 'blog_shortname', 'string', 'new blog' );
		param( 'blog_tagline', 'html', '' );
		param( 'blog_description', 'string', '' );
		param( 'blog_longdesc', 'html', '' );
		param( 'blog_lang', 'string', $default_language );
		param( 'blog_siteurl', 'string', '' );
		param( 'blog_filename', 'string', 'new_file.php' );
		param( 'blog_staticfilename', 'string', '' );
		param( 'blog_stub', 'string', 'new_file.php' );
		param( 'blog_roll', 'html', '' );
		param( 'blog_keywords', 'string', '' );
		param( 'blog_UID', 'string', '' );
		param( 'blog_allowtrackbacks', 'integer', 0 );
		param( 'blog_allowpingbacks', 'integer', 0 );
		param( 'blog_pingb2evonet', 'integer', 0 );
		param( 'blog_pingtechnorati', 'integer', 0 );
		param( 'blog_pingweblogs', 'integer', 0 );
		param( 'blog_pingblodotgs', 'integer', 0 );
		param( 'blog_disp_bloglist', 'integer', 0 );
		param( 'blog_default_skin', 'string', '' );
		$next_action = 'create';
		require(dirname(__FILE__).'/_blogs_form.php');
		echo '</div>';
		break;
	
		
		
	case 'create':
		require(dirname(__FILE__).'/_menutop.php');
		require(dirname(__FILE__).'/_menutop_end.php');
		if ($user_level < 9) 
		{
			die( '<p>'.T_('You have no right to edit the blogs.').'</p>' );
		}
		param( 'blog_name', 'string', true );
		param( 'blog_shortname', 'string', true );
		param( 'blog_tagline', 'html', '' );
		param( 'blog_description', 'string', '' );
		param( 'blog_longdesc', 'html', '' );
		param( 'blog_lang', 'string', 'en' );
		param( 'blog_siteurl', 'string', true );
		param( 'blog_filename', 'string', true );
		param( 'blog_staticfilename', 'string', '' );
		param( 'blog_stub', 'string', '' );
		param( 'blog_roll', 'html', '' );
		param( 'blog_keywords', 'string', '' );
		param( 'blog_UID', 'string', '' );
		param( 'blog_allowtrackbacks', 'integer', 0 );
		param( 'blog_allowpingbacks', 'integer', 0 );
		param( 'blog_pingb2evonet', 'integer', 0 );
		param( 'blog_pingtechnorati', 'integer', 0 );
		param( 'blog_pingweblogs', 'integer', 0 );
		param( 'blog_pingblodotgs', 'integer', 0 );
		param( 'blog_disp_bloglist', 'integer', 0 );
		param( 'blog_default_skin', 'string', '' );

		$blog_tagline = format_to_post($blog_tagline, 0, 0);
		$blog_longdesc = format_to_post($blog_longdesc, 0, 0);
		$blog_roll = format_to_post($blog_roll, 0, 0);

		if ( errors_display( T_('Cannot update, please correct these errors:'),
			'[<a href="javascript:history.go(-1)">'.T_('Back to new blog form').'</a>]'))
		{
			require( dirname(__FILE__).'/_footer.php' );
			die();
			break;
		}

	
		echo '<p>'.T_('Creating blog...').'</p>';
		
		$blog_ID = blog_create( $blog_name, $blog_shortname, $blog_siteurl, $blog_filename, 
									$blog_stub,  $blog_staticfilename, 
									$blog_tagline, $blog_description, $blog_longdesc, $blog_lang, $blog_roll, 
									$blog_keywords, $blog_UID, blog_disp_bloglist ) or mysql_oops( $query );
		
	
		// Quick hack to create a stub file:
		if( $blog_siteurl == '' )
		{
			echo '<p>', T_('Trying to create stub file'), '</p>';
			// Determine the edit folder:
			$current_folder = str_replace( '\\', '/', dirname(__FILE__) );
			$last_pos = 0;
			while( $pos = strpos( $current_folder, $admin_subdir, $last_pos ) )
			{	// make sure we use the last occurrence
				$edit_folder = substr( $current_folder, 0, $pos-1 );
				$last_pos = $pos+1;
			}
	
			$stub_contents = file( $edit_folder.'/stub.model' );
			echo '<p>', T_('Loading'), ': ', $stub_contents, '</p>';
			
			if( empty( $stub_contents ) )
			{
					echo '<p class="error">', T_('Could not load stub model.'), '</p>';
			}	
			else
			{
				$new_stub_file = $edit_folder.'/'.$blog_filename;
				echo '<p>', T_('Creating'), ': ', $new_stub_file, '</p>';
				$f = fopen( $new_stub_file , "w" );
				if( $f == false )
				{
					echo '<p class="error">Cannot create!</p>';
				}
				else
				{
					$found = false;
					foreach( $stub_contents as $idx => $stub_line )
					{
						$stub_line = ereg_replace( '\$blog *= *.+;', '$blog = '.$blog_ID.';', $stub_line );
						fwrite( $f, $stub_line);
					}
					fclose($f);
				}
				
				if( isset($default_stub_mod) ) 
				{
					printf( T_('<p>Changing mod to %o</p>'), $default_stub_mod );
					if( ! chmod( $new_stub_file, $default_stub_mod ) )
					{
						echo '<p class="error">', T_('Warning'), ': ', T_('chmod failed!'), '</p>';
					}
				}
				
				if( isset($default_stub_owner) ) 
				{
					printf( T_('<p>Changing owner to %s</p>'), $default_stub_owner );
					if( ! chown( $new_stub_file, $default_stub_owner ) )
					{
						echo '<p class="error">', T_('Warning'), ': ', T_('chown failed!'), '</p>';
					}
				}
			}
		}
		
		?>
		<p><strong><?php printf( T_('You should <a href="%s">create categories</a> for this blog now!'), 'b2categories.php?action=newcat&blog_ID='.$blog_ID ); ?></strong></p>
		<?php
		require( dirname(__FILE__).'/_footer.php' ); 
		exit();
		break;
	
	
	case 'edit':
		param( 'blog', 'integer', true );
		require(dirname(__FILE__).'/_menutop.php');
		require(dirname(__FILE__).'/_menutop_end.php');
		if ($user_level < 9) 
		{
			die( '<p>'.T_('You have no right to edit the blogs.').'</p>' );
		}
		echo "<div class=\"panelblock\">\n";
		echo '<h2>', T_('Blog params for:'), ' ', get_bloginfo('name'), "</h2>\n";
		// EDIT FORM:
		$blog_name = get_bloginfo('name');
		$blog_shortname = get_bloginfo('shortname');
		$blog_tagline = get_bloginfo('tagline');
		$blog_description = get_bloginfo('description');
		$blog_longdesc = get_bloginfo('longdesc');
		$blog_lang = get_bloginfo('lang');
		$blog_siteurl = get_bloginfo('subdir');
		$blog_filename = get_bloginfo('filename');
		$blog_staticfilename = get_bloginfo('staticfilename');
		$blog_stub = get_bloginfo('stub');
		$blog_roll = get_bloginfo('blogroll');
		$blog_keywords = get_bloginfo('keywords');
		$blog_allowtrackbacks = get_bloginfo('allowtrackbacks');
		$blog_allowpingbacks = get_bloginfo('allowpingbacks');
		$blog_pingb2evonet = get_bloginfo('pingb2evonet');
		$blog_pingtechnorati = get_bloginfo('pingtechnorati');
		$blog_pingweblogs = get_bloginfo('pingweblogs');
		$blog_pingblodotgs = get_bloginfo('pingblodotgs');
		$blog_disp_bloglist = get_bloginfo('disp_bloglist');
		$blog_default_skin = get_bloginfo('default_skin');
		$next_action = 'update';
		require(dirname(__FILE__).'/_blogs_form.php');
		echo '</div>';
		break;
		
		
	case 'update':
		if ($user_level < 9) 
		{
			die( '<p>'.T_('You have no right to edit the blogs.').'</p>' );
		}
		param( 'blog', 'integer', true );
		param( 'blog_name', 'string', true );
		param( 'blog_shortname', 'string', true );
		param( 'blog_tagline', 'html', '' );
		param( 'blog_description', 'string', '' );
		param( 'blog_longdesc', 'html', '' );
		param( 'blog_lang', 'string', 'en' );
		param( 'blog_siteurl', 'string', true );
		param( 'blog_filename', 'string', true );
		param( 'blog_staticfilename', 'string', '' );
		param( 'blog_stub', 'string', '' );
		param( 'blog_roll', 'html', '' );
		param( 'blog_keywords', 'string', '' );
		param( 'blog_UID', 'string', '' );
		param( 'blog_allowtrackbacks', 'integer', 0 );
		param( 'blog_allowpingbacks', 'integer', 0 );
		param( 'blog_pingb2evonet', 'integer', 0 );
		param( 'blog_pingtechnorati', 'integer', 0 );
		param( 'blog_pingweblogs', 'integer', 0 );
		param( 'blog_pingblodotgs', 'integer', 0 );
		param( 'blog_disp_bloglist', 'integer', 0 );
		param( 'blog_default_skin', 'string', '' );

		$blog_tagline = format_to_post($blog_tagline, 0, 0);
		$blog_longdesc = format_to_post($blog_longdesc, 0, 0);
		$blog_roll = format_to_post($blog_roll, 0, 0);

		if ( errors_display( T_('Cannot update, please correct these errors:'),
			'[<a href="javascript:history.go(-1)">'.T_('Back to blog editing').'</a>]'))  
		{
			require( dirname(__FILE__).'/_footer.php' );
			die();
			break;
		}
	
		blog_update( $blog, $blog_name, $blog_shortname, $blog_siteurl, $blog_filename, $blog_stub,
									$blog_staticfilename, 
									$blog_tagline, $blog_description, $blog_longdesc, $blog_lang, $blog_roll, 
									$blog_keywords, $blog_UID, $blog_allowtrackbacks, $blog_allowpingbacks,
									$blog_pingb2evonet, $blog_pingtechnorati, $blog_pingweblogs, $blog_pingblodotgs,
									$blog_disp_bloglist )
								or mysql_oops( $query );
		
		/*
		 * Update the user permissions for this blog:
		 */
		// Delete old perms for thos blog:
		$query = "DELETE FROM $tableblogusers
							WHERE bloguser_blog_ID = $blog";
		// echo $query, '<br />';
		$res_delete = mysql_query($query) or mysql_oops( $query ); 
		$querycount++; 
		
		// Now we need a full user list:
		$query = "SELECT ID FROM $tableusers";
		$result = mysql_query($query) or mysql_oops( $query ); 
		$querycount++; 
		
		$inserted_values = array();
		while($loop_row = mysql_fetch_array($result) )
		{	// Check new permissions for each user:
			$loop_user_ID = $loop_row['ID'];
		
			$perm_post = array();
			
			$perm_name_published = 'blog_perm_published_'.$loop_user_ID;
			param( $perm_name_published, 'string', '' );
			if( !empty($$perm_name_published) ) $perm_post[] = $$perm_name_published;

			$perm_name_protected = 'blog_perm_protected_'.$loop_user_ID;
			param( $perm_name_protected, 'string', '' );
			if( !empty($$perm_name_protected) ) $perm_post[] = $$perm_name_protected;

			$perm_name_private = 'blog_perm_private_'.$loop_user_ID;
			param( $perm_name_private, 'string', '' );
			if( !empty($$perm_name_private) ) $perm_post[] = $$perm_name_private;

			$perm_name_draft = 'blog_perm_draft_'.$loop_user_ID;
			param( $perm_name_draft, 'string', '' );
			if( !empty($$perm_name_draft) ) $perm_post[] = $$perm_name_draft;

			$perm_name_deprecated = 'blog_perm_deprecated_'.$loop_user_ID;
			param( $perm_name_deprecated, 'string', '' );
			if( !empty($$perm_name_deprecated) ) $perm_post[] = $$perm_name_deprecated;

			$perm_name_delpost = 'blog_perm_delpost_'.$loop_user_ID;
			param( $perm_name_delpost, 'integer', 0 );
			
			$perm_name_comments = 'blog_perm_comments_'.$loop_user_ID;
			param( $perm_name_comments, 'integer', 0 );
			
			// Update those permissions in DB:
	
			if( count($perm_post) || $$perm_name_delpost || $$perm_name_comments )
			{	// There are some permissions for this user:
				// insert new perms:
				$inserted_values[] = " ( $blog, $loop_user_ID, '".implode(',',$perm_post)."', ".
																	$$perm_name_delpost.", ".$$perm_name_comments." )";
			}
		}

		// Proceed insertions:
		if( count( $inserted_values ) )
		{
			$query_insert = "INSERT INTO $tableblogusers( bloguser_blog_ID, bloguser_user_ID, 
												bloguser_perm_poststatuses, bloguser_perm_delpost, bloguser_perm_comments ) 
											 VALUES ".implode( ',', $inserted_values );
			// echo $query_insert, '<br />';
			$res_update = mysql_query($query_insert) or mysql_oops( $query_insert ); 
			$querycount++; 
		}

		header( 'Location: b2blogs.php' );
		exit();
		break;
	
	
	
	case 'GenStatic':
		param( 'blog', 'integer', true );
		require(dirname(__FILE__).'/_menutop.php');
		require(dirname(__FILE__).'/_menutop_end.php');
	?>
		<div class="panelinfo">
			<p><?php echo T_('Blog'), ': ', get_bloginfo('name') ?></p>
	<?php
		if ($user_level < 2) 
		{
			die( '<p>'.T_('You have no right to generate static pages.').'</p>' );
		}
	
		$staticfilename = get_bloginfo('staticfilename');
		if( empty( $staticfilename ) )
		{
			echo '<p>', T_('You haven\'t set a static filename for this blog!'), "</p>\n</div>\n";
			break;
		}
	
		// Determine the edit folder:
		$edit_folder = get_path( 'base' ) .get_bloginfo('subdir');
		$filename = $edit_folder.'/'.get_bloginfo('filename');
		$staticfilename = $edit_folder.'/'.$staticfilename; 
		
		printf( T_('Generating page from <strong>%s</strong> to <strong>%s</strong>...'), $filename, $staticfilename );
		echo "<br />\n";
		flush();
		
		ob_start();
		require $filename;	
		$page = ob_get_contents();
		ob_end_clean();
		
		// Switching back to default locale (the blog page may have changed it):
		locale_activate( $default_locale );

		echo T_('Writing to file...'), '<br />', "\n";
	
		$fp = fopen ( $staticfilename, "w");  
		fwrite($fp, $page);
		fclose($fp);
	
		echo T_('Done.'), '<br />', "\n";
	?>
		</div>
	<?php 
		
		break;
	
	
	default:
		require(dirname(__FILE__).'/_menutop.php');
		require(dirname(__FILE__).'/_menutop_end.php');
		if ($user_level < 9 && ! $demo_mode) 
		{
			die( '<p>'.T_('You have no right to edit the blogs.').'</p>' );
		}
		
}

// List the blogs:
require( dirname(__FILE__).'/_blogs_list.php' ); 
require( dirname(__FILE__).'/_footer.php' ); 
?>