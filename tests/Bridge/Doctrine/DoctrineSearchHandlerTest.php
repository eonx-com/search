<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Bridge\Doctrine;

use Eonx\TestUtils\Stubs\Vendor\Doctrine\ORM\EntityManagerStub;
use LoyaltyCorp\Search\Bridge\Doctrine\Exceptions\InvalidRepositoryException;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ChangeSubscription;
use LoyaltyCorp\Search\DataTransferObjects\Handlers\ObjectForUpdate;
use stdClass;
use Tests\LoyaltyCorp\Search\Stubs\Bridge\Doctrine\DoctrineSearchHandlerStub;
use Tests\LoyaltyCorp\Search\Stubs\Bridge\Doctrine\FillableRepositoryStub;
use Tests\LoyaltyCorp\Search\Stubs\Entities\EntityStub;
use Tests\LoyaltyCorp\Search\TestCase;
use Traversable;

/**
 * @covers \LoyaltyCorp\Search\Bridge\Doctrine\DoctrineSearchHandler
 */
class DoctrineSearchHandlerTest extends TestCase
{
    /**
     * Tests that the function throws when it receives a repository that doesnt
     * implement FillableRepositoryInterface.
     *
     * @return void
     */
    public function testGetFillIterableBadRepository(): void
    {
        $entityManager = new EntityManagerStub([
            'getRepository' => [
                new stdClass(),
            ],
        ]);

        $handler = new DoctrineSearchHandlerStub(
            EntityStub::class,
            $entityManager
        );

        $this->expectException(InvalidRepositoryException::class);
        $this->expectExceptionMessage(
            'A repository used for DoctrineSearchHandler must implement FillableRepositoryInterface.'
        );

        $iterable = $handler->getFillIterable();

        // Trigger the generator
        if ($iterable instanceof \Traversable) {
            \iterator_to_array($iterable);
        }
    }

    /**
     * Tests that the fill iterable function returns the result of the
     * repository getFillIterable() method instead of working it out
     * itself.
     *
     * @return void
     */
    public function testGetFillIterableRepository(): void
    {
        $update = new ObjectForUpdate(EntityStub::class, []);

        $entityManager = new EntityManagerStub([
            'getRepository' => [
                new FillableRepositoryStub([
                    'getFillIterable' => [
                        [$update],
                    ],
                ]),
            ],
        ]);

        $handler = new DoctrineSearchHandlerStub(
            EntityStub::class,
            $entityManager
        );

        $iterator = $handler->getFillIterable();

        $result = $iterator instanceof Traversable === true
            ? \iterator_to_array($iterator)
            : $iterator;

        self::assertEquals([$update], $result);
    }

    /**
     * Tests that get subscriptions returns a basic subscription by default.
     *
     * @return void
     */
    public function testGetSubscriptions(): void
    {
        $handler = new DoctrineSearchHandlerStub(
            EntityStub::class,
            new EntityManagerStub()
        );

        $expected = [
            new ChangeSubscription(EntityStub::class),
        ];

        $subscriptions = $handler->getSubscriptions();

        $result = $subscriptions instanceof \Traversable
            ? \iterator_to_array($subscriptions)
            : $subscriptions;

        self::assertEquals($expected, $result);
    }

    /**
     * Tests that prefill calls the repository.
     *
     * @return void
     */
    public function testPrefill(): void
    {
        $fillableRepository = new FillableRepositoryStub();
        $entityManager = new EntityManagerStub([
            'getRepository' => [
                $fillableRepository,
            ],
        ]);

        $updates = [
            new ObjectForUpdate(EntityStub::class, ['id' => 1]),
            new ObjectForUpdate(EntityStub::class, ['id' => 2]),
        ];

        $handler = new DoctrineSearchHandlerStub(
            EntityStub::class,
            $entityManager
        );

        $handler->prefill($updates);

        self::assertSame([['changes' => $updates]], $fillableRepository->getCalls('prefillSearch'));
    }
}
