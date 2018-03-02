<?php

namespace {
    define('GRAVITYSCAN_AGENT_VERSION', '1.0.1');
    !defined('GRAVITYSCAN_AGENT_FILE') && define('GRAVITYSCAN_AGENT_FILE', __FILE__);
    !defined('GRAVITYSCAN_AGENT_IS_WRITABLE') && define('GRAVITYSCAN_AGENT_IS_WRITABLE', is_writable(__FILE__));
    define('GRAVITYSCAN_AGENT_FC_PATH', dirname(__FILE__));
    define('GRAVITYSCAN_AGENT_CLASS_PATH', GRAVITYSCAN_AGENT_FC_PATH . '/classes');
}
namespace Gravityscan {
    class AgentException extends \Exception
    {
    }
}
namespace Gravityscan {
    interface LocalStorage
    {
        public function initialize();
        public function has($key);
        public function set($key, $value);
        public function get($key);
        public function delete($key);
        public function destroy();
    }
    class LocalStorageSession implements LocalStorage
    {
        public function initialize()
        {
            if (!(@session_id() || @session_start())) {
                throw new AgentException('Unable to start session for local storage. ' . error_get_last(), 500);
            }
        }
        public function has($key)
        {
            return is_array($_SESSION) && array_key_exists('gravityscan-agent', $_SESSION) && is_array($_SESSION['gravityscan-agent']) && array_key_exists($key, $_SESSION['gravityscan-agent']);
        }
        public function set($key, $value)
        {
            $_SESSION['gravityscan-agent'][$key] = $value;
        }
        public function get($key)
        {
            if ($this->has($key)) {
                return $_SESSION['gravityscan-agent'][$key];
            }
            return null;
        }
        public function delete($key)
        {
            unset($_SESSION['gravityscan-agent'][$key]);
        }
        public function destroy()
        {
            @session_destroy();
        }
    }
}
namespace Gravityscan {
    class Request
    {
        private $body;
        private $headers;
        private $method;
        private $documentRoot;
        /**
         *
         */
        public static function createFromGlobals()
        {
            $request = new static();
            $headers = array();
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $headers[$header] = $value;
                }
            }
            $request->setHeaders($headers)->setBody(file_get_contents('php://input'))->setMethod(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET')->setDocumentRoot(isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : GRAVITYSCAN_AGENT_FC_PATH);
            return $request;
        }
        /**
         * @param mixed $body
         * @return Request
         */
        public function setBody($body)
        {
            $this->body = $body;
            return $this;
        }
        /**
         * @return mixed
         */
        public function getBody()
        {
            return $this->body;
        }
        /**
         * @param mixed $headers
         * @return Request
         */
        public function setHeaders($headers)
        {
            $this->headers = $headers;
            return $this;
        }
        /**
         * @return mixed
         */
        public function getHeaders()
        {
            return $this->headers;
        }
        /**
         * @param $header
         * @return mixed
         */
        public function getHeader($header)
        {
            return array_key_exists($header, $this->headers) ? $this->headers[$header] : null;
        }
        /**
         * @param mixed $method
         * @return Request
         */
        public function setMethod($method)
        {
            $this->method = $method;
            return $this;
        }
        /**
         * @return mixed
         */
        public function getMethod()
        {
            return $this->method;
        }
        /**
         * @param mixed $documentRoot
         * @return Request
         */
        public function setDocumentRoot($documentRoot)
        {
            $this->documentRoot = $documentRoot;
            return $this;
        }
        /**
         * @return mixed
         */
        public function getDocumentRoot()
        {
            return $this->documentRoot;
        }
    }
}
namespace Gravityscan {
    class AgentAuth
    {
        private $signature;
        private $message;
        private $publicKey;
        /**
         * @param string $signature
         * @param string $message
         * @param string $publicKey
         */
        public function __construct($signature = null, $message = null, $publicKey = null)
        {
            $this->signature = $signature;
            $this->message = $message;
            $this->publicKey = $publicKey;
        }
        /**
         * @return bool
         * @throws AgentException
         */
        public function authenticate()
        {
            // Verify signature is correct
            if (!$this->verifySignature()) {
                throw new AgentException('Access denied.', 403);
            }
            // Check that the message has not expired.
            $jsonBody = @json_decode($this->getMessage(), true);
            if (!$jsonBody) {
                throw new AgentException('Error parsing the JSON request body. Error code: ' . json_last_error(), 422);
            }
            if (!isset($jsonBody['issued-at']) || !isset($jsonBody['expires-at'])) {
                throw new AgentException('`issued-at` and `expires-at` timestamps must be supplied in the request body.', 422);
            }
            $now = microtime(true);
            if (!($jsonBody['issued-at'] <= $now && $jsonBody['expires-at'] >= $now)) {
                throw new AgentException('Request has expired. Current server time is ' . $now, 422);
            }
            return true;
        }
        /**
         * @return bool
         */
        public function verifySignature()
        {
            return openssl_verify($this->getMessage(), $this->getSignature(), $this->getPublicKey(), OPENSSL_ALGO_SHA1) === 1;
        }
        /**
         * @param string $signature
         * @return AgentAuth
         */
        public function setSignature($signature)
        {
            $this->signature = $signature;
            return $this;
        }
        /**
         * @return string
         */
        public function getSignature()
        {
            return $this->signature;
        }
        /**
         * @param string $message
         * @return AgentAuth
         */
        public function setMessage($message)
        {
            $this->message = $message;
            return $this;
        }
        /**
         * @return string
         */
        public function getMessage()
        {
            return $this->message;
        }
        /**
         * @param string $publicKey
         * @return AgentAuth
         */
        public function setPublicKey($publicKey)
        {
            $this->publicKey = $publicKey;
            return $this;
        }
        /**
         * @return string
         */
        public function getPublicKey()
        {
            return $this->publicKey;
        }
    }
}
namespace Gravityscan {
    use Error;
    use Exception;
    use RuntimeException;
    use TypeError;
    class Utils
    {
        public static function getRandomString($length = 16, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|')
        {
            // This is faster than calling self::random_int for $length
            $bytes = self::random_bytes($length);
            $return = '';
            $maxIndex = self::strlen($chars) - 1;
            for ($i = 0; $i < $length; $i++) {
                $fp = (double) ord($bytes[$i]) / 255.0;
                // convert to [0,1]
                $index = (int) round($fp * $maxIndex);
                $return .= $chars[$index];
            }
            return $return;
        }
        /**
         * Polyfill for random_bytes.
         *
         * @param int $bytes
         * @return string
         */
        public static function random_bytes($bytes)
        {
            $bytes = (int) $bytes;
            if (function_exists('random_bytes')) {
                try {
                    $rand = random_bytes($bytes);
                    if (is_string($rand) && self::strlen($rand) === $bytes) {
                        return $rand;
                    }
                } catch (Exception $e) {
                    // Fall through
                } catch (TypeError $e) {
                    // Fall through
                } catch (Error $e) {
                    // Fall through
                }
            }
            if (function_exists('mcrypt_create_iv')) {
                $rand = @mcrypt_create_iv($bytes, MCRYPT_DEV_URANDOM);
                if (is_string($rand) && self::strlen($rand) === $bytes) {
                    return $rand;
                }
            }
            if (function_exists('openssl_random_pseudo_bytes')) {
                $rand = @openssl_random_pseudo_bytes($bytes, $strong);
                if (is_string($rand) && self::strlen($rand) === $bytes) {
                    return $rand;
                }
            }
            // Last resort is insecure
            $return = '';
            for ($i = 0; $i < $bytes; $i++) {
                $return .= chr(mt_rand(0, 255));
            }
            return $return;
        }
        /**
         * Polyfill for random_int.
         *
         * @param int $min
         * @param int $max
         * @return int
         */
        public static function random_int($min = 0, $max = 0x7fffffff)
        {
            if (function_exists('random_int')) {
                try {
                    return random_int($min, $max);
                } catch (Exception $e) {
                    // Fall through
                } catch (TypeError $e) {
                    // Fall through
                } catch (Error $e) {
                    // Fall through
                }
            }
            $diff = $max - $min;
            $bytes = self::random_bytes(4);
            if ($bytes === false || self::strlen($bytes) != 4) {
                throw new RuntimeException("Unable to get 4 bytes");
            }
            $val = unpack("Nint", $bytes);
            $val = $val['int'] & 0x7fffffff;
            $fp = (double) $val / 2147483647.0;
            // convert to [0,1]
            return (int) (round($fp * $diff) + $min);
        }
        /**
         * Set the mbstring internal encoding to a binary safe encoding when func_overload
         * is enabled.
         *
         * When mbstring.func_overload is in use for multi-byte encodings, the results from
         * strlen() and similar functions respect the utf8 characters, causing binary data
         * to return incorrect lengths.
         *
         * This function overrides the mbstring encoding to a binary-safe encoding, and
         * resets it to the users expected encoding afterwards through the
         * `reset_mbstring_encoding` function.
         *
         * It is safe to recursively call this function, however each
         * `mbstring_binary_safe_encoding()` call must be followed up with an equal number
         * of `reset_mbstring_encoding()` calls.
         *
         * @see wfWAFUtils::reset_mbstring_encoding
         *
         * @staticvar array $encodings
         * @staticvar bool  $overloaded
         *
         * @param bool $reset Optional. Whether to reset the encoding back to a previously-set encoding.
         *                    Default false.
         */
        public static function mbstring_binary_safe_encoding($reset = false)
        {
            static $encodings = array();
            static $overloaded = null;
            if (is_null($overloaded)) {
                $overloaded = function_exists('mb_internal_encoding') && ini_get('mbstring.func_overload') & 2;
            }
            if (false === $overloaded) {
                return;
            }
            if (!$reset) {
                $encoding = mb_internal_encoding();
                array_push($encodings, $encoding);
                mb_internal_encoding('ISO-8859-1');
            }
            if ($reset && $encodings) {
                $encoding = array_pop($encodings);
                mb_internal_encoding($encoding);
            }
        }
        /**
         * Reset the mbstring internal encoding to a users previously set encoding.
         *
         * @see wfWAFUtils::mbstring_binary_safe_encoding
         */
        public static function reset_mbstring_encoding()
        {
            self::mbstring_binary_safe_encoding(true);
        }
        /**
         * @param callable $function
         * @param array $args
         * @return mixed
         */
        protected static function callMBSafeStrFunction($function, $args)
        {
            self::mbstring_binary_safe_encoding();
            $return = call_user_func_array($function, $args);
            self::reset_mbstring_encoding();
            return $return;
        }
        /**
         * Multibyte safe strlen.
         *
         * @param $binary
         * @return int
         */
        public static function strlen($binary)
        {
            $args = func_get_args();
            return self::callMBSafeStrFunction('strlen', $args);
        }
        /**
         * @param $haystack
         * @param $needle
         * @param int $offset
         * @return int
         */
        public static function stripos($haystack, $needle, $offset = 0)
        {
            $args = func_get_args();
            return self::callMBSafeStrFunction('stripos', $args);
        }
        /**
         * @param $string
         * @return mixed
         */
        public static function strtolower($string)
        {
            $args = func_get_args();
            return self::callMBSafeStrFunction('strtolower', $args);
        }
        /**
         * @param $string
         * @param $start
         * @param $length
         * @return mixed
         */
        public static function substr($string, $start, $length = null)
        {
            $args = func_get_args();
            return self::callMBSafeStrFunction('substr', $args);
        }
        /**
         * @param $haystack
         * @param $needle
         * @param int $offset
         * @return mixed
         */
        public static function strpos($haystack, $needle, $offset = 0)
        {
            $args = func_get_args();
            return self::callMBSafeStrFunction('strpos', $args);
        }
        /**
         * @param string $haystack
         * @param string $needle
         * @param int $offset
         * @param int $length
         * @return mixed
         */
        public static function substr_count($haystack, $needle, $offset = 0, $length = null)
        {
            $haystack = self::substr($haystack, $offset, $length);
            return self::callMBSafeStrFunction('substr_count', array($haystack, $needle));
        }
        /**
         * @param $string
         * @return mixed
         */
        public static function strtoupper($string)
        {
            $args = func_get_args();
            return self::callMBSafeStrFunction('strtoupper', $args);
        }
        /**
         * @param string $haystack
         * @param string $needle
         * @param int $offset
         * @return mixed
         */
        public static function strrpos($haystack, $needle, $offset = 0)
        {
            $args = func_get_args();
            return self::callMBSafeStrFunction('strrpos', $args);
        }
    }
}
namespace Gravityscan\Commands {
    class Info extends BaseCommand
    {
        public function execute()
        {
            $this->getResponse()->setHeader('Content-Type', 'text/plain')->sendHeaders();
            ob_start('strip_tags');
            phpinfo();
        }
    }
}
namespace Gravityscan\Commands {
    use Gravityscan\AgentException;
    class LocalStorage extends BaseCommand
    {
        public function execute()
        {
            $action = $this->getArg('action');
            if (!is_string($action)) {
                throw new AgentException('Invalid [action] argument supplied', 422);
            }
            $key = $this->getArg('key');
            $value = $this->getArg('value', null);
            $storage = $this->getAgent()->getLocalStorage();
            switch ($action) {
                case 'set':
                case 'get':
                case 'delete':
                    if (!is_string($key)) {
                        throw new AgentException('Invalid [key] argument supplied', 422);
                    }
                    break;
            }
            switch ($action) {
                case 'set':
                    $storage->set($key, $value);
                    break;
                case 'get':
                    if ($storage->has($key)) {
                        $this->getResponse()->setJson(array('value' => $storage->get($key)))->send();
                    }
                    throw new AgentException('LocalStorage [' . $key . '] not found.', 422);
                case 'delete':
                    $storage->delete($key);
                    break;
                case 'destroy':
                    $storage->destroy();
                    break;
                default:
                    throw new AgentException('Invalid [action] argument supplied', 422);
            }
            $this->getResponse()->setJson(array('success' => true))->send();
        }
    }
}
namespace Gravityscan\Commands {
    class Version extends BaseCommand
    {
        public function execute()
        {
            $this->getResponse()->setJson(array('version' => GRAVITYSCAN_AGENT_VERSION))->send();
        }
    }
}
namespace Gravityscan\Commands {
    use Gravityscan\AgentException;
    use Gravityscan\Utils;
    class MalwareScan extends BaseCommand
    {
        public function getPipedArguments()
        {
            return array('fileList');
        }
        public function execute()
        {
            $regexList = $this->getArg('regexList');
            if (!is_array($regexList)) {
                throw new AgentException('Invalid [regexList] argument supplied', 422);
            }
            $fileList = $this->getArg('fileList');
            if (!is_array($regexList)) {
                throw new AgentException('Invalid [fileList] argument supplied', 422);
            }
            $matchingPattern = $this->getArg('matchingPattern');
            $readLength = $this->getArg('readLength');
            $this->startCommandStream();
            foreach ($fileList as $file) {
                try {
                    $filePath = $this->validateFile($file);
                } catch (AgentException $e) {
                    continue;
                }
                if (!$matchingPattern || preg_match($matchingPattern, $filePath)) {
                    $result = $this->scanFileForMalware($filePath, $regexList, $readLength);
                    if ($result) {
                        list($key, $matchString, $beforeString, $afterString, $hash) = $result;
                        $this->writeCommandStream(json_encode(array($file, $key, base64_encode($matchString), base64_encode($beforeString), base64_encode($afterString), base64_encode($hash))));
                    }
                }
            }
            $this->endCommandStream();
        }
        /**
         * @param string $file
         * @param array $signatures
         * @param null $readLength
         * @return array|bool
         */
        protected function scanFileForMalware($file, $signatures, $readLength = null)
        {
            $fh = @fopen($file, 'r');
            if (!$fh) {
                return false;
            }
            $totalRead = 0;
            $maxReadLength = is_int($readLength) && $readLength > 0 ? $readLength : 50 * 1024 * 1024;
            $match = false;
            while (!feof($fh)) {
                $length = min(1 * 1024 * 1024, $maxReadLength);
                $data = fread($fh, $length);
                // read 1 megs max per chunk
                $bytesRead = Utils::strlen($data);
                $totalRead += $bytesRead;
                $maxReadLength -= $bytesRead;
                if ($totalRead < 1) {
                    break;
                }
                foreach ($signatures as $rule) {
                    list($key, , $signature, , $type) = $rule;
                    if ($type === 'browser') {
                        continue;
                    }
                    if (preg_match('/(' . $signature . ')/i', $data, $matches, PREG_OFFSET_CAPTURE)) {
                        $matchString = $matches[1][0];
                        $matchOffset = $matches[1][1];
                        $beforeString = Utils::substr($data, max(0, $matchOffset - 100), $matchOffset - max(0, $matchOffset - 100));
                        $afterString = Utils::substr($data, $matchOffset + Utils::strlen($matchString), 100);
                        $match = array($key, $matchString, $beforeString, $afterString);
                        break;
                    }
                }
                if ($maxReadLength <= 0) {
                    break;
                }
            }
            if ($match) {
                fseek($fh, 0, SEEK_SET);
                $sha256Context = hash_init('sha256');
                while (!feof($fh)) {
                    $data = fread($fh, 65536);
                    if ($data === false) {
                        break;
                    }
                    hash_update($sha256Context, str_replace(array("\n", "\r", "\t", " "), "", $data));
                }
                $match[] = hash_final($sha256Context, true);
            }
            fclose($fh);
            return $match;
        }
    }
}
namespace Gravityscan\Commands {
    use DirectoryIterator;
    use Gravityscan\AgentException;
    class ListFiles extends BaseCommand
    {
        public function execute()
        {
            $directory = $this->getArg('directory');
            if (!is_string($directory)) {
                throw new AgentException('Invalid [directory] argument supplied', 422);
            }
            $directory = $this->validateFile($directory);
            $max = $this->getArg('max', 10000);
            if (!is_numeric($max)) {
                throw new AgentException('Invalid [max] argument supplied', 422);
            }
            $max = abs(intval($max));
            $offset = $this->getArg('offset', 0);
            if (!is_numeric($offset)) {
                throw new AgentException('Invalid [offset] argument supplied', 422);
            }
            $offset = abs(intval($offset));
            $this->startCommandStream();
            $count = 0;
            if (is_dir($directory)) {
                $directoryIterator = new DirectoryIterator($directory);
                $directoryIterator->seek($offset);
                while ($directoryIterator->valid()) {
                    $file = $directoryIterator->current();
                    if (!$file->isDot() && !$file->isLink() && $file->isFile()) {
                        $this->writeCommandStream($file->key() . ':' . $file->getFilename());
                        if (++$count >= $max) {
                            break;
                        }
                    }
                    $directoryIterator->next();
                }
            }
            $this->endCommandStream();
        }
    }
}
namespace Gravityscan\Commands {
    use Gravityscan\AgentException;
    use Gravityscan\Utils;
    class PatternMatch extends BaseCommand
    {
        public function getPipedArguments()
        {
            return array('fileList');
        }
        public function execute()
        {
            $regexList = $this->getArg('regexList');
            if (!is_array($regexList)) {
                throw new AgentException('Invalid [regexList] argument supplied', 422);
            }
            $fileList = $this->getArg('fileList');
            if (!is_array($regexList)) {
                throw new AgentException('Invalid [fileList] argument supplied', 422);
            }
            $matchingPattern = $this->getArg('matchingPattern');
            $readLength = $this->getArg('readLength');
            $this->startCommandStream();
            foreach ($fileList as $file) {
                try {
                    $filePath = $this->validateFile($file);
                } catch (AgentException $e) {
                    continue;
                }
                if (!$matchingPattern || preg_match($matchingPattern, $filePath)) {
                    $result = $this->scanFile($filePath, $regexList, $readLength);
                    if ($result) {
                        list($pattern, $matchString) = $result;
                        $this->writeCommandStream(json_encode(array($file, $pattern, base64_encode($matchString))));
                    }
                }
            }
            $this->endCommandStream();
        }
        /**
         * @param string $file
         * @param array $patterns
         * @param null $readLength
         * @return array|bool
         */
        protected function scanFile($file, $patterns, $readLength = null)
        {
            $fh = @fopen($file, 'r');
            if (!$fh) {
                return false;
            }
            $totalRead = 0;
            $maxReadLength = is_int($readLength) && $readLength > 0 ? $readLength : 50 * 1024 * 1024;
            $match = false;
            while (!feof($fh)) {
                $length = min(1 * 1024 * 1024, $maxReadLength);
                $data = fread($fh, $length);
                // read 1 megs max per chunk
                $bytesRead = Utils::strlen($data);
                $totalRead += $bytesRead;
                $maxReadLength -= $bytesRead;
                if ($totalRead < 1) {
                    break;
                }
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $data, $matches)) {
                        $matchString = $matches[0];
                        $match = array($pattern, $matchString);
                        break;
                    }
                }
                if ($maxReadLength <= 0) {
                    break;
                }
            }
            fclose($fh);
            return $match;
        }
    }
}
namespace Gravityscan\Commands {
    use DirectoryIterator;
    use Gravityscan\AgentException;
    class ListDirectories extends BaseCommand
    {
        public function execute()
        {
            $directory = $this->getArg('directory');
            if (!is_string($directory)) {
                throw new AgentException('Invalid [directory] argument supplied', 422);
            }
            $directory = $this->validateFile($directory);
            $max = $this->getArg('max', 10000);
            if (!is_numeric($max)) {
                throw new AgentException('Invalid [max] argument supplied', 422);
            }
            $max = abs(intval($max));
            $offset = $this->getArg('offset', 0);
            if (!is_numeric($offset)) {
                throw new AgentException('Invalid [offset] argument supplied', 422);
            }
            $offset = abs(intval($offset));
            $this->startCommandStream();
            $count = 0;
            if (is_dir($directory)) {
                $directoryIterator = new DirectoryIterator($directory);
                $directoryIterator->seek($offset);
                while ($directoryIterator->valid()) {
                    $file = $directoryIterator->current();
                    if (!$file->isDot() && !$file->isLink() && $file->isDir()) {
                        $this->writeCommandStream($file->key() . ':' . $file->getFilename());
                        if (++$count >= $max) {
                            break;
                        }
                    }
                    $directoryIterator->next();
                }
            }
            $this->endCommandStream();
        }
    }
}
namespace Gravityscan\Commands {
    use Gravityscan\AgentException;
    use Gravityscan\Utils;
    class Update extends BaseCommand
    {
        public function execute()
        {
            // Check for JWT
            $jwt = $this->getArg('jwt');
            if (!$jwt) {
                throw new AgentException('Invalid [jwt] argument supplied.', 422);
            }
            // Test if writable
            if (!GRAVITYSCAN_AGENT_IS_WRITABLE) {
                throw new AgentException('Agent is not writable.', 422);
            }
            // Call Gravity API to download new build
            $build = file_get_contents(sprintf(gsConfig('update_api_url'), rawurlencode(gsConfig('site_id')), rawurlencode($jwt)));
            // TODO: Add a checksum here.
            if (is_string($build) && Utils::strlen($build) > 0) {
                // Install new build
                file_put_contents(GRAVITYSCAN_AGENT_FILE, $build);
                $this->getResponse()->setJson(array('success' => true))->send();
            } else {
                throw new AgentException('Output from API was not a Phar file.', 422);
            }
        }
    }
}
namespace Gravityscan\Commands {
    use Gravityscan\AgentException;
    use Gravityscan\Response;
    class Multicall extends BaseCommand
    {
        private $pipes;
        public function execute()
        {
            $commands = $this->getArgs();
            if (!is_array($commands)) {
                throw new AgentException('Invalid [commands] argument supplied', 422);
            }
            foreach ($commands as $index => $commandArray) {
                if (!is_array($commandArray) || !isset($commandArray['command'])) {
                    throw new AgentException('Invalid [commands] argument supplied', 422);
                }
                $commandName = $commandArray['command'];
                $commandClass = '\\Gravityscan\\Commands\\' . $commandName;
                if (!class_exists($commandClass)) {
                    throw new AgentException('Command ' . $commandName . ' not found.', 422);
                }
                $commands[$index]['class'] = $commandClass;
            }
            $this->pipes = array();
            foreach ($commands as $commandArray) {
                // $command = $commandArray['command'];
                $args = isset($commandArray['args']) && is_array($commandArray['args']) ? $commandArray['args'] : array();
                $pipe = isset($commandArray['pipe']) && is_string($commandArray['pipe']) ? $commandArray['pipe'] : null;
                $commandClass = $commandArray['class'];
                if ($pipe) {
                    $response = $this->pipes[$pipe] = new MulticallResponse();
                } else {
                    $response = $this->getResponse();
                }
                /** @var BaseCommand $command */
                $command = new $commandClass($this->getAgent());
                foreach ($command->getPipedArguments() as $pipedArgument) {
                    if (isset($args[$pipedArgument]) && is_string($args[$pipedArgument]) && strpos($args[$pipedArgument], '@') === 0) {
                        $pipedArgumentName = substr($args[$pipedArgument], 1);
                        if (isset($this->pipes[$pipedArgumentName])) {
                            $args[$pipedArgument] = $this->pipes[$pipedArgumentName]->getResponseArray();
                        }
                    }
                }
                /** @var BaseCommand $command */
                $result = $command->setArgs($args)->setRequest($this->getRequest())->setResponse($response)->execute();
                if ($result) {
                    $command->startCommandStream()->writeCommandStream($result)->endCommandStream();
                }
            }
        }
    }
    class MulticallResponse extends Response
    {
        private $responseArray;
        /**
         * @param string $message
         */
        public function sendStreamMessage($message)
        {
            $this->sendHeaders();
            echo $message . "\0";
            flush();
            if (preg_match('/^\\d+:/', $message)) {
                list(, $message) = explode(':', $message, 2);
            }
            $this->responseArray[] = $message;
        }
        /**
         * @return mixed
         */
        public function getResponseArray()
        {
            return array_slice($this->responseArray, 1, -1);
        }
    }
}
namespace Gravityscan\Commands {
    use Gravityscan\Agent;
    use Gravityscan\AgentException;
    use Gravityscan\Request;
    use Gravityscan\Response;
    use Gravityscan\Utils;
    abstract class BaseCommand
    {
        /** @var Agent */
        private $agent;
        public abstract function execute();
        /**
         * @var array
         */
        private $args;
        private $request;
        /**
         * @var Response
         */
        private $response;
        private $streamBoundary;
        /**
         * @param Agent $agent
         * @param array $args
         * @param Request $request
         * @param Response $response
         */
        public function __construct($agent, $args = null, $request = null, $response = null)
        {
            $this->agent = $agent;
            $this->args = $args;
            $this->request = $request;
            $this->response = $response;
        }
        /**
         * Returns an array of argument params that can be piped in a multicall request.
         *
         * @return array
         */
        public function getPipedArguments()
        {
            return array();
        }
        /**
         * @return BaseCommand
         */
        public function startCommandStream()
        {
            return $this->writeCommandStream('cmd_start:' . $this->getCommandName() . ':' . $this->getStreamBoundary());
        }
        /**
         * @param string $message
         * @return $this
         */
        public function writeCommandStream($message)
        {
            $this->getResponse()->sendStreamMessage($message);
            return $this;
        }
        /**
         * @return BaseCommand
         */
        public function endCommandStream()
        {
            return $this->writeCommandStream('cmd_end:' . $this->getCommandName() . ':' . $this->getStreamBoundary());
        }
        /**
         * @return string
         */
        public function getCommandName()
        {
            $className = get_class($this);
            if (preg_match('/^Gravityscan\\\\Commands\\\\(.*?)$/i', $className, $matches)) {
                return $matches[1];
            }
            return $className;
        }
        /**
         * @return mixed
         */
        public function getStreamBoundary()
        {
            if (!$this->streamBoundary) {
                $this->streamBoundary = Utils::getRandomString(32, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
            }
            return $this->streamBoundary;
        }
        /**
         * @param string $file
         * @return string
         * @throws AgentException
         */
        public function validateFile($file)
        {
            $file = $this->getRequest()->getDocumentRoot() . DIRECTORY_SEPARATOR . ltrim($file, '/\\');
            if (!is_dir($file) && !is_file($file)) {
                throw new AgentException('File is not readable.', 422);
            }
            $file = realpath($file);
            if (!$file) {
                throw new AgentException('Error resolving canonicalized absolute pathname for ' . $file . '.', 422);
            }
            //        if (strpos($file, $this->getRequest()->getDocumentRoot()) !== 0) {
            //            throw new GravityscanException('File is outside the document root.', 422);
            //        }
            return $file;
        }
        /**
         * @param array $args
         * @return BaseCommand
         */
        public function setArgs($args)
        {
            $this->args = $args;
            return $this;
        }
        /**
         * @return array
         */
        public function getArgs()
        {
            return $this->args;
        }
        /**
         * @param string $name
         * @param null $default
         * @return mixed|null
         */
        public function getArg($name, $default = null)
        {
            if (is_array($this->args) && array_key_exists($name, $this->args)) {
                $sessionPrefix = '$localStorage.';
                if (is_string($this->args[$name]) && strpos($this->args[$name], $sessionPrefix) !== false) {
                    $sessionKey = substr($this->args[$name], strlen($sessionPrefix));
                    $storage = $this->getAgent()->getLocalStorage();
                    if ($storage->has($sessionKey)) {
                        return $storage->get($sessionKey);
                    }
                }
                return $this->args[$name];
            }
            return $default;
        }
        /**
         * @param Request $request
         * @return BaseCommand
         */
        public function setRequest($request)
        {
            $this->request = $request;
            return $this;
        }
        /**
         * @return Request
         */
        public function getRequest()
        {
            return $this->request;
        }
        /**
         * @param Response $response
         * @return BaseCommand
         */
        public function setResponse($response)
        {
            $this->response = $response;
            return $this;
        }
        /**
         * @return Response
         */
        public function getResponse()
        {
            return $this->response;
        }
        /**
         * @return Agent
         */
        public function getAgent()
        {
            return $this->agent;
        }
    }
}
namespace Gravityscan {
    use Gravityscan\Commands\BaseCommand;
    class Agent
    {
        private $request;
        private $response;
        private $localStorage;
        /**
         * @param Request $request
         * @param Response $response
         */
        public function __construct($request, $response)
        {
            $this->request = $request;
            $this->response = $response;
        }
        /**
         * @return bool
         */
        public function verifySignature()
        {
            return true;
        }
        /**
         * @throws AgentException
         */
        public function run()
        {
            $body = $this->getRequest()->getBody();
            $jsonBody = @json_decode($body, true);
            if (!$jsonBody) {
                throw new AgentException('Error parsing the JSON body.', 422);
            }
            $commandName = !empty($jsonBody['command']) && is_string($jsonBody['command']) ? $jsonBody['command'] : null;
            $commandArgs = isset($jsonBody['args']) ? $jsonBody['args'] : null;
            $commandClass = '\\Gravityscan\\Commands\\' . $commandName;
            if (!class_exists($commandClass)) {
                throw new AgentException('Command ' . $commandName . ' not found.', 422);
            }
            /** @var BaseCommand $command */
            $command = new $commandClass($this);
            $result = $command->setArgs($commandArgs)->setRequest($this->getRequest())->setResponse($this->getResponse())->execute();
            if ($result) {
                $this->getResponse()->setBody($result)->send();
            }
        }
        /**
         * @return Request
         */
        public function getRequest()
        {
            return $this->request;
        }
        /**
         * @param Request $request
         * @return $this
         */
        public function setRequest($request)
        {
            $this->request = $request;
            return $this;
        }
        /**
         * @return Response
         */
        public function getResponse()
        {
            return $this->response;
        }
        /**
         * @param Response $response
         * @return $this
         */
        public function setResponse($response)
        {
            $this->response = $response;
            return $this;
        }
        /**
         * @return LocalStorage
         * @throws AgentException
         */
        public function getLocalStorage()
        {
            if (!$this->localStorage) {
                $storageDrivers = array('Gravityscan\\LocalStorageSession');
                foreach ($storageDrivers as $driver) {
                    if (class_exists($driver)) {
                        /** @var LocalStorage $storage */
                        $storage = new $driver();
                        try {
                            $storage->initialize();
                            $this->localStorage = $storage;
                        } catch (AgentException $e) {
                            continue;
                        }
                    }
                }
            }
            if (!$this->localStorage) {
                if (isset($e)) {
                    throw $e;
                }
                throw new AgentException('Unable to utilize local storage.', 500);
            }
            return $this->localStorage;
        }
    }
}
namespace Gravityscan {
    class Response
    {
        public static $statusTexts = array(100 => 'Continue', 101 => 'Switching Protocols', 102 => 'Processing', 200 => 'OK', 201 => 'Created', 202 => 'Accepted', 203 => 'Non-Authoritative Information', 204 => 'No Content', 205 => 'Reset Content', 206 => 'Partial Content', 207 => 'Multi-Status', 208 => 'Already Reported', 226 => 'IM Used', 300 => 'Multiple Choices', 301 => 'Moved Permanently', 302 => 'Found', 303 => 'See Other', 304 => 'Not Modified', 305 => 'Use Proxy', 307 => 'Temporary Redirect', 308 => 'Permanent Redirect', 400 => 'Bad Request', 401 => 'Unauthorized', 402 => 'Payment Required', 403 => 'Forbidden', 404 => 'Not Found', 405 => 'Method Not Allowed', 406 => 'Not Acceptable', 407 => 'Proxy Authentication Required', 408 => 'Request Timeout', 409 => 'Conflict', 410 => 'Gone', 411 => 'Length Required', 412 => 'Precondition Failed', 413 => 'Payload Too Large', 414 => 'URI Too Long', 415 => 'Unsupported Media Type', 416 => 'Range Not Satisfiable', 417 => 'Expectation Failed', 418 => 'I\'m a teapot', 421 => 'Misdirected Request', 422 => 'Unprocessable Entity', 423 => 'Locked', 424 => 'Failed Dependency', 425 => 'Reserved for WebDAV advanced collections expired proposal', 426 => 'Upgrade Required', 428 => 'Precondition Required', 429 => 'Too Many Requests', 431 => 'Request Header Fields Too Large', 451 => 'Unavailable For Legal Reasons', 500 => 'Internal Server Error', 501 => 'Not Implemented', 502 => 'Bad Gateway', 503 => 'Service Unavailable', 504 => 'Gateway Timeout', 505 => 'HTTP Version Not Supported', 506 => 'Variant Also Negotiates (Experimental)', 507 => 'Insufficient Storage', 508 => 'Loop Detected', 510 => 'Not Extended', 511 => 'Network Authentication Required');
        private $statusCode = 200;
        private $body = '';
        private $headers = array();
        public function __construct()
        {
        }
        /**
         *
         */
        public function send()
        {
            $this->sendHeaders();
            echo $this->getBody();
            exit;
        }
        /**
         * @param string $message
         */
        public function sendStreamMessage($message)
        {
            $this->sendHeaders();
            echo $message . "\0";
            flush();
        }
        /**
         * @return bool
         */
        public function sendHeaders()
        {
            if (headers_sent()) {
                return false;
            }
            $statusCode = $this->getStatusCode();
            if ($statusCode !== null) {
                header(sprintf('HTTP/1.0 %s %s', $statusCode, array_key_exists($statusCode, self::$statusTexts) ? self::$statusTexts[$statusCode] : 'Unknown status'), true, $statusCode);
            }
            $headers = $this->getHeaders();
            if (is_array($headers)) {
                foreach ($headers as $header => $value) {
                    if (is_numeric($header)) {
                        header($value);
                    } else {
                        header("{$header}: {$value}");
                    }
                }
            }
            return true;
        }
        /**
         * @param mixed $statusCode
         * @return Response
         */
        public function setStatusCode($statusCode)
        {
            $this->statusCode = $statusCode;
            return $this;
        }
        /**
         * @return mixed
         */
        public function getStatusCode()
        {
            return $this->statusCode;
        }
        /**
         * @param $body
         * @return Response
         */
        public function setJson($body)
        {
            return $this->setHeader('Content-Type', 'application/json')->setBody(json_encode($body));
        }
        /**
         * @param mixed $body
         * @return Response
         */
        public function setBody($body)
        {
            $this->body = $body;
            return $this;
        }
        /**
         * @return mixed
         */
        public function getBody()
        {
            return $this->body;
        }
        /**
         * @param $header
         * @param $value
         * @return Response
         */
        public function setHeader($header, $value)
        {
            $this->headers[$header] = $value;
            return $this;
        }
        /**
         * @param mixed $headers
         * @return Response
         */
        public function setHeaders($headers)
        {
            $this->headers = $headers;
            return $this;
        }
        /**
         * @return mixed
         */
        public function getHeaders()
        {
            return $this->headers;
        }
    }
}
namespace {
    function gsConfig($key)
    {
        static $config;
        if (!isset($config)) {
            $config = array('public_key' => '-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAz7U6vhTyrK14VvUL5ObH
2AahdYcK9zbpw1qN9jHKN06+FbsffKNKGVOVnQLM7PMJGiDlDQbKuhUNmnJWfDUh
blTDZe2vkEli5R27AuLXeZthWwKYYmzF4qVFny4OOjF3P+s7Rr/GjwxhWJcJsDuf
a7wYtUoeEKfNgNMhsTRUE+f4OTU5vajGn7YzPVoI3ZfJNFKPk81pK/jht/CTAglT
p0udTWyoda3GQQN4Y9Elv51qNJa5Tnla8+6yg6hCCPFov4F3GejLkLLLk/RddTT9
zdWrGfrfLh1tkdb7bp8pLI9f1JPn0s5rg3EjmUKHkWP2QIYex4LfUNaPFs89Aa1Y
AzDNQZHRX3oBhnhPgFvyNKyKa7gKbOPox5APuRB50IRNHq4p8D7qtgVZ3iEVzVH0
wswcrU9RDkCcjCDIgKaJ1XQYHzVHhTV9ikto16j2bydRJe/cAVsMCGvyNSJPsY1C
e4RH2whxzLGxOSRO4hGLjNpUHmqe3GXp3L65xo2hudOdky0NhcMc8zcy8ZoXjIVy
SNLSO3Av1et/ZW84oDfAg3mYSEqV7aLTYx9YM33AV7rveinGysJe4YOQxkN64ykZ
lILhSXD9TiBsFSaQY0Qf8VEmFmjocxt99FO8czqoSWtjBNqVeW1HqLFlUYxrByFP
yJP+8KeUtDsW2Z9RwXdNstsCAwEAAQ==
-----END PUBLIC KEY-----
', 'site_id' => 57394, 'update_api_url' => 'https://www.gravityscan.com/api/sites/%d/download-agent?token=%s');
        }
        return array_key_exists($key, $config) ? $config[$key] : null;
    }
}
namespace {
    function diagnostics()
    {
        ob_start();
        ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">
    <title>Gravityscan Accelerator</title>
    <style type="text/css">
        body {
            width: 42em;
            margin: 0 auto;
            font-family: sans-serif;
            background: #fff;
            font-size: 1em;
        }
        h1 {
            letter-spacing: -0.04em;
            margin: 1em 0px .5em;
        }
        h1 + p {
            margin: 0 0 2em;
            color: #333;
            font-size: 90%;
            font-style: italic;
        }
        code {
            font-family: monaco, monospace;
        }
        a {
            color: #1e6fc0;
        }
        a:hover {
            color: #2695ff;
        }
        #results {
            margin: 0.8em 0px;
            font-size: 1.5em;
        }
        #results div.pass,
        #results div.warn,
        #results div.fail {
            color: #fff;
            margin: 20px 0px;
            padding: 20px;
        }
        #results div.pass {
            background: #191;
        }
        #results div.fail {
            background: #911;
        }
        #results div.warn {
            background: #e59c00;
        }
        #results table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0px;
        }
        #results table th,
        #results table td {
            padding: 0.4em;
            text-align: left;
            vertical-align: top;
            border: 1px solid #c9c9c9;
        }
        #results table th {
            width: 12em;
            font-weight: normal;
        }
        #results table thead th {
            border-color: #333333;
            background-color: #333333;
            color: #fff;
            font-weight: bold;
        }
        #results table.errors thead th {
            background-color: #911;
            border-color: #911;
        }
        #results table.warnings thead th {
            background-color: #e59c00;
            border-color: #e59c00;
        }
        #results table tbody tr:nth-child(odd) {
            background: #eee;
        }
        #results table td.pass {
            color: #191;
        }
        #results table td.fail {
            color: #911;
        }
        #results table td.warn {
            color: #b9822a;
        }
    </style>
</head>
<body>
<h1>Gravityscan Accelerator Diagnostics</h1>
<p>You are currently running Gravityscan Accelerator version <strong><?php 
        echo GRAVITYSCAN_AGENT_VERSION;
        ?>
</strong>.
</p>
<?php 
        $compatibilityChecks = array(array('test' => version_compare(PHP_VERSION, '5.3.3', '>='), 'title' => 'PHP Version', 'error' => 'Gravityscan Accelerator requires PHP 5.3.3 or newer, this version is ' . PHP_VERSION . '.'), array('test' => function_exists('openssl_verify'), 'title' => 'OpenSSL', 'error' => 'PHP <a href="http://www.php.net/openssl">OpenSSL</a> is either not loaded or not compiled in.'), array('test' => @preg_match('/^.$/u', 'ñ'), 'title' => 'PCRE UTF-8', 'error' => '<a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support.'), array('test' => @preg_match('/^\\pL$/u', 'ñ'), 'title' => 'PCRE UTF-8', 'error' => '<a href="http://php.net/pcre">PCRE</a> has not been compiled with Unicode property support.'), array('test' => !extension_loaded('mbstring') || !(ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING), 'title' => 'Mbstring Overloaded', 'error' => 'The <a href="http://php.net/mbstring">mbstring</a> extension is overloading PHP\'s native string functions.'));
        $warningChecks = array(array('test' => GRAVITYSCAN_AGENT_IS_WRITABLE, 'title' => 'Accelerator File Permissions', 'error' => 'The Accelerator file is not writable. Automatic updates are disabled.'));
        $failed = false;
        $errors = array();
        foreach ($compatibilityChecks as $check) {
            if (!$check['test']) {
                $failed = true;
                $errors[] = $check;
            }
        }
        $warning = false;
        $warnings = array();
        foreach ($warningChecks as $check) {
            if (!$check['test']) {
                $warning = true;
                $warnings[] = $check;
            }
        }
        ?>
<div id="results">
    <?php 
        if ($failed || $warning) {
            ?>
        <?php 
            if ($failed) {
                ?>
            <div class="fail">
                ✘ Gravityscan Accelerator may not work correctly with your environment.
            </div>
        <?php 
            } else {
                ?>
            <div class="warn">
                (<strong>!</strong>) Gravityscan Accelerator will function correctly, but some optional features may be
                disabled.
            </div>
        <?php 
            }
            ?>
        <?php 
            if ($failed) {
                ?>
            <table cellpadding="0" cellspacing="0" class="errors">
                <thead>
                <tr>
                    <th colspan="2">Errors:</th>
                </tr>
                </thead>
                <tbody>
                <?php 
                foreach ($errors as $error) {
                    ?>
                    <tr>
                        <th><?php 
                    echo $error['title'];
                    ?>
</th>
                        <td class="fail"><?php 
                    echo $error['error'];
                    ?>
</td>
                    </tr>
                <?php 
                }
                ?>
                </tbody>
            </table>
        <?php 
            }
            ?>
        <?php 
            if ($warning) {
                ?>
            <table cellpadding="0" cellspacing="0" class="warnings">
                <thead>
                <tr>
                    <th colspan="2">Warnings:</th>
                </tr>
                </thead>
                <tbody>
                <?php 
                foreach ($warnings as $error) {
                    ?>
                    <tr>
                        <th><?php 
                    echo $error['title'];
                    ?>
</th>
                        <td class="warn"><?php 
                    echo $error['error'];
                    ?>
</td>
                    </tr>
                <?php 
                }
                ?>
                </tbody>
            </table>
        <?php 
            }
            ?>
    <?php 
        } else {
            ?>
        <div class="pass">✔ Your environment passed all requirements.</div>
    <?php 
        }
        ?>
</div>
<h1>Public Key</h1>
<pre><?php 
        echo gsConfig('public_key');
        ?>
</pre>
</body>
</html>
<?php 
        return ob_get_clean();
    }
}
namespace {
    use Gravityscan\Agent;
    use Gravityscan\AgentAuth;
    use Gravityscan\AgentException;
    use Gravityscan\Request;
    use Gravityscan\Response;
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $authenticated = false;
    try {
        // Parse request
        $request = Request::createFromGlobals();
        $gravityscanAgent = new Agent($request, new Response());
        if ($request->getMethod() === 'GET') {
            $gravityscanAgent->getResponse()->setStatusCode(200)->setHeader('Content-Type', 'text/html')->setBody(diagnostics())->send();
        }
        // Authentication
        $gravityscanAuth = new AgentAuth();
        $gravityscanAuth->setSignature(pack("H*", $request->getHeader('X-Gravityscan-Signature')))->setMessage($request->getBody())->setPublicKey(gsConfig('public_key'))->authenticate();
        $authenticated = true;
        // Issue commands
        $gravityscanAgent->run();
    } catch (AgentException $e) {
        $gravityscanAgent->getResponse()->setStatusCode($e->getCode())->setJson(array('code' => $e->getCode(), 'error' => $e->getMessage()))->send();
    } catch (Exception $e) {
        $gravityscanAgent->getResponse()->setStatusCode(500)->setJson(array('code' => 500, 'error' => $authenticated ? $e->getMessage() : 'There was an error processing your request.'))->send();
    }
}