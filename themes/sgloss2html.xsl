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

  <xsl:variable name="articles_with_text" select="/g:sgloss/g:article[g:text]"/>

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
            <xsl:apply-templates select="g:article[g:text]"/>
          </div>
        </div>
      </body>
    </html>
  </xsl:template>

  <xsl:template match="g:article">
    <xsl:param name="editable"/>
    <div class="sg-article">
      <h2 id="article-{g:title}">
        <xsl:choose>
          <xsl:when test="$editable">
            <a href="?title={g:title}">
              <xsl:value-of select="g:title"/>
            </a>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="g:title"/>
          </xsl:otherwise>
        </xsl:choose>
      </h2>
      <xsl:if test="$editable">
        <a href="?title={g:title}&amp;action=edit">&#x2605;</a>
      </xsl:if>
      <xsl:if test="g:property[@name='syn']">
        <span class="sg-alias">
          <xsl:for-each select="g:property[@name='syn']">
            <xsl:if test="position() &gt; 1">, </xsl:if>
            <xsl:value-of select="."/>
          </xsl:for-each>
        </span>
      </xsl:if>
      <xsl:apply-templates select="g:text"/>
      <xsl:apply-templates select="." mode="commonprop"/>

      <xsl:apply-templates select="." mode="properties"/>
    </div>
  </xsl:template>

  <xsl:template match="g:article" mode="properties"/>

  <xsl:template match="g:article" mode="commonprop">
      <xsl:variable name="see" select="g:property[@name='see']"/>
      <xsl:variable name="lit" select="g:property[@name='lit']"/>
      <xsl:variable name="aut" select="g:property[@name='author']"/>
      <xsl:if test="$see|$lit|$aut">
        <div style='padding-top:0.3em'>
          <xsl:if test="$see">
            <ul class="sg-sees csv">
              <xsl:for-each select="$see">
                <!-- TODO -->
                <li><a class="sg-link"><xsl:value-of select="."/></a></li>
              </xsl:for-each>
            </ul>
          </xsl:if>
          <xsl:if test="$lit">
            <div class="sg-references">
              <xsl:for-each select="$lit">
                <div class="sg-reference">
                  <xsl:apply-templates/>
                </div>
              </xsl:for-each>
            </div>
          </xsl:if>
          <xsl:if test="$aut">
            <div class="sg-authors">
              <xsl:apply-templates select="$aut"/>
            </div>
          </xsl:if>
        </div>
      </xsl:if>
  </xsl:template>

  <xsl:template match="g:text">
    <div class="sg-text">
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <!-- TODO -->
  <xsl:template match="g:property[@name='author']">
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
        <xsl:variable name="to" select="@to"/>
        <xsl:variable name="class">
          <xsl:text>sg-link</xsl:text>
          <xsl:if test="not(//g:article[g:title=$to])"> missing</xsl:if>
        </xsl:variable>
        <a class="{$class}">
          <xsl:attribute name="href">
             <xsl:choose>
               <xsl:when test="$articles_with_text[g:title=$to]">#<xsl:value-of select="@to"/></xsl:when>
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
