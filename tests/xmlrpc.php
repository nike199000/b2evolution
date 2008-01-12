<?php
/**
 * XML-RPC Tests
 *
 * b2evolution - {@link http://b2evolution.net/}
 *
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 *
 * @copyright (c)2003-2007 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package tests
 */
require_once  dirname(__FILE__).'/../blogs/conf/_config.php';

define( 'EVO_MAIN_INIT', true );

/**
 * class loader
 */
require_once $inc_path.'_core/_class4.funcs.php';

load_funcs('_core/_misc.funcs.php');
load_funcs('_ext/xmlrpc/_xmlrpc.php');
load_funcs('_ext/xmlrpc/_xmlrpc.php' );

echo '<h1>XML-RPC tests</h1>';

$target = 'local';

echo "<p>Target: $target</p>";

switch( $target )
{
	case 'local':
		$test_user = 'admin';
		$test_pass = $install_password;
		$client = new xmlrpc_client( $basesubpath.$xmlsrv_subdir.'xmlrpc.php', $basehost, $baseport );
		break;

	default:
		die('unknown target');
}

$bloggerAPIappkey = 'testkey';

	// ----------------------------------------------------------------------------------------------------

	echo '<h2>blogger.getUserInfo</h2>';
	$client->debug = false;
	$message = new xmlrpcmsg( 'blogger.getUserInfo', array(
														new xmlrpcval($bloggerAPIappkey),
														new xmlrpcval($test_user),
														new xmlrpcval($test_pass)
													)  );
	$result = $client->send($message);
	$ret = xmlrpc_displayresult( $result );
	pre_dump( $ret );

	// ----------------------------------------------------------------------------------------------------

	echo '<h2>blogger.getUsersBlogs</h2>';
	$client->debug = false;
	$message = new xmlrpcmsg( 'blogger.getUsersBlogs', array(
														new xmlrpcval($bloggerAPIappkey),
														new xmlrpcval($test_user),
														new xmlrpcval($test_pass)
													)  );
	$result = $client->send($message);
	$ret = xmlrpc_displayresult( $result );
	// pre_dump( $ret );
	if( is_array( $ret ) )foreach( $ret as $a )
	{
		echo '<li>'.$a['blogName'].'</li>';
	}

 	// ----------------------------------------------------------------------------------------------------

	echo '<h2>b2.getCategories</h2>';
	$client->debug = false;
	$message = new xmlrpcmsg( 'b2.getCategories', array(
														new xmlrpcval(1),
														new xmlrpcval($test_user),
														new xmlrpcval($test_pass)
													)  );
	$result = $client->send($message);
	$ret = xmlrpc_displayresult( $result );
	// pre_dump( $ret );
	if( is_array( $ret ) )foreach( $ret as $a )
	{
		echo '<li>'.$a['categoryName'].'</li>';
	}

	// ----------------------------------------------------------------------------------------------------

	echo '<h2>b2.newPost</h2>';
	$post_text = 'XML-RPC b2.newPost : random # '.rand( 1, 10000 );
	echo 'Post_text : '.$post_text;
	$client->debug = false;
	$message = new xmlrpcmsg( 'b2.newPost', array(
														new xmlrpcval(''),
														new xmlrpcval(''),
														new xmlrpcval($test_user),
														new xmlrpcval($test_pass),
														new xmlrpcval( "<p>$post_text</p>\n" ),
														new xmlrpcval(true,"boolean"),		// Published
														new xmlrpcval( $post_text ),	// TITLE
														new xmlrpcval(1), // Category
														new xmlrpcval('') // Date
													)  );
	$result = $client->send($message);
	$ret = xmlrpc_displayresult( $result );
	if( empty($ret)  )
	{
		die( 'ERROR' );
	}
	$msg_ID = xmlrpc_decode_recurse($result->value());
	echo '<p>OK - Message ID: '.$msg_ID.'</p>';

	// ----------------------------------------------------------------------------------------------------

	echo '<h2>blogger.newPost</h2>';
	$post_text = 'XML-RPC post : random # '.rand( 1, 10000 );
	echo 'Post_text : '.$post_text;
	$client->debug = false;

	$content = "<title>$post_text</title>
							<p>$post_text</p>\n";
	$content .= '<category>2,03</category>';


	$message = new xmlrpcmsg( 'blogger.newPost', array(
														new xmlrpcval($bloggerAPIappkey),
														new xmlrpcval(1),
														new xmlrpcval($test_user),
														new xmlrpcval($test_pass),
														new xmlrpcval( $content ),
														new xmlrpcval(true,"boolean")		// published
													)  );
	$result = $client->send($message);
	$ret = xmlrpc_displayresult( $result );
	if( empty($ret)  )
	{
		die( 'ERROR' );
	}
	$msg_ID = xmlrpc_decode_recurse($result->value());
	echo '<p>OK - Message ID: '.$msg_ID.'</p>';

	// ----------------------------------------------------------------------------------------------------

	echo '<h2>blogger.getRecentPosts</h2>';
	$client->debug = false;
	$message = new xmlrpcmsg( 'blogger.getRecentPosts', array(
														new xmlrpcval($bloggerAPIappkey),
														new xmlrpcval(1),
														new xmlrpcval($test_user),
														new xmlrpcval($test_pass),
														new xmlrpcval(5, 'int' ),
													)  );
	$result = $client->send($message);
	$ret = xmlrpc_displayresult( $result );

	// pre_dump( $ret );
	if( is_array( $ret ) )foreach( $ret as $a )
	{
		echo '<li>'.htmlspecialchars($a['content']).'</li>';
	}

	// Get latest message:
	$latest = $ret[0];
	pre_dump( $latest );

	echo '<p>Message ID: '.$latest['postid'];
	if( $latest['postid'] == $msg_ID )
	{
		echo '- OK match';
	}
	else
	{
		die( 'ERROR' );
	}
	echo '</p>';

	echo '<p>Content: '.htmlspecialchars($latest['content']);
	if( strpos( $latest['content'], $post_text ) )
	{
		echo ' - OK';
	}
	else
	{
		die( 'ERROR' );
	}
	echo '</p>';


	if( strpos( $ret[2]['content'], 'XML-RPC post :' ) )
	{	// This is a previous XML-RPC test post
		$delete_post = $ret[2]['postid'];
	}
	if( strpos( $ret[3]['content'], 'XML-RPC b2.newPost :' ) )
	{	// This is a previous XML-RPC test post
		$delete_post2 = $ret[3]['postid'];
	}


	// ----------------------------------------------------------------------------------------------------

	echo '<h2>blogger.editPost</h2>';
	$client->debug = false;

	// Add something to message:
	$post_content = $latest['content']."\n* This has been edited! *";

	$message = new xmlrpcmsg( 'blogger.editPost', array(
														new xmlrpcval($bloggerAPIappkey),
														new xmlrpcval($msg_ID),
														new xmlrpcval($test_user),
														new xmlrpcval($test_pass),
														new xmlrpcval( $post_content ),
														new xmlrpcval(true, 'boolean')		// PUBLISH !!
													)  );
	$result = $client->send($message);
	$ret = xmlrpc_displayresult( $result );
	// pre_dump( $ret );
	if( $ret == 1 )
	{
		echo 'OK';
	}
	else
	{
		die('ERROR');
	}

	// ----------------------------------------------------------------------------------------------------

	echo '<h2>blogger.getPost</h2>';
	$client->debug = false;
	$message = new xmlrpcmsg( 'blogger.getPost', array(
														new xmlrpcval($bloggerAPIappkey),
														new xmlrpcval($msg_ID),
														new xmlrpcval($test_user),
														new xmlrpcval($test_pass),
													)  );
	$result = $client->send($message);
	$ret = xmlrpc_displayresult( $result );
	// pre_dump( $ret );

 	echo '<p>Content: '.htmlspecialchars($ret['content']);
	if( strpos( $ret['content'], $post_text ) && strpos( $ret['content'], '* This has been edited! *' ) )
	{
		echo ' - OK';
	}
	else
	{
		die( 'ERROR' );
	}
	echo '</p>';

	// ----------------------------------------------------------------------------------------------------

	echo '<h2>b2.getPostURL</h2>';
	$client->debug = false;
	$message = new xmlrpcmsg( 'b2.getPostURL', array(
														new xmlrpcval(0),
														new xmlrpcval(''),
														new xmlrpcval($test_user),
														new xmlrpcval($test_pass),
														new xmlrpcval($msg_ID),
													)  );
	$result = $client->send($message);
	$ret = xmlrpc_displayresult( $result );
	// pre_dump( $ret );

	echo 'OK - <a href="'.$ret.'">'.$ret.'</a>';

	// ----------------------------------------------------------------------------------------------------

	echo '<h2>blogger.deletePost</h2>';
	if( empty( $delete_post ) )
	{
		echo 'no post to delete yet. run again.';
	}
	else
	{
		$client->debug = false;
		$message = new xmlrpcmsg( 'blogger.deletePost', array(
															new xmlrpcval($bloggerAPIappkey),
															new xmlrpcval( $delete_post ),
															new xmlrpcval($test_user),
															new xmlrpcval($test_pass),
														)  );
		$result = $client->send($message);
		$ret = xmlrpc_displayresult( $result );
		// pre_dump( $ret );
		if( $ret == 1 )
		{
			echo "OK<br/>\n";
		}
		else
		{
			die('ERROR');
		}
	}

	if( empty( $delete_post2 ) )
	{
		echo 'no post2 to delete yet. run again.';
	}
	else
	{
		$client->debug = false;
		$message = new xmlrpcmsg( 'blogger.deletePost', array(
															new xmlrpcval($bloggerAPIappkey),
															new xmlrpcval( $delete_post2 ),
															new xmlrpcval($test_user),
															new xmlrpcval($test_pass),
														)  );
		$result = $client->send($message);
		$ret = xmlrpc_displayresult( $result );
		// pre_dump( $ret );
		if( $ret == 1 )
		{
			echo "OK<br/>\n";
		}
		else
		{
			die('ERROR');
		}
	}

	// ----------------------------------------------------------------------------------------------------

// Missing tests:

// mt.getPostCategories
// mt.setPostCategories
// mt.getCategoryList

// metaWeblog.newMediaObject
// metaWeblog.newPost
// metaWeblog.EditPost
// metaWeblog.getCategories
// metaWeblog.getRecentPosts
// metaweblog.getPost
?>