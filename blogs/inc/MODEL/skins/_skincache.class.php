<?php
/**
 * This file implements the SkinCache class.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2006 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2004-2006 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * {@internal License choice
 * - If you have received this file as part of a package, please find the license.txt file in
 *   the same folder or the closest folder above for complete license terms.
 * - If you have received this file individually (e-g: from http://evocms.cvs.sourceforge.net/)
 *   then you must choose one of the following licenses before using the file:
 *   - GNU General Public License 2 (GPL) - http://www.opensource.org/licenses/gpl-license.php
 *   - Mozilla Public License 1.1 (MPL) - http://www.opensource.org/licenses/mozilla1.1.php
 * }}
 *
 * {@internal Open Source relicensing agreement:
 * }}
 *
 * @package evocore
 *
 * @author fplanque: Francois PLANQUE
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * Includes:
 */
require_once dirname(__FILE__).'/../dataobjects/_dataobjectcache.class.php';

load_class( 'MODEL/skins/_skin.class.php' );

/**
 * Skin Cache Class
 *
 * @package evocore
 */
class SkinCache extends DataObjectCache
{
	/**
	 * Cache by folder
	 * @var array
	 */
	var $cache_by_folder = array();

	/**
	 * Constructor
	 */
	function SkinCache()
	{
		parent::DataObjectCache( 'Skin', false, 'T_skin', 'skin_', 'skin_ID', 'skin_name', NULL,
			/* TRANS: "None" select option */ T_('No skin') );
	}


	/**
	 * Add object to cache, handling our own indices.
	 *
	 * @param Skin
	 * @return boolean True on add, false if already existing.
	 */
	function add( & $Skin )
	{
		$this->cache_by_folder[ $Skin->folder ] = & $Skin;

		return parent::add( $Skin );
	}


	/**
	 * Get an object from cache by its folder name.
	 *
	 * Load the object into cache, if necessary.
	 *
	 * @param string folder name of object to load
	 * @param boolean false if you want to return false on error
	 * @return Skin A Skin object on success, false on failure (may also halt!)
	 */
	function & get_by_folder( $req_folder, $halt_on_error = true )
	{
		global $DB, $Debuglog;

		if( isset($this->cache_by_folder[$req_folder]) )
		{
			return $this->cache_by_folder[$req_folder];
		}

		// Load just the requested object:
		$Debuglog->add( "Loading <strong>$this->objtype($req_folder)</strong> into cache", 'dataobjects' );
		$sql = "
				SELECT *
				  FROM $this->dbtablename
				 WHERE skin_folder = ".$DB->quote($req_folder);
		$row = $DB->get_row( $sql );

		if( empty( $row ) )
		{ // Requested object does not exist
			if( $halt_on_error ) debug_die( "Requested $this->objtype does not exist!" );
			$r = false;
			return $r;
		}

		$Skin = new Skin( $row ); // COPY!
		$this->add( $Skin );

		return $Skin;
	}

}


/*
 * $Log$
 * Revision 1.1  2006/12/29 01:10:06  fplanque
 * basic skin registering
 *
 */
?>