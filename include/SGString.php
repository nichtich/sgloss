<?php
/**
 * This file implements methods to handle plain strings.
 *
 * Copyright (c) 2011 Jakob Voss. All Rights Reserved.
 *
 * The contents of this file may be used under the terms of the 
 * GNU Affero General Public License (the [AGPLv3] License).
 *
 * @file
 */

require_once 'normal/UtfNormal.php';

define( 'MW_CHAR_REFS_REGEX',
        '/&([A-Za-z0-9\x80-\xff]+);
         |&\#([0-9]+);
         |&\#x([0-9A-Za-z]+);
         |&\#X([0-9A-Za-z]+);
         |(&)/x' );

/**
 *
 * This class reuses code from MediaWiki's 'Sanitize' class.
 */
class SGString {

        /**
         * Returns true if a given Unicode codepoint is a valid character in XML.
         * @param $codepoint Integer
         * @return Boolean
         */
        private static function validateCodepoint( $codepoint ) {
                return ($codepoint ==    0x09)
                        || ($codepoint ==    0x0a)
                        || ($codepoint ==    0x0d)
                        || ($codepoint >=    0x20 && $codepoint <=   0xd7ff)
                        || ($codepoint >=  0xe000 && $codepoint <=   0xfffd)
                        || ($codepoint >= 0x10000 && $codepoint <= 0x10ffff);
        }

        /**
         * Decode any character references, numeric or named entities,
         * in the text and return a UTF-8 string.
         *
         * @param $text String
         * @return String
         */
        public static function decodeCharReferences( $text ) {
                return preg_replace_callback(
                        MW_CHAR_REFS_REGEX,
                        array( 'SGString', 'decodeCharReferencesCallback' ),
                        $text );
        }

        /**
         * Decode any character references, numeric or named entities,
         * in the next and normalize the resulting string. (bug 14952)
         *
         * This is useful for page titles, not for text to be displayed,
         * MediaWiki allows HTML entities to escape normalization as a feature.
         *
         * @param $text String (already normalized, containing entities)
         * @return String (still normalized, without entities)
         */
        public static function decodeCharReferencesAndNormalize( $text ) {
         #       global $wgContLang;
                $text = preg_replace_callback(
                        MW_CHAR_REFS_REGEX,
                        array( 'SGString', 'decodeCharReferencesCallback' ),
                        $text, /* limit */ -1, $count );

          # TODO: Convert a UTF-8 string to normal form C.
          #      if ( $count ) {
          #              return $wgContLang->normalize( $text );
          #      } else {
          #              return $text;
          #      }
                 return $text;
        }

       /**
         * @param $matches String
         * @return String
         */
        static function decodeCharReferencesCallback( $matches ) {
                if( $matches[1] != '' ) {
                        return SGString::decodeEntity( $matches[1] );
                } elseif( $matches[2] != '' ) {
                        return  SGString::decodeChar( intval( $matches[2] ) );
                } elseif( $matches[3] != ''  ) {
                        return  SGString::decodeChar( hexdec( $matches[3] ) );
                } elseif( $matches[4] != '' ) {
                        return  SGString::decodeChar( hexdec( $matches[4] ) );
                }
                # Last case should be an ampersand by itself
                return $matches[0];
        }

        /**
         * Return UTF-8 string for a codepoint if that is a valid
         * character reference, otherwise U+FFFD REPLACEMENT CHARACTER.
         * @param $codepoint Integer
         * @return String
         */
        static function decodeChar( $codepoint ) {
            if( SGString::validateCodepoint( $codepoint ) ) {
                return codepointToUtf8( $codepoint );
            } else {
                return UTF8_REPLACEMENT;
            }
        }

    # static function cleanUrl( $url ) ....

    /**
     * @param $name String
     * @return String
     */
    static function decodeEntity( $name ) {
	if ( isset( self::$namedEntities[$name] ) ) {
	    return self::$namedEntities[$name];
	} else {
	    return UTF8_REPLACEMENT;
	}
    }

    static $namedEntities = array(
        "lt"   => "<",
        "gt"   => ">",
        "apos" => "'",
        "quot" => '"',
        "amp"  => "&"
    );
}

?>
