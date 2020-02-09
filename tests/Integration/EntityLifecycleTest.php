<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Integration;

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
        $blog = new Blog('Body', 'Title');

        $entityManager = $this->getEntityManager();
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

        $blog1 = new Blog('Body1', 'Title');
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
                            '_id' => '1'
                        ]
                    ],
                    1 => [
                        'body' => 'Body1',
                        'title' => 'Title'
                    ],
                    2 => [
                        'index' => [
                            '_index' => 'blog',
                            '_type' => 'doc',
                            '_id' => '2'
                        ]
                    ],
                    3 => [
                        'body' => 'Body2',
                        'title' => 'Title'
                    ]
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
    protected function getRegisteredHandlers(): array
    {
        return [
            BlogSearchHandler::class
        ];
    }
}
