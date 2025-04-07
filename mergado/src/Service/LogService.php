<?php declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   license.txt
 */


namespace Mergado\Service;

use Configuration;
use Mergado;
use Mergado\Helper\DebugHelper;
use Mergado\Traits\SingletonTrait;
use Tools;

class LogService
{
    use SingletonTrait;

    public const LOG_TOKEN_NAME = 'MERGADO_LOG_TOKEN';

    public function info(string $message): void
    {
        $date = date('d-m-Y H:i:s');

        $finalMessage = '[INFO] - ' . $date .  ' - ' . $message . PHP_EOL ;

        $this->log($finalMessage);
    }

    public function error(string $message, $params = null): void
    {
        $date = date('d-m-Y H:i:s');

        $finalMessage = '[ERROR] - ' . $date .  ' - ' . $message . PHP_EOL ;

        if (isset($params['exception'])) {
            $exception = $params['exception'];

            if ($message !== $exception->getMessage()) {
                $finalMessage .= 'MESSAGE: ' . $exception->getMessage() . PHP_EOL;
            }
            $finalMessage .= 'FILE: ' . $exception->getFile() . PHP_EOL;
            $finalMessage .= 'LINE: ' . $exception->getLine() . PHP_EOL;
            $trace = $exception->getTrace();
        } else {
            $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4);
        }

        $sanitizedTrace = $this->sanitizeTrace($trace);

        // Debug for local installations
//        DebugHelper::dd(['message' => $finalMessage, 'trace' => $sanitizedTrace]);

        $finalMessage .= 'TRACE: ' . json_encode($sanitizedTrace, JSON_PRETTY_PRINT) . PHP_EOL;

        $this->log($finalMessage);
    }

    public function getLogDir()
    {
        $token = $this->getLogToken();
        $folder = _PS_MODULE_DIR_ . Mergado::MERGADO['MODULE_NAME'] . '/log/';
        $file = 'log_' . $token . '.txt';

        if (!$token) {
            $file = 'log.txt';
        }

        if (file_exists($folder) && file_exists($folder . $file)) {
            return $folder . $file;
        }

        return false;
    }

    private function log($message): void
    {
        $token = $this->getLogToken();

        $folder = __MERGADO_DIR__ . '/log/';
        $file = 'log_' . $token . '.txt';

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        if (file_exists($folder . $file)) {
            if (filesize($folder . $file) > 2000 * 1024) {
                $content = file($folder . $file);
                $countLines = count($content);

                $content = array_slice($content, (int) round($countLines / 2, 0));

                $f = fopen($folder . $file, 'w');
                fwrite($f, implode('', $content));
                fclose($f);
            }
        }

        $f = fopen($folder . $file, "a");
        fwrite($f, $message);
        fclose($f);
    }

    private function getLogToken()
    {
        $logToken = Configuration::get(self::LOG_TOKEN_NAME);

        if (!$logToken) {
            $this->setLogToken();
        }

        return Configuration::get(self::LOG_TOKEN_NAME);
    }

    private function setLogToken()
    {
        $logToken = Tools::getAdminTokenLite('AdminMergadoLog');

        Configuration::updateValue(self::LOG_TOKEN_NAME, $logToken);

        return $logToken;
    }

    /**
     * Sanitize trace by removing circular references
    **/
    private function sanitizeTrace(array $trace): array
    {
        return array_map(function ($entry) {
            if (isset($entry['args'])) {
                // Avoid potential circular references in args
                $entry['args'] = array_map(function ($arg) {
                    return is_object($arg) ? get_class($arg) : (is_array($arg) ? '[array]' : $arg);
                }, $entry['args']);
            }
            return $entry;
        }, $trace);
    }
}
