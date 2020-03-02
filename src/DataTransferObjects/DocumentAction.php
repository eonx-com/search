<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects;

/**
 * This DTO represents an action that will be taken for a document in the search
 * indices.
 */
abstract class DocumentAction
{
    /**
     * The id of the document.
     *
     * @var string
     */
    private $documentId;

    /**
     * Constructor.
     *
     * @param string $documentId
     */
    public function __construct(string $documentId)
    {
        $this->documentId = $documentId;
    }

    /**
     * Returns the action constant to be used when processing this action.
     *
     * @return string
     */
    abstract public static function getAction(): string;

    /**
     * Returns the id of the document.
     *
     * @return string
     */
    public function getDocumentId(): string
    {
        return $this->documentId;
    }
}
