<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:xhtml="http://www.w3.org/1999/xhtml"
  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
  xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <xsl:template match="/">
    <html lang="uk">
      <head>
        <meta charset="UTF-8"/>
        <title>Карта сайту Hydrophob</title>
        <style>
          body { font: 14px/1.5 -apple-system, Inter, Arial, sans-serif; color: #10161c; margin: 0; padding: 32px; background: #f6f7f9; }
          h1 { font-size: 22px; margin: 0 0 4px; }
          p.meta { color: #6b7280; margin: 0 0 24px; }
          table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(16,22,28,.08); }
          th { text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; padding: 12px 16px; border-bottom: 1px solid #eceef1; }
          td { padding: 11px 16px; border-bottom: 1px solid #f2f3f5; vertical-align: top; }
          tr:last-child td { border-bottom: 0; }
          a { color: #1a63e8; text-decoration: none; }
          a:hover { text-decoration: underline; }
          .num { color: #9aa1ab; width: 48px; }
          .alt { color: #6b7280; font-size: 12px; }
          .media { margin: 8px 0 2px; padding-left: 2px; border-left: 2px solid #eceef1; }
          .media__row { display: flex; align-items: baseline; gap: 8px; padding: 3px 0 3px 10px; font-size: 12px; }
          .media__row a { word-break: break-all; }
          .tag { flex: 0 0 auto; font-size: 9px; font-weight: 700; letter-spacing: .04em; padding: 2px 6px; border-radius: 4px; }
          .tag--img { background: #e8f0fe; color: #1a63e8; }
          .tag--vid { background: #fde8ec; color: #d6455f; }
          .tag--thumb { background: #f1f3f5; color: #6b7280; }
          .topbar { margin: 0 0 18px; }
          .xmlbtn { font: inherit; font-size: 13px; padding: 8px 14px; border: 1px solid #d5d9de; border-radius: 8px; background: #fff; color: #10161c; cursor: pointer; }
          .xmlbtn:hover { border-color: #1a63e8; color: #1a63e8; }
          .rawxml { background: #10161c; color: #d6e4f5; padding: 18px; border-radius: 12px; font-size: 12px; line-height: 1.55; overflow-x: auto; margin: 0 0 22px; white-space: pre-wrap; word-break: break-all; }
        </style>
      </head>
      <body>
        <div class="topbar">
          <button type="button" id="show-xml" class="xmlbtn">Показати XML (як бачить Googlebot)</button>
        </div>
        <pre id="raw-xml" class="rawxml" style="display:none;"></pre>
        <script>
          document.getElementById('show-xml').addEventListener('click', function () {
            var pre = document.getElementById('raw-xml');
            var btn = this;

            if (pre.style.display !== 'none') {
              pre.style.display = 'none';
              btn.textContent = 'Показати XML (як бачить Googlebot)';
              return;
            }

            if (!pre.textContent) {
              btn.textContent = 'Завантаження…';
              fetch(window.location.href, { headers: { 'Accept': 'application/xml' } })
                .then(function (r) { return r.text(); })
                .then(function (text) {
                  pre.textContent = text;
                  pre.style.display = 'block';
                  btn.textContent = 'Сховати XML';
                })
                .catch(function () { btn.textContent = 'Не вдалося завантажити'; });
            } else {
              pre.style.display = 'block';
              btn.textContent = 'Сховати XML';
            }
          });
        </script>
        <xsl:choose>
          <xsl:when test="s:sitemapindex">
            <h1>Карта сайту — розділи</h1>
            <p class="meta">Файлів: <xsl:value-of select="count(s:sitemapindex/s:sitemap)"/></p>
            <table>
              <tr><th class="num">#</th><th>Адреса розділу</th><th>Оновлено</th></tr>
              <xsl:for-each select="s:sitemapindex/s:sitemap">
                <tr>
                  <td class="num"><xsl:value-of select="position()"/></td>
                  <td><a href="{s:loc}"><xsl:value-of select="s:loc"/></a></td>
                  <td><xsl:value-of select="s:lastmod"/></td>
                </tr>
              </xsl:for-each>
            </table>
          </xsl:when>
          <xsl:otherwise>
            <h1>Карта сайту</h1>
            <p class="meta">
              Сторінок: <xsl:value-of select="count(s:urlset/s:url)"/>
              <xsl:if test="count(s:urlset/s:url/image:image) &gt; 0"> · зображень: <xsl:value-of select="count(s:urlset/s:url/image:image)"/></xsl:if>
              <xsl:if test="count(s:urlset/s:url/video:video) &gt; 0"> · відео: <xsl:value-of select="count(s:urlset/s:url/video:video)"/></xsl:if>
            </p>
            <table>
              <tr><th class="num">#</th><th>Адреса</th><th>Медіа</th><xsl:if test="s:urlset/s:url/xhtml:link"><th>Мовні версії</th></xsl:if><th>Оновлено</th></tr>
              <xsl:for-each select="s:urlset/s:url">
                <tr>
                  <td class="num"><xsl:value-of select="position()"/></td>
                  <td>
                    <a href="{s:loc}"><xsl:value-of select="s:loc"/></a>
                    <xsl:if test="image:image or video:video">
                      <div class="media">
                        <xsl:for-each select="image:image">
                          <div class="media__row">
                            <span class="tag tag--img">IMG</span>
                            <a href="{image:loc}"><xsl:value-of select="image:loc"/></a>
                          </div>
                        </xsl:for-each>
                        <xsl:for-each select="video:video">
                          <div class="media__row">
                            <span class="tag tag--vid">VIDEO</span>
                            <a href="{video:content_loc}"><xsl:value-of select="video:content_loc"/></a>
                            <span class="alt"> — <xsl:value-of select="video:title"/> (<xsl:value-of select="video:duration"/> с)</span>
                          </div>
                          <div class="media__row">
                            <span class="tag tag--thumb">PREVIEW</span>
                            <a href="{video:thumbnail_loc}"><xsl:value-of select="video:thumbnail_loc"/></a>
                          </div>
                        </xsl:for-each>
                      </div>
                    </xsl:if>
                  </td>
                  <td class="alt">
                    <xsl:if test="count(image:image) &gt; 0"><xsl:value-of select="count(image:image)"/> фото<br/></xsl:if>
                    <xsl:if test="count(video:video) &gt; 0"><xsl:value-of select="count(video:video)"/> відео</xsl:if>
                  </td>
                  <xsl:if test="/s:urlset/s:url/xhtml:link">
                    <td class="alt">
                      <xsl:for-each select="xhtml:link">
                        <xsl:value-of select="@hreflang"/><xsl:if test="position() != last()">, </xsl:if>
                      </xsl:for-each>
                    </td>
                  </xsl:if>
                  <td class="alt"><xsl:value-of select="substring(s:lastmod, 1, 10)"/></td>
                </tr>
              </xsl:for-each>
            </table>
          </xsl:otherwise>
        </xsl:choose>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
