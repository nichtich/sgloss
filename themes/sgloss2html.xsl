<?xml version="1.0" encoding="UTF-8"?>
<!--
Copyright (c) 2010 Jakob Voss. All Rights Reserved.

The contents of this file may be used under the terms of the 
GNU Affero General Public License (the [AGPLv3] License).
-->
<xsl:stylesheet
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
  xmlns:g="http://jakobvoss.de/sgloss/"
  xmlns="http://www.w3.org/1999/xhtml"
>

  <xsl:param name="themeurl"/>

  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <xsl:template match="/g:sgloss">
    <html>
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>
          <xsl:value-of select="g:title"/>
        </title>
        <link rel="stylesheet" type="text/css" href="{$themeurl}sgloss.css"/>
      </head>
      <body>
        <div class="sgloss">
          <h1>
            <xsl:value-of select="g:title"/>
          </h1>
          <div class="sg-articles">
            <xsl:apply-templates select="g:article"/>
          </div>
        </div>
      </body>
    </html>
  </xsl:template>

  <xsl:template match="g:article">
    <xsl:param name="editable"/>
    <div class="sg-article">
      <h2 id="article-{g:title}">
        <xsl:if test="not($editable)"><xsl:value-of select="g:title"/></xsl:if>
        <xsl:if test="$editable">
          <a href="?title={g:title}&amp;action=edit"><xsl:value-of select="g:title"/></a>
        </xsl:if>
      </h2>
      <xsl:if test="g:alias">
        <span class="sg-alias">
          <xsl:value-of select="g:alias"/> <!-- TODO: multiple -->
        </span>
      </xsl:if>
      <xsl:apply-templates select="g:text"/>
      <xsl:if test="g:see|g:reference|g:author">
        <div style='padding-top:0.3em'> <!-- TODO: grouping -->
          <xsl:if test="g:see">
            <div class="sg-sees">
              <xsl:for-each select="g:see">
<!-- TODO -->
  <!--      /*print $out "<div class='vernet'>&#x2197;&#xA0;";
        $vernet =~ s/([^,]+)(,?\s*)/title2link($1)."$2"/ge;-->
              </xsl:for-each>
            </div>
          </xsl:if>
          <xsl:if test="g:reference">
            <div class="sg-references">
              <xsl:apply-templates select="g:reference"/>
            </div>
          </xsl:if>
          <xsl:if test="g:author">
            <div class="sg-authors">
              <xsl:apply-templates select="g:author"/>
            </div>
          </xsl:if>
        </div>
      </xsl:if>
      <xsl:apply-templates select="." mode="properties"/>
    </div> <xsl:comment>article</xsl:comment>
  </xsl:template>

  <xsl:template match="g:article" mode="properties"/>

  <xsl:template match="g:text">
    <div class="sg-text">
        <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="g:reference">
    <div class="sg-reference">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="g:author">
    <div class="sg-author">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="g:link">
    <xsl:variable name="text">
      <xsl:choose>
        <xsl:when test="normalize-space(.)">
          <xsl:value-of select="normalize-space(.)"/>
        </xsl:when>
        <xsl:when test="@to">
          <xsl:value-of select="@to"/>
        </xsl:when>
        <xsl:when test="@href">
          <xsl:value-of select="@href"/>
        </xsl:when>
      </xsl:choose>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="@to">
        <xsl:variable name="class">
          <xsl:text>sg-link</xsl:text>
          <xsl:if test="@missing"> missing</xsl:if>
        </xsl:variable>
        <a class="{$class}">
          <xsl:attribute name="href">
             <xsl:choose>
               <xsl:when test="@action"><xsl:value-of select="@action"/></xsl:when>
               <!--xsl:when test="not(@action)"><xsl:value-of select="@to"/></xsl:when-->
               <xsl:otherwise>#<xsl:value-of select="@to"/></xsl:otherwise>
             </xsl:choose>
          </xsl:attribute>
          <xsl:value-of select="$text"/>
        </a>
      </xsl:when>
      <xsl:when test="@href">
        <a href="{@href}" class="sg-extlink">
          <xsl:value-of select="$text"/>
        </a>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
