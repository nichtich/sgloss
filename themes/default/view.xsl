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

  <xsl:import href="../sgloss2html.xsl"/>
  <xsl:import href="_core.xsl"/>

  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <xsl:param name="action">view</xsl:param>

  <xsl:param name="title" select="/sg:sgloss/sg:title"/>

  <xsl:template match="sg:article" mode="properties">
    <span class="sg-length"><xsl:value-of select="string-length(normalize-space(sg:text))"/></span>
    <xsl:if test="sg:property">
      <div class="properties">
        <dl>
          <xsl:apply-templates select="sg:property"/>
        </dl>
      </div>
    </xsl:if>
  </xsl:template>

  <xsl:template match="sg:property">
    <dt><xsl:value-of select="@name"/></dt>
    <dd><xsl:value-of select="."/></dd>
  </xsl:template>

</xsl:stylesheet>
