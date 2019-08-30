<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search;

use LoyaltyCorp\Search\Interfaces\TransformableSearchHandlerInterface;
use LoyaltyCorp\Search\Interfaces\TransformerInterface;
use Traversable;

class Transformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function bulkTransform(
        TransformableSearchHandlerInterface $handler,
        iterable $objects
    ): Traversable {
        foreach ($objects as $object) {
            $searchId = $handler->getSearchId($object);

            if ($searchId === null) {
                // the handler didnt generate a search id
                continue;
            }

            $document = $handler->transform($object);

            if ($document === null) {
                // no search document was generated
                continue;
            }

            yield $searchId => $document;
        }
    }
}
