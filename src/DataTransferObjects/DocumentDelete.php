<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects;

/**
 * This DTO indicates that the document should be deleted.
 */
final class DocumentDelete extends DocumentAction
{
    /**
     * {@inheritdoc}
     */
    public static function getAction(): string
    {
        return 'delete';
    }
}
