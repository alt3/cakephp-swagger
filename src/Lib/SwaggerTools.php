<?php
declare(strict_types=1);

namespace Alt3\Swagger\Lib;

use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\Exception\NotFoundException;

class SwaggerTools
{
    /**
     * @var string Prepended to filesystem swagger json files
     */
    protected static $filePrefix = 'cakephp_swagger_';

    /**
     * Returns a single swagger document from filesystem or crawl-generates
     * a fresh one.
     *
     * @param string $id Name of the document
     * @param string $host Hostname of system serving swagger documents (without protocol)
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function getSwaggerDocument($id, $host)
    {
        // load document from filesystem
        $filePath = CACHE . self::$filePrefix . $id . '.json';
        if (!Configure::read('Swagger.docs.crawl')) {
            if (!file_exists($filePath)) {
                throw new NotFoundException("Swagger json document was not found on filesystem: $filePath");
            }
            $fh = new File($filePath);

            return $fh->read();
        }

        // otherwise crawl-generate a fresh document
        $swaggerOptions = [];
        if (Configure::read("Swagger.library.$id.exclude")) {
            $swaggerOptions = [
                'exclude' => Configure::read("Swagger.library.$id.exclude"),
            ];
        }
        if (Configure::read('Swagger.analyser')) {
            $swaggerOptions['analyser'] = Configure::read('Swagger.analyser');
        }
        $swagger = \Swagger\scan(Configure::read("Swagger.library.$id.include"), $swaggerOptions);

        // set object properties required by UI to generate the BASE URL
        $swagger->host = $host;
        if (empty($swagger->basePath)) {
            $swagger->basePath = '/' . Configure::read('App.base');
        }
        $swagger->schemes = Configure::read('Swagger.ui.schemes');

        // write document to filesystem
        self::writeSwaggerDocumentToFile($filePath, $swagger);

        return $swagger;
    }

    /**
     * Write swagger document to filesystem.
     *
     * @param string $path Full path to the json document including filename
     * @param object $content Swagger content
     * @throws \Cake\Http\Exception\InternalErrorException
     * @return bool
     */
    protected static function writeSwaggerDocumentToFile($path, $content)
    {
        // we need to silence the warning so we can satisfy our Test by throwing without a warning interfering
        $fh = @fopen($path, 'w'); // phpcs:ignore
        if (!$fh || !fwrite($fh, $content->__toString())) {
            throw new InternalErrorException('Error writing Swagger json document to filesystem');
        }

        return true;
    }

    /**
     * Convenience function used by the shell to create filesystem documents
     * for all entries found in the library.
     *
     * @param string $host Hostname of system serving swagger documents (without protocol)
     * @throws \InvalidArgumentException
     * @return bool true if successful, false on all errors
     */
    public static function makeDocs($host)
    {
        if (!Configure::read('Swagger.library')) {
            throw new \InvalidArgumentException('Swagger configuration file does not contain a library section');
        }

        // make sure documents will be crawled and not read from filesystem
        Configure::write('Swagger.docs.crawl', true);

        // generate docs
        foreach (array_keys(Configure::read('Swagger.library')) as $doc) {
            self::getSwaggerDocument($doc, $host);
        }

        return true;
    }
}
