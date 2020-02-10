<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Integration;

use EonX\EasyEntityChange\Interfaces\DeletedEntityEnrichmentInterface;
use Tests\LoyaltyCorp\Search\Integration\Fixtures\DeletedEntityIdEnrichment;
use Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities\Blog;
use Tests\LoyaltyCorp\Search\Integration\Fixtures\SearchHandlers\BlogSearchHandler;
use Tests\LoyaltyCorp\Search\TestCases\IntegrationTestCase;

class EntityLifecycleTest extends IntegrationTestCase
{
    /**
     * Tests that a new entity flush will cause a search update.
     *
     * @return void
     */
    public function testNewEntityCausesSearchInsertion(): void
    {
        $entityManager = $this->getEntityManager();

        $blog = new Blog('Body', 'Title');

        $entityManager->persist($blog);
        $entityManager->flush();

        $expected = [
            [
                'body' => [
                    0 => [
                        'index' => [
                            '_index' => 'blog',
                            '_type' => 'doc',
                            '_id' => '1'
                        ]
                    ],
                    1 => [
                        'body' => 'Body',
                        'title' => 'Title'
                    ]
                ]
            ]
        ];

        $client = $this->getContainer()->get('search_elasticsearch_client');

        self::assertEquals($expected, $client->getBulkCalls());
    }

    /**
     * Tests multiple entities go in batches
     *
     * @return void
     */
    public function testNewEntityCausesBatchSearchInsertion(): void
    {
        $entityManager = $this->getEntityManager();

        $deleteBlog = new Blog('Deleted', 'Deleted');
        $entityManager->persist($deleteBlog);

        $blog1 = new Blog('Body1', 'Title');
        $entityManager->persist($blog1);

        $entityManager->flush();

        $this->getContainer()->get('search_elasticsearch_client')->resetBulkCalls();

        $entityManager->remove($deleteBlog);

        $blog1->setTitle('UPDATED BLOG 1');
        $entityManager->persist($blog1);

        $blog2 = new Blog('Body2', 'Title');
        $entityManager->persist($blog2);

        $blog3 = new Blog('Body3', 'Title');
        $entityManager->persist($blog3);

        $entityManager->flush();

        $expected = [
            [
                'body' => [
                    0 => [
                        'index' => [
                            '_index' => 'blog',
                            '_type' => 'doc',
                            '_id' => '2'
                        ]
                    ],
                    1 => [
                        'body' => 'Body1',
                        'title' => 'UPDATED BLOG 1'
                    ],
                    2 => [
                        'delete' =>[
                            '_index' => 'blog',
                            '_type' => 'doc',
                            '_id' => '1'
                        ]
                    ],
                ]
            ],
            [
                'body' => [
                    0 => [
                        'index' => [
                            '_index' => 'blog',
                            '_type' => 'doc',
                            '_id' => '3'
                        ]
                    ],
                    1 => [
                        'body' => 'Body2',
                        'title' => 'Title'
                    ],
                    2 => [
                        'index' => [
                            '_index' => 'blog',
                            '_type' => 'doc',
                            '_id' => '4'
                        ]
                    ],
                    3 => [
                        'body' => 'Body3',
                        'title' => 'Title'
                    ]
                ]
            ]
        ];

        $client = $this->getContainer()->get('search_elasticsearch_client');

        self::assertEquals($expected, $client->getBulkCalls());
    }

    /**
     * {@inheritdoc}
     *
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function getDeleteEnrichment(): ?DeletedEntityEnrichmentInterface
    {
        return new DeletedEntityIdEnrichment();
    }

    /**
     * {@inheritdoc}
     *
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function getRegisteredHandlers(): array
    {
        return [
            BlogSearchHandler::class
        ];
    }
}
