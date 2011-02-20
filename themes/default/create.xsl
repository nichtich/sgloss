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

  <xsl:param name="article" select="/sg:sgloss/sg:article[1]"/>

  <xsl:param name="title">Create new article</xsl:param>
  
  <xsl:template match="sg:article">
    <form method="post">
      <div>
        <label for="title">Title:</label>&#xA0;
        <input type="text" name="title" id="title" value="{sg:title}"/>
      </div>

      <input type="hidden" name="action" value="create"/>

      <textarea name="data" cols="80" rows="16">
        <xsl:apply-templates select="." mode="wikisyntax"/>
      </textarea> 
      <br/>
      <input type="submit" name="edit" value="save"/>
      &#xA0;
      &#xA0;
      &#xA0;
      <input type="submit" name="edit" value="cancel"/>
    </form> 
  </xsl:template>

</xsl:stylesheet>
