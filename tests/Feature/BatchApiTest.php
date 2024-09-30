<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use Illuminate\Testing\Fluent\AssertableJson;

use Faker\Factory as Faker;

class BatchApiTest extends TestCase
{

    public $faker;

    /**
     * A basic test example.
     */

/*     
    public function test_api_get_batch_list(): void
    {
        $response = $this->get('/api/coda/show');
        $response->assertStatus(200);
    }

    public function test_api_get_batch_by_id(): void
    {
        $batch_uuid = 'BATCH1767';
        $response = $this->get('/api/batch/' . $batch_uuid);
        $response
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
            $json->has(1)
                 ->first(fn (AssertableJson $json) =>
                    $json->where('batch_uuid', 'BATCH1767')
                         ->etc()
                 )
            );
          
    }
*/
    

    public function test_api_create_batch_by_post(): void
    {
        $this->faker = Faker::create('PostCommentTest');
        $batch_id = 'BATCH' . $this->faker->numberBetween($min = 1, $max = 2000);


        $response = $this->postJson('/api/batch', [
            'batch_uuid' => $batch_id,
            'batch_description' => $batch_id,
            'batch_action' => 'CHECK_CONFIG',
            'batch_options' => '{}'
        ]);

        $data_decoded = json_encode($response);

        $response->assertStatus(200);
           
    }
    
/*
    
    public function test_api_qmgr_by_post_check_config(): void    
    {

        $this->faker = Faker::create('PostCommentTest');
        $batch_id = 'BATCH' . $this->faker->numberBetween($min = 1, $max = 2000);

        $response = $this->postJson('/api/qmgr', [
            'QMGR_ACTION' => 'CHECK_CONFIG',
            'batch_uuid' => $batch_id,
        ]);

        dd($response->json());

        $response->assertStatus(200);

    }
    

    public function test_api_qmgr_by_post_error_501(): void    
    {

        $this->faker = Faker::create('PostCommentTest');
        $batch_id = 'BATCH' . $this->faker->numberBetween($min = 1, $max = 2000);

        $response = $this->postJson('/api/qmgr', [
            'QMGR_ACTION' => 'DEMO',
            'batch_uuid' => $batch_id,
        ]);

        // dd($response);

        $response->assertStatus(501);

    }

    */
}
