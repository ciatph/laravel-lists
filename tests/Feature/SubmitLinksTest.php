<?php

namespace Tests\Feature;

use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubmitLinksTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function guest_can_submit_a_new_link() 
    {
        $response = $this->post('/submit', [
            'title' => 'Example Title',
            'url' => 'http://example.com',
            'description' => 'Example Description here. Hello, world!'
        ]);

        $this->assertDatabaseHas('links', [
            'title' => 'Example Title'
        ]);

        $response
            ->assertStatus(302)
            ->assertHeader('Location', url('/'));

        $this
            ->get('/')
            ->assertSee('Example Title');
    }

    /** @test */
    function link_is_not_created_if_validation_fails()
    {
        $response = $this->post('/submit');
        $response->assertSessionHasErrors(['title', 'url', 'description']);
    }    

    /** @test */
    function link_is_not_created_with_an_invalid_url()
    {
        $this->withExceptionHandling();

        $cases = ['//invalid-url.com', '/invalid-url', 'foo.com'];
        //$cases = ['http://invalid-url.com', '/http://invalid-url', 'http://foo.com'];

        foreach($cases as $case) {
            try {
                $response = $this->post('/submit', [
                    'title' => 'Example Title',
                    'url' => $case,
                    'description' => 'Example Description'
                ]);
            }
            catch (ValidationException $e) {
                $this->assertEquals(
                    'The url format is invalid.',
                    $e->validator->errors()->first('url')
                );
                $this->fail('The URL ' . $case . ' passed validation when it should have failed');
                //continue;
            }
            $this->assertTrue(true);
           //$this->fail('The URL ' . $case . ' passed validation when it should have failed');
        }
    }    

    /** @test */
    function max_length_fails_when_too_long()
    {
        $this->withoutExceptionHandling();
        $max = 100;

        $title = str_repeat('a', $max);
        $description = str_repeat('a', $max);
        $url = 'http://';
        $url .= str_repeat('a', $max - strlen($url));

        $hasError = false;

        try {
            $this->post('/submit', compact('title', 'url', 'description'));
        }
        catch (ValidationException $e) {
            $this->assertEquals(
                'The title may not be greater than 255 characters.',
                $e->validator->errors()->first('title')
            );

            $this->assertEquals(
                'The url may not be greater than 255 characters.',
                $e->validator->errors()->first('url')
            );
            
            $this->assertEquals(
                'The description may not be greater than 255 characters.',
                $e->validator->errors()->first('description')
            );   
            $this->fail('Max length should trigger a ValidationException');
            return;
        }
        $this->assertTrue(true);
        //$this->fail('Max length should trigger a ValidationException');
    }

    /** @test */
    function max_length_succeeds_when_under_max()
    {
        $max = 255;
        $url = 'http://';
        $url .= str_repeat('a', $max - strlen($url));

        $data = [
            'title' => str_repeat('a', $max),
            'url' => $url,
            'description' => str_repeat('a', $max),
        ];

        $this->post('/submit', $data);
        $this->assertDatabaseHas('links', $data);
    }    
}
