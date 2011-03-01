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

  <xsl:output method="html"/>

  <xsl:param name="author"/>
  <xsl:param name="publisher"/>

  <xsl:template match="/">
    <html>
      <head>
      </head>
      <body>
         <div>
           <xsl:apply-templates select="//sg:article[sg:text]">
             <xsl:sort select="sg:title"/>
           </xsl:apply-templates>
        </div>
      </body>
    </html>
  </xsl:template>

  <xsl:template match="sg:article">
    <p>
      <xsl:text>RANG_</xsl:text>
      <xsl:value-of select="sg:property[@name='rang']"/>
    </p>
    <p>
      <xsl:text>SYNANG: </xsl:text>
      <xsl:for-each select="sg:property[@name='syn']">
        <xsl:if test="position() &gt; 1">, </xsl:if>
        <xsl:value-of select="."/>
      </xsl:for-each>
    </p>
    <p>
      <xsl:text>VERPFB: </xsl:text>
    </p>
    <p>
      <xsl:text>ARTEXT: </xsl:text>
      <xsl:apply-templates select="sg:text"/>
    </p>
    <p>
      <xsl:text>VERNET: </xsl:text>
      <xsl:if test="sg:property[@name='see']">-&gt; </xsl:if>
      <xsl:for-each select="sg:property[@name='see']">
        <xsl:if test="position() &gt; 1">, </xsl:if>
        <xsl:value-of select="."/>
      </xsl:for-each>
    </p>
    <p>
      <xsl:text>LITANG: </xsl:text>
      <xsl:for-each select="sg:property[@name='lit']">
        <xsl:if test="position() &gt; 1">&#xA0;</xsl:if>
        <xsl:value-of select="."/>
      </xsl:for-each>
    </p>
    <p>
      <xsl:text>VERANG: </xsl:text>
      <xsl:value-of select="$author"/>
    </p>
    <p>
      <xsl:text>ABBDAT: </xsl:text>
    </p>
    <p>
      <xsl:text>ABBTEX: </xsl:text>
    </p>
    <p>
      <xsl:text>ISTUMF: </xsl:text>
      <xsl:value-of select="string-length(normalize-space(sg:text))"/>
    </p>
    <p>
      <xsl:text>ZUHRSG: </xsl:text>
      <xsl:value-of select="$publisher"/>
    </p>
  </xsl:template>


  <xsl:template match="sg:text">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="text()">
    <xsl:value-of select="translate(normalize-space(concat('&#x7F;',.,'&#x7F;')),'&#x7F;','')"/>
  </xsl:template>

  <xsl:template match="sg:link">
    <xsl:choose>
      <xsl:when test="normalize-space(.)">
        <xsl:text>-&gt; </xsl:text>
        <xsl:value-of select="normalize-space(.)"/>
      </xsl:when>
      <xsl:when test="@to">
        <xsl:text>-&gt; </xsl:text>
        <xsl:value-of select="@to"/>
      </xsl:when>
      <xsl:when test="@href">
        <xsl:value-of select="@href"/>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
