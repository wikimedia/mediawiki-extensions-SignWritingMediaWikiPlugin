<?php
/**
 * SignWriting MediaWiki Plugin
 *
 * This file is part of SWMP: the SignWriting MediaWiki Plugin.
 *
 * SWMP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SWMP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SWMP.	If not, see <http://www.gnu.org/licenses/>.
 *
 * END Copyright
 *
 * @copyright 2007-2013 Stephen E Slevinski Jr
 * @author Steve (slevin@signpuddle.net)
 * @version 3.0.0
 * @section License
 *   GPL 2, http://www.opensource.org/licenses/GPL-2.0
 * @brief MediaWiki wrapper for the SignWriting Thin Viewer
 * @file
 *
 */

$wgSWMPVersion = '3.0.0';
$wgExtensionCredits['interface'][] = array(
	'name' => 'SignWriting MediaWiki Plugin',
	'version' => $wgSWMPVersion,
	'author' => 'Stephen E Slevinski Jr',
	'url' => 'http://www.mediawiki.org/wiki/Extension:SignWriting_MediaWiki_Plugin',
	'description' => 'MediaWiki wrapper for the SignWriting Thin Viewer'
	);
$wgResourceModules['ext.swmp'] = array(
        'scripts' => 'signwriting_thin.js', 
	'localBasePath' => __DIR__,
        'remoteExtPath' => 'swmp',
);

$wgHooks['BeforePageDisplay'][] = 'swmpBeforePageDisplay';

function swmpBeforePageDisplay(&$out){
  $out->addModules( 'ext.swmp' );
  return true;
}
?>
