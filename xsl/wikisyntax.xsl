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

  <xsl:output method="text"/>

  <xsl:template match="sg:article" mode="wikisyntax">
    <xsl:apply-templates select="sg:text" mode="wikisyntax"/>
  </xsl:template>

  <xsl:template match="text()" mode="wikisyntax">
    <!-- TODO: replace sequences of whitspace by one, but not remove ws like normalize-space -->
    <xsl:value-of select="."/>
  </xsl:template>

   <xsl:template match="sg:link" mode="wikisyntax">
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
        <xsl:text>[[</xsl:text>
        <xsl:value-of select="@to"/>
        <xsl:if test="@to != $text">
          <xsl:text>|</xsl:text>
          <xsl:value-of select="@text"/>
        </xsl:if>
        <xsl:text>]]</xsl:text>
      </xsl:when>
      <xsl:when test="@href">
        <xsl:if test="@href = $text">
          <xsl:value-of select="@ref"/>
        </xsl:if>
        <xsl:if test="@href != $text">
          <xsl:text>[</xsl:text>
          <xsl:value-of select="@ref"/>
          <xsl:text> </xsl:text>
          <xsl:value-of select="$text"/>
          <xsl:text>]</xsl:text>
        </xsl:if>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
 
</xsl:stylesheet>
