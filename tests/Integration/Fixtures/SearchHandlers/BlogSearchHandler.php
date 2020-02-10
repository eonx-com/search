<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Integration\Fixtures\SearchHandlers;

use Doctrine\ORM\EntityManagerInterface;
use LoyaltyCorp\Search\Bridge\Doctrine\DoctrineSearchHandler;
use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\DataTransferObjects\DocumentDelete;
use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForDelete;
use RuntimeException;
use Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities\Blog;

/**
 * @extends DoctrineSearchHandler<\Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities\Blog>
 */
class BlogSearchHandler extends DoctrineSearchHandler
{
    /**
     * Constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct(Blog::class, $entityManager);
    }

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
    public function getHandlerKey(): string
    {
        return 'blog';
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexName(): string
    {
        return 'blog';
    }

    /**
     * {@inheritdoc}
     */
    public function transform(ObjectForChange $change): ?DocumentAction
    {
        if ($change instanceof ObjectForDelete === true) {
            $deletedId = $change->getMetadata()['deletedId'] ?? null;

            if ($deletedId === null) {
                return null;
            }

            return new DocumentDelete((string)$deletedId);
        }

        $blog = $change->getObject();

        if ($blog instanceof Blog === false) {
            // Since the DoctrineSearchHandler prefills objects if we didnt get one
            // something is super wrong.
            throw new RuntimeException();
        }

        return new DocumentUpdate(
            (string)$blog->getId(),
            [
                'body' => $blog->getBody(),
                'title' => $blog->getTitle(),
            ]
        );
    }
}
