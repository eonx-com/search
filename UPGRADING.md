# Upgrading

## 2.0

- The `EntitySearchHandlerInterface` no longer exists, and should be replaced with `TransformableSearchHandlerInterface`
- This interface defines an additional method `getFillIterable()` which should return an iterable of all objects that are intended to fill the search index.
  - For Doctrine entities, defining a method on the entities' repository that will return the iterable that can be used by the search handler:
  
```php
// EntityRepository.php
    public function getFillIterable(): iterable
    {
        $builder = $this->createQueryBuilder('e');
        foreach ($builder->getQuery()->iterate() as $result) {
            yield $result[0];
        }
    }

// AbstractSearchHandler.php
    public function getFillIterable(): iterable
    {
        $repository = $this->entityManager->getRepository($this->entityClass);
        if ($repository instanceof MultitenancyRepositoryInterface === false) {
            throw new UnsupportedRepositoryException(
                'exceptions.services.search.handlers.unsupported_repository'
            );
        }

        /**
         * @var \App\Database\Repositories\MultitenantRepository $repository
         *
         * @see https://youtrack.jetbrains.com/issue/WI-37859 - typehint required until PhpStorm recognises === check
         */

        return $repository->getFillIterable();
    }
```
