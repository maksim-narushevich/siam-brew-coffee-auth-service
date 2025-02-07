<?php

declare(strict_types=1);

namespace Tests\Integration\Auth;

use Symfony\Component\HttpFoundation\Response;
use tests\Integration\BaseWebTestCase;

describe('GET /users/{uuid}', function () {
    it('rejects for unauthorized', function () {
        $this->getJson(
            getUrl(BaseWebTestCase::GET_USER_BY_UUID_ROUTE_NAME, ['user' => 'test']),
        )
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson(
                [
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Unauthenticated.',
                ]
            );
    });

    it('returns not found for invalid UUID', function () {
        $this->getJson(
            getUrl(BaseWebTestCase::GET_USER_BY_UUID_ROUTE_NAME, ['user' => 'test']),
            headers: getAuthorizationHeader($this->token)
        )
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJson(
                [
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => 'Not found.',
                ]
            );
    })->group('with-auth');

    it('returns user data for valid UUID', function () {
        $this->getJson(
            getUrl(BaseWebTestCase::GET_USER_BY_UUID_ROUTE_NAME, ['user' => $this->user->uuid]),
            headers: getAuthorizationHeader($this->token)
        )
            ->assertOk()
            ->assertJsonPath('data.uuid', $this->user->uuid)
            ->assertJsonPath('data.first_name', $this->user->first_name)
            ->assertJsonPath('data.last_name', $this->user->last_name)
            ->assertJsonPath('data.role', $this->user->role)
            ->assertJsonPath('data.email', $this->user->email)
            ->assertJsonPath('data.address.country', $this->user->address->country);
    })->group('with-auth');
})->group('users');
