<?php
/**
 * A Theme provides a set of scripts to transform SGloss data for 
 * different actions. At least the action 'view' must be supported.
 */
class SGWTheme {
    static $basedir = "themes";
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
         } 
         if (empty($this->actions["view"])) {
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

    function hasAction( $action ) {
        return array_key_exists( $action, $this->actions );
    }

    function xslFor($action) {
        if ( $this->hasAction($action) ) {
            return self::$basedir . '/' . $this->name . '/' . $action . '.xsl';
        } else {
            return "";
        }
    }

}

?>
