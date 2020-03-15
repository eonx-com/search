<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Factories;

use LoyaltyCorp\Search\Bridge\Symfony\Interfaces\RegisteredSearchHandlersFactoryInterface;
use LoyaltyCorp\Search\Helpers\RegisteredSearchHandlers;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;

final class RegisteredSearchHandlersFactory implements RegisteredSearchHandlersFactoryInterface
{
    /**
     * @var \Traversable<\LoyaltyCorp\Search\Interfaces\SearchHandlerInterface>
     */
    private $searchHandlers;

    /**
     * RegisteredSearchHandlersFactory constructor.
     *
     * @param \Traversable<\LoyaltyCorp\Search\Interfaces\SearchHandlerInterface> $searchHandlers
     */
    public function __construct(\Traversable $searchHandlers)
    {
        $this->searchHandlers = $searchHandlers;
    }

    /**
     * Create search RegisteredSearchHandlersInterface.
     *
     * @return \LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface
     */
    public function create(): RegisteredSearchHandlersInterface
    {
        // Because tagged services are wrapped in Symfony\Component\DependencyInjection\Argument\RewindableGenerator,
        // We need to convert iterator to array to get the actual instances.
        return new RegisteredSearchHandlers(\iterator_to_array($this->searchHandlers));
    }
}
