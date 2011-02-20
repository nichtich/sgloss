<?xml version="1.0" encoding="UTF-8"?>
<!--
Copyright (c) 2010 Jakob Voss. All Rights Reserved.

The contents of this file may be used under the terms of the 
GNU Affero General Public License (the [AGPLv3] License).
-->
<xsl:stylesheet
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
  xmlns:sg="http://jakobvoss.de/sgloss/"
  xmlns="http://www.w3.org/1999/xhtml"
>

  <xsl:import href="_xmlverbatim.xsl" />
  <xsl:param name="indent-elements" select="true()" />

  <xsl:template match="/">
    <html>
      <head>
<style>
body { font-family: monospace; }
h1 { font-size: 120%; margin: 0; }
/* xmlverbatim */
.xmlverb-default          { color: #333; font-family: monospace;
 font-size: medium; background-color: #eee; border: 1px solid #666;
 padding: 0.5em; 
}
.xmlverb-element-name     { color: #900 }
.xmlverb-element-nsprefix { color: #660 }
.xmlverb-attr-name        { color: #600 }
.xmlverb-attr-content     { color: #009; font-weight: bold }
.xmlverb-ns-name          { color: #660 }
.xmlverb-ns-uri           { color: #309 }
.xmlverb-text             { color: #000; font-weight: bold }
.xmlverb-comment          { color: #060; font-style: italic }
.xmlverb-pi-name          { color: #060; font-style: italic }
.xmlverb-pi-content       { color: #066; font-style: italic }

</style>
      </head>
      <body>
        <!--h1>SGloss XML response</h1-->
        <xsl:apply-templates select="." mode="xmlverb"/>
      </body>
    </html>
  </xsl:template>

</xsl:stylesheet>
