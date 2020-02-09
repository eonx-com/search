<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Tests\LoyaltyCorp\Search\Integration\Fixtures\Repositories\FillableRepository")
 */
class Blog
{
    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $body;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $title;

    /**
     * Constructor
     *
     * @param string $body
     * @param string $title
     */
    public function __construct(string $body, string $title)
    {
        $this->body = $body;
        $this->title = $title;
    }

    /**
     * Gets the body.
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Return id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the body.
     *
     * @param string $body
     *
     * @return void
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Sets the title.
     *
     * @param string $title
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
