<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">
    <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
    <xsl:template match="/">
        <html>
            <head>
                <title>XML Sitemap - Magento Blog – Tutorials, Tips, News &amp; Insights | Meetanshi</title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <style type="text/css">
                    body
                    {
                    margin: 0;
                    padding: 0;
                    font-family: "Segoe UI";
                    }

                    table,tbody
                    {
                    margin: 0;
                    padding: 0;
                    }

                    .xml-sitemap-container, .xml-sitemap-header-container, .flwh
                    {
                    float: left;
                    width: 100%;
                    }

                    .xml-sitemap-container .xml-sitemap-header-container
                    {
                    min-height: 220px;
                    float: left;
                    width: 100%;
                    background-color: #d3e5a0;
                    text-align: center;
                    }

                    .xml-sitemap-container .sitemap-header-title
                    {
                    font-size: 32px;
                    font-weight: bold;
                    font-family: "Segoe UI";
                    text-decoration: underline;
                    text-transform: uppercase;
                    margin-top: 57px;
                    color: #23273f;
                    }

                    .sitemap-header-content-parent
                    {
                    float: left;
                    width: 100%;
                    display: flex;
                    justify-content: center;
                    }

                    .xml-sitemap-container .sitemap-header-content
                    {
                    margin-top: 34px;
                    font-family: Segoe UI semibold;
                    font-size: 16px;
                    color: #23273f;
                    max-width: 1054px;
                    text-align: center;
                    margin-bottom: 44px;
                    }

                    .xml-sitemap-container .sitemap-header-content a
                    {
                    color: black;
                    text-decoration: underline;
                    }

                    .xml-sitemap-container .xml-sitemap-content-container
                    {
                    float: left;
                    width: 100%;
                    text-align: center;
                    }

                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-title
                    {
                    color: #896126;
                    font-size: 18px;
                    margin-top: 54px;
                    font-family: Segoe UI semibold;
                    }

                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-title count
                    {
                    color: black;
                    }

                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table-parent
                    {
                    float: left;
                    width: 100%;
                    display: flex;
                    justify-content: center;
                    }

                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table
                    {
                    overflow-x: auto;
                    max-width: 1730px;
                    margin-top: 38px;
                    }
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table
                    {
                    float: left;
                    width: 100%;
                    }
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table table
                    {
                    float: left;
                    width: 100%;
                    border-collapse: collapse;
                    text-align: left;
                    }

                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table tr th
                    {
                    background-color: #23273f;
                    height: 70px;
                    color: white;
                    }
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table tr td{height: 62px}
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table tr th,
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table tr td
                    {
                    padding-left: 32px;
                    }
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table
                    tr:nth-child(odd){background-color: #f2f2f2}
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table tr
                    th:nth-child(1){width: 70%}
                    .xml-sitemap-container .xml-sitemap-content-container .sitemap-content-table tr
                    th:nth-child(2){width: 30%}
                </style>
            </head>
            <body>
                <div class="xml-sitemap-container">
                    <div class="xml-sitemap-header-container">
                        <div class="sitemap-header-title">
                            XML SITEMAP
                        </div>
                        <div class="sitemap-header-content-parent">
                            <div class="sitemap-header-content">
                                This XML Sitemap is generated by <a>Meetanshi's Magento 2 Google Sitemap Extension</a>.
                                It helps search
                                engines like Google, Bing to crawl and re-crawl categories/products/CMS pages/images of
                                your website.
                                Learn more about <a href="http://sitemaps.org" target="_blank">XML Sitemaps</a>.
                            </div>
                        </div>
                    </div>
                    <div class="xml-sitemap-content-container">
                        <div class="sitemap-content-title">
                            <xsl:if test="sitemap:sitemapindex/sitemap:sitemap">
                                This XML Sitemap Index file Contains
                                <count><xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"/></count>
                                Sitemaps.
                            </xsl:if>
                            <xsl:if test="sitemap:urlset/sitemap:url">
                                This XML Sitemap file Contains
                                <count><xsl:value-of select="count(sitemap:urlset/sitemap:url)"/></count>
                                URLs.
                            </xsl:if>

                        </div>
                        <div class="sitemap-content-table-parent">
                            <div class="sitemap-content-table">
                                <table>
                                    <tr>
                                        <th>Sitemaps</th>
                                        <th>Last Modified</th>
                                    </tr>
                                    <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
                                        <xsl:variable name="sitemapURL">
                                            <xsl:value-of select="sitemap:loc"/>
                                        </xsl:variable>
                                        <tr>
                                            <td>
                                                <a href="{$sitemapURL}">
                                                <xsl:value-of select="sitemap:loc"/>
                                                </a>
                                            </td>
                                            <td>
                                                <xsl:value-of select="sitemap:lastmod"/>
                                            </td>
                                        </tr>
                                    </xsl:for-each>
                                    <xsl:for-each select="sitemap:urlset/sitemap:url">
                                        <xsl:variable name="sitemapURL">
                                            <xsl:value-of select="sitemap:loc"/>
                                        </xsl:variable>
                                        <tr>
                                            <td>
                                                <a href="{$sitemapURL}">
                                                    <xsl:value-of select="sitemap:loc"/>
                                                </a>
                                            </td>
                                            <td>
                                                <xsl:value-of select="sitemap:lastmod"/>
                                            </td>
                                        </tr>
                                    </xsl:for-each>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>