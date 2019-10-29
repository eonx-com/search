<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects;

final class DocumentUpdate
{
    /**
     * The document to be written.
     *
     * @var mixed
     */
    private $document;

    /**
     * The id of the document.
     *
     * @var string
     */
    private $documentId;

    /**
     * The index the document will be written to.
     *
     * @var string
     */
    private $index;

    /**
     * Constructor.
     *
     * @param string $index
     * @param string $documentId
     * @param mixed $document
     */
    public function __construct(string $index, string $documentId, $document)
    {
        $this->index = $index;
        $this->documentId = $documentId;
        $this->document = $document;
    }

    /**
     * Returns the document to be indexed.
     *
     * @return mixed
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Returns the id of the document.
     *
     * @return string
     */
    public function getDocumentId(): string
    {
        return $this->documentId;
    }

    /**
     * Returns the index name to be used for the update.
     *
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }
}
