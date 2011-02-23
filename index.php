<?php
/**
 * SGloss - Simple Glossary Wiki
 */

# load libraries
include 'src/SGWTheme.php';

# load configuration
include 'config-default.php';
if (file_exists('config-local.php')) { 
    include 'config-local.php';
}

# initialize wiki
$wiki = new SGlossWiki( $sgconf );


# get and check parameters
$title  = trim(@$_REQUEST['title']);
$title = preg_replace('/[<>`&\[\]]\|]/','',$title); # TODO: remove illegal charcters and normalize

$action = preg_replace('/[^a-z]/','',trim(@$_REQUEST['action']));

$format = "html"; #trim(@$_REQUEST['format']);

$edit = preg_replace('/[^a-z]/','',trim(@$_REQUEST['edit']));

# TODO: better trim and add error message
$data   = trim(@$_REQUEST['data']);
if (strlen($data) > 30*1024) $data = substr($data,0,30*1024) ;

if ( !empty($_REQUEST['theme']) ) 
    $wiki->setTheme( $_REQUEST['theme'] );


$wiki->debug = array(
#    "request" => $_REQUEST
);

# perform action
$perm = $wiki->permissions["all"];
if ( !empty($action) ) {
    if ( empty($perm[ $action ])) {
        $wiki->err[] = "action not allowed";
        $action = "view";
    }
}

if ( $action == "list" ) {
    $wiki->listArticles();
} else if ( $action == "links" ) {
    $wiki->listLinks();
} else if ( $action == "create" ) {
    $wiki->createArticle( $title, $data, $edit );
} else if ( $action == "edit" ) {
    if ( $title == "" ) 
        $wiki->createArticle( $title, $data, $edit );
    else
        $wiki->editArticle( $title, $data, $edit );
} else if ( $action == "import" ) {
    # TODO
} else {
    $wiki->viewArticle( $title, $format );
}

class SGlossWiki {
    var $title = "SGlossWiki";
    var $dbh;
    var $err = array();
    var $msg = array();
    var $home = "";
    var $theme;
    var $base;
    var $permissions;

    function SGLossWiki( $config ) {
        $this->permissions = $config['permissions'];

        if( isset($config['home']) ) $this->home = $config['home'];
        $this->base = $config['base'];

        if (isset($config['title'])) $this->title = $config['title'];
      
        try {
            if (!($this->dbh = new PDO( @$config['pdo'] ))) 
                throw new Exception('could not create PDO object');
            $this->dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $this->dbh->exec( self::$sql_create );
        } catch(Exception $e) {
            $this->err[] = "Failed to connect to database: " . $e->getMessage();
        }

        if ( !empty($config['theme']) ) 
            $this->setTheme( $config['theme'] );
    }
 
    function setTheme( $theme ) {
        try {
            $this->theme = new SGWTheme( $theme ); # constructor checks valid names
        } catch (Exception $e) {
            $this->err[] = "Could not load theme " . $theme;
        }
 
        if ( $theme != "default" && !$this->theme ) try {
            $this->theme = new SGWTheme( 'default' );
        } catch (Exception $e) {
            $this->err[] = "Could not load theme $theme";
        }
    }
 
    function listMissingArticles() {
        # TODO
    }

    function createArticle( $title, $data, $edit ) {
        if ( $edit == "cancel" ) {
            header("Location: " . $this->base);
            exit;
        }
        if ( $title != "" ) {
            $article = $this->_loadArticle( $title );
            if ( $article->exists() )
                $this->err[] = "Article “${title}” already exists";
        } 
        $article = new SGlossArticle( $title );
        $article->setData( $data ); 
        if ( $edit == "save" && $title != "" && !$this->err ) {
            $this->_saveArticle( $article );
            if (!$this->err) {
                $this->msg[] = "Created article “${title}”";
                $this->_viewArticle( $article, "html" );
                exit;
            }
        }
        $this->_createArticle( $article );
     }

    function editArticle( $title, $data, $edit ) {
        if ( $edit == "cancel" ) {
            header("Location: " . $this->base . "?title=" . urlencode($title));
            exit;
        }
        if ( $data === "" or $edit == "reset" ) {
            $article = $this->_loadArticle( $title );
            $this->_editArticle( $article );
            exit;
        } 

        if ( $edit == "delete" ) {
            $this->_deleteArticle( $title );
            if (!$this->err) {
                $this->msg[] = "Deleted article “${title}”";
                $this->listArticles();
                exit;
            }
        }

        $article = new SGlossArticle( $title );
        $sth = $this->dbh->prepare('SELECT title FROM articles WHERE title=?');
        $article->exists = ( $sth->execute(array($title)) && $sth->fetch() ) ? TRUE : FALSE;
        
        $article->setData( $data, TRUE ); 
        
        if ( $edit == "save" ) {
            $this->_saveArticle( $article );
            if (!$this->err) {
                $this->msg[] = "Saved article “${title}”";
                $this->_viewArticle( $article, "html" );
                exit;
            }
        }

        $this->_editArticle( $article );
    }

    function viewArticle( $title, $format="html" ) {
        if ( $title == "" ) $title = $this->home;
        if ( $title == "" ) { $this->listArticles(); return; } # TODO: only if allowed!
        if ( !$format ) $format = "html";

        $article = $this->_loadArticle( $title );

        if ( $article->exists() ) {
            $this->_viewArticle( $article, $format ); 
        } else if ( $this->permissions["all"]["edit"] ) {
            # TODO: send 404 if not exists and format != html
            header("Location: " . $this->base . "?action=edit&title=" . urlencode($title));
            exit;
        } else {
            $article = new SGlossArticle( $title );
            $this->_viewArticle( $article, $format ); 
        }
    }

    function listLinks() {

        $articles = array();
        $links    = array();

        if (!$this->err) try {
            $sth = $this->dbh->prepare('SELECT title,xml FROM articles');
            if ( $sth->execute() ) {
                while ( $row = $sth->fetch(PDO::FETCH_ASSOC) ) {
                   $a = new SGlossArticle( $row['title'] );
                   $a->xml = $row['xml'];
                   $a->exists = TRUE;

                   $adom = $a->getDOM();
                   $alinks = $a->getLinks();
                   foreach ($alinks as $to) {
                      if (!array_key_exists($to,$links))
                         $links[$to] = array();
                      $links[$to][ $a->title ] = 1;
                   }

                   $articles[ $a->title ] = $a;
                }
            }
        } catch ( Exception $e ) {
            $this->err[] = "Failed to load articles: " . $e->getMessage();
        }

        foreach ( $links as $to => $back ) {
            if (!array_key_exists($to,$articles)) {
                $a = new SGlossArticle( $to );
                $a->exists = FALSE;
                $articles[ $to ] = $a;
            } else {
                $a = $articles[ $to ];
            }
            $adom = $a->getDOM(); // MISSING?
            foreach ( array_keys( $back ) as $to ) {
                $elem = $adom->createElement('backlink');
                $attr = $adom->createAttribute('to');
                $attr->appendChild( $adom->createTextNode( $to ) );
                $elem->appendChild( $attr );
                $adom->documentElement->appendChild( $elem );
            }
            $a->dom = $adom;
        }

        $dom = $this->_createDOM( "links" );

        foreach ( $articles as $a ) {
            $adom = $a->getDOM();
            $a2 = $dom->importNode( $adom->documentElement, TRUE );
            $dom->documentElement->appendChild( $a2  );
        }

        $this->debug = $links;

        $this->_sendDOM($dom);
    }

    function listArticles() {
        $articles = array();
        if (!$this->err) try {
            $sth = $this->dbh->prepare('SELECT * FROM articles');
            if ( $sth->execute() ) {
                $articles = $sth->fetchAll( PDO::FETCH_CLASS, 'SGlossArticle' );
            }
        } catch ( Exception $e ) {
            $this->err[] = "Failed to load articles: " . $e->getMessage();
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
        $this->_enrichDOM($dom);
        print $dom->saveXML();
    }

    function _enrichDOM($dom) {
        if ($this->err) return;

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
    }

    function _sendArticle( $article, $action ) {
        $properties = $article->properties;
        $adom = $article->getDOM( $properties );
        #$this->debug['a'] = $article;
        $dom = $this->_createDOM( $action );
        $a2 = $dom->importNode( $adom->documentElement, TRUE );
        $dom->documentElement->appendChild( $a2  );
        $this->_sendDOM($dom);
    }

    function _viewArticle( $article, $format ) {
        $this->_sendArticle( $article, "view" );
    }

    function _editArticle( $article ) {
        $this->_sendArticle( $article, "edit" );
    }

    function _createArticle( $article ) {
        $this->_sendArticle( $article, "create" );
    }

    function _createDOM( $action = "view" ) {
        $dom = new DomDocument('1.0','UTF-8');

        if ( $this->theme ) {
            if ( !$this->theme->hasAction( $action ) ) {
                $this->err[] = "Theme '".$this->theme->name." does not support action '". $action . "'";
                $action = "view";
            }
            $xslt = $this->theme->xslFor( $action );
            $xslt = $dom->createProcessingInstruction("xml-stylesheet", "type=\"text/xsl\" href=\"$xslt\"");
            $dom->appendChild( $xslt );
        }

        $root = $dom->createElementNS( SGlossWiki::$NS, 'sgloss' );
        $dom->appendChild( $root );
        $title = $dom->createElementNS( SGlossWiki::$NS, 'title' );
        $title->appendChild( $dom->createTextNode( $this->title ) ); 
        $root->appendChild( $title );
        foreach ( $this->err as $msg ) {
            $e = $dom->createElementNS( SGlossWiki::$NS, 'error' );
            $e->appendChild( $dom->createTextNode( $msg ) ); 
            $root->appendChild( $e );
        }
        foreach ($this->msg as $msg ) {
            $e = $dom->createElementNS( SGlossWiki::$NS, 'message' );
            $e->appendChild( $dom->createTextNode( $msg ) ); 
            $root->appendChild( $e );
        }
        if ( FALSE && $this->debug ) {
            $node = $dom->createElementNS( SGlossWiki::$NS, 'debug' );
            $node->appendChild( $dom->createTextNode( print_r($this->debug,1) ) ); 
            $root->appendChild( $node );
        }

        return $dom;
    }

    function _saveArticle( $article ) {
        if ( $this->err ) return;
        try {
            # TODO: remove meta elements (backlink etc.)
            $xml = $article->dom->saveXML();
            $xml = preg_replace('/^<\?[^>]+?>\s*<article[^>]+>/m','', $xml);
            $xml = preg_replace('/<\/article>$/m','',$xml);
            $article->xml = $xml;
            $sth = $this->dbh->prepare('INSERT INTO articles VALUES (?,?)');
            $sth->execute(array( $article->title, $article->xmlContent() ));
            $article->exists = TRUE;
            $this->_replaceProperties( $article->title, $article->properties );
        } catch ( Exception $e ) {
            $this->err = "Failed to save article: " . $e->getMessage();
        }
    }

    function _deleteArticle( $title ) {
        if ( $this->err ) return;
        try {
            $sth = $this->dbh->prepare('DELETE FROM articles WHERE title = ?');
            $sth->execute(array( $title ));
            $sth = $this->dbh->prepare("DELETE FROM properties WHERE `article` = ?");
            $sth->execute(array( $title ));
        } catch ( Exception $e ) {
            $this->err = "Failed to delete article: " . $e->getMessage();
        }
    }

    function _loadArticle( $title ) {
        $article = new SGlossArticle;
        if (!$this->err) {
            $sth = $this->dbh->prepare('SELECT * FROM articles WHERE title=?');
            if ( $sth->execute(array( $title )) ) {
                $result = $sth->fetchAll();
            } 
            if ( count($result) ) {
               $article = new SGlossArticle( $result[0]['title'] );
               $article->exists = TRUE;
               $article->xml = $result[0]['xml'];
            } else {
               $article->title = $title;
            }
        }
        $article->properties = $this->_getProperties( $article->title );
        return $article;
    }

    function _getProperties( $title ) {
        if ($this->err) return array();
        $properties = array();
        $sth = $this->dbh->prepare("SELECT `property`,`value` FROM properties WHERE article=?");
        if ( $sth->execute(array( $title )) ) {
            $result = $sth->fetchAll();
            foreach ( $result as $r ) {
                $p = $r[0];
                $v = $r[1];
                if (array_key_exists($p,$properties)) 
                    $properties[$p][] = $v; else $properties[$p] = array( $v );
            }
        }
        return $properties;
    }

    function _replaceProperties( $title, $properties ) {
        if ($this->err) return array();
        $sth = $this->dbh->prepare("DELETE FROM properties WHERE article=?");
        $sth->execute(array($title));
        $sth = $this->dbh->prepare("INSERT INTO properties VALUES(?,?,?)");
        foreach ($properties as $p => $values) {
            foreach ( $values as $v ) {
                $sth->execute(array( $title, $p, $v ));
            }
        }
    }

    static $NS = "http://jakobvoss.de/sgloss/";

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

# TODO: split this object from storage details
class SGlossArticle {
    public $title;
    public $xml;
    public $dom;
    public $exists = FALSE;
    public $properties = array();

    public function SGlossArticle( $title = "" ) {
        $this->title = $title;
    }

    public function exists() {
#        return ($this->title != "" && $this->title != NULL && $this->xml != NULL);
         return $this->exists;
    }

    public function xmlContent() {
        return $this->xml;
    }

    // TODO: return documentfragment as content
    public function getDOM( $properties = array() ) {
        if (!$this->dom) {
            $this->dom = new DomDocument('1.0','UTF-8');
            if ( $this->xml ) {
                $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><article xmlns=\"http://jakobvoss.de/sgloss/\">"
                     . $this->xml . "</article>";
                $this->dom->loadXML( $xml );
        
            } else { // create new article
                $dom = $this->dom;  
                $root = $dom->createElementNS( SGlossWiki::$NS, 'article' );
                if ( $this->title != NULL ) {
                    $title = $dom->createElementNS( SGlossWiki::$NS, 'title' );
                    $title->appendChild( $dom->createTextNode( $this->title ) ); 
                    $root->appendChild( $title );
                } 
                $dom->appendChild( $root );
            }
            if (!$this->exists) {
                $attr = $this->dom->createAttribute('missing');
                $attr->appendChild( $this->dom->createTextNode('1') );
                $this->dom->documentElement->appendChild( $attr );
            }
        }
        foreach ( $properties as $p => $values ) {
            foreach ( $values as $v ) {
                $dom = $this->dom;  
                $elem = $dom->createElementNS( SGlossWiki::$NS, 'property' );
                $elem->appendChild( $dom->createTextNode( $v ) ); 
                $attr = $dom->createAttribute('name');
                $attr->appendChild( $dom->createTextNode($p) );
                $elem->appendChild( $attr );
                $this->dom->documentElement->appendChild( $elem );
            }
        }
        return $this->dom;
    }

    function getLinks() {
        $xpath = new DOMXPath( $this->getDOM() );
        $xpath->registerNamespace('g',SGlossWiki::$NS);

        $links = array();

        $alinks = $xpath->evaluate('g:text/g:link[@to]');
        if (!is_null($alinks)) {
            foreach ($alinks as $link) {
                $to = $link->getAttribute('to');
                $links[$to] = 1;
            }
        }

        return array_keys($links);
    }

    function setData( $data, $exists = NULL ) {
        $properties = array();

        if ( $exists !== NULL ) $this->exists = $exists;        

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

           if ( $lastempty ) {
               if ( preg_match('/^([a-zA-Z][a-zA-Z]+)\s*[=:]\s*(.*)$/',$line,$match) ) {
                  $key = $match[1];
                  $v = trim($match[2]);
                  if (array_key_exists($key,$properties)) 
                      $properties[$key][] = $v; else $properties[$key] = array($v);
                  continue;
               }
           }

           if ( preg_match('/^\[\[([^]]*)\]\]/',$text) ) $text = " $text";
           
           while ( $text != "" and preg_match('/^(.*?)\[\[([^]]*)\]\](.*)$/', $text, $match) ) {
              if ( $match[1] != "" ) {
                  $textElem->appendChild( $dom->createTextNode( $match[1] ) );
              }

              $text = $match[3];
              $link = explode("|",$match[2]);
              $to = $link[0];
              $linktext = (count($link) > 1 && $link[1] != "") ? $link[1] : $link[0];

              # TODO: normalize title

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

        $this->properties = $properties;
    }
}

?>
