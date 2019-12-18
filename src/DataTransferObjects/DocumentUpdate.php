<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\DataTransferObjects;

final class DocumentUpdate
{
    /**
     * The index action will index/update the document.
     *
     * @const string
     */
    public const ACTION_INDEX = 'index';

    /**
     * The index action will delete the document.
     *
     * @const string
     */
    public const ACTION_DELETE = 'delete';

    /**
     * @var string
     */
    private $action;

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
     * Constructor.
     *
     * @param string $action
     * @param string $documentId
     * @param mixed|null $document
     */
    public function __construct(string $action, string $documentId, $document = null)
    {
        $this->action = $action;
        $this->document = $document;
        $this->documentId = $documentId;
    }

    /**
     * Returns the action to be performed on the document.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Returns the document to be indexed.
     *
     * @return mixed|null
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
}
