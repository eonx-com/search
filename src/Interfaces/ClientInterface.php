<?php
declare(strict_types=1);

namespace LoyaltyCorp\Search\Interfaces;

interface ClientInterface
{
    /**
     * Does a bulk delete action for all ids provided. Expects
     * the format to be :
     *
     * [
     *   'index_name' => [
     *     'ID1',
     *     'ID2'
     *   ],
     *   'index_name_2' => [
     *     'ID3',
     *   ]
     * ]
     *
     * @param string[][] $searchIds
     *
     * @return void
     */
    public function bulkDelete(array $searchIds): void;

    /**
     * Upserts all documents provided into the index.
     *
     * @param string $index
     * @param string[][] $documents
     *
     * @return void
     */
    public function bulkUpdate(string $index, array $documents): void;
}
