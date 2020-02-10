<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Tests\LoyaltyCorp\Search\Integration\Fixtures\Repositories\FillableRepository")
 */
class Comment
{
    /**
     * @ORM\ManyToOne(targetEntity="Blog")
     *
     * @var \Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities\Blog
     */
    private $blog;

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
     * Constructor
     *
     * @param \Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities\Blog $blog
     * @param string $body
     */
    public function __construct(Blog $blog, string $body)
    {
        $this->blog = $blog;
        $this->body = $body;
    }

    /**
     * Gets comment blog.
     *
     * @return \Tests\LoyaltyCorp\Search\Integration\Fixtures\Entities\Blog
     */
    public function getBlog(): Blog
    {
        return $this->blog;
    }

    /**
     * Gets comment body.
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Gets comment id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Sets comment body.
     *
     * @param string $body
     *
     * @return void
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }
}
