<?php
/**
 * Various file functions
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2004 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package evocore
 */
if( !defined('DB_USER') ) die( 'Please, do not access this page directly.' );


/**
 * converts bytes to readable bytes/kb/mb/gb
 *
 * @param integer bytes
 * @return string bytes made readable
 */
function bytesreadable( $bytes )
{
	$type = array ('b', 'kb', 'mb', 'gb');

	for ($i = 0; $bytes > 1024; $i++)
		$bytes /= 1024;

	return str_replace(',', '.', round($bytes, 2)) . $type[$i];
}


/**
 * create crossplatform-safe filename
 * @param string filename/path
 * @return string converted path
 */
function safefilename( $path )
{
	$path = preg_replace( '/[^A-Za-z0-9]+/', '_', $path );

	// remove trailing/leading '_'
	$path = preg_replace( '/^_+/', '', $path );
	$path = preg_replace( '/_+$/', '', $path );

	return $path;
}


/**
 * get size of directory, including anything in there.
 *
 * @param string the dir's full path
 */
function get_dirsize_recursive( $path )
{
	$dir = opendir( $path );
	$total = 0;
	while( $cur = readdir($dir) ) if( !in_array( $cur, array('.', '..')) )
	{
		if( is_dir($path.'/'.$cur) )
		{
			$total += get_dirsize_recursive($path.'/'.$cur);
		}
		else
		{
			$total += filesize($path.'/'.$cur);
		}
	}
	return $total;
}


/**
 * deletes a dir recursive, wiping out all subdirectories!!
 *
 * @param string the dir
 */
function deldir_recursive( $dir )
{
	$current_dir = opendir( $dir );
	while( $entryname = readdir($current_dir) )
	{
		if( is_dir( "$dir/$entryname" ) && ( $entryname != '.' && $entryname != '..') )
		{
			deldir( "$dir/$entryname" );
		}
		elseif( $entryname != '.' && $entryname != '..' )
		{
			unlink( "$dir/$entryname" );
		}
	}
	closedir( $current_dir );
	return rmdir( $dir );
}


/**
 * Get the size of an image file
 *
 * @param string absolute file path
 * @param string what property/format to get: 'width', 'height', 'widthxheight', 'type', 'string' (as for img tags), else 'widthheight' (array)
 * @return mixed false if no image, otherwise what was requested through $param
 */
function imgsize( $path, $param )
{
	if( !($size = @getimagesize( $path )) )
	{
		return false;
	}
	elseif( $param == 'width' )
	{
		return $size[0];
	}
	elseif( $param == 'height' )
	{
		return $size[1];
	}
	elseif( $param == 'widthxheight' )
	{
		return $size[0].'x'.$size[1];
	}
	elseif( $param == 'type' )
	{
		switch( $size[1] )
		{
			case 1: return 'gif';
			case 2: return 'jpg';
			case 3: return 'png';
			case 4: return 'swf';
			default: return 'unknown';
		}
	}
	elseif( $param == 'string' )
	{
		return $size[3];
	}
	else
	{ // default: 'widthheight'
		return array( $size[0], $size[1] );
	}
}


/**
 * add a trailing slash, if none present
 *
 * @param string the path/url
 * @return string the path/url with trailing slash
 */
function trailing_slash( $path )
{
	return( preg_match( '#/$#', $path ) ? $path : $path.'/' );
}


/**
 * Displays file permissions like 'ls -l'
 *
 * @author zilinex at linuxmail dot com {@link www.php.net/manual/en/function.fileperms.php}
 * @todo move out of class
 * @param string
 */
function translatePerm( $in_Perms )
{
	$sP = '';

	if(($in_Perms & 0xC000) == 0xC000)     // Socket
		$sP = 's';
	elseif(($in_Perms & 0xA000) == 0xA000) // Symbolic Link
		$sP = 'l';
	elseif(($in_Perms & 0x8000) == 0x8000) // Regular
		$sP = '&minus;';
	elseif(($in_Perms & 0x6000) == 0x6000) // Block special
		$sP = 'b';
	elseif(($in_Perms & 0x4000) == 0x4000) // Directory
		$sP = 'd';
	elseif(($in_Perms & 0x2000) == 0x2000) // Character special
		$sP = 'c';
	elseif(($in_Perms & 0x1000) == 0x1000) // FIFO pipe
		$sP = 'p';
	else                                   // UNKNOWN
		$sP = 'u';

	// owner
	$sP .= (($in_Perms & 0x0100) ? 'r' : '&minus;') .
					(($in_Perms & 0x0080) ? 'w' : '&minus;') .
					(($in_Perms & 0x0040) ? (($in_Perms & 0x0800) ? 's' : 'x' ) :
																	(($in_Perms & 0x0800) ? 'S' : '&minus;'));

	// group
	$sP .= (($in_Perms & 0x0020) ? 'r' : '&minus;') .
					(($in_Perms & 0x0010) ? 'w' : '&minus;') .
					(($in_Perms & 0x0008) ? (($in_Perms & 0x0400) ? 's' : 'x' ) :
																	(($in_Perms & 0x0400) ? 'S' : '&minus;'));

	// world
	$sP .= (($in_Perms & 0x0004) ? 'r' : '&minus;') .
					(($in_Perms & 0x0002) ? 'w' : '&minus;') .
					(($in_Perms & 0x0001) ? (($in_Perms & 0x0200) ? 't' : 'x' ) :
																	(($in_Perms & 0x0200) ? 'T' : '&minus;'));
	return $sP;
}


/**
	Does the same thing as the function realpath(), except it will
	also translate paths that don't exist on the system.

	@param string the path to be translated
	@return array [0] = the translated path (with trailing slash); [1] = TRUE|FALSE (path exists?)
*/
function str2path( $path )
{
	$path = str_replace( '\\', '/', $path );
	$pwd = realpath( $path );

	if( !empty($pwd) )
	{ // path exists
		$pwd = str_replace( '\\', '/', $pwd);
		if( substr( $pwd, -1 ) !== '/' )
		{
			$pwd .= '/';
		}
		return array( $pwd, true );
	}
	else
	{ // no realpath
		$pwd = '';
		$strArr = preg_split( '#/#', $path, -1, PREG_SPLIT_NO_EMPTY );
		$pwdArr = array();
		$j = 0;
		for( $i = 0; $i < count($strArr); $i++ )
		{
			if( $strArr[$i] != '..' )
			{
				if( $strArr[$i] != '.' )
				{
					$pwdArr[$j] = $strArr[$i];
					$j++;
				}
			}
			else
			{
				array_pop( $pwdArr );
				$j--;
			}
		}
		return array( implode('/', $pwdArr).'/', false );
	}
}

/**
 * Check a filename if it has an image extension
 *
 * @param string the filename to check
 * @return boolean true if the filename indicates an image, false otherwise
 */
function is_image( $filename )
{
	global $regexp_images;

	return (boolean)preg_match( $regexp_images, $filename );
}

?>
