<?php

/**
 * @package		GoldSrc_Live_Status
 * @author		www.gentle.ro
 * @copyright	Copyright (c) 2010 - 2011, Gentle.ro 
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link		http://www.gentle.ro/proiecte/goldsource-live-status/
 */
 
// ------------------------------------------------------------------------

define('GS_LOAD', true);

define('DS', DIRECTORY_SEPARATOR);
define('EXT', '.php');

// absolute path to script directory
if ( !defined('BASEPATH') )
	define('BASEPATH', dirname(__FILE__) . DS);

require(BASEPATH.'includes'.DS.'lib'.DS.'GoldSource'.EXT);

if ( !isset($_REQUEST['ip']) )
    die(json_encode( array('error' => 'Invalid server address.') ));

$gs_ip      = $_REQUEST['ip'];
$gs_port    = ( isset($_REQUEST['port']) ) ? (int)$_REQUEST['port'] : 27015;

$GS         = new GoldSource($gs_ip, $gs_port);

// fetch data
$server = $GS->get_details();

if ( !is_array($server) )
    die( json_encode(array('error' => $GS->get_message($server))) );

$response['server']     = array(
    'hostname'      => $server['hostname'],
    'address'       => $server['address'],
    'map'           => $server['map'],
    'desc'          => $server['desc'],
    'protocol'      => $server['protocol'],
    'type'          => ($server['type'] == 'd') ? 'Dedicated' : 'Listen',
    'password'      => ($server['password'] == 0) ? 'No' : 'Yes',
    'os'            => ($server['os'] == 'w') ? 'Windows' : 'Linux',
    'ping'          => $server['ping'].' ms',
    'players'       => $server['players_on'].'/'.$server['players_max'],
);

$response['players']    = $GS->get_players();
$response['map']        = $GS->get_geoMap();

echo json_encode($response);

//TODO: implement data level cache
//TODO: implement geo level cache
?>