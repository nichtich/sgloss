<?php
/**
 * This file implements core concepts of the SGloss data model.
 *
 * Copyright (c) 2011 Jakob Voss. All Rights Reserved.
 *
 * This file reuses code from MediaWiki's class 'Title'.
 *
 * The contents of this file may be used under the terms of the 
 * GNU Affero General Public License (the [AGPLv3] License).
 *
 * @file
 */

/**
 * Represents the title of an SGloss article.
 *
 * A title is a normalized Unicode string with some disallowed characters and 
 * diallowed substrings. Invalid parts of a title are removed on creation. 
 * Instances of this class are only created once but never modified. You can 
 * use them just link strings but never assign values to them.
 */
class SGTitle {

    // contains the cleaned title as string
    private $title = '';
    
    // returns the title as string
    public function __toString() {
        return $this->title;
    }

    // a regex of all allowed character
    // this excludes []{}|#, non printable characters 0-31 and character 127
    private static $legalChars = " %!\"$&'()*,\\-.\\/0-9:;=?@A-Z\\\\^_`a-z~\\x80-\\xFF+";

    /**
     * Create a new title. The passed string must already be urldecoded.
     */
    public function __construct( $title = "" ) { 
        $this->title = $this->cleanTitle( $title );
    }

    /**
     * Clean up all illegal character sequences.
     */
    private static function cleanTitle( $title ) {

        # NOTE: MediaWiki used a title cache that is not implemented here.
 
        # TODO: Convert things like &eacute; &#257; or &#x3017; into normalized (bug 14952) text
	# $filteredText = Sanitizer::decodeCharReferencesAndNormalize( $text );

        # TODO: XML allows  #x9 | #xA | #xD | [#x20-#xD7FF] | [#xE000-#xFFFD] | [#x10000-#x10FFFF]
        # $pattern = '/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u';
        # $title = preg_replace( $pattern, '', $title );
  
        # initialization
        static $rxTc = false;
        if( !$rxTc ) {
            # Matching titles will be held as illegal.
            $rxTc = '/' .
                # Any character not allowed is forbidden...
                '[^' . SGTitle::$legalChars . ']' .
                # URL percent encoding sequences interfere with the ability
                # to round-trip titles -- you can't link to them consistently.
                '|%[0-9A-Fa-f]{2}' .
                # XML/HTML character references produce similar issues.
                '|&[A-Za-z0-9\x80-\xff]+;' .
                '|&#[0-9]+;' .
                '|&#x[0-9A-Fa-f]+;' .
                '/S';
        }

        # Replace illegal characters
        $title = preg_replace( $rxTc, '', $title );

	# Strip Unicode bidi override characters.
	# Sometimes they slip into cut-n-pasted page titles, where the
	# override chars get included in list displays.
	$title = preg_replace( '/\xE2\x80[\x8E\x8F\xAA-\xAE]/S', '', $title );

	# Clean up whitespace
        # Note: use of the /u option on preg_replace here will cause
	# input with invalid UTF-8 sequences to be nullified out in PHP 5.2.x,
	# conveniently disabling them.
	$title = preg_replace( '/[ _\xA0\x{1680}\x{180E}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}]+/u', ' ', $title );
	$title = trim( $title );

        if ( $title == '' ) return '';

        # TODO: Is this still needed in PHP 5.2.x ?
        # TODO: normalize to Unicode Canonical Form C
        # Contained illegal UTF-8 sequences or forbidden Unicode chars.
        # if( false !== strpos( $title, UTF8_REPLACEMENT ) ) {
        #     return '';
        # }


        # Pages with "/./" or "/../" appearing in the URLs will often be un-
        # reachable due to the way web browsers deal with 'relative' URLs.
        if ( strpos( $title, '.' ) !== false && (
             $title === '.' || $title === '..' ||
             strpos( $title, './' ) === 0  ||
             strpos( $title, '../' ) === 0 ||
             strpos( $title, '/./' ) !== false ||
             strpos( $title, '/../' ) !== false  ||
             substr( $title, -2 ) == '/.' ||
             substr( $title, -3 ) == '/..' ) ) {
             return '';
        }

        # Limit the size of titles to 255 bytes.
        if ( strlen( $title ) > 255 ) {
            return '';
        }

        return $title;
    }
}

?>
