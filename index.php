<?php

/**
 * SGloss - Simple Glossary Wiki
 */ 

$title  = @$_REQUEST['title'];
$format = @$_REQUEST['format'];
$action = @$_REQUEST['action'];
$edit   = @$_REQUEST['edit'];
$data   = @$_REQUEST['data'];

# Get current base URL
$base = empty($_SERVER['SERVER_NAME']) ? 'localhost' : $_SERVER['SERVER_NAME'];
$base = (!empty($_SERVER['HTTPS']) ? "https" : "http") . "://"
      . $base . $_SERVER['PHP_SELF'];

$wiki = new SGlossWiki( array( 
    "pdo"  => "sqlite:example-lbi.sqlite",
    "home" => "SGloss",
    "base" => $base 
) );

if ( $action == "list" ) {
    $wiki->listArticles();
} else if ( $action == "edit" ) {
    $wiki->editArticle( $title, $data, $edit );
} else if ( $action == "import" ) {
    # TODO
} else {
    $wiki->viewArticle( $title, $format );
}

#print $wiki->err;
#    $dbh->exec(<<<SQL
#CREATE TABLE IF NOT EXISTS (
#    title NOT NULL,
#    data,
#    timestamp  
#); SQL;

class SGlossWiki {
    var $title = "A Simple Glossary";
    var $dbh;
    var $err;
    var $msg;
    var $home;
    var $xslpath = "xsl/";
    var $base;

    function SGLossWiki( $config ) {
        $this->home = $config['home'];
        $this->base = $config['base'];

        if (isset($config['title'])) $this->title = $config['title'];
      
        try {
            if (!($this->dbh = new PDO( $config['pdo'] ))) 
                throw new Exception('could not create PDO object');
            $this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $this->dbh->exec( self::$sql_create );
        } catch(Exception $e) {
            $this->err = "Failed to connect to database: " . $e->getMessage();
        }
    }

    function editArticle( $title, $data, $edit ) {
        if ( $edit == "cancel" ) {
            header("Location: " . $this->base . "?title=" . urlencode($title));
            exit;
        }
        if ( $data === NULL or $edit == "reset" ) {
            $article = $this->_loadArticle( $title );
        } else {
            $article = new SGlossArticle( $title );
            $article->setData( $data ); 
            if ( $edit == "save" ) {
                $this->_saveArticle( $article );
                if (!$this->err) {
                    $this->msg = "Saved article";
                    $this->_viewArticle( $article, "html" );
                    exit;
                }
            }
        }
        $this->_editArticle( $article );
    }

    function viewArticle( $title, $format="html" ) {
        if ( $title == "" ) $title = $this->home;
        if ( !$format ) $format = "html";

        $article = $this->_loadArticle( $title );

        if ( $article->exists() ) {
            $this->_viewArticle( $article, $format ); 
        } else {
            # TODO: send 404 if not exists and format != html
            header("Location: " . $this->base . "?action=edit&title=" . urlencode($title));
            exit;
        }
    }

    function listArticles() {
        $articles = array();
        if (!$this->err) try {
            $sth = $this->dbh->prepare('SELECT * FROM articles');
            if ( $sth->execute() ) {
                $articles = $sth->fetchAll( PDO::FETCH_CLASS, 'SGlossArticle' );
            }
        } catch ( Exception $e ) {
            $this->err = "Failed to save article: " . $e->getMessage();
        }

        $dom = $this->_createDOM( "list" );
        foreach ( $articles as $a ) {

            $adom = $a->getDOM();
            $a2 = $dom->importNode( $adom->documentElement, TRUE );
            $dom->documentElement->appendChild( $a2  );
        }

        $this->_sendDOM($dom);
    }

    function _sendDOM($dom) {
        header('content-type: text/xml; encoding=UTF-8');
        # preprocess:

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('g',SGlossWiki::$NS);

        $articles = array();
        // TODO: fill with articles from DOM

        $sth = $this->dbh->prepare('SELECT title FROM articles WHERE title=?');

        $alinks = $xpath->evaluate('g:article/g:text/g:link[@to]');
        if (!is_null($alinks)) {
            foreach ($alinks as $link) {
                $to = $link->getAttribute('to');
                if (!array_key_exists($to,$articles)) {
                   $articles[ $to ] =
                       ( $sth->execute(array($to)) && $sth->fetch() ) ? 1 : 0;
                }
                if ($articles[ $to ]) {
                    $action = "?title=" .urlencode($to);
                } else {
                    $action = "?action=edit&title=" .urlencode($to);
                    $attr = $dom->createAttribute('missing');
                    $attr->appendChild( $dom->createTextNode('1') );
                    $link->appendChild( $attr );
                }
                $attr = $dom->createAttribute('action');
                $attr->appendChild( $dom->createTextNode( $action ) );
                $link->appendChild( $attr );
            } 
        }

        print $dom->saveXML();
    }

    function _viewArticle( $article, $format ) {
        $dom = $this->_createDOM( "view" );
        $adom = $article->getDOM();
        $a2 = $dom->importNode( $adom->documentElement, TRUE );
        $dom->documentElement->appendChild( $a2  );
        $this->_sendDOM($dom);
    }

    function _editArticle( $article ) {
        $dom = $this->_createDOM( "edit" );
        $adom = $article->getDOM();
        $a2 = $dom->importNode( $adom->documentElement, TRUE );
        $dom->documentElement->appendChild( $a2  );
        $this->_sendDOM($dom);
    }

    function _createDOM( $action = "view" ) {
        $dom = new DomDocument('1.0','UTF-8');
        $xslt = $this->xslpath . "$action.xsl"; 
        $xslt = $dom->createProcessingInstruction("xml-stylesheet", "type=\"text/xsl\" href=\"$xslt\"");
        $dom->appendChild( $xslt );
        $root = $dom->createElementNS( SGlossWiki::$NS, 'sgloss' );
        $dom->appendChild( $root );
        $title = $dom->createElementNS( SGlossWiki::$NS, 'title' );
        $title->appendChild( $dom->createTextNode( $this->title ) ); 
        $root->appendChild( $title );
        if ($this->err) {
            $msg = $dom->createElementNS( SGlossWiki::$NS, 'error' );
            $msg->appendChild( $dom->createTextNode( $this->err ) ); 
            $root->appendChild( $msg );
        }
        if ($this->msg) {
            $msg = $dom->createElementNS( SGlossWiki::$NS, 'message' );
            $msg->appendChild( $dom->createTextNode( $this->msg ) ); 
            $root->appendChild( $msg );
        }
        return $dom;
    }

    function _saveArticle( $article ) {
        if ( $this->err ) return;
        try {
#        if ( $article->exists() ) {
#        } else {
            $article->xml = $article->dom->saveXML();
            $sth = $this->dbh->prepare('INSERT INTO articles VALUES (?,?)');
            $sth->execute(array( $article->title, $article->xml ));
            $article->exists = TRUE;
        } catch ( Exception $e ) {
            $this->err = "Failed to save article: " . $e->getMessage();
        }
    }

    function _loadArticle( $title ) {
        $article = new SGlossArticle;
        if (!$this->err) {
            $sth = $this->dbh->prepare('SELECT * FROM articles WHERE title=?');
            if ( $sth->execute(array( $title )) ) {
                $result = $sth->fetchAll( PDO::FETCH_CLASS, 'SGlossArticle' );
            } 
            if ( count($result) ) {
               $article = $result[0];
               $article->exists = TRUE;
            } else {
               $article->title = $title;
            }
        }
        return $article;
    }

    static $NS = "http://jakobvoss.de/sgloss/";

    static $sql_create = <<<TEST
CREATE TABLE IF NOT EXISTS articles (
   title PRIMARY KEY ON CONFLICT REPLACE,
   xml
);
TEST;

}

class SGlossArticle {
    public $title;
    public $xml;
    public $dom;
    public $exists = FALSE;

    public function SGlossArticle( $title = "" ) {
        $this->title = $title;
    }

    public function exists() {
#        return ($this->title != "" && $this->title != NULL && $this->xml != NULL);
         return $this->exists;
    }

    public function getDOM() {
        if (!$this->dom) {
            $this->dom = new DomDocument('1.0','UTF-8');
            if ( $this->xml ) {
                $this->dom->loadXML( $this->xml );
            } else { // create new article
                $dom = $this->dom;  
                $root = $dom->createElementNS( SGlossWiki::$NS, 'article' );
                if ( $this->title != NULL ) {
                    $title = $dom->createElementNS( SGlossWiki::$NS, 'title' );
                    $title->appendChild( $dom->createTextNode( $this->title ) ); 
                    $root->appendChild( $title );
                } 
                $dom->appendChild( $root );
                return $this->dom;
            }
        }
        return $this->dom;
    }

    function setData( $data ) {

        $dom = $this->getDOM();
        $root = $dom->documentElement;
        $textElem = $dom->createElementNS( SGlossWiki::$NS, 'text' );

        // parse
        $lines = explode("\n",$data);
        $lastempty = FALSE;
        $lasttext = NULL;

        foreach ($lines as $line) {
           $text = trim($line);

           if ($text == "") {
              $lastempty = TRUE;
              continue;
           }

           if ($lastempty) {
               # TODO: metadata
           }

           if ( preg_match('/^\[\[([^]]+)\]\]/',$text) ) $text = " $text";
           
           while ( $text != "" and preg_match('/^(.*?)\[\[([^]]+)\]\](.*)$/', $text, $match) ) {
              if ( $match[1] != "" ) {
                  $textElem->appendChild( $dom->createTextNode( $match[1] ) );
              }

              $text = $match[3];
              $link = explode("|",$match[2]);
              $to = $link[0];
              $linktext = (count($link) > 1 && $link[1] != "") ? $link[1] : $link[0];

              if ( $to != "" ) {
                  $linkElem = $dom->createElementNS( SGlossWiki::$NS, 'link' );

                  $attr = $dom->createAttribute( 'to' );
                  $attr->appendChild( $dom->createTextNode( $to ) );
                  $linkElem->appendChild( $attr );
              
                  $linkElem->appendChild( $dom->createTextNode( $linktext ) );
                  $textElem->appendChild( $linkElem );
              }

           }
           if ( $text == "" ) $text = " ";
           $textElem->appendChild( $dom->createTextNode( $text ) );

           $lastempty = FALSE;
        }
        # TODO:parse text and convert to XML
        # SYN: ... (all repeatable)
        # SEE: ...
        # REF: ...

# TODO: was wenn schon vorhanden?
        #if ( $root->text ) {
        #    $textElem = $root->text[0];
        #    while ( $textElem->hasChildNodes ) {
        #        $textElem->removeChild( $textElem->firstChild );
        #    }
        #} else {
        #}

        $root->appendChild( $textElem );

    }
}

?>
