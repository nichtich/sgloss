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

  <xsl:import href="_core.xsl"/>

  <xsl:param name="title">Import articles</xsl:param>
  <xsl:param name="action">import</xsl:param>

  <xsl:template match="sg:sgloss" mode="post-articles">
    <form method="post">
      <fieldset>
        <legend>upload articles in XML format</legend>
        <dl>
          <dt><label for="file">File</label></dt>
          <dd><input type="file" name="file" size="40"/>
              <input type="submit" class="button" value="upload and import file"/></dd>
          <dt><label for="url">URL</label></dt>
          <dd>
            <input type="text" name="url" size="40"/>
            <input type="submit" class="button" value="import from url"/>
          </dd>
        </dl>
      </fieldset>
    </form>
  </xsl:template>

</xsl:stylesheet>
