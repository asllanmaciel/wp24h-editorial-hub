=== WP24H Editorial Hub ===
Contributors: asllanmaciel
Tags: editorial, blog, content, courses
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Transforma a página pública de conteúdos em um hub editorial próprio, acessível e responsivo.

== Description ==

WP24H Editorial Hub substitui somente o conteúdo central da página configurada do blog. Ele apresenta um artigo principal, dois destaques secundários, navegação por categorias, pesquisa, feed em duas colunas, paginação e uma chamada final para cursos.

O conteúdo permanece público. O plugin não cria assinaturas, não coleta dados, não envia telemetria e não depende do JetEngine para renderizar a listagem.

== Installation ==

1. Envie e ative o arquivo wp24h-editorial-hub-1.0.4.zip.
2. Por padrão, o hub atua na página de ID 6.
3. Para usar outro ID, defina `WP24H_EDITORIAL_HUB_PAGE_ID` no wp-config.php.
4. Edite um post e use a caixa “Destaque editorial” para selecionar o conteúdo principal.

Na ausência de uma seleção manual, o post publicado mais recente é usado como destaque. Se o hub não conseguir consultar os conteúdos, a saída original da página é preservada.

== Frequently Asked Questions ==

= O plugin bloqueia artigos para assinantes? =

Não. Todo conteúdo permanece livre. A chamada para cursos funciona como continuidade opcional da jornada editorial.

= Preciso remover o JetEngine? =

Não. O plugin deixa de depender do Listing Grid apenas na página configurada e não modifica o JetEngine em outras áreas.

== Changelog ==

= 1.0.4 =
* Remove a altura mínima residual dos cards no celular.

= 1.0.3 =
* Compacta os cards do feed e reduz a proporção dedicada às imagens.
* Torna as categorias coloridas e clicáveis, preservando os filtros do hub.

= 1.0.2 =
* Impede rolagem horizontal no layout em largura total.

= 1.0.1 =
* Remove o cabeçalho legado do Elementor na página do hub.
* Adota cartões editoriais horizontais e layout em largura total.

= 1.0.0 =

* Added first-party editorial hero, secondary features, topic chips, search, two-column feed and pagination.
* Added one exclusive featured-post selector with safe fallback to the latest post.
* Added a responsive, accessible public design and course continuation CTA.
