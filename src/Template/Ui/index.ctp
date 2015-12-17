<?php

use Cake\View\Helper\HtmlHelper;
use Cake\View\Helper\UrlHelper;

if (empty($userConfig['title'])) {
    $userConfig['title'] = "cakephp-swagger";
}

if (!isset($uiConfig['validator'])) {
    $uiConfig['validator'] = true;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= $userConfig['title'] ?></title>
    <?php

    // favicons
    echo $this->Html->meta([
        'link' => $this->Url->assetUrl('Alt3/Swagger./images/favicon-32x32.png', ['fullBase' => true]),
        'rel' => 'icon',
        'sizes' => '32x32',
        'type' => 'image/png',
    ]);
    echo $this->Html->meta([
        'link' => $this->Url->assetUrl('Alt3/Swagger./images/favicon-16x16.png', ['fullBase' => true]),
        'rel' => 'icon',
        'sizes' => '16x16',
        'type' => 'image/png'
    ]);

    // screen stylesheets
    echo $this->Html->css([
        'Alt3/Swagger.typography.css',
        'Alt3/Swagger.reset.css',
        'Alt3/Swagger.screen.css'
    ], ['media' => 'screen', 'once' => false, 'fullBase' => true]);

    // print stylesheet
    echo $this->Html->css([
        'Alt3/Swagger.reset.css',
        'Alt3/Swagger.print.css',
    ], ['media' => 'print', 'once' => false, 'fullBase' => true]);

    // javascript libraries
    echo $this->Html->script([
        'Alt3/Swagger./lib/jquery-1.8.0.min.js',
        'Alt3/Swagger./lib/jquery.slideto.min.js',
        'Alt3/Swagger./lib/jquery.wiggle.min.js',
        'Alt3/Swagger./lib/jquery.ba-bbq.min.js',
        'Alt3/Swagger./lib/handlebars-2.0.0.js',
        'Alt3/Swagger./lib/underscore-min.js',
        'Alt3/Swagger./lib/backbone-min.js',
        'Alt3/Swagger./swagger-ui.js',
        'Alt3/Swagger./lib/highlight.7.3.pack.js',
        'Alt3/Swagger./lib/marked.js',
        'Alt3/Swagger./lib/swagger-oauth.js'
    ], ['fullBase' => true]);

    ?>

    <!-- Some basic translations -->
    <!-- <script src='lang/translator.js' type='text/javascript'></script> -->
    <!-- <script src='lang/ru.js' type='text/javascript'></script> -->
    <!-- <script src='lang/en.js' type='text/javascript'></script> -->

    <script type="text/javascript">
        $(function () {
            var url = window.location.search.match(/url=([^&]+)/);
            if (url && url.length > 1) {
                url = decodeURIComponent(url[1]);
            } else {
                url = "<?= $url ?>";
            }

            // Pre load translate...
            if(window.SwaggerTranslator) {
                window.SwaggerTranslator.translate();
            }
            window.swaggerUi = new SwaggerUi({
                url: url,
                <?php if ($uiConfig['validator'] === false) : ?>
                    validatorUrl: false,
                <?php endif ?>
                dom_id: "swagger-ui-container",
                supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
                onComplete: function(swaggerApi, swaggerUi){
                    if(typeof initOAuth == "function") {
                        initOAuth({
                            clientId: "your-client-id",
                            clientSecret: "your-client-secret-if-required",
                            realm: "your-realms",
                            appName: "your-app-name",
                            scopeSeparator: ","
                        });
                    }

                    if(window.SwaggerTranslator) {
                        window.SwaggerTranslator.translate();
                    }

                    $('pre code').each(function(i, e) {
                        hljs.highlightBlock(e)
                    });

                    addApiKeyAuthorization();
                },
                onFailure: function(data) {
                    log("Unable to Load SwaggerUI");
                },
                docExpansion: "none",
                apisSorter: "alpha",
                defaultModelRendering: 'schema',
                showRequestHeaders: false
            });

            function addApiKeyAuthorization(){
                var key = encodeURIComponent($('#input_apiKey')[0].value);
                if(key && key.trim() != "") {
                    var apiKeyAuth = new SwaggerClient.ApiKeyAuthorization("api_key", key, "query");
                    window.swaggerUi.api.clientAuthorizations.add("api_key", apiKeyAuth);
                    log("added key " + key);
                }
            }

            $('#input_apiKey').change(addApiKeyAuthorization);

            // if you have an apiKey you would like to pre-populate on the page for demonstration purposes...
            /*
             var apiKey = "myApiKeyXXXX123456789";
             $('#input_apiKey').val(apiKey);
             */

            window.swaggerUi.load();

            function log() {
                if ('console' in window) {
                    console.log.apply(console, arguments);
                }
            }
        });
    </script>
</head>

<body class="swagger-section">
<div id='header'>
    <div class="swagger-ui-wrap">
        <a id="logo" href="http://swagger.io">swagger</a>
        <form id='api_selector'>
            <div class='input'><input placeholder="http://example.com/api" id="input_baseUrl" name="baseUrl" type="text"/></div>
            <div class='input'><input placeholder="api_key" id="input_apiKey" name="apiKey" type="text"/></div>
            <div class='input'><a id="explore" href="#" data-sw-translate>Explore</a></div>
        </form>
    </div>
</div>

<div id="message-bar" class="swagger-ui-wrap" data-sw-translate>&nbsp;</div>
<div id="swagger-ui-container" class="swagger-ui-wrap"></div>
</body>
</html>
