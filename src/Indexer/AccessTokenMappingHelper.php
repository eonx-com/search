<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Indexer;

use LoyaltyCorp\Search\Exceptions\InvalidMappingException;
use LoyaltyCorp\Search\Interfaces\CustomAccessHandlerInterface;
use LoyaltyCorp\Search\Interfaces\Indexer\MappingHelperInterface;
use LoyaltyCorp\Search\Interfaces\SearchHandlerInterface;

final class AccessTokenMappingHelper implements MappingHelperInterface
{
    /**
     * Defines the property that is used to store access tokens in the document
     * inside elasticsearch.
     *
     * Any proxying code should make a best effort to remove this property from being
     * returned to the user.
     *
     * @const string
     */
    public const ACCESS_TOKEN_PROPERTY = '_access_tokens';

    /**
     * {@inheritdoc}
     *
     * @throws \LoyaltyCorp\Search\Exceptions\InvalidMappingException
     */
    public function buildIndexMappings(SearchHandlerInterface $searchHandler): array
    {
        $mappings = $searchHandler::getMappings();

        // If the search handler implements CustomAccessHandlerInterface it is indicating
        // it does its own thing regarding access control.
        if ($searchHandler instanceof CustomAccessHandlerInterface === true) {
            return $mappings;
        }

        // Add our extra mappings for access control.
        return $this->addAccessMappings($mappings);
    }

    /**
     * Adds access mappings to the index.
     *
     * @param mixed[] $mappings
     *
     * @return mixed[]
     *
     * @throws \LoyaltyCorp\Search\Exceptions\InvalidMappingException
     */
    private function addAccessMappings(array $mappings): array
    {
        // Find what should be the only key in the mappings array root,
        // which is the type name (a deprecated elasticsearch feature)
        $key = \array_key_first($mappings);

        // If there is no key, or we have more than one entry in mappings
        // we've got something we dont know how to modify.
        if ($key === null || \count($mappings) !== 1) {
            throw new InvalidMappingException(
                'Unknown mapping format. Mapping must return a multidimensional array with a single key.'
            );
        }

        // Add a property for access tokens
        return \array_merge_recursive($mappings, [
            $key => [
                'properties' => [
                    self::ACCESS_TOKEN_PROPERTY => [
                        'type' => 'keyword',
                    ],
                ],
            ],
        ]);
    }
}
