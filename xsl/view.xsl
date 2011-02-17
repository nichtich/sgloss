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
  <xsl:import href="sgloss2html.xsl"/>

  <xsl:param name="action">view</xsl:param>

  <xsl:param name="cssurl">sgloss.css</xsl:param>
  <xsl:param name="jsurl"></xsl:param>
  <xsl:param name="title" select="/sg:sgloss/sg:title"/>

  <xsl:output method="html"/>

  <xsl:template match="/sg:sgloss">
    <html>
      <xsl:call-template name="htmlhead"/>
      <body>
        <xsl:apply-templates select="." mode="header"/>
        <xsl:apply-templates select="sg:message|sg:error"/>
        <div id="body">
           <!--h1><xsl:value-of select="$title"/></h1-->
           <div class="sg-articles">
            <xsl:apply-templates select="sg:article">
              <xsl:with-param name="editable" select="true()"/>
            </xsl:apply-templates>
          </div>
        </div>
        <xsl:apply-templates select="." mode="footer"/>
      </body>
    </html>
  </xsl:template>

</xsl:stylesheet>
