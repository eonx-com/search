<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Integration\Fixtures\SearchHandlers;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use EonX\EasyEntityChange\DataTransferObjects\ChangedEntity;
use EonX\EasyEntityChange\DataTransferObjects\DeletedEntity;
use LoyaltyCorp\Search\Bridge\Doctrine\DoctrineSearchHandler;
use LoyaltyCorp\Search\DataTransferObjects\DocumentAction;
use LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForDelete;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use RuntimeException;
use Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities\Blog;
use Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities\Comment;

/**
 * @extends DoctrineSearchHandler<\Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities\Comment>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommentSearchHandler extends DoctrineSearchHandler
{
    /**
     * Constructor
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct(Comment::class, $entityManager);
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
        return 'comment';
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexName(): string
    {
        return 'comment';
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptions(): iterable
    {
        yield from parent::getSubscriptions();

        $entityManager = $this->getEntityManager();

        /**
         * @param \EonX\EasyEntityChange\DataTransferObjects\ChangedEntity $changedEntity
         *
         * @return \LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange[]
         *
         * @phpstan-return iterable<\LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForChange<Comment>>
         */
        $transform = static function (ChangedEntity $changedEntity) use ($entityManager): iterable {
            if ($changedEntity instanceof DeletedEntity === true) {
                return [];
            }

            $builder = $entityManager->createQueryBuilder();
            $builder->select('c.id');
            $builder->from(Comment::class, 'c');

            $builder->where('IDENTITY(c.blog) = :blog');
            $builder->setParameter('blog', $changedEntity->getIds()['id']);

            foreach ($builder->getQuery()->iterate([], AbstractQuery::HYDRATE_SCALAR) as $result) {
                yield new ObjectForUpdate(
                    Comment::class,
                    ['id' => $result[0]['id']]
                );
            }
        };

        yield new ChangeSubscription(
            Blog::class,
            ['title'],
            $transform
        );
    }

    /**
     * {@inheritdoc}
     */
    public function transform(ObjectForChange $change): ?DocumentAction
    {
        if ($change instanceof ObjectForDelete === true) {
            return null;
        }

        $comment = $change->getObject();

        if ($comment instanceof Comment === false) {
            // Since the DoctrineSearchHandler prefills objects if we didnt get one
            // something is super wrong.
            throw new RuntimeException();
        }

        return new DocumentUpdate(
            (string)$comment->getId(),
            [
                'body' => $comment->getBody(),
                'title' => $comment->getBlog()->getTitle()
            ]
        );
    }
}
