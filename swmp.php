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
 * @copyright 2007-2012 Stephen E Slevinski Jr
 * @author Steve (slevin@signpuddle.net)
 * @version 2.0.0
 * @section License
 *   GPL 2, http://www.opensource.org/licenses/GPL-2.0
 * @brief MediaWiki functions for the SignWriting Image Server
 * @file
 *
 */

/**
 * Options:
 *
 * $wgSWMPautoWrap - boolean to auto wrap SW on plane 15 Unicode
 * $wgSWMPclear - boolean to break and clear all after SignText
 * $wgSWMPlength - length in pixels of SignText
 *                  vertical length for columns
 *                  horizontal length for rows
 * $wgSWMPsize - size of SignText, 1 is default
 * $wgSWMPfont - font type, either "png" or "svg"
 * $wgSWMPcolor - line color of SignText in 6 digit color hex codes
 * $wgSWMPbackground - background color of SignText in 6 digit color hex codes
 * $wgSWMPcolorize - boolean for colorizing SignText according to the standard colors
 *
 * $wgSWMPwidth - width in pixels of column or row of SignText
 *                horizontal width for columns
 *                vertical width for rows
 * $wgSWMPpadding - width padding in pixels between symbols and sized of columns or rows
 * $wgSWMPform  - form of SignText: either "col" or "row". Blank value will auto detect based on contents
 * $wgSWMPstyle - style of width sizing
 *                "flex" for flexible width dependent on content and padding
 *                "fix" for fixed width,
 * $wgSWMPsignTop - top padding in pixels before signs
 * $wgSWMPsignBottom - bottom padding in pixels after signs
 * $wgSWMPpuncTop - top padding in pixels before punctuation
 * $wgSWMPpuncBottom - bottom padding in pixels after punctuation
 * $wgSWMPoffset - lane offset in pixels for writing in lanes
 * $wgSWMPtop - top padding in pixels for start of SignText
 * $wgSWMPjustify - SignText justification
 *                  0 does nothing, 1 pulls punc, 2 pushes signs, 3 does both
 */
$wgSWMPautoWrap = false;
$wgSWMPclear = true;
$wgSWMPlength = 400;
$wgSWMPsize = .7;
$wgSWMPfont = "svg";
$wgSWMPcolor = '';
$wgSWMPbackground = '';
$wgSWMPcolorize = false;

$wgSWMPwidth = 0;
$wgSWMPpadding = 15;
$wgSWMPform = '';
$wgSWMPstyle = 'flex';
$wgSWMPsignTop = 12;
$wgSWMPsignBottom = 12;
$wgSWMPpuncTop = 0;
$wgSWMPpuncBottom = 18;
$wgSWMPoffset = 50;
$wgSWMPtop = 0;
$wgSWMPjustify = 3;

include 'msw_main.php';

$wgSWMPVersion = '2.0.0';
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'SignWriting MediaWiki Plugin',
	'version' => $wgSWMPVersion,
	'author' => 'Stephen E Slevinski Jr',
	'url' => 'http://www.mediawiki.org/wiki/Extension:SignWriting_MediaWiki_Plugin',
	'description' => 'This Extension adds SignWriting support to MediaWiki'
	);

$wgResourceModules['ext.swmp'] = array(
	'styles' => 'swmp.css',
		'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'swmp'
);

$wgHooks['BeforePageDisplay'][] = 'efSWMPaddModules';
function efSWMPaddModules( OutputPage &$out ) {
	$out->addModules( 'ext.swmp' );
	return true;
}

// utf-8 plane 15 transformation
if ( $wgSWMPautoWrap ) $wgHooks['ParserBeforeStrip'][] = 'fnSWMPHook';
function fnSWMPHook( &$parser, &$text, &$stripState ) {
	// plane 15 UTF-8 with white space
	$patternMany = '/[\x{FD800}-\x{FDFF9}]+[ +[\x{FD800}-\x{FDFF9}]+]*/u';
	$patternOne = '/[\x{FD800}-\x{FDFF9}]/u';
	if ( preg_match_all( $patternMany, $text, $matches ) ) {
		forEach ( $matches[0] as $match ) {
			$text = str_replace( $match, "<signtext>" . bsw2fsw( csw2bsw( $match ) ) . "</signtext>", $text );
		}
	}
	return true;
}

$wgHooks['ParserFirstCallInit'][] = 'efSWMPInit';
// $wgExtensionFunctions[] = 'efSWMPParserInit';
function efSWMPInit( Parser $parser ) {
	$parser->setHook( 'signtext', 'efSWMPRenderSignText' );
	return true;
}

function efSWMPRenderSignText( $input, $args, $parser ) {
	global $wgScriptPath;
	global $wgSWMPclear;
	global $wgSWMPlength, $wgSWMPsize, $wgSWMPfont, $wgSWMPcolor, $wgSWMPbackground, $wgSWMPcolorize;
	global $wgSWMPwidth, $wgSWMPpadding, $wgSWMPform, $wgSWMPstyle;
	global $wgSWMPsignTop, $wgSWMPsignBottom, $wgSWMPpuncTop, $wgSWMPpuncBottom, $wgSWMPoffset, $wgSWMPtop, $wgSWMPjustify;

	if ( cswText( $input ) ) {
	    $input = bsw2fsw( csw2bsw( $input ) );
	}
	$output = '';
	$valid = array( 'clear', 'length', 'size', 'font', "color", "background", "colorize" );
	foreach ( $valid as $value ) {
		if ( array_key_exists( $value, $args ) ) {
			$$value = $args[$value];
		} else {
			$arg = "wgSWMP" . $value;
			$$value = $$arg;
		}
	}

	$valid = array( 'width', 'padding', 'form', 'style', 'signTop', 'signBottom', 'puncTop', 'puncBottom', 'offset', 'top', 'justify' );
	foreach ( $valid as $value ) {
		if ( !array_key_exists( $value, $args ) ) {
		    $arg = "wgSWMP" . $value;
			$args[$value] = $$arg;
		}
	}

	if ( !$args['form'] ) {
		if ( isVert( $input ) ) {
			$args['form'] = 'col';
		} else {
			$args['form'] = 'row';
		}
	}

	$display = explode( ' ', ksw2panel( fsw2ksw( $input ), intval( $length / $size ), $args ) );
	$cnt = count( $display );
	if ( $cnt == 1 ) $display[0] = panelTrim( $display[0] );
	$fmt = substr( $font, 0, 3 );
	switch ( $fmt ) {
	case "png":
		if ( @$form == 1 ) {
			$pre = '<div class="signtextrow">';
		} else {
			$pre = '<div class="signtextcolumn">';
		}
		$pre .= '<img src="' . $wgScriptPath . '/extensions/swmp/glyphogram.php?font=' . $font . '&size=' . $size;
		if ( @$color ) $pre .= '&line=' . $color;
		if ( !( ( strtolower( @$colorize ) == 'false' ) || !@$colorize ) ) $pre .= '&colorize=1';
		if ( @$background ) {
			$pre .= '&back=' . $background;
		}

		forEach ( $display as $col ) {
			$output .= $pre . '&text=' . $col . '"></div>';
		}

		break;
	case "svg":
		if ( @$form == 1 ) {
			$pre = '<div class="signtextrow">';
		} else {
			$pre = '<div class="signtextcolumn">';
		}
		forEach ( $display as $col ) {
		    if ( kswPanel( $col ) ) {
		    	$cluster = panel2cluster( $col );
				$col = cluster2ksw( $cluster );
				$max = str2koord( $cluster[0][1] );
				$wsvg = ceil( $max[0] * $size );
				$hsvg = ceil( $max[1] * $size );
			} else {
				$cluster = ksw2cluster( $col );
				$min = cluster2min( $cluster );
				$max = str2koord( $cluster[0][1] );
				$wsvg = ceil( ( $max[0] -$min[0] ) * $size );
				$hsvg = ceil( ( $max[1] -$min[1] ) * $size );
			}
			$output .= $pre . '<embed type="image/svg+xml" width="' . $wsvg . '" ';
			$output .= 'height="' . $hsvg . '" src="' . $wgScriptPath . '/extensions/swmp/glyphogram.php?font=' . $font . '&';
			if ( $size != 1 ) $output .= 'size=' . $size . '&';
			if ( @$color ) $output .= 'line=' . $color . '&';
			if ( !( ( strtolower( @$colorize ) == 'false' ) || !@$colorize ) ) $output .= 'colorize=1&';
			if ( @$background ) $output .= 'back=' . $background . '&';
			$output .= 'text=' . $col . '" ';
			$output .= 'pluginspage="http://www.adobe.com/svg/viewer/install/" style="overflow:hidden">';
			$output .= '</embed></div>';
		}
		break;
	}
	if ( !( ( strtolower( $clear ) == 'false' ) || !$clear ) ) $output .= '<br clear="all">';

	# sneak past the parser due to added <p>'s
	return $output;
}

?>
