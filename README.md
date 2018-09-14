# list of links

A completed experimental demo app for testing laravel from [https://laravel-news.com/your-first-laravel-application](https://laravel-news.com/your-first-laravel-application).

Discusses database migrations with seeding, and phpunit testing. Displays the seeded links. Discusses an add item (title, url and description) method, but does not route it back to display (homescreen).

## Tools and Plugins

1. Composer
2. MySQL via xampp (7.2.9 / PHP 7.2.9)
2. VS Code for IDE
3. [Laravel Blade Snippets](https://marketplace.visualstudio.com/items?itemName=onecentlin.laravel-blade) as plugin for VS Code

## Usage

1. Clone this repository into your local directory.
2. Navigate to the cloned directory from the command line. Run `Composer install`.
3. Open `.env`. See `DB_DATABASE`. Create a `quickstart-demo` database in MySQL.
4. Run `php artisan make:migration`
5. Run this project: `php serve artisan`. Open the resulting URL to a web browser.


# Methods

## A. Prepare your Development Environment

1. Create a database that your laravel project will use in MySQL.

2. Create an empty laravel project<br>
`composer create-project laravel/laravel list 5.7.*`

3. Navigate into your project directory from the command line.

4. Edit the following codes in `.env. The following have been used from local xampp

		APP_NAME=<YOUR_APP_NAME>
		DB_DATABASE=<YOUR_DB_NAME>
		DB_USERNAME=root
		DB_PASSWORD=

5. Scaffold the authentication system: <br>
`php artisan make:auth`

6. If you are using Laravel 5.4.* and MySQL less than 5.5, do the following first: Edit *app/Providers/AppServiceProvider.php*
	
		use Illuminate\Support\Facades\Schema;
		public function boot()
		{
			Schema::defaultStringLength(191);
		}


## B. Model and Tables Initialization

1. **Create a migration:** build tables that your app will use. <br>
	`php artisan make:migration create_links_table --create=links`

	This will create a 	`/database/migrations/{{datetime}}_create_links_table.php` file. Modify this to contain the columns which you will put in MySQL table `link`.

		Schema::create('links', function(Blueprint $table){
			$table->increments('id');
			$table->string('title');
			$table->string('url')->unique();
			$table->text('description');
			$table->timestamps();
		});

	Save the file and run the migration: <br>
	`php artisan migrate`

	NOTE: When working with test data, you can quickly apply the schema: <br>
	`php artisan migrate:fresh`

2. **Create a Model** (*Link* factory) to interface with data. <br>
`php artisan make:model --factory Link`

	This will create a new blank factory file at `/database/factories/LinkFactory.php` Edit this file to contain the following:

		<?php
		use Faker\Generator as Faker;
		
		$factory->define(App\Link::class, function(Faker $faker) {
			return [
				'title' => substr($faker->sentence(2), 0, 1),
				'url' => $faker->url,
				'description' => $faker->paragraph
			];
		});

9. **Create a Link seeder** - to easily add demo data to the table.
`php artisan make:seeder LinksTableSeeder`

	This creates a new database seeder class to seed the `links` table. Modify it to contian the following:

		public function run() 
		{
			factory(App\Link::class, 5)->create();
		}

	"Activate" the LinksTableSeeder by calling it from `main/database/seeds/DatabaseSeeder.php`

		public function run()
		{
			$this->call(LinksTableSeeder::class);
		}

10. **Run the migration:** to insert demo data into the database.

	Start from a fresh (empty) database <br>
	`php artisan migrate:fresh --seed`

	Play with the *tinker shell* (queries) <br>
	`php artisan tinker`
	



## C. Routing and Views

We can use a **route closure** or a *dedicated controller class*. Routing reads the URL and redirects the app to a specific page.

1. Modify `/routes/web.php`. The key in the associative array becomes the *variable name* in the template file.

		Route::get('/', function() {
			$links = \App\link::all();
			return view('welcome', ['links' => $links]);
		});

	The following are *fluent API* alternatives to define variables:

		// with()
		return view('welcome')->with('links', $links);
		
		// dynamic method to name the variable
		return view('welcome')->withLinks($links);

2. Edit the `/resources/views/welcome.blade.php`, add a `foreach` loop to show all the links. Delete and replace the `<a>'s` inside `<div class="content">`.

		@foreach ($links as $link)
			<a href="{{ $link->url }}">{{ $link->title }}</a>
		@endforeach

3. Check if your seeded links are displaying. Run:
`php artisan serve`


## D. Displaying the Link Submission Form

1. Create a (stub, default) route for the submission form. Add in `/routes/web.php`.

		Route::get('/submit', function() {
			return view(submit);
		});

2. Create a submit form blade template at `/resources/views/submit.blade.php` (using bootstrap classes)

	Notes:

	-`$errors` - contains error exceptions and are live across all app pages <br>
	-`{{ $errors->first('title') }}` - returns the first error of a given field

	-If the user submits invalid data, the route will store validation in the session and redirect the user back to the form. The {{ old('title') }} function will populate the originally submitted data.

		@extends('layouts.app')
		
		@section('content')
		    <div class="container">
		        <div class="row">
		            <h1>Submit a Link</h1>
		
		            <form action="/submit" method="post">
		                @if ($errors->any())
		                    <div class="alert alert-danger" role="alert">
		                        Please fix the following errors:
		                    </div>
		                @endif
		
		                {!! csrf_field() !!}
		                <div class="form-group{{ $errors->has('title') ? 'has-error' : '' }}">
		                    <label for="title">Title</label>
		                    <input type="text" class="form-control" id="title" name="title" placeholder="Title" value="{{ old('title') }}">
		                    @if ($errors->has('title'))
		                        <span class="help-block">{{ $errors->first('title') }}</span>
		                    @endif
		                </div>
		
		                <div class="form-group{{ $errors->has('url') ? 'has-error' : '' }}">
		                    <label for="url">Url</label>
		                    <input type="text" class="form-control" id="url" name="url" placeholder="URL" value="{{ old('url') }}">
		                    @if ($errors->has('url'))
		                        <span class="help-block">{{ $errors->first('url') }}</span>
		                    @endif
		                </div>           
		                
		                <div class="form-group{{ $errors->has('description') ? 'has-error' : '' }}">
		                    <label for="description">Url</label>
		                    <input type="text" class="form-control" id="description" name="description" placeholder="description" value="{{ old('description') }}">
		                    @if ($errors->has('description'))
		                        <span class="help-block">{{ $errors->first('description') }}</span>
		                    @endif
		                </div>       
		                
		                <button type="submit" class="btn btn-default">Submit</button>
		            </form>
		        </div>
		    </div>
		@endsection

3. Handle the POST data. Update the submit form route from #1, add data validation and url redirection.

		use Illuminate\Http\Request;
		
		Route::post('/submit', function(Request, $request) {
			$data = $request->validate([
				'title' => 'required|max:255',
				'url' => 'required|max:255',
				'description' => 'required|max255'
			]);
		
			$link = tap(new App\Link($data))->save();
			return redirect('/');
		});

	The following is a long-way without using the `tap()` function

		$link = new \App\Link($data);
		$link->save();
		
		return $link;

4. Make the Model fields *"fillable"* via mass assignment. The fillable property prevents fields from being mass-assigned except for the items defined in the array. In our case, we are validating each field so allowing them to be mass-assigned is safe. 

	Allow our *Links* Model to assign values to these fields for them to be mass-fillable. Edit `/app/Link.php` to contain the following:	

		<?php
			namespace App;
		
			use Illuminate\Database\Eloquent\Model;
		
			class Link extends Model
			{
				protected $fillable = [
					'title',
					'url',
					'description'
				];
			}

	To prevent mass-assignment:

		$data = $request->validate([
			'title' => 'required|max:255',
			'url' => 'required|max:255',
			'description' => 'required|max:255'
		]);
		
		$link = new \App\Link;
		$link->title = $data['title'];
		$link->url = $data['url'];
		$link->description = $data['description'];
		
		$link->save();


## E. Testing the Form Submission

Perform integration tests against routes and middleware. Here we will write a few feature tests to verify our code works as expected without actually filling in the input fields in the submit form. We will test the `/submit` form throug HTTP requests to make sure that *route validation, saving and redirecting* are working as expected.

1. Change the database connection using environment variables. Open /`phpunit.xml` and add the following (if not, MySQL database wil be used):

		<php>
		    <env name="DB_CONNECTION" value="sqlite"/>
		    <env name="DB_DATABASE" value=":memory:"/>
		</php>

2. Remove the placeholder test that ships with Laravel: <br>
`rm tests/Feature/ExampleTest.php`

3. Create a new feature test to test the route: <br>
`php artisan make:test SubmitLinksTest`

	We will create scripts to test the following scenarios here:

		- verify that valid links get saved in the database
		- when validation fails, links are not in the database
		- Invalid URLs are not allowed
		- Validation should fail when the fields are longer than the `max:255` validation rule
		- validation should succeed when the fields are long enough according to `max:255`


4. **Test case: Saving a valid link** <br>
Write the following code:

	NOTES: `use RefreshDatabase;` makes each test has a new database environment with all the migrations.

	NOTES: If there is a warning: *Warning
No tests found in class "Tests\Feature\SubmitLinksTest"*, do the following: <br>
	-use the `/** @test */` annotation before the function <br>
	-make each test function name start with `function test_*`

	1. Submit a POST data.
	2. Verify the database now contains a record with the created title.
	3. Verify that the response was 302 and Location header pointing to the homepage
	4. Request and verify the homepage has the created title visible in it

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
					// Submit a POST data
			        $response = $this->post('/submit', [
			            'title' => 'Example Title',
			            'url' => 'http://example.com',
			            'description' => 'Example description.',
			        ]);
			
					// Verify that the database now contains a record with the created Title
			        $this->assertDatabaseHas('links', [
			            'title' => 'Example Title'
			        ]);
			
					// Verify that the response was a 302 status code + Location header pointing to homepage
			        $response
			            ->assertStatus(302)
			            ->assertHeader('Location', url('/'));
			
					// Request and verify the homepage has the link title visible 
			        $this
			            ->get('/')
			            ->assertSee('Example Title');
			    }
			}
	Run `vendor/bin/phpunit`to test.

4. **Test case: Testing Failed Validation** <br>
Write the following code:

		/** @test */
		function link_is_not_created_if_validation_fails()
		{
			$response = $this->post('/submit');
			$response->assertSessionHasErrors(['title', 'url', 'description']);
		}

	`assertSessionHasErrors()` makes sure that the session has validation errors for each of our required fields.

	Run `vendor/bin/phpunit`to test.


5. **Test case: Testing URL Validation** <br>
We expect only valid URL input fields to pass validation. Write the following code to deliberately test POSTing of invalid URLs.

	NOTES: `withoutExceptionHandling()` available in Laravel 5.5+ disables laravel's route exception handling code used to generate an HTTP response after an exception. We use this to inspect the validation exception object.

	The catch block uses the validator object to check the url error and asserts that the actual error message matches the expected validation error message.
	
	I like using the try/catch technique, followed by a $this->fail() as a safety harness instead of using exception annotations provided by PHPUnit. I feel catching the exception allows the ability to do assertions that wouldn’t otherwise be possible and provides a more granular control that I like in most cases. -author

	Run `vendor/bin/phpunit`to test.

		/** @test */
		function test_link_is_not_created_with_an_invalid_url()
		{
			$this->withoutExceptionHandling();
		
			$cases = ['//invalid-url.com', '/invalid-url', 'foo.com'];
		
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
					continue;
				}
		
				// auto-fail test
				$this->fail('The URL $case passed validation when it should have failed');
			}
		}


7. **Test case: Testing Max Length Validation** <br>
Test when POST fields exceed the `max:255` character length. Write the following codes for the max length scenario:

		/** @test */
		function max_length_fails_when_too_long()
		{
			$this->withoutExceptionHandling();
		
			$title = str_repeat('a', 256);
			$description = str_repeat('a', 256);
			$url = 'http://';
			$url .= str_repeat('a', 256 - strlen($url));
		
			try {
				$this->post('/submit', compact('title', 'url', 'description'));
			}
			catch (ValidationExceptopm $e) {
				$this->assertEquals(
					'The title may not be greater than 255 characters',
					$e->validator->errors()->first('title')
				);
		
				$this->assertEquals(
					'The url may not be greater than 255 characters',
					$e->validator->errors()->first('url')
				);
		
				$this->assertEquals(
					'The description may not be greater than 255 characters',
					$e->validator->errors()->first('description')
				);
		
				return;
			}
		
			$this->fail('Max length should trigger a ValidationException');
		}

	Run `vendor/bin/phpunit`to test.


8. **Test case: Input passed the 'under the max' scenario** <br>
Write the following codes:


		/** @test */
		function max_length_succeeds_when_under_max()
		{
			$url = 'http://';
			$url .= str_repeat('a', 255 - strlen($url));
		
			$data = [
				'title' => str_repeat('a', 255),
				'url' => $url,
				'description' => str_repeat('a', 255),
			];
		
			$this->post('/submit', $data);
			$this->assertDatabaseHas('links', $data);
		}

	Run `vendor/bin/phpunit`to test.


**Date Created:** 20180913 <br>
**Date Modified:** 20180914