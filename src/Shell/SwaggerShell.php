<?php
declare(strict_types=1);

namespace Alt3\Swagger\Shell;

use Alt3\Swagger\Controller\AppController;
use Alt3\Swagger\Lib\SwaggerTools;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Utility\Hash;

class SwaggerShell extends Shell
{
    public $config;

    /**
     * Define available subcommands, arguments and options.
     *
     * @return \Cake\Console\ConsoleOptionParser
     * @throws \Aura\Intl\Exception
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        $parser = parent::getOptionParser();

        $parser->addSubcommand('makedocs', [
            'description' => __('Crawl-generate fresh swagger file system documents for all entries found in the library.'),
        ])
        ->addArgument('host', [
            'help' => __("Swagger host FQDN (without protocol) as to be inserted into the swagger doc property 'host'"),
            'required' => true,
        ]);

        return $parser;
    }

    /**
     * Generate fresh filesystem documents for all entries found in the library.
     *
     * @param string $host Hostname of system serving swagger documents (without protocol)
     * @return void
     */
    public function makedocs($host)
    {
        // make same configuration as used by the API availble inside the lib
        if (Configure::read('Swagger')) {
            $this->config = Hash::merge(AppController::$defaultConfig, Configure::read('Swagger'));
        }

        $this->out('Crawl-generating swagger documents...');
        SwaggerTools::makeDocs($host);
        $this->out('Command completed successfully.');
    }
}
