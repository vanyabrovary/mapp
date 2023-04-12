<?php

namespace Tests\Feature;

use Tests\TestCase;

abstract class TestCaseFeature extends TestCase
{
    const ARGS = [];

    protected function withHeadersJSON():object
    {
        return $this->withHeaders(
            ['Content-Type' => 'application/json', 'Accept' => 'application/json']
        );
    }

    protected function withHeadersAuth(string $token)
    {
        return $this->withHeaders(
            ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token]
        );
    }
/*
    protected function withHeadersAuthenUser()
    {
        // delete users with real email from database
        $this->caseModelsUserDelete();

        // Insert into database user with real email
        $user = User::create(
            self::ARGS['register']['real'] +
            [ 'email' => self::ARGS['login']['real']['email'], 'password' => self::ARGS['login']['real']['password'], 'verified' => 1 ]
        )->save();
        
        //  Predicate, which activate removing created user model, after tests
        if ($user) {
            $this->needToDeleteUserModelPredicate = true;
        }

        // Login user
        $res = $this->caseReqLogin(self::ARGS["login"]["real"]);

        // Get token
        $arr = json_decode($res->content(), true);

        // Return headers with 'Bearer token'
        return $this->withHeadersAuth($arr['data']['token']);
    }

    protected function caseReqLogin($args, $method = 'POST')
    {
        return $this->withHeadersJSON()->json($method, '/api/v1/login', $args);
    }

    protected function tearDown():void
    {
        // If user account was created for testing by child class
        // if ($this->needToDeleteUserModelPredicate) {
            // Remove user account which was created for testing
        //    $this->caseModelsUserDelete();
        // }
    }
*/

}
