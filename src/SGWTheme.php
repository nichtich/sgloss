<?php

/**
 * A Theme is a directory of xslt scripts to transform SGWiki output
 * Each Theme should provide a set of core actions: 
 * - view (view one or more articles)
 * - edit (edit one or more articles)
 * - create (create a new article)
 */
class SGWTheme {

    var $name;
    var $actions = array();

    function SGWTheme( $name ) {
         $dir = self::$basedir . "/$name";
         if ( preg_match('/^[a-z][a-z0-9-_]*$/',$name) && is_dir($dir) ) {
             $this->name = $name;
             foreach ( scandir($dir) as $action ) {
                 if ( preg_match('/^[a-z]+\.xsl$/',$action) ) {
                     $action = substr($action,0,-4);
                     $this->actions[ $action ] = $action;
                 }
             }
         } else {
             throw new Exception("could not initialize theme `$name`");
         }
    }

    function asXML() {
        $xml = array('<theme name="' . $this->name . '">');
        foreach ( $this->actions as $a ) {
            $xml[] = "  <action name=\"$a\"/>";
        }
        $xml[] = '</theme>';
        return implode("\n",$xml);
    }

    function actionExists( $action ) {
        return array_key_exists( $action, $this->actions );
    }

    function xslfile($action) {
        if ( $this->actionExists($action) ) {
            return self::$basedir . '/' . $this->name . '/' . $action . '.xsl';
        } else {
            return "";
        }
    }

    static $basedir = ".";
    static function loadThemes() {
        $themes = array();
        foreach ( scandir( self::$basedir ) as $entry ) {
            try {
                $themes[] = new SGWTheme( $entry );
            } catch( Exception $e ) {
            }
        }
        return $themes;
    }
}

?>
