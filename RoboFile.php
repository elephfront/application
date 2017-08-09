<?php
use Cake\Event\EventDispatcherTrait;
use Elephfront\RoboCssMinify\Task\Loader\LoadCssMinifyTasksTrait;
use Elephfront\RoboImportJs\Task\Loader\LoadImportJavascriptTasksTrait;
use Elephfront\RoboJsMinify\Task\Loader\LoadJsMinifyTasksTrait;
use Elephfront\RoboLiveReload\Task\Loader\LoadLiveReloadTaskTrait;
use Elephfront\RoboSass\Task\Loader\LoadSassTaskTrait;
use Robo\Tasks;

/**
 * This is the Robo commands file.
 */
class RoboFile extends Tasks
{

    use EventDispatcherTrait {
        dispatchEvent as protected;
        eventManager as protected;
    }
    use LoadCssMinifyTasksTrait;
    use LoadImportJavascriptTasksTrait;
    use LoadJsMinifyTasksTrait;
    use LoadSassTaskTrait;
    use LoadLiveReloadTaskTrait;

    /**
     * Filepath of the user configuration 
     * 
     * @var string
     */
    const ELEPHFRONT_CONFIG = 'elephfront-config.php';

    /**
     * Filepath of the bootstrap (useful to bind events listener for instance)
     * 
     * @var string
     */
    const ELEPHFRONT_BOOTSTRAP = 'elephfront-bootstrap.php';

    /**
     * Configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * RoboFile constructor.
     *
     * Will load the basic configuration and the merge it with the user configuration (if any).
     */
    public function __construct()
    {
        $this->config = $this->loadDefaultConfig();

        if (is_file(self::ELEPHFRONT_BOOTSTRAP)) {
            include self::ELEPHFRONT_BOOTSTRAP;
        }
        
        // We store it in a variable so the included file has access to the existing configuration if needed
        $config = $this->config;

        if (is_file(self::ELEPHFRONT_CONFIG)) {
            $userConfig = include self::ELEPHFRONT_CONFIG;
            $this->config = $this->merge($this->config, $userConfig);
        }
    }

    /**
     * Load the default configuration.
     *
     * @return array
     */
    protected function loadDefaultConfig()
    {
        $source = 'src' . DIRECTORY_SEPARATOR;
        $build = 'build' . DIRECTORY_SEPARATOR;

        return [
            'paths' => [
                'source' => $source,
                'build' => $build
            ],
            'sources' => [
                'css' => $source . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR,
                'js' => $source . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR,
                'directories' => [
                    'pages' => $source . 'pages' . DIRECTORY_SEPARATOR,
                    'system' => $source . 'system' . DIRECTORY_SEPARATOR
                ]
            ],
            'compile' => [
                'css' => [
                    $source . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'main.scss' => $build . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'main.min.css'
                ],
                'js' => [
                    $source . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'main.js' => $build . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'main.min.js'
                ],
                'directories' => [
                    $source . 'pages' . DIRECTORY_SEPARATOR => $build . 'pages' . DIRECTORY_SEPARATOR,
                    $source . 'system' . DIRECTORY_SEPARATOR => $build . 'system' . DIRECTORY_SEPARATOR
                ]
            ],
            'router' => $build . 'system' . DIRECTORY_SEPARATOR . 'router.php'
        ];
    }

    /**
     * Serve task
     * - will build the entire project
     * - will start the PHP Built-in server to serve the pages
     * - will open the browser with the location of the locale PHP built-in server
     * - will start the WebSocket server to enable the live-reload
     * - will start watching for changes in the source CSS, JS and pages files
     *
     * @param array $opts Array of options. Two options are supported :
     * - *host* : the name of the host the server should respond to
     * - *port* : the port binded to the host
     *
     * @return void
     */
    public function serve($opts = ['host' => 'localhost', 'port' => 9876])
    {
        $this->build();
        $this->taskLiveReload()->run();
        $this->startServer($opts['host'], $opts['port']);
        $this->startBrowser($opts['host'], $opts['port']);

        $this->startWatch();
    }

    /**
     * Start the built-in PHP Server in the background.
     * Once started, the URL "http://$host:$port" will respond to the server.
     *
     * @param string $host The name of the host the server should respond to
     * @param int $port The port binded to the host
     * @return void
     */
    protected function startServer($host = 'localhost', $port = 9876)
    {
        $this->taskServer($port)
            ->background()
            ->host($host)
            ->arg($this->config['router'])
            ->dir($this->config['paths']['build'])
            ->run();
    }

    /**
     * Opens the browser at the host and port location for the `startServer()` task.
     *
     * @param string $host The name of the host the server should respond to
     * @param int $port The port binded to the host
     * @return void
     */
    protected function startBrowser($host = 'localhost', $port = 9876)
    {
        $url = sprintf('http://%s:%d', $host, $port);

        $this->taskOpenBrowser($url)
            ->run();
    }

    /**
     * Starts the watch for the CSS and JS folders. Any changes in those folders will trigger the assets compilation
     * tasks, as well as send a message to the WebSocket server.
     *
     * @return void
     */
    protected function startWatch()
    {
        $this->taskWatch()
            ->monitor($this->config['sources']['css'], function() {
                $this->compileScss(true);
                $this->taskLiveReload()->sendReloadMessage();
            })
            ->monitor($this->config['sources']['js'], function() {
                $this->compileJs();
                $this->taskLiveReload()->sendReloadMessage();
            })
            ->monitor($this->config['sources']['directories'], function() {
                $this->copyDirectories();
                $this->taskLiveReload()->sendReloadMessage();
            })
            ->run();
    }

    /**
     * Build the entire `build` directory from the `src` directory
     * 
     * @return void
     */
    public function build()
    {
        if (is_dir($this->config['paths']['build'])) {
            $this->taskCleanDir([$this->config['paths']['build']])->run();
        }
        
        $this->copyDirectories();
        $this->compileAssets();
    }

    /**
     * Copy the pages sub-directory to the build directory
     *
     * @return void
     */
    public function copyDirectories()
    {
        $directoriesSourceMap = $this->config['compile']['directories'];
        foreach ($directoriesSourceMap as $source => $build) {
            $this->prepareBuildDir(dirname($build));
        }

        $this
            ->taskCopyDir($directoriesSourceMap)
            ->run();
    }

    /**
     * Compile all of the assets source to a minified build.
     *
     * Will execute the following commands :
     *
     * - compileCss
     * - compileJs
     *
     * @return void
     */
    public function compileAssets()
    {
        $this->compileScss();
        $this->compileJs();
    }

    /**
     * Executes the compilation of CSS assets
     *
     * @param bool $disableEvents Whether events should be dispatched or not. Default to false.
     * @return void
     */
    public function compileScss($disableEvents = false)
    {
        $cssSourceMap = $this->config['compile']['css'];

        if ($disableEvents === false) {
            $event = new \Cake\Event\Event('Elephfront.Scss.beforeCompile', $this, [
                'sourceMap' => $cssSourceMap,
                'config' => $this->config
            ]);
            $this->eventManager()->dispatch($event);
        }

        $collection = $this->collectionBuilder();
        $collection
            ->taskSass()
            ->setDestinationsMap($cssSourceMap)
            ->taskCssMinify()
            ->run();

        if ($disableEvents === false) {
            $event = new \Cake\Event\Event('Elephfront.Scss.afterCompile', $this, [
                'sourceMap' => $cssSourceMap,
                'config' => $this->config
            ]);
            $this->eventManager()->dispatch($event);
        }
    }

    /**
     * Executes the compilation of JS assets
     *
     * @return void
     */
    public function compileJs()
    {
        $jsSourceMap = $this->config['compile']['js'];

        $collection = $this->collectionBuilder();
        $collection
            ->taskImportJavascript()
                ->setDestinationsMap($jsSourceMap)
                ->disableWriteFile()
            ->taskJsMinify()
            ->run();
    }

    /**
     * Will prepare the build directory. If no target is specified, the entire build directory will be wiped.
     * If the build preparation should be done for a specific target (e.g. only for building CSS files), the parameter
     * target should be set as a string, being the name of the target (e.g. 'css').
     *
     * @param bool|string $target False if no specific target, string of the build target.
     * @return void
     */
    protected function prepareBuildDir($target = false)
    {
        if (is_dir($target)) {
            return;
        }

        mkdir($target, 0755, true);
    }

    /**
     * This function can be thought of as a hybrid between PHP's `array_merge` and `array_merge_recursive`.
     *
     * The difference between this method and the built-in ones, is that if an array key contains another array, then
     * Hash::merge() will behave in a recursive fashion (unlike `array_merge`). But it will not act recursively for
     * keys that contain scalar values (unlike `array_merge_recursive`).
     *
     * Note: This function will work with an unlimited amount of arguments and typecasts non-array parameters into arrays.
     *
     * @param array $data Array to be merged
     * @param mixed $merge Array to merge with. The argument and all trailing arguments will be array cast when merged
     * @return array Merged array
     * 
     * Taken from the CakePHP framework
     *
     * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
     * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
     *
     * Licensed under The MIT License
     * For full copyright and license information, please see the LICENSE.txt
     * Redistributions of files must retain the above copyright notice.
     *
     * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
     * @since         2.2.0
     * @license       http://www.opensource.org/licenses/mit-license.php MIT License
     */
    protected function merge(array $data, $merge)
    {
        $args = array_slice(func_get_args(), 1);
        $return = $data;

        foreach ($args as &$curArg) {
            $stack[] = [(array)$curArg, &$return];
        }
        unset($curArg);
        $this->_merge($stack, $return);

        return $return;
    }

    /**
     * Merge helper function to reduce duplicated code between merge() and expand().
     *
     * @param array $stack The stack of operations to work with.
     * @param array $return The return value to operate on.
     * @return void
     *
     * Taken from the CakePHP framework
     *
     * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
     * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
     *
     * Licensed under The MIT License
     * For full copyright and license information, please see the LICENSE.txt
     * Redistributions of files must retain the above copyright notice.
     *
     * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
     * @since         2.2.0
     * @license       http://www.opensource.org/licenses/mit-license.php MIT License
     */
    protected function _merge($stack, &$return)
    {
        while (!empty($stack)) {
            foreach ($stack as $curKey => &$curMerge) {
                foreach ($curMerge[0] as $key => &$val) {
                    $isArray = is_array($curMerge[1]);
                    if ($isArray && !empty($curMerge[1][$key]) && (array)$curMerge[1][$key] === $curMerge[1][$key] && (array)$val === $val) {
                        // Recurse into the current merge data as it is an array.
                        $stack[] = [&$val, &$curMerge[1][$key]];
                    } elseif ((int)$key === $key && $isArray && isset($curMerge[1][$key])) {
                        $curMerge[1][] = $val;
                    } else {
                        $curMerge[1][$key] = $val;
                    }
                }
                unset($stack[$curKey]);
            }
            unset($curMerge);
        }
    }
}
