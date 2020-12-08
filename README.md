# Statable trait for Laravel Eloquent models

![Run tests](https://github.com/iben12/laravel-statable/workflows/Run%20tests/badge.svg?event=push)
[![StyleCI](https://github.styleci.io/repos/158932879/shield?branch=master)](https://github.styleci.io/repos/158932879)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/iben12/laravel-statable/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/iben12/laravel-statable/?branch=master)

This trait provides drop-in functionality to manage state and state history of an existing Eloquent Model based on [winzou/state-machine](https://github.com/winzou/state-machine) using [sebdesign/laravel-state-machine](https://github.com/sebdesign/laravel-state-machine) service provider.

## Installation

Compatibility:
* `v0.1` requires `sebdesign/laravel-state-machine:^1.3` and compatible with Laravel < 5.5
* `v1.0` requires `sebdesign/laravel-state-machine:^2.0` and compatible with Laravel 5.5+
* `v1.4` requires `sebdesign/laravel-state-machine:^3.0` and compatible with Laravel 7+

So if you are below Laravel 5.5, require `0.1` version explicitly. For Laravel below 7 require version `v1.3`.

Use composer to pull in the package:
```
$ composer require iben12/laravel-statable
```
Publish the database migration and state machine config:
```
$ php artisan vendor:publish --provider="Iben\Statable\ServiceProvider"
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

        'property_path' => 'last_state', // should exist on model

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
                'from' => ['draft', 'published'],
                'to' => 'archived'
            ],
            'unarchive' => [
                'from' => ['archived'],
                'to' => 'draft'
            ]
        ],
        'callbacks' => [
            'after' => [
                'history' => [
                    'do' => 'StateHistoryManager@storeHistory'
                ]
            ]
        ]
    ]
]

```

Now you have to edit the `Post` model:
```php
namespace App;

use \Illuminate\Database\Eloquent\Model;
use \Iben\Statable\Statable;

class Post extends Model
{
    use Statable;

    protected function getGraph()
    {
    	return 'post'; // the SM config to use
    }
}
```

And that's it!

#### Usage
You can now access the following methods on your model:
```php
$post = \App\Post::first();

$post->last_state; // returns current state

try {
    $post->apply('publish'); // applies transition
} catch (\SM\SMException $e) {
    abort(500, $e->getMessage()); // if transition is not allowed, throws exception
}

$post->canApply('publish'); // returns boolean

$post->stateHistory()->get(); // returns PostState collection for the given Post

$post->stateHistory()->where('user_id', \Auth::id())->get(); // you can query history as any Eloquent relation
```

NOTE: The history saves the currently authenticated user, when applying a transition. This makes sense in most cases, but if you do not use the default Laravel authentication you can override the `getActorId` method to store the user with the history.

```php
class Post extends Model
{
    // ...

    public function getActorId()
    {
        // return id;
    }
}
```
If the model is newly created (never been saved), so it does not have an `id` when applying
a transition, history will not be saved. If you want to be sure that all transitions
are saved in history, you can add this method to your model:
```php
    protected function saveBeforeTransition()
    {
        return true;
    }
```

## State machine

[sebdesign/laravel-state-machine](https://github.com/sebdesign/laravel-state-machine)
provides a lot of features:
* using Gates and Policies
* Events
* callbacks for guards or other tasks

You can find the documentation [in the repo](https://github.com/sebdesign/laravel-state-machine).

If you want to interact directly with the `StateMachine` object of your model, call `$model->stateMachine()`.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
