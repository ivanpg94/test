
PROCESO DE INSTALACION:
Instalamos con composer la última versión estable de drupal. Antes de comenzar la instalación
iremos a la carpeta web/profiles, copiamos y descomprimimos el archivo aaiicc.zip.

Luego iremos a la raiz del proyecto e instalaremos el siguiente paquete con composer
composer require wikimedia/composer-merge-plugin
Al final del composer.json del proyecto, añadimos el siguiente script al mismo nivel que “extra”
"merge-plugin": {
            "require": [
                "web/profiles/aaiicc_base/composer.json"
            ]
        }
Este paquete nos permitira unir ambos composer.json

Ejecutamos un composer install en la raíz del proyecto
Abrimos la web en el navegador y continuaremos desde el navegador el proceso de instalación

composer create-project --repository='{"type": "vcs", "url": "https://github.com/ivanpg94/test"}' ivanpg94/test src
