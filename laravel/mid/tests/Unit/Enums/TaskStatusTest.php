<?php

use App\Enums\TaskStatus;

it('recognises open statuses', function () {
    expect(TaskStatus::Todo->isOpen())->toBeTrue()
        ->and(TaskStatus::InProgress->isOpen())->toBeTrue()
        ->and(TaskStatus::Completed->isOpen())->toBeFalse()
        ->and(TaskStatus::Cancelled->isOpen())->toBeFalse();
});

it('exposes label options for every case', function () {
    $options = TaskStatus::options();

    expect($options)->toHaveCount(count(TaskStatus::cases()))
        ->and($options['todo'])->toBe('To Do');
});
