<?php

test('header has manual and AI search modes', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('data-mode-btn="manual"', false);
    $response->assertSee('data-mode-btn="ai"', false);
    $response->assertSee('ai-search-open', false);
    // Manual search still submits to shop
    $response->assertSee('action="/shop"', false);
});

test('AI assistant modal is present on storefront', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('cbAiAssistant', false);
    $response->assertSee('Bay AI', false);
    $response->assertSee('role="dialog"', false);
});

test('voice typing buttons exist on manual and AI search', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('data-voice', false);
});
