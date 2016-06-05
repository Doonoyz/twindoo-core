<?xml version="1.0" encoding="iso-8859-1"?>

<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1"
	xmlns:php="http://schemas/phpdoc/">

	<!-- Common templates used by the code exporter -->
	
	<!-- PHP starting mark -->
	<xsl:template name="codeHeader">
		<xsl:param name="path"/>
		<xsl:param name="title"/>
		<xsl:text>&lt;?php&#xa;</xsl:text>
		<xsl:text>/**&#xa;</xsl:text>
		<xsl:value-of select="concat(' * @author ', $appName, '&#xa;')"/>
		<xsl:value-of select="concat(' * @since ', $genDate, '&#xa;')"/>
		<xsl:if test="$title!=''">
			<xsl:value-of select="concat(' * @package ', $title, '&#xa;')"/>
		</xsl:if>
		<xsl:text> */&#xa;</xsl:text>
	</xsl:template>

	<!-- PHP closing mark -->
	<xsl:template name="codeFooter">
		<xsl:text>?&gt;&#xa;</xsl:text>
	</xsl:template>

	<!-- Displays the parameters of a function as a bracketed list -->
	<xsl:template name="codeParametersBracket">
		<xsl:param name="relPathTop" />
		<xsl:text>(</xsl:text>
		<xsl:for-each select="ownedParameter[@direction!='return' or not(@direction)]">
			<xsl:call-template name="codeType">
				<xsl:with-param name="relPathTop" select="$relPathTop"/>
				<xsl:with-param name="context" select="."/>
			</xsl:call-template>
			<xsl:if test="not(starts-with(@name, '$'))">
				<xsl:text>$</xsl:text>
			</xsl:if>
			<xsl:value-of select="@name" />
			<xsl:if test="defaultValue/@value">
				<xsl:value-of select="concat('=', defaultValue/@value)"/>
			</xsl:if>
			<xsl:if test="position() &lt; last()">
				<xsl:text>, </xsl:text>
			</xsl:if>
		</xsl:for-each>
		<xsl:text>)</xsl:text>
	</xsl:template>
	
	<!-- Displays a type hint -->
	<!-- (still no type hint in PHP, except userland classes and Array) -->
	<xsl:template name="codeType">
		<xsl:param name="relPathTop" />
		<xsl:param name="context" />
		<xsl:param name="includeDatatypes" select="boolean(0)"/>

		<xsl:variable name="idref" select="$context/type/@xmi:idref | $context/@type"/>
		<xsl:if test="$idref!=''">
			<xsl:variable name="typeElement" select="key('getElementById', $idref)"/>
			<xsl:if test="$includeDatatypes or ($typeElement/@xmi:type!='uml:DataType' or translate($typeElement/@name,'A','a')='array')">
				<xsl:value-of select="concat($typeElement/@name,' ')"/>
			</xsl:if>
		</xsl:if>
	</xsl:template>

	<!-- Adds a " * " to the breaklines, for a proper display of the comment/docblock -->
	<xsl:template name="nl-docblock-replace">
	  <xsl:param name="str"/>
	  <xsl:param name="tab"/>

	  <xsl:choose>
			<xsl:when test="contains($str, $cr)">
				<xsl:value-of select="substring-before($str, $cr)"/>
				<xsl:text>&#xa;</xsl:text>
				<xsl:value-of select="concat($tab, ' * ')"/>
				<xsl:call-template name="nl-docblock-replace">
					<xsl:with-param name="str" select="substring-after($str, $cr)"/>
					<xsl:with-param name="tab" select="$tab"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$str"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="ownedComment">
		<xsl:param name="relPathTop" />
		<xsl:param name="tab"/>

		<xsl:if test="ownedComment/@body!='' or ownedComment/body/text()!=''">
			<xsl:value-of select="concat($tab, '/**&#xa;')"/>

			<xsl:value-of select="concat($tab, ' * ')"/>
			<xsl:for-each select="ownedComment">
				<xsl:value-of select="@body"/>
				<xsl:call-template name="nl-docblock-replace">
					<xsl:with-param name="str" select="body/text()"/>
					<xsl:with-param name="tab" select="$tab"/>
				</xsl:call-template>
			</xsl:for-each>
			<xsl:value-of select="string('&#xa;')"/>

			<!-- @var -->
			<xsl:if test="local-name()='ownedAttribute'">
				<xsl:value-of select="concat($tab, ' *&#xa;')"/>
				<xsl:value-of select="concat($tab, ' * @var ')"/>
				<xsl:call-template name="codeType">
					<xsl:with-param name="relPathTop" select="$relPathTop"/>
					<xsl:with-param name="context" select="."/>
					<xsl:with-param name="includeDatatypes" select="boolean(1)"/>
				</xsl:call-template>
				<xsl:value-of select="string('&#xa;')"/>
			</xsl:if>

			<!-- @param -->
			<xsl:if test="ownedParameter[@direction!='return' or not(@direction)]">
				<xsl:value-of select="concat($tab, ' *&#xa;')"/>
				<xsl:for-each select="ownedParameter[@direction!='return' or not(@direction)]">
					<xsl:value-of select="concat($tab, ' * @param ')"/>
					<xsl:call-template name="codeType">
						<xsl:with-param name="relPathTop" select="$relPathTop"/>
						<xsl:with-param name="context" select="."/>
						<xsl:with-param name="includeDatatypes" select="boolean(1)"/>
					</xsl:call-template>
					<xsl:if test="not(starts-with(@name, '$'))">
						<xsl:text>$</xsl:text>
					</xsl:if>
					<xsl:value-of select="concat(@name, ' ')" />
					<xsl:for-each select="ownedComment">
						<xsl:value-of select="@body"/>
						<xsl:call-template name="nl-docblock-replace">
							<xsl:with-param name="str" select="body/text()"/>
							<xsl:with-param name="tab" select="$tab"/>
						</xsl:call-template>
					</xsl:for-each>
					<xsl:value-of select="string('&#xa;')"/>
				</xsl:for-each>
			</xsl:if>
			
			<!-- @return -->
			<xsl:if test="ownedParameter[@direction='return']">
				<xsl:value-of select="concat($tab, ' *&#xa;')"/>
				<xsl:for-each select="ownedParameter[@direction='return']">
					<xsl:value-of select="concat($tab, ' * @return ')"/>
					<xsl:call-template name="codeType">
						<xsl:with-param name="relPathTop" select="$relPathTop"/>
						<xsl:with-param name="context" select="."/>
						<xsl:with-param name="includeDatatypes" select="boolean(1)"/>
					</xsl:call-template>
					<xsl:for-each select="ownedComment">
						<xsl:value-of select="@body"/>
						<xsl:call-template name="nl-docblock-replace">
							<xsl:with-param name="str" select="body/text()"/>
							<xsl:with-param name="tab" select="$tab"/>
						</xsl:call-template>
					</xsl:for-each>
					<xsl:value-of select="string('&#xa;')"/>
				</xsl:for-each>
			</xsl:if>

			<xsl:value-of select="concat($tab, ' */&#xa;')"/>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>