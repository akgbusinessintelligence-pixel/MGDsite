<?php
    /**
     * Class Debug
     *
     * package Wojo Framework
     * @author wojoscripts.com
     * @copyright 2022
     * @version $Id: debug.class.php, v1.00 2022-02-20 18:20:24 gewa Exp $
     */
    
    if (!defined("_WOJO"))
        die('Direct access to this location is not allowed.');
    
    class Debug
    {
        private static float $_startTime = 0;
        private static float $_endTime = 0;
        private static array $_arrGeneral = array();
        private static array $_arrParams = array();
        private static array $_arrWarnings = array();
        private static array $_arrErrors = array();
        private static array $_arrQueries = array();
        
        
        /**
         * Debug::init()
         *
         * @return false|void
         */
        public static function run()
        {
            if (!DEBUG)
                return false;
            
            self::$_startTime = self::_getFormattedMicrotime();
        }
        
        /**
         * Debug::addMessage()
         *
         * @param string $type
         * @param string $key
         * @param string $val
         * @param string $storeType
         * @return false|void
         */
        public static function addMessage($type = 'params', $key = '', $val = '', $storeType = '')
        {
            if (!DEBUG)
                return false;
            
            if ($storeType == 'session') {
                App::Session()->setKey('debug-' . $type, $key, $val);
            }
            
            switch ($type) {
                case "general" :
                    self::$_arrGeneral[$key][] = $val;
                    break;
                
                case "params" :
                    self::$_arrParams[$key] = $val;
                    break;
                
                case "errors" :
                    self::$_arrErrors[$key][] = $val;
                    break;
                
                case "warnings" :
                    self::$_arrWarnings[$key][] = $val;
                    break;
                
                case "queries" :
                    self::$_arrQueries[$key][] = $val;
                    break;
            }
            
        }
        
        /**
         * Debug::getMessage()
         *
         * @param string $type
         * @param string $key
         * @return mixed|string
         */
        public static function getMessage($type = 'params', $key = '')
        {
            $output = '';
            
            if ($type == 'errors')
                $output = self::$_arrErrors[$key] ?? '';
            
            return $output;
        }
        
        /**
         * Debug::displayInfo()
         *
         * @return false|void
         */
        public static function displayInfo()
        {
            if (!DEBUG) {
                return false;
            }
            $core = App::Core();
            $session = App::Session();
            
            $nl = "\n";
            if ($session->isExists('debug-warnings')) {
                $warnCount = count($session->get('debug-warnings'));
                $warnings = count(self::$_arrWarnings);
                $twarn = ($warnCount + $warnings);
            } else {
                $twarn = count(self::$_arrWarnings);
            }
            
            if ($session->isExists('debug-errors')) {
                $errcount = count($session->get('debug-errors'));
                $errors = count(self::$_arrErrors);
                $terr = ($errcount + $errors);
            } else {
                $terr = count(self::$_arrErrors);
            }
            echo $nl . '
                <div id="debug-panel">
                   <div class="debug-wrapper">
                      <div id="debug-panel-legend" class="legend">
                         <span>Debug</span>
                         <div class="arrow">
                            <a id="debugArrow" class="debugArrow"><i class="icon small chevron up"></i></a>
                         </div>
                          <a data-id="contentGeneral" class="tab">General</a>
                          <a data-id="contentParams" class="tab">Params (' . count(self::$_arrParams) . ')</a>
                          <a data-id="contentWarnings" class="tab">Warnings (' . $twarn . ')</a>
                          <a data-id="contentErrors" class="tab">Errors (' . $terr . ')</a>
                          <a data-id="contentQueries" class="tab">SQL Queries (' . count(self::$_arrQueries) . ')</a>
                          <a data-width="auto" data-tooltip="Clear Log" class="clear_session"><i class="icon x alt"></i></a>
                      
                      </div>
                      <div id="contentGeneral" class="items">
                         <p>Total execution time: ' . microtime(self::$_endTime - self::$_startTime) . ' sec.</p>
                         <p>Framework ' . $core->wojon . ' v' . $core->wojov . '</p>';
            if (function_exists('getrusage') && ($cusage = getrusage()) != '') {
                echo '<p>Cpu Usage ' . $cusage['ru_stime.tv_usec'] . ' sec.</p>';
            }
            if (function_exists('memory_get_usage') && ($usage = memory_get_usage()) != '') {
                echo '<p>Memory Usage ' . File::getSize($usage) . '</p>';
            }
            if (count(self::$_arrGeneral) > 0) {
                echo '<pre>';
                print_r(self::$_arrGeneral);
                echo '</pre>';
            }
            echo 'POST:
                         <pre style="white-space:pre-wrap">';
            if (isset($_POST)) {
                print_r($_POST);
            }
            echo '
                         </pre>';
            echo 'GET:
                         <pre style="white-space:pre-wrap">';
            if (isset($_GET)) {
                print_r($_GET);
            }
            echo '
                         </pre>
                      </div>
                      <div id="contentParams" class="items">';
            if (count(self::$_arrParams) > 0) {
                echo '<pre>';
                print_r(self::$_arrParams);
                echo '</pre><br>';
            }
            echo '
                      </div>
                      <div id="contentWarnings" class="items">';
            if (count(self::$_arrWarnings) > 0) {
                echo '<pre>';
                print_r(self::$_arrWarnings);
                echo '</pre>';
            }
            if ($session->isExists('debug-warnings')) {
                echo '<pre>';
                print_r($session->get('debug-warnings'));
                echo '</pre>';
            }
            echo '
                      </div>
                      <div id="contentErrors" class="items">';
            if (count(self::$_arrErrors) > 0) {
                echo '<pre>';
                print_r(self::$_arrErrors);
                echo '</pre>';
            }
            if ($session->isExists('debug-errors')) {
                echo '<pre>';
                print_r($session->get('debug-errors'));
                echo '</pre>';
            }
            echo '
                      </div>
                      <div id="contentQueries" class="items">';
            if (count(self::$_arrQueries) > 0) {
                foreach (self::$_arrQueries as $msgKey => $msgVal) {
                    echo $msgKey . '<br>';
                    echo $msgVal[0] . '<br><br>';
                }
            }
            if ($session->isExists('debug-queries')) {
                foreach ($session->get('debug-queries') as $k => $line) {
                    $k++;
                    echo '<pre>';
                    print_r($k . '. ' . $line);
                    echo '</pre>';
                }
            }
            echo '
                      </div>';
            echo '
                   </div>
                </div>
                ';
        }
        
        /**
         * Debug::pre()
         *
         * @param string $data
         * @return void
         */
        public static function pre($data)
        {
            print '<pre>' . print_r($data, true) . '</pre>';
        }
        
        /**
         * Debug::_getFormattedMicrotime()
         *
         * @return false|float
         */
        private static function _getFormattedMicrotime()
        {
            if (!DEBUG)
                return false;
            
            list($usec, $sec) = explode(' ', microtime());
            return ((float)$usec + (float)$sec);
        }
        
    }