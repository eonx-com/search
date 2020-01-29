<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects;

/**
 * This DTO indicates that a document should be inserted or updated in the search index.
 */
final class DocumentUpdate extends DocumentAction
{
    /**
     * The document body to be written.
     *
     * @var mixed
     */
    private $document;

    /**
     * Constructor.
     *
     * @param string $documentId
     * @param mixed|null $document
     */
    public function __construct(string $documentId, $document = null)
    {
        parent::__construct($documentId);

        $this->document = $document;
    }

    /**
     * {@inheritdoc}
     */
    public static function getAction(): string
    {
        return 'index';
    }

    /**
     * Returns the document body to be indexed.
     *
     * @return mixed|null
     */
    public function getDocument()
    {
        return $this->document;
    }
}
