# PathConfig

PathConfig allows a single point of configuration for your application's paths.

The library's main purpose it to allow restructuring of applications, working around framework's existing limitations.

The animation below demonstrates restructuring a Laravel 5 project to silo less-used components to a "support" folder:

![path-config](https://cloud.githubusercontent.com/assets/132681/10692334/bd8cc5ac-7988-11e5-8c25-513a01e03f54.gif)

The library provides both helper classes and Application class stubs to facilitate this.

## Features

- A single root-level configuration file of project paths
- Named root-relative paths such as `config` or `storage`
- Ability to add additional paths programatically
- Integration with existing frameworks (Lumen and Laravel currently supported)

## Configuration

Create a `paths.php` file in your project's root folder.

Return from it a single array with as many key/path pairs as your application requires:

```php
return array
(
    'app'       => 'app',
    'config'    => 'resources/config',
    'storage'   => 'storage',
);
```

All paths should be **relative** to the project root, which is determined separately.

Example configurations for Laravel 5.x and Lumen 5.x are available in the `config/` folder.

For configuration-free instantiation, it is important that the config file remain in the root of the project!

## Usage

There are 2 ways to use PathConfig:
 
1. Standalone
2. With Laravel or Lumen


### Standalone

Load the PathConfig instance like so:

```php
$paths = pathconfig\PathConfig::instance()->load();
```

If a the `basepath()` option has not been set (the default) the library will search up the folder tree from it's `vendor` folder and look for the `paths.php` configuration file.

As soon as this has loaded, you're free to call `get()` methods on the `$paths` object as required.

### With Laravel or Lumen

Assuming you have already edited your configuration file, there are 4 steps you need to take to refactor your app:
 
1. Replace the default Application instance 
2. Physically move the framework folders
3. Update some key path references
4. Dump Composer's autoload

**Firstly**, you'll need to swap out your existing `Illuminate` Application class, with a `pathconfig` version that implements the new centralised path functionality, and overrides existing path-related methods. 

In your application's `bootstrap/app.php` (or equivilent) file, replace the existing `$app` instantiation like so:

```php
$app = new pathconfig\apps\Laravel50();
```

Make sure to use the appropriate class for your framework and version.

**Secondly**, make sure you physically *move* the folders on your hard disk, reflecting the paths in your config file.

**Thirdly**, update any framework files that may refer to your moved folders:

File > location| Search | Replace
:-- | :-- | :--
artisan | bootstrap/ | *path to bootstrap*
public/index.php | ../bootstrap/ | *path to bootstrap*
bootstrap/autoload.php | ../vendor/ | *path to vendor*
bootstrap/app.php | $app = new Illuminate\Foundation\Application(...); | $app = new pathconfig\apps\ &lt;Class&gt; ;
composer.json > autoload | database | *path to database*
composer.json > autoload-dev | tests/ | *path to tests*

**Finally**, dump Composer's autoload with `composer dump-autoload`.
 
At this point, you should be able to reload your application, and everything should just work.

If your app errors, double-check your path edits, and make sure they are correct.

## Getting and setting paths

Get paths directly from instance using the `get()` method:

```php
$config = $paths->get('config');

//  /home/vagrant/code/project.app/support/config/
```

To resolve an additional filepath (which also resolves any ../ references) add it as the second argument:

```php
$file = $paths->get('config', 'path/to/file.php');

//  /home/vagrant/code/project.app/support/config/path/to/file.php
```

Passing a single argument that is NOT a key resolves the path from the base folder:

```php
$file = $paths->get('path/to/file/from/root.php');

//  /home/vagrant/code/project.app/path/to/file/from/root.php
```
Passing no arguments returns the base folder:

```php
$root = $paths->get();

//  /home/vagrant/code/project.app/
```

To get all paths, call `all()` method:

```php
$root = $paths->all();

//  Array
//  (
//      [base] => /home/vagrant/code/project.app
//      [app] => /home/vagrant/code/project.app/app
//      [public] => /home/vagrant/code/project.app/public
//      [routes.php] => /home/vagrant/code/project.app/app/Http/routes.php
//  )
```

Pass `false` to return relative paths.

Set additional paths new path using `set()`:

```php
$paths->set('plugins', 'support/plugins/');
```

## Options

There are various options to control the loading and conversion of paths.

By default they are set to mimic PHP's `realpath()` which outputs differently depending on the platform it's running on.

### Set an alternative base path

To set a new base path:

```php
$paths->option('basepath', $value);
```

The base path can only be set before paths are loaded.

### Convert slashes

To automatically convert slashes (on by default):

```php
$paths->option('convertslashes', true);
```

There are 3 options which include conversion of the base path, configured paths, and generated paths:

1. true: convert all slashes to the platform type
2. false: don't convert any slashes
3: 'auto' convert all slashes to the first slash type found in the config file


### Trim trailing folder slashes

Some frameworks expect trailing slashes, some don't. To preserve trailing slashes passed in the config file (they are trimmed by default), call:

```php
$paths->option('trimslashes', false);
```

### Test paths exist

By default the library doesn't test passed in paths to see if they exist, however PHP's realpath does. To mimic this behaviour call:

```php
$paths->option('testpaths', true);
```

### Allow paths to be set more than once

The library allows only allows you to set paths one, but you can override this by setting the `mutable` option to false:

```php
$paths->option('mutable', 'false');
```

## Additional info 

### To alias the get() method as a global function

It might be incovenient to pass around a `$paths` variable. You can alias the method with a helper in one of two ways:

#### Manually:

```php
function path($key = '', $filepath = '')
{
    return pathconfig\PathConfig::instance()->get($key, $filepath);
}
```

#### Using the static `alias()` method:

```php
pathconfig\PathConfig::alias('path');
```

Both methods will create a global helper method called `path()` than you can use from anywhere:

```php
$path = path('config', 'email.php');
```


### To load paths from a custom location

If you want to move your paths config file to a custom location, you'll need to set both the basepath and config paths manually: 
 
```php
pathconfig\PathConfig::instance()
    ->option('basepath', __DIR__ . '/../../')
    ->load('config/paths.php');
```

Note that base paths should be absolute, but the config path:
 
 - can be absolute
 - can be relative from the base path location
 - can be just the folder reference (will default to `paths.php`)
 - can be the path to the filename such as `my-paths.php`

### How PathConfig works

Once the library is loaded, it will:
 
- add that folder to the config as the `base` path
- load the configured paths
- add them internally, converting and trimming slashes depending on the options set
