<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs;

use EoneoPay\Externals\Logger\Interfaces\LoggerInterface;
use Throwable;

final class LoggerStub implements LoggerInterface
{

    /**
     * @inheritDoc
     */
    public function alert($message, array $context = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function emergency($message, array $context = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function exception(Throwable $exception, ?string $level = null, ?array $context = null): void
    {
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function notice($message, array $context = [])
    {
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = [])
    {
    }
}
