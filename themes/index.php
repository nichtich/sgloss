<?php
/**
 * Returns a list of all available themes in XML format
 */

include '../src/SGWTheme.php';
SGWTheme::$basedir = '.';

header('content-type: text/xml; encoding=UTF-8');

print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
print "<?xml-stylesheet type=\"text/xsl\" href=\"themes.xsl\"?>\n";
print "<themes xmlns=\"http://jakobvoss.de/sgloss/\">\n";
foreach ( SGWTheme::loadThemes() as $t ) {
    print $t->asXML() . "\n";
}
print "</themes>\n";

?>
