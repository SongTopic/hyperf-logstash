<?php
// +----------------------------------------------------------------------
// | LogstashLogger.php
// +----------------------------------------------------------------------
// | Description:
// +----------------------------------------------------------------------
// | Time: 2020/6/30 20:09
// +----------------------------------------------------------------------
// | Author: wufly <wfxykzd@163.com>
// +----------------------------------------------------------------------

namespace Palmbuy\Logger;

use App\Common\IdGenerator\Uuid;
use Hyperf\Cache\Cache;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;


/**
 * Class LogstashLogger
 * @package Wufly\logger
 */
class LogstashLogger implements LoggerInterface
{
    protected $channel; // 日志channel

    protected $handler;

    protected $writer;

    protected $context = [];

    /**
     * LogstashLogger constructor.
     * @param string $channel
     */
    public function __construct($channel = '')
    {
        $appName = env('APP_NAME');
        $handler = new RotatingFileHandler(
            config('logstash.logstash_log_path'),
            config('logstash.log_max_files'),
            Logger::DEBUG,
            true,
            config('logstash.log_file_permission')
        );
        $handler->setFormatter(new LogstashFormatter($appName));
        $writer = new Logger($channel ?: $appName, [$handler]);
        $this->writer = $writer;
        $this->setContext();
    }

    /**
     * Set default context
     *
     * @return void
     */
    public function setContext()
    {
        if (!$this->context) {
            $request = ApplicationContext::getContainer()->get(\Hyperf\HttpServer\Contract\RequestInterface::class);
            $requestId = $request->getHeader(config('logstash.request_id_name'));
            if (!$requestId) {
                $requestId = make(Uuid::class)->setPrefix('req')->generate();
            }
            $user_id = auth()->id();
            $context = [
                'path'        => $request->getRequestUri(),
                'param'       => json_encode($request->all()),
                'request_id'  => $requestId,
                'user_id'     => $user_id,
                'system_name' => env('APP_NAME'), // 系统名称
            ];
            $this->context = array_merge($this->context, $context);
        }
    }


    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    public function emergency($message, array $context = [])
    {
        $this->context = array_merge($this->context, $context);
        $this->writer->emergency($message, $this->context);
    }

    public function alert($message, array $context = [])
    {
        $this->context = array_merge($this->context, $context);
        $this->writer->alert($message, $this->context);
    }

    public function critical($message, array $context = array())
    {
        $this->context = array_merge($this->context, $context);
        $this->writer->emergency($message, $this->context);
    }

    public function error($message, array $context = array())
    {
        $this->context = array_merge($this->context, $context);
        $this->writer->error($message, $this->context);
    }

    public function warning($message, array $context = array())
    {
        $this->context = array_merge($this->context, $context);
        $this->writer->warning($message, $this->context);
    }

    public function notice($message, array $context = array())
    {
        $this->context = array_merge($this->context, $context);
        $this->writer->notice($message, $this->context);
    }

    public function info($message, array $context = array())
    {
        $this->context = array_merge($this->context, $context);
        $this->writer->info($message, $this->context);
    }

    public function debug($message, array $context = array())
    {
        $this->context = array_merge($this->context, $context);
        $this->writer->debug($message, $this->context);
    }

    public function log($level, $message, array $context = array())
    {
        $this->context = array_merge($this->context, $context);
        $this->writer->log($level, $message, $this->context);
    }

}
