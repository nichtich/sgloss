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

  <xsl:import href="../wikisyntax/_wikisyntax.xsl"/>
  <xsl:import href="_core.xsl"/>

  <xsl:param name="cssurl">sgloss.css</xsl:param>
  <xsl:param name="jsurl"></xsl:param>

  <xsl:param name="article" select="/sg:sgloss/sg:article[1]"/>

    <xsl:param name="title">
      <xsl:choose>
        <xsl:when test="$article/@missing">
          <xsl:text>Create </xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>Edit </xsl:text>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:text>article: </xsl:text>
      <xsl:value-of select="$article/sg:title"/>
    </xsl:param>

  <xsl:template match="sg:article">
    <!--h2><xsl:value-of select="sg:title"/></h2-->
    <form method="post">
      <input type="hidden" name="title" value="{sg:title}"/>
      <input type="hidden" name="action" value="edit"/>

      <textarea name="data" cols="80" rows="16">
        <xsl:apply-templates select="." mode="wikisyntax"/>
      </textarea> 
      <br/>
      <input type="submit" name="edit" value="save"/>
      &#xA0;
      &#xA0;
      &#xA0;
      <input type="submit" name="edit" value="reset"/>
      <input type="submit" name="edit" value="cancel"/>
      &#xA0;
      &#xA0;
      <input type="submit" name="edit" value="delete"/>
    </form> 
  </xsl:template>

</xsl:stylesheet>
