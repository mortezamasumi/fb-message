<?php

use Illuminate\Support\Facades\Gate;
use Mortezamasumi\FbMessage\Policies\FbMessagePolicy;
use Mortezamasumi\FbMessage\Tests\Services\FbMessage;
use Mortezamasumi\FbMessage\Tests\Services\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('grants every action when the user has the matching permission', function () {
    foreach ([
        'ViewAny:FbMessage',
        'View:FbMessage',
        'Create:FbMessage',
        'Delete:FbMessage',
        'Forward:FbMessage',
        'Reply:FbMessage',
        'Archive:FbMessage',
        'Trash:FbMessage',
    ] as $ability) {
        Gate::define($ability, fn () => true);
    }

    $policy = new FbMessagePolicy;
    $message = FbMessage::factory()->create();

    expect($policy->viewAny($this->user))->toBeTrue();
    expect($policy->view($this->user, $message))->toBeTrue();
    expect($policy->create($this->user))->toBeTrue();
    expect($policy->delete($this->user, $message))->toBeTrue();
    expect($policy->forward($this->user))->toBeTrue();
    expect($policy->reply($this->user))->toBeTrue();
    expect($policy->archive($this->user))->toBeTrue();
    expect($policy->trash($this->user))->toBeTrue();
});

it('denies every action when the user lacks the permission', function () {
    $policy = new FbMessagePolicy;
    $message = FbMessage::factory()->create();

    expect($policy->viewAny($this->user))->toBeFalse();
    expect($policy->view($this->user, $message))->toBeFalse();
    expect($policy->create($this->user))->toBeFalse();
    expect($policy->delete($this->user, $message))->toBeFalse();
    expect($policy->forward($this->user))->toBeFalse();
    expect($policy->reply($this->user))->toBeFalse();
    expect($policy->archive($this->user))->toBeFalse();
    expect($policy->trash($this->user))->toBeFalse();
});
