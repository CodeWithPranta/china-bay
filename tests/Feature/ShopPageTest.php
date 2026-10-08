<?php

test('shop page uses mobile filter drawer instead of inline toggle', function () {
    $response = $this->get('/shop');

    $response->assertStatus(200);
    // Slide-over drawer state + active-filter badge + drawer actions
    $response->assertSee('filtersOpen', false);
    $response->assertSee('filterCount', false);
    $response->assertSee('clearFilters', false);
    $response->assertSee('Show results', false);
    // New card template uses logical properties (RTL-safe)
    $response->assertSee('absolute start-2 top-2', false);
    $response->assertSee('absolute end-2 top-1.5', false);
});

test('shop page has responsive grid and toolbar', function () {
    $response = $this->get('/shop');

    $response->assertStatus(200);
    $response->assertSee('id="shopGrid"', false);
    $response->assertSee('grid-cols-2', false);
    $response->assertSee('id="sortSel"', false);
});

test('category pills share one chip shape with snap scroll', function () {
    $response = $this->get('/shop');

    $response->assertStatus(200);
    $response->assertSee('data-pill="all"', false);
    $response->assertSee('snap-start', false);
    $response->assertSee('shrink-0', false);
});
