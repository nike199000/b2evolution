<?php
/**
 * This file implements the UI view for the Advanced blog properties.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2006 by Francois PLANQUE - {@link http://fplanque.net/}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 *
 * @package admin
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

global $skins_path, $skins_url;

/**
 * @var SkinCache
 */
$SkinCache = & get_Cache( 'SkinCache' );
$SkinCache->load_all();

echo '<h2>'.T_('Skins available for installation').'</h2>';

$skin_folders = get_filenames( $skins_path, false, true, true, false, true );

foreach( $skin_folders as $skin_folder )
{
  if( $SkinCache->get_by_folder( $skin_folder, false ) )
	{	// Already installed...
		continue;
	}

	echo '<div class="skinshot">';
	echo '<div class="skinshot_placeholder">';
	if( file_exists( $skins_path.$skin_folder.'/skinshot.jpg' ) )
	{
		echo '<img src="'.$skins_url.$skin_folder.'/skinshot.jpg" width="240" height="180" alt="'.$skin_folder.'" />';
	}
	else
	{
		echo '<div class="skinshot_noshot">'.T_('No skinshot available for').'</div>';
		echo '<div class="skinshot_name">'.$skin_folder.'</div>';
	}
	echo '</div>';
	echo '<div class="legend">';
	echo '<div class="actions">';
	echo '<a href="?ctrl=skins&amp;action=create&amp;skin_folder='.rawurlencode($skin_folder).'" title="'.T_('Install NOW!').'">';
	echo T_('Install NOW!').'</a>';
	echo '</div>';
	echo '<strong>'.$skin_folder.'</strong>';
	echo '</div>';
	echo '</div>';

}


echo '<div class="clear"></div>';
?>