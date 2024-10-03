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
    

    // Creazione di un BATCH CHECK_CONFIG

    public function test_api_create_batch_by_post(): string
    {
        $this->faker = Faker::create('PostCommentTest');
        $batch_id = 'BATCH' . $this->faker->numberBetween($min = 1, $max = 2000);


        $response = $this->postJson('/api/batch', [
            'batch_uuid' => $batch_id,
            'batch_description' => $batch_id,
            'batch_action' => 'RUN',
            'batch_options' => '{"action_selected":0,"engines_selected":[1,44],"files_selected":[]}'
        ]);

        $data_decoded = json_encode($response);

        $response->assertStatus(200);

        return $batch_id;
           
    }
    
    // Run the batch newly just created

    /**
     * @depends test_api_create_batch_by_post
    */
    public function test_api_qmgr_by_post_check_config(string $batch_id): void    
    {

        // $this->faker = Faker::create('PostCommentTest');
        // $batch_id = 'BATCH' . $this->faker->numberBetween($min = 1, $max = 2000);

        $response = $this->postJson('/api/qmgr', [
            'BATCH_UUID' => $batch_id,
        ]);

        // dd($response->json());

        $response->assertStatus(200);

    }
   
 /* 
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
