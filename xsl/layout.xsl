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

  <xsl:variable name="VERSION">0.0.1</xsl:variable>

  <!-- general layout templates -->

  <xsl:template name="htmlhead">
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <title>
       <xsl:value-of select="$title"/>
      </title>
      <xsl:if test="$cssurl">
        <link rel="stylesheet" type="text/css" href="{$cssurl}"/>
      </xsl:if>
      <xsl:if test="$jsurl">
        <script type="text/javascript" src="{$jsurl}"/>
      </xsl:if>
    </head>
  </xsl:template>

  <xsl:template match="sg:sgloss" mode="header">
    <div id="header">
      <a class="title" href="?"><xsl:value-of select="sg:title"/></a>
      &#xA0;
      <a href="?action=list">a-z</a>
    </div>
  </xsl:template>

  <xsl:template match="sg:sgloss" mode="footer">
    <div id="footer">
      powered by <a href="https://github.com/nichtich/sgloss">SGloss</a>
      &#xA0;<xsl:value-of select="$VERSION"/>&#xA0;
      (just SQLite, PHP, XML, XSLT, HTML, CSS)
    </div>
  </xsl:template>

  <xsl:template match="sg:message">
    <div class="message">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="sg:error">
    <div class="error">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

</xsl:stylesheet>
