<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Integration;

use LoyaltyCorp\Search\Interfaces\PopulatorInterface;
use Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities\Blog;
use Tests\LoyaltyCorp\Search\Integration\Fixtures\SearchHandlers\BlogSearchHandler;
use Tests\LoyaltyCorp\Search\TestCases\IntegrationTestCase;

class PopulationTest extends IntegrationTestCase
{
    /**
     * Tests that index names are transformed for updates, inserts and deletes.
     *
     * @return void
     */
    public function testPopulator(): void
    {
        $entityManager = $this->getEntityManager();

        $blog1 = new Blog('Body1', 'Title');
        $entityManager->persist($blog1);
        $blog2 = new Blog('Body2', 'Title');
        $entityManager->persist($blog2);
        $blog3 = new Blog('Body3', 'Title');
        $entityManager->persist($blog3);
        $blog4 = new Blog('Body4', 'Title');
        $entityManager->persist($blog4);
        $entityManager->flush();

        $this->getContainer()->get('search_elasticsearch_client')->resetBulkCalls();

        $blogHandler = $this->getContainer()->get(BlogSearchHandler::class.'0');
        $this->getContainer()->get(PopulatorInterface::class)
            ->populate(
                $blogHandler,
                '_new',
                2
            );

        $expected = [
            [
                'body' => [
                    0 => [
                        'index' => [
                            '_index' => 'blog_new',
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
                            '_index' => 'blog_new',
                            '_type' => 'doc',
                            '_id' => '2'
                        ]
                    ],
                    3 => [
                        'body' => 'Body2',
                        'title' => 'Title'
                    ],
                ]
            ],
            [
                'body' => [
                    0 => [
                        'index' => [
                            '_index' => 'blog_new',
                            '_type' => 'doc',
                            '_id' => '3'
                        ]
                    ],
                    1 => [
                        'body' => 'Body3',
                        'title' => 'Title'
                    ],
                    2 => [
                        'index' => [
                            '_index' => 'blog_new',
                            '_type' => 'doc',
                            '_id' => '4'
                        ]
                    ],
                    3 => [
                        'body' => 'Body4',
                        'title' => 'Title'
                    ],
                ]
            ],
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
