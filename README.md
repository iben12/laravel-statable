# Statable trait for Laravel Eloquent models

This trait provides drop-in functionality to manage state and state history of an existing Eloquent Model based on [winzou/state-machine](https://github.com/winzou/state-machine) using [sebdesign/laravel-state-machine](https://github.com/sebdesign/laravel-state-machine) service provider.

## Installation

Use composer to pull in the package:
```
$ composer require iben12/laravel-statable
```
Publish the database migration and state machine config:
```
$ php artisan vendor:publish --provider="Iben\Statable\ServiceProvider" --tag="migrations"
$ php artisan vendor:publish --provider="Sebdesign\SM\ServiceProvider" --tag="config" 
```
Migrate the database:
```
$ php artisan migrate
```
This migration creates the table for storing history of your models as a polymorphic relation.

## Usage

#### Prerequisites
* Model class with some property holding state (we use `last_state` in the example)

#### Setup

For this manual we will use a `Post` model as example.

First you configure the SM graph. Open `config/state-machine.php` and define a new graph:
```php
return [
    'post' => [
        'class' => App\Post::class,
        'graph' => 'post',

        'property_path': 'last_state',

        'states' => [
            'draft',
            'published',
            'archived'
        ],
        'transitions' => [
            'publish' => [
                'from' => ['draft'],
                'to' => 'published'
            ],
            'unpublish' => [
                'from' => ['published'],
                'to' => 'draft'
            ],
            'archive' => [
                'from' => ['published'],
                'to' => 'archived'
            ],
            'unarchive' => [
                'from' => ['archived'],
                'to' => 'published'
            ]
        ],
        'callbacks' => [
            'history' => [
                'do' => 'Sebdesign\SM\Services\StateHistoryManager@storeHistory'
            ]
        ]
    ]
]

```

Now you have to edit the `Post` model:
```php
namespace App;

use \Illuminate\Database\Eloquent\Model;
use \Sebdesign\SM\Traits\Statable;

class Post extends Model
{
    use Statable;

    protected function getGraph()
    {
    	retrun 'post'; // the SM config to use
	}
}
```

And that's it!

#### Usage
You can now access the following methods on your entity:
```php
$post = \App\Post::first();

$post->last_state; // returns current state

try {
    $post->transition('publish'); // applies transition
} catch (\SM\SMException $e) {
    abort(500, $e->getMessage()); // if transition is not allowed, throws exception
}

$post->transitionAllowed('publish'); // return boolean

$post->history()->get(); // returns PostState collection for the given Post

$post->history()->where('user_id', \Auth::id())->get(); // you can query history as any Eloquent relation
```

NOTE: The history saves the currently authenticated user, when applying a transition. This makes sense in most cases, but if you do not use the default Laravel authentication you can override the `getActorId` method to store the user with the history.

```php
class Post extends Model
{
	// ...
	
	public function getActorId()
	{
		// return user id;
	}
}
```

### Debug command

An artisan command for debugging graphs is included. It accepts the name of the graph as an argument. If no arguments are passed, the graph name will be asked interactively.

```bash
$ php artisan winzou:state-machine:debug simple

+--------------------+
| Configured States: |
+--------------------+
| new                |
| pending_review     |
| awaiting_changes   |
| accepted           |
| published          |
| rejected           |
+--------------------+
+-----------------+------------------+------------------+
| Transition      | From(s)          | To               |
+-----------------+------------------+------------------+
| create          | new              | pending_review   |
+-----------------+------------------+------------------+
| ask_for_changes | pending_review   | awaiting_changes |
|                 | accepted         |                  |
+-----------------+------------------+------------------+
| cancel_changes  | awaiting_changes | pending_review   |
+-----------------+------------------+------------------+
| submit_changes  | awaiting_changes | pending_review   |
+-----------------+------------------+------------------+
| approve         | pending_review   | accepted         |
|                 | rejected         |                  |
+-----------------+------------------+------------------+
| publish         | accepted         | published        |
+-----------------+------------------+------------------+
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email info@sebdesign.eu instead of using the issue tracker.

## Credits

- [Alexandre Bacco](https://github.com/winzou)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
