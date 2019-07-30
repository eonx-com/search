<?php
declare(strict_types=1);

namespace Tests\LoyaltyCorp\Search\Stubs\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class EntityStub
{
    /**
     * Coupon ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="string", name="id")
     *
     * @var string
     */
    protected $identifier;

    /**
     * Getter for primary key
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Setter for primary key
     *
     * @param string $identifier
     *
     * @return \Tests\LoyaltyCorp\Search\Stubs\Entities\EntityStub
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }
}
