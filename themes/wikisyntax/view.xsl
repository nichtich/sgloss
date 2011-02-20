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

  <xsl:import href="_wikisyntax.xsl" />

  <xsl:template match="/">
    <html>
      <head>
<!--style>
body { font-family: monospace; }
h1 { font-size: 100%; margin: 0; font-weigth: bold; }
.raw { 
  color: #333;
  font-size: medium; background-color: #eee; border: 1px solid #666;
  padding: 0.5em; 
}
</style-->
      </head>
      <body>
        <!--xsl:apply-templates select="sg:message|sg:error"/>
        <div id="body">
           <xsl:if test="$title">
             <h1><xsl:value-of select="$title"/></h1>
           </xsl:if-->
           <div class="raw">
            <xsl:apply-templates select="//sg:article" mode="wikisyntax">
              <xsl:sort select="sg:title"/>
            </xsl:apply-templates>
          </div>
        <!--/div-->
      </body>
    </html>
  </xsl:template>

</xsl:stylesheet>
