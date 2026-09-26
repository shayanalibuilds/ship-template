<?php

declare(strict_types=1);

test('health endpoint responds', function (): void {
    $this->get('/up')->assertStatus(200);
});
