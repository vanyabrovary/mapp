<?php

namespace Tests\Feature\Api;

use Tests\Feature\TestCaseFeature;

class MyTest extends TestCaseFeature
{
    public function testGet()
    {
        // $this->withHeadersJSON()->json('GET', '/api/v1/my/get', [])->assertStatus(200);

        $res = $this->withHeadersJSON()->json('GET', '/api/v1/my/get', []);
        $arr = json_decode($res->content(), true);
        fwrite(STDOUT, var_dump($arr));

    }
}


