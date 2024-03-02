<?php

it('returns a 404 response on home page route', function () {
    $response = $this->get('/');
    $response->assertStatus(404);
});
