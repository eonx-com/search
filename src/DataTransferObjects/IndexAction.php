<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects;

final class IndexAction
{
    /**
     * @var \LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate
     */
    private $documentUpdate;

    /**
     * @var string
     */
    private $index;

    /**
     * Constructor
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate $documentUpdate
     * @param string $index
     */
    public function __construct(DocumentUpdate $documentUpdate, string $index)
    {
        $this->documentUpdate = $documentUpdate;
        $this->index = $index;
    }

    /**
     * Returns the DocumentUpdate DTO.
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\DocumentUpdate
     */
    public function getDocumentUpdate(): DocumentUpdate
    {
        return $this->documentUpdate;
    }

    /**
     * Returns the index to be used for the document update.
     *
     * @return string
     */
    public function getIndex(): string
    {
        return $this->index;
    }
}
