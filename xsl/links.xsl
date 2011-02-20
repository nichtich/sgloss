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

  <xsl:import href="layout.xsl"/>

  <xsl:param name="title">Links</xsl:param>
  <xsl:param name="action">links</xsl:param>

  <xsl:template match="sg:article">
    <div class="sg-article">
      <a>
        <xsl:attribute name="class">
          <xsl:text>sg-link</xsl:text>
          <xsl:if test="@missing"> missing</xsl:if>
        </xsl:attribute>
        <xsl:attribute name="href">
          <xsl:choose>
            <xsl:when test="@action"><xsl:value-of select="@action"/></xsl:when>
            <xsl:otherwise>?title=<xsl:value-of select="sg:title"/></xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <xsl:value-of select="sg:title"/>
      </a>
      <xsl:if test="sg:backlink">
        <ul class="backlinks">
          <xsl:for-each select="sg:backlink">
            <xsl:sort select="@to"/>
            <li>
              <a href="?title={@to}"><xsl:value-of select="@to"/></a>
            </li>
          </xsl:for-each>
        </ul>
      </xsl:if>
    </div>
  </xsl:template>

</xsl:stylesheet>
