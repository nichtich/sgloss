<?xml version="1.0" encoding="UTF-8"?>
<!--
Copyright (c) 2011 Jakob Voss. All Rights Reserved.

The contents of this file may be used under the terms of the 
GNU Affero General Public License (the [AGPLv3] License).
-->
<xsl:stylesheet
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
  xmlns:sg="http://jakobvoss.de/sgloss/"
  xmlns="http://www.w3.org/1999/xhtml"
>

  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <xsl:param name="themeurl">themes/default/</xsl:param>

  <xsl:variable name="VERSION">0.0.2</xsl:variable>

  <xsl:template match="/sg:sgloss">
    <html>
      <xsl:call-template name="htmlhead"/>
      <body>
        <xsl:if test="sg:message|sg:error">
          <div id="msg-container">
             <xsl:apply-templates select="sg:message|sg:error"/>
          </div>
        </xsl:if>
        <xsl:apply-templates select="." mode="header"/>
        <div id="body">
           <xsl:if test="$title">
             <h1><xsl:value-of select="$title"/></h1>
           </xsl:if>
           <div class="sg-articles">
            <xsl:apply-templates select="sg:article">
              <xsl:sort select="sg:title"/>
              <xsl:with-param name="editable" select="true()"/>
            </xsl:apply-templates>
          </div>
        </div>
        <xsl:apply-templates select="." mode="footer"/>
      </body>
    </html>
  </xsl:template>

  <xsl:template name="htmlhead">
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <title>
       <xsl:value-of select="$title"/>
      </title>
      <link rel="stylesheet" type="text/css" href="{$themeurl}sgloss.css"/>
      <script type="text/javascript" src="{$themeurl}jquery-1.5.min.js"/>
      <script type="text/javascript" src="{$themeurl}sgloss.js"/>
    </head>
  </xsl:template>

  <xsl:template match="sg:sgloss" mode="header">
    <div id="header">
      <a class="title" href="?"><xsl:value-of select="sg:title"/></a>
      &#xA0;
      <a href="?action=list">a-z</a>
      &#xA0;
      <a href="?action=create">create</a>
      &#xA0;
      <a href="?action=links">links</a>
    </div>
  </xsl:template>

  <xsl:template match="sg:sgloss" mode="footer">
    <xsl:if test="//sg:debug">
      <div class="debug"><pre><xsl:value-of select="//sg:debug"/></pre></div>
    </xsl:if>
    <div id="footer">
      powered by <a href="https://github.com/nichtich/sgloss">SGloss</a>
      &#xA0;<xsl:value-of select="$VERSION"/>&#xA0;
      (just SQLite, PHP, XML, XSLT, HTML, CSS)
    </div>
  </xsl:template>

  <!-- messages -->
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