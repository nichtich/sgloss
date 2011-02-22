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

  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <xsl:template match="/">
    <html>
      <body>
        <h1>SGloss Themes</h1>
        <p>
This directory contains several SGloss <em>themes</em>, which can be
used to display SGloss glossaries in different forms. A theme consists
of a set of XSLT scripts (and possibly some addition layout files such
as CSS and JavaScript), that each provide one or more <em>actions</em>.
Every theme must at least provide action <tt>view</tt> to simply display
a glossary or a single glossary article.
        </p>
        <xsl:apply-templates/>
      </body>
    </html>
  </xsl:template>

  <xsl:template match="sg:theme">
    <h2><a href="{@name}/"><xsl:value-of select="@name"/></a></h2>
    <div>
      This theme supports the following actions
      <ul>
       <xsl:apply-templates select="sg:action"/>
      </ul>
    </div>
  </xsl:template>

  <xsl:template match="sg:action">
    <li><xsl:value-of select="@name"/></li>
  </xsl:template>

</xsl:stylesheet>
