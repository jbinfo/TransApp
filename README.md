TransApp
=======
Description
-----------

TransApp is an application based on the Silex micro framework designed to facilitate the integration of websites in Symfony 2.

Front developers can integrate directly PSD templates in Twig format without the need to use the Symfony 2 framework.

TransApp can also be used for static websites that requires multilingual

Requirements
------------

TransApp is only supported on PHP 5.3.3 and up.

How I can use TransApp ?
------------------------

The front developper can only work on the app/resources folder:

+ config folder: is for configuration per environment(dev, prod)
+ translations folder: is for translations catalogues
+ views folder: is for TWIG views

The must important here is the config folder, config files is on JSON format, reason to choose the JSON is that all front developers already handle this format, so the structure is as follows:
```json
{
    "config": {
        "twig": {
            "vars": {
                "app_name": "TransApp"
            }
        },
        "routing": {
            "locales": ["en", "fr"],
            "default_locale": "en",
            "routes": {
                "transapp_homepage": {
                    "paths": {
                        "transapp_homepage_base": "/", 
                        "transapp_homepage_index": "/index.html"
                    }, 
                    "template": "main/index.html.twig"
                }
            }
        }
    }
}
```

Under the `vars` key you can put all the variables you want in TWIG tamplates and you can access to him like that: `app.twig.twigvars.app_name`;

`locales` key is to set the locales that your application support(en, fr, es, ar, ...);

`default_locale` set the default locale of the app, when the locale is messing in the URL this is the locale that be using by the app;





This documentation is in progress ....
