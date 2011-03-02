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


/**
 *
 */
class SGArticle {
    var $title;          // SGTitle
    private $content;    // array of paragraphs
    private $properties; // SGProperties

    # returns a list of titles linked in the article's content
    public function getLinks() {
        # ...TODO...
    }

    # set content (and properties?) by parsing wikitext
    public function setFromWikitext( $str ) {
        # ...TODO...
    }

    # get the content as DOMFragment added to a given DOMDocument
    public function getContentDOMFragment( $doc ) {
        $dom = $doc->createDocumentFragment();

        # ...TODO...append content

        return $dom;
    }
}

class SGProperties {
}


/**
 * Read-only access to an SGlossary.
 */
interface SGlossSource {
    public function hasTitle( $title );
    public function getTitle( $title );
    public function isAlias( $title );

    # TODO: getArticle, getProperties

}

/**
 * Read-only access to an SGlossary that can be queried by properties.
 */
interface SGlossPropertySource {
    public function findArticlesOfProperty( $key, $value ); # TODO (property should better be a class)
}

/**
 * Read-write access to an SGlossary.
 */
interface SGlossStore extends SGlossSource {
    public function deleteArticle( $title );

    # TODO
}


/**
 * SGlossary stored in a database (PDO).
 */
class SGlossPDOStore implements SGlossStore { # TODO: implements SGlossPropertySource
    var $dbh; # TODO: private
    
    function __construct( $config ) {
        $this->dbh = new PDO( $config );
        if ( !$this->dbh ) throw new Exception('could not create PDO object');
        $this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $this->dbh->exec( self::$sql_create );
    }

    # Maps title strings to SGTitle objects or strings (alias) or NULL.
    private $titleCache = array( "" => NULL );

    private function _provideTitleCache( $title ) {
        if ( isset( $this->titleCache["$title"] ) ) return;

        $sql = "SELECT title FROM articles WHERE title=?";
        $sth = $this->dbh->prepare( $sql );
        if ( $sth->execute(array("$title")) && $sth->fetchAll() ) {
            # title exists as article: save SGTitle object
            $this->titleCache["$title"] = $title;
            return;
        }

        $sql = "SELECT article FROM properties WHERE value=? AND property='syn'";
        $sth = $this->dbh->prepare( $sql );
        if ( $sth->execute(array("$title")) && ($result = $sth->fetchAll(PDO::FETCH_COLUMN,0)) ) { 
            # title exists as alias: save string
            $t = $result[0];
            $this->titleCache["$title"] = $t;
            $this->titleCache[ $result[0] ] = new SGTitle( $t );
            return;
        }

        # title does not exist: save NULL
        $this->titleCache["$title"] = NULL;
    }

    function isAlias( $title ) {
        if ( !is_object($title) ) $title = new SGTitle( $title );
        $this->_provideTitleCache( $title );
        return is_string($this->titleCache["$title"]);
    }

    function hasTitle( $title ) {
        if ( is_array($title) ) {
            foreach ( $title as $t ) {
                if ( $this->_hasTitle($t) ) 
                    return true;
            }
        } else {
            return $this->_hasTitle($title);
        }
        return false;
    }

    private function _hasTitle( $title ) {
        if ( !is_object($title) ) $title = new SGTitle( $title );
        $this->_provideTitleCache( $title );
        return ($this->titleCache["$title"] !== NULL);
    }

    # Get the preferred title (SGTitle or NULL)
    public function getTitle( $title ) {
        if ( !is_object($title) ) $title = new SGTitle( $title );
        $this->_provideTitleCache( $title );
        if ( is_string( $this->titleCache["$title"] ) ) {
            # title is an alias, so get preferred Title
            $t = $this->titleCache["$title"];
            return $this->titleCache[ $t ];
        }
        return $this->titleCache["$title"];
    }

    public function deleteArticle( $title ) {
        $t = $this->getTitle( $title );
        if ($t === NULL) throw new Exception("Article $t not found");

        $this->dbh->beginTransaction();
        $sql = "DELETE FROM articles WHERE title=?";
        $sth = $this->dbh->prepare( $sql );
        $sth->execute(array( "$t" ));
        $sth = $this->dbh->prepare("DELETE FROM properties WHERE `article` = ?");
        $sth->execute(array( "$t" ));
        $this->dbh->commit();
    }

    static $sql_create = <<<TEST
CREATE TABLE IF NOT EXISTS articles (
   title PRIMARY KEY ON CONFLICT REPLACE,
   xml
);
CREATE TABLE IF NOT EXISTS properties (
  'article' NOT NULL,
  'property' NOT NULL,
  'value'
);
TEST;

}


?>
