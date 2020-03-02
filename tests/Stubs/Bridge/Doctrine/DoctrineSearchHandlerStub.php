<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Bridge\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use LoyaltyCorp\Search\Bridge\Doctrine\DoctrineSearchHandler;
use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange;

/**
 * @coversNothing
 *
 * @template T
 *
 * @extends \LoyaltyCorp\Search\Bridge\Doctrine\DoctrineSearchHandler<T>
 */
class DoctrineSearchHandlerStub extends DoctrineSearchHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getMappings(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSettings(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return parent::getEntityManager();
    }

    /**
     * {@inheritdoc}
     */
    public function getHandlerKey(): string
    {
        return 'handlerkey';
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexName(): string
    {
        return 'index';
    }

    /**
     * {@inheritdoc}
     */
    public function transform(ObjectForChange $change): ?DocumentAction
    {
        return null;
    }
}
