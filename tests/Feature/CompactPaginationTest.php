<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class CompactPaginationTest extends TestCase
{
    public function test_pagination_renders_at_most_three_page_numbers_with_previous_and_next_controls(): void
    {
        foreach ([1, 5, 10] as $page) {
            $paginator = new LengthAwarePaginator(range(1, 10), 100, 10, $page);
            $html = view('vendor.pagination.compact', ['paginator' => $paginator])->render();
            $pageLinks = preg_match_all('/aria-label="Go to page \d+"/', $html);
            $currentPage = preg_match_all('/aria-current="page"/', $html);

            $this->assertSame(3, $pageLinks + $currentPage);
            $this->assertSame($page > 1, str_contains($html, 'rel="prev"'));
            $this->assertSame($page < 10, str_contains($html, 'rel="next"'));
        }
    }
}
