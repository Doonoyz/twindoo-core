<?xml version="1.0" encoding="iso-8859-1"?>

<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1">
	
	<!-- Called on the top container. Lists all the container elements (packages) -->
	<xsl:template name="overview-summary">
		<xsl:param name="containerSet"/>

		<html>
			<xsl:call-template name="htmlStartPage"/>
			<xsl:call-template name="htmlHead">
				<xsl:with-param name="path" />
				<xsl:with-param name="title" select="concat(@name,' - Overview')"/>
			</xsl:call-template>
			<body>

				<div id="navigation">
					<div id="banner">
						<ul class="sections">
							<li class="active">Overview</li>
							<li>Package</li>
							<li>Class</li>
							<li><a href="{$fileIndexAll}">Index</a></li>
						</ul>
						<ul class="navigFrame">
							<a href="{concat($fileIndex,'?',$fileOverviewSummary)}" class="navigFrameElem" target="_top">FRAMES</a>
							<xsl:text>&#32;</xsl:text>
							<a href="{$fileOverviewSummary}" class="navigFrameElem" target="_top">NO FRAMES</a>
						</ul>
					</div>
				</div>

				<div id="content">
					<div class="classSummary">
						<h1 id="entityName"><xsl:value-of select="@name" /></h1>
					</div>
					<p>
						<xsl:apply-templates select="ownedComment"/>
					</p>
					<hr/>
					<xsl:if test="count($containerSet) &gt; 0">
						<h2>Packages</h2>
						<table border="1" class="tableSummary">
							<xsl:for-each select="$containerSet">
								<tr>
									<td>
									<xsl:variable name="path">
										<xsl:call-template name="getPackageFilePath">
											<xsl:with-param name="context" select="."/>
										</xsl:call-template>
										<xsl:value-of select="@name"/>
									</xsl:variable>
									<strong><a href="{concat('./', $path, '/', $filePackageSummary)}">
										<xsl:call-template name="getPackageNamePart"/>
										<xsl:value-of select="@name"/>
									</a></strong></td>
									<td>
										<xsl:call-template name="titleComment"/>
									</td>
								</tr>
							</xsl:for-each>
						</table>
					</xsl:if>

				</div>
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>