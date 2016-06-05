<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<!-- KO_20050713: Adding a doctype breaks the display of the page. This seems to be a wide-spread issue. Commented out for consistancy for now.
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        /KO_20050713-->

<xsl:transform 
     xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:template match="phpcheckstyle">

<xsl:variable name="files" select="//file"/>
<xsl:variable name="filesWithErrors" select="//file/error[1]"/>
<xsl:variable name="errors" select="//error"/>

<xsl:variable name="totalFiles" select="count($files)"/>
<xsl:variable name="totalErrorFiles" select="count($filesWithErrors)"/>
<xsl:variable name="totalErrors" select="count($errors)"/>

<xsl:variable name="home" select="//home/@src"/>
<xsl:variable name="lineTotal" select="//home/@lineTotal"/>

<html xmlns="http://www.w3.org/1999/xhtml">
  <head>

    <title>PHPCheckstyle Results</title>
        <link href="{$home}css/spikesource.css" rel="stylesheet" type="text/css" />
        <link href="{$home}css/global.css" rel="stylesheet" type="text/css" />

        <style type="text/css">
            /* KO_20050713: Override Global CSS */

            .spikeDataTableHeadCenter, .spikeDataTableHeadCenterLast {text-align:left;}
            .spikeVerticalTableHead, .spikeVerticalTableHeadLast, .spikeDataTableHeadCenter, .spikeDataTableHeadCenterLast {font-size:11px;}
            .spikeVerticalTableHead, .spikeVerticalTableHeadLast, .spikeVerticalTableCell, .spikeVerticalTableCellLast {vertical-align:middle;}

            h1 {
                padding-top:10px;
                margin-bottom:5px;
                                font-size:14px;
            }

                        .copyright {border-top:3px solid #06c; padding-top:2px;}
                        div.ahrefTop {width:100%; text-align:right; font-size:10px;}
        </style>
  </head>
  <body>
  <center>

        <div id="top" class="content-small" style="padding-left:10px;padding-top:3px;width:95%">
            <table border="0" cellpadding="0" cellspacing="0" style="width:750px;">
                <tr>

                    <td width="10%">
                        <a href="http://www.spikesource.com/" style="text-decoration:none" target="_blank">
                            <img src="{$home}images/spikesource_logo.gif" border="0" style="padding-bottom:5px;"/>
                        </a>
                    </td>

                    <td style="text-align:right; vertical-align:bottom; padding-bottom:10px;">
                        <h1><font size="5">Spike PHPCheckstyle</font></h1>
                    </td>

            </tr>
<!-- KO_20050713: The following block is junk - should be removed with padding but will be left alone for consistancy. -->
                <tr><td class="content" colspan="4"><img src="{$home}images/spacer.gif" height="5" width="1" border="0"/></td></tr>
<!-- /KO_20050713 -->
            <tr>
                    <td colspan="2" class="content-main" align="left" valign="top" style="border-top:3px solid #06c; padding-right:0px;">


<!-- Summary -->
                        <h1>Summary @ "<xsl:value-of select="//home/@path"/>"</h1>
                        <table class="spikeVerticalTable" cellpadding="4" cellspacing="0" width="100%" border="0">

                            <tr>
                                <td class="spikeVerticalTableHead" style="width:33%;">Total Number of Files</td>
                                <td class="spikeVerticalTableCell" style="width:67%;"><span class="emphasis"><xsl:value-of select="$totalFiles"/></span></td>
                            </tr>
                            <tr>
                                <td class="spikeVerticalTableHead">Number of Files With Errors</td>
                                <td class="spikeVerticalTableCell"><span class="emphasis"><xsl:value-of select="$totalErrorFiles"/></span></td>
                            </tr>
                            <tr>
                                <td class="spikeVerticalTableHead">Total Number of Errors</td>
                                <td class="spikeVerticalTableCell"><span class="emphasis"><xsl:value-of select="$totalErrors"/></span></td>
                            </tr>
                            <tr>
                                <td class="spikeVerticalTableHead">Average Errors Per File</td>
                                <td class="spikeVerticalTableCell"><span class="emphasis"><xsl:value-of select="format-number($totalErrors div $totalFiles, '###.00')"/></span></td>
                            </tr>
                            <tr>
                                <td class="spikeVerticalTableHead">Total Number of Lines</td>
                                <td class="spikeVerticalTableCell"><span class="emphasis"><xsl:value-of select="$lineTotal"/></span></td>
                            </tr>
                            <tr>
                                <td class="spikeVerticalTableHeadLast" align="center">Total Validity</td>
                                <td class="spikeVerticalTableCellLast">
                                <table class="spikeVerticalTable" cellpadding="2" cellspacing="0" width="100%" border="0">
                                    <tr>
                                        <xsl:variable name="validity" select="format-number(($lineTotal - $totalErrors) div $lineTotal * 100,'###.00')"/>
                                        <td class="spikeVerticalTableCellLast" width="{$validity}%" bgcolor="#66FF66"><span class="emphasis"><xsl:value-of select="$validity"/>%</span></td>
                                        <xsl:if test="$totalErrors != 0">
                                            <td bgcolor="#FF3333"></td>
                                        </xsl:if>
                                    </tr>
                                </table>
                                </td>
                            </tr>

                        </table>

<!-- Files With Errors -->
    <xsl:if test="$totalErrors != 0">
                        <h1>Files With Errors</h1>
                        <table width="100%" class="spikeDataTable" cellpadding="4" cellspacing="0">
                            <thead>

                                <tr>
                                    <th nowrap="nowrap" class="spikeDataTableHeadCenter" style="width:75%; test-align:left;">
                                        Filename
                                    </th>
                                    <th style="width:25%" class="spikeDataTableHeadCenterLast">
                                        Number of Errors
                                    </th>
                                </tr>
                          </thead>
                          <tbody>

        <xsl:for-each select="$files">
            <xsl:variable name="this" select="."/>
            <xsl:variable name="myname" select="@name"/>
            <xsl:variable name="fileTotal" select="count(//file[@name=$myname]/error)"/>
            <xsl:if test="$fileTotal != 0">

                                <tr>
                                    <td class="spikeDataTableCellLeftBorder">
                                        <A HREF="{$myname}.html"><xsl:value-of select="$myname"/></A>

                                    </td>
                                    <td class="spikeDataTableCellLeft">
                                        <xsl:value-of select="$fileTotal"/>
                                    </td>
                                </tr>
            </xsl:if>
        </xsl:for-each>

                            </tbody>

                        </table>
                        <div class="ahrefTop"><a href="#top">Top</a></div>

    </xsl:if>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">

                        <div style="width:100%;">
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="copyright" align="left">
                                        <a href="http://www.spikesource.com/projects/phpcheckstyle/">Spike PHPCheckstyle Home</a>
                                    </td>
                                    <td class="copyright" align="right">
                                        Copyright 2004<script language="JavaScript" type="text/javascript">var d=new Date();yr=d.getFullYear();if (yr!=2004)document.write("-"+yr);</script>, SpikeSource, Inc.
                                    </td>

                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
  </center>
  </body>

</html>
</xsl:template>
</xsl:transform>
