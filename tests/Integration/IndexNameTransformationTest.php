<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Integration;

use EonX\EasyEntityChange\Interfaces\DeletedEntityEnrichmentInterface;
use LoyaltyCorp\Search\Interfaces\Transformers\IndexNameTransformerInterface;
use Tests\LoyaltyCorp\Search\Integration\Fixtures\DeletedEntityIdEnrichment;
use Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities\Blog;
use Tests\LoyaltyCorp\Search\Integration\Fixtures\SearchHandlers\BlogSearchHandler;
use Tests\LoyaltyCorp\Search\Stubs\Transformers\CustomIndexNameTransformerStub;
use Tests\LoyaltyCorp\Search\TestCases\IntegrationTestCase;

class IndexNameTransformationTest extends IntegrationTestCase
{
    /**
     * Tests that index names are transformed for updates, inserts and deletes.
     *
     * @return void
     */
    public function testNewEntityCausesBatchSearchInsertion(): void
    {
        $this->getContainer()->instance(
            IndexNameTransformerInterface::class,
            new CustomIndexNameTransformerStub()
        );

        $entityManager = $this->getEntityManager();

        $deleteBlog = new Blog('Deleted', 'Deleted');
        $entityManager->persist($deleteBlog);

        $blog1 = new Blog('Body1', 'Title');
        $entityManager->persist($blog1);

        $entityManager->flush();

        $entityManager->remove($deleteBlog);

        $blog1->setTitle('UPDATED BLOG 1');
        $entityManager->persist($blog1);

        $entityManager->flush();

        $expected = [
            [
                'body' => [
                    0 => [
                        'index' => [
                            '_index' => 'blog_customId',
                            '_type' => 'doc',
                            '_id' => '1'
                        ]
                    ],
                    1 => [
                        'body' => 'Deleted',
                        'title' => 'Deleted'
                    ],
                    2 => [
                        'index' => [
                            '_index' => 'blog_customId',
                            '_type' => 'doc',
                            '_id' => '2'
                        ]
                    ],
                    3 => [
                        'body' => 'Body1',
                        'title' => 'Title'
                    ],
                ]
            ],
            [
                'body' => [
                    0 => [
                        'index' => [
                            '_index' => 'blog_customId',
                            '_type' => 'doc',
                            '_id' => '2'
                        ]
                    ],
                    1 => [
                        'body' => 'Body1',
                        'title' => 'UPDATED BLOG 1'
                    ],
                    2 => [
                        'delete' => [
                            '_index' => 'blog_customId',
                            '_type' => 'doc',
                            '_id' => '1'
                        ]
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
