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

  <xsl:param name="action">view</xsl:param>

  <xsl:param name="title" select="/sg:sgloss/sg:title"/>

</xsl:stylesheet>
