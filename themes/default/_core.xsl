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

  <xsl:variable name="themes" select="document('../')"/>

  <xsl:variable name="VERSION" select="normalize-space(/*/@version)"/>

    <xsl:variable name="aonetitle">
      <xsl:if test="count(//sg:article)=1">
        <xsl:value-of select="//sg:article/sg:title"/>
      </xsl:if>
    </xsl:variable>

  <xsl:template match="/sg:sgloss">
    <html>
      <xsl:call-template name="htmlhead"/>
      <body>
        <xsl:if test="sg:message|sg:error|sg:warning">
          <div id="msg-container">
             <xsl:apply-templates select="sg:message|sg:error|sg:warning"/>
          </div>
        </xsl:if>
        <xsl:apply-templates select="." mode="header"/>
        <div id="body">
           <xsl:if test="$title">
             <h1><xsl:value-of select="$title"/></h1>
           </xsl:if>
          <xsl:apply-templates select="." mode="pre-articles"/>
           <xsl:if test="sg:article">
             <div class="sg-articles">
              <xsl:apply-templates select="sg:article">
                <xsl:sort select="sg:title"/>
                <xsl:with-param name="editable" select="true()"/>
              </xsl:apply-templates>
            </div>
          </xsl:if>
          <xsl:apply-templates select="." mode="post-articles"/>
        </div>
        <xsl:apply-templates select="." mode="footer"/>
      </body>
    </html>
  </xsl:template>

  <!-- hooks -->
  <xsl:template match="sg:sgloss" mode="pre-articles"/>
  <xsl:template match="sg:sgloss" mode="post-articles"/>

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
      <ul>
        <!-- TODO: the actions should come from the server -->
        <li><a href="?action=list">a-z</a></li>
        <li><a href="?action=create">create</a></li>
        <li><a href="?action=links">links</a></li>
        <li><a href="?action=import">import</a></li>
      </ul>
    </div>
  </xsl:template>

  <xsl:template match="sg:themes">
    <div id="select-themes">
      Themes:
      <ul class="csv">
        <xsl:for-each select=".//sg:theme">
          <li>
            <a href="?theme={@name}&amp;title={$aonetitle}">
              <xsl:if test="@name = 'default'">
                <xsl:attribute name="class">selected</xsl:attribute>
              </xsl:if>
              <xsl:value-of select="@name"/>
            </a>
          </li>
        </xsl:for-each>
      </ul>
    </div>
  </xsl:template>

  <xsl:template match="sg:sgloss" mode="footer">
    <xsl:if test="//sg:debug">
      <div class="debug"><pre><xsl:value-of select="//sg:debug"/></pre></div>
    </xsl:if>
    <xsl:apply-templates select="$themes"/>
    <div id="footer">
      powered by <a href="https://github.com/nichtich/sgloss">SGloss</a>
      <xsl:text> </xsl:text>
      <xsl:value-of select="$VERSION"/>
      <xsl:text> </xsl:text>
      (just SQLite, PHP, XML, XSLT, HTML, CSS)
    </div>
  </xsl:template>

  <!-- messages -->
  <xsl:template match="sg:message|sg:error|sg:warning">
    <div class="{local-name(.)}">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

</xsl:stylesheet>
