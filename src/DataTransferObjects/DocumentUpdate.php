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
     * Stores additional keys that may need to be added to a document after the
     * search handler has created the DocumentUpdate DTO.
     *
     * Extra keys will not be added to the document if the document already contains
     * the key.
     *
     * @var mixed[]
     */
    private $extra = [];

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
     * Adds an extra key to the document.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function addExtra(string $key, $value): void
    {
        $this->extra[$key] = $value;
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

    /**
     * Returns extra fields for the document.
     *
     * @return mixed[]
     */
    public function getExtra(): array
    {
        return $this->extra;
    }
}
