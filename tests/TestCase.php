<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search;

use Doctrine\ORM\EntityManagerInterface as DoctrineEntityManagerInterface;
use EoneoPay\Externals\Logger\Interfaces\LoggerInterface;
use EoneoPay\Externals\Logger\Logger;
use EoneoPay\Externals\ORM\Interfaces\EntityManagerInterface as EoneoPayEntityManagerInterface;
use Illuminate\Contracts\Foundation\Application;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Doctrine\EntityManagerStub as DoctrineEntityManagerStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Doctrine\RegistryStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\EoneoPay\Externals\ORM\EntityManagerStub as EoneoPayEntityManagerStub;
use Tests\LoyaltyCorp\Search\Stubs\Vendor\Illuminate\Contracts\Foundation\ApplicationStub;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create configured application instance for service provider testing
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function createApplication(): Application
    {
        $application = new ApplicationStub();

        // Bind logger to container so app->make on interface works
        $application->singleton(LoggerInterface::class, static function (): LoggerInterface {
            return new Logger();
        });

        // Bind Doctrine EntityManager to container so app->make on interface works
        $application->singleton(DoctrineEntityManagerInterface::class, static function (): DoctrineEntityManagerStub {
            return new DoctrineEntityManagerStub();
        });

        // Bind eoneopay EntityManager to container so app->make on interface works
        $application->singleton(EoneoPayEntityManagerInterface::class, static function (): EoneoPayEntityManagerStub {
            return new EoneoPayEntityManagerStub();
        });

        $application->singleton('registry', RegistryStub::class);

        return $application;
    }
}
