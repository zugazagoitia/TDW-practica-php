<?php

namespace TDW\ACiencia\Factory;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Factory.
 */
class LoggerFactory
{
    private string $path;

    private int $level;

    private int $file_permission;

    private array $handler = [];

    /**
     * The constructor.
     *
     * @param array $settings The settings
     */
    public function __construct(array $settings = [])
    {
        $this->path = (string) ($settings['path'] ?? '');
        $this->level = (int) ($settings['level'] ?? 0);
        $this->file_permission = (int) ($settings['file_permission'] ?? 777);
    }

    /**
     * Build the logger.
     *
     * @param string $name The name
     *
     * @return LoggerInterface The logger
     */
    public function createInstance(string $name): LoggerInterface
    {
        $logger = new Logger($name);

        foreach ($this->handler as $handler) {
            $logger->pushHandler($handler);
        }
        $this->handler = [];

        return $logger;
    }

    /**
     * Add a handler.
     *
     * @param HandlerInterface $handler The handler
     *
     * @return self The logger factory
     */
    public function addHandler(HandlerInterface $handler): self
    {
        $this->handler[] = $handler;

        return $this;
    }

    /**
     * Add rotating file logger handler.
     *
     * @param string $filename The filename
     * @param ?int $level The level (optional)
     *
     * @return LoggerFactory The logger factory
     */
    public function addFileHandler(string $filename, int $level = null): self
    {
        $filename = sprintf('%s/%s', $this->path, $filename);
        $rotatingFileHandler = new RotatingFileHandler(
            $filename,
            0,
            $level ?? $this->level,
            true,
            $this->file_permission
        );

        $rotatingFileHandler->setFormatter(new LineFormatter(null, null, true, false));
        $this->addHandler($rotatingFileHandler);

        return $this;
    }

    /**
     * Add a console logger.
     *
     * @param int|null $level The level (optional)
     *
     * @return self The instance
     */
    public function addConsoleHandler(?int $level = null): self
    {
        $streamHandler = new StreamHandler('php://output', $level ?? $this->level);
        $streamHandler->setFormatter(new LineFormatter(null, null, true, false));
        $this->addHandler($streamHandler);

        return $this;
    }
}
