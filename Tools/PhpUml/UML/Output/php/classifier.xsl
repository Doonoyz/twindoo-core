<?xml version="1.0" encoding="iso-8859-1"?>

<xsl:stylesheet version="1.0" exclude-result-prefixes="uml xmi exslt exslt-set exslt-functions" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
	xmlns:xmi="http://schema.omg.org/spec/XMI/2.1"
	xmlns:exslt="http://exslt.org/common" xmlns:exslt-set="http://exslt.org/sets"
	xmlns:exslt-functions="http://exslt.org/functions">

	<!-- Called on every class/interface. One file per class/interface -->
	<xsl:template name="classifier">
		<xsl:param name="relPathTop"/>
		<xsl:param name="entity"/>
		<xsl:param name="nestingPackageName"/>
		<xsl:param name="ownedAttributeSet"/>
		<xsl:param name="ownedOperationSet" />
		<xsl:param name="generalization"/>
		<xsl:param name="implements"/>
		<xsl:param name="prevEntity"/>
		<xsl:param name="nextEntity"/>
		<xsl:param name="filePrefix"/>
		<xsl:param name="relPathClass"/>


		<!-- Header and namespace declaration -->
		<xsl:call-template name="codeHeader">
			<xsl:with-param name="path" select="$relPathTop"/>
			<xsl:with-param name="title" select="$nestingPackageName"/>
		</xsl:call-template>

		<xsl:if test="$nestingPackageName!=''">
			<xsl:text>namespace </xsl:text>
			<xsl:value-of select="$nestingPackageName"/>
			<xsl:text>;&#xa;</xsl:text>
		</xsl:if>

		<xsl:text>&#xa;</xsl:text>

		<xsl:call-template name="ownedComment">
			<xsl:with-param name="relPathTop" select="$relPathTop"/>
			<xsl:with-param name="tab" select="string('')"/>
		</xsl:call-template>

		<!-- Class declaration -->
		<xsl:if test="@isAbstract='true' and $entity!='Interface'">
			<xsl:text>abstract </xsl:text>
		</xsl:if>
		<xsl:call-template name="toLowerCase">
			<xsl:with-param name="str" select="$entity"/>
		</xsl:call-template>
		<xsl:value-of select="concat(' ', @name)" />

		<xsl:if test="count($generalization) &gt; 0">
			<xsl:text> extends </xsl:text><xsl:value-of select="$generalization/@name" />
		</xsl:if>
		
		<xsl:if test="count($implements) &gt; 0">
			<xsl:text> implements </xsl:text>
			<xsl:for-each	select="$implements">
				<xsl:value-of select="@name" />
				<xsl:if test="position() &lt; last()">, </xsl:if>
			</xsl:for-each>
		</xsl:if>
		<xsl:text>&#xa;{&#xa;</xsl:text>


		<!-- Properties -->
		<xsl:for-each select="$ownedAttributeSet">
			<xsl:call-template name="class-property">
				<xsl:with-param name="relPathTop" select="$relPathTop"/>
			</xsl:call-template>
		</xsl:for-each>
	
		<!-- Functions -->
		<xsl:for-each select="$ownedOperationSet">
			<xsl:call-template name="class-method">
				<xsl:with-param name="relPathTop" select="$relPathTop"/>
			</xsl:call-template>
		</xsl:for-each>
		
		<xsl:text>}&#xa;</xsl:text>

		<!-- Footer -->
		<xsl:call-template name="codeFooter" />
	</xsl:template>

	
	<!-- Properties template -->
	<xsl:template name="class-property">
		<xsl:param name="relPathTop"/>

		<xsl:call-template name="ownedComment">
			<xsl:with-param name="relPathTop" select="$relPathTop"/>
			<xsl:with-param name="tab" select="string('&#x9;')"/>
		</xsl:call-template>

		<xsl:text>&#x9;</xsl:text>

		<xsl:choose>
			<xsl:when test="@isReadOnly='true'"><xsl:text>const </xsl:text>
				<xsl:call-template name="toUpperCase">
					<xsl:with-param name="str" select="@name"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="@visibility='private'">private </xsl:when>
					<xsl:when test="@visibility='protected'">protected </xsl:when>
					<xsl:otherwise>public </xsl:otherwise>
				</xsl:choose>
				<xsl:if test="@isStatic='true'">static </xsl:if>
				<xsl:if test="not(starts-with(@name, '$'))">
					<xsl:text>$</xsl:text>
				</xsl:if>
				<xsl:value-of select="@name" />
			</xsl:otherwise>
		</xsl:choose>
		<!-- Type hint: xsl:call-template name="codeType">
			<xsl:with-param name="relPathTop" select="$relPathTop"/>
			<xsl:with-param name="context" select="."/>
		</xsl:call-template-->
		<xsl:if test="defaultValue/@value!=''">
			<xsl:value-of select="concat('=', defaultValue/@value)"/>
		</xsl:if>
		<xsl:text>;&#xa;&#xa;</xsl:text>
	</xsl:template>


	<!-- Function template -->
	<xsl:template name="class-method">
		<xsl:param name="relPathTop"/>

		<xsl:call-template name="ownedComment">
			<xsl:with-param name="relPathTop" select="$relPathTop"/>
			<xsl:with-param name="tab" select="string('&#x9;')"/>
		</xsl:call-template>

		<xsl:text>&#x9;</xsl:text>

		<xsl:choose>
			<xsl:when test="@visibility='private'">private </xsl:when>
			<xsl:when test="@visibility='protected'">protected </xsl:when>
		</xsl:choose>
		<xsl:if test="@isAbstract='true'">
			<xsl:text>abstract </xsl:text> 
		</xsl:if>
		<xsl:if test="@isStatic='true'">
			<xsl:text>static </xsl:text>
		</xsl:if>
		<xsl:value-of select="concat('function ', @name)"/>

		<xsl:call-template name="codeParametersBracket">
			<xsl:with-param name="relPathTop" select="$relPathTop"/>
		</xsl:call-template>

		<xsl:text>&#xa;	{&#xa;		&#xa;	}&#xa;&#xa;</xsl:text>
			
	</xsl:template>
	
</xsl:stylesheet>
