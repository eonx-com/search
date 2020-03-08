<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Bridge\Symfony\Factories;

use LoyaltyCorp\Search\Bridge\Symfony\Interfaces\RegisteredSearchHandlersFactoryInterface;
use LoyaltyCorp\Search\Helpers\RegisteredSearchHandlers;
use LoyaltyCorp\Search\Interfaces\Helpers\RegisteredSearchHandlersInterface;

final class RegisteredSearchHandlersFactory implements RegisteredSearchHandlersFactoryInterface
{
    /**
     * @var iterable<\LoyaltyCorp\Search\Interfaces\SearchHandlerInterface>
     */
    private $searchHandlers;

    /**
     * RegisteredSearchHandlersFactory constructor.
     *
     * @param iterable<\LoyaltyCorp\Search\Interfaces\SearchHandlerInterface> $searchHandlers
     */
    public function __construct(iterable $searchHandlers)
    {
        $this->searchHandlers = $searchHandlers;
    }

    /**
     * @inheritDoc
     */
    public function create(): RegisteredSearchHandlersInterface
    {
        // Because tagged services are wrapped in Symfony\Component\DependencyInjection\Argument\RewindableGenerator,
        // We need to iterate to get the actual instance.
        $searchHandlers = [];
        foreach ($this->searchHandlers as $searchHandler) {
            $searchHandlers[] = $searchHandler;
        }

        return new RegisteredSearchHandlers($searchHandlers);
    }
}
