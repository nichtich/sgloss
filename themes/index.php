<?php
/**
 * Returns a list of all available themes in XML format
 */

include '../src/SGWTheme.php';

header('content-type: text/xml; encoding=UTF-8');

print '<?xml version="1.0" encoding="UTF-8"?>';
print "\n<themes>\n";
foreach ( SGWTheme::loadThemes() as $t ) {
    print $t->asXML() . "\n";
}
print "</themes>\n";

?>
