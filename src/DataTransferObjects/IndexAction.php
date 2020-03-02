<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects;

/**
 * The DTO represents an action that will be performed (a DocumentAction) against a specific index.
 */
final class IndexAction
{
    /**
     * @var \LoyaltyCorp\Search\DataTransferObjects\DocumentAction
     */
    private $documentAction;

    /**
     * @var string
     */
    private $index;

    /**
     * Constructor.
     *
     * @param \LoyaltyCorp\Search\DataTransferObjects\DocumentAction $documentAction
     * @param string $index
     */
    public function __construct(DocumentAction $documentAction, string $index)
    {
        $this->documentAction = $documentAction;
        $this->index = $index;
    }

    /**
     * Returns the DocumentAction DTO.
     *
     * @return \LoyaltyCorp\Search\DataTransferObjects\DocumentAction
     */
    public function getDocumentAction(): DocumentAction
    {
        return $this->documentAction;
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
