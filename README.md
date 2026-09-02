# WP24H Editorial Hub

Plugin WordPress que transforma uma página comum em um hub editorial próprio, responsivo e independente de construtores de listings.

## Recursos

- destaque editorial principal e dois destaques secundários;
- feed horizontal e responsivo de conteúdos recentes;
- busca, filtros por categoria e paginação;
- categorias coloridas e clicáveis;
- tempo estimado de leitura;
- seleção de destaque pelo editor de posts;
- chamada para cursos;
- fallback seguro para o conteúdo original da página.

## Requisitos

- WordPress 6.4 ou superior;
- PHP 8.1 ou superior.

## Instalação

1. Baixe o ZIP da versão desejada em **Releases**.
2. No WordPress, acesse **Plugins → Adicionar plugin → Enviar plugin**.
3. Envie o ZIP, instale e ative o plugin.

Por padrão, o hub substitui o conteúdo da página com ID `6`. Para usar outra página, defina `WP24H_EDITORIAL_HUB_PAGE_ID` antes do carregamento do plugin.

## Desenvolvimento

Execute os testes:

```powershell
php bin/run-tests.php
```

Gere o pacote instalável:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File bin/build-package.ps1
```

O ZIP será criado em `dist/`.

## Licença

GPL-2.0-or-later. Consulte [LICENSE](LICENSE).
