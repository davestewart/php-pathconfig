# PathConfig

PathConfig provides programatic management of a set of file or folder paths. 

It can be used as a single point of configuration for your application's paths.

## Features

- A single configuration file of project paths
- Named root-relative paths such as `config` or `storage`
- Ability to add additional paths programatically
- Concatenation of files onto paths
- Automatic resolution of ../ paths
- Configuration options regarding treatment of slashes


## Installation

Install the package using composer:

    composer require davestewart/pathconfig


## Configuration

Create a new PHP file to store your paths.

Return from it a single array with key/path pairs:

```php
return array
(
    'app'       => 'app',
    'config'    => 'resources/config',
    'storage'   => 'support/storage',
);
```

Paths will be **relative** to a "base path", usually the project root.

Once done, save the file as `paths.php` somewhere that suits you. For configuration-free loading, save the file in the root of the project. 


## Instantiation

PathConfig was designed principally to describe your application's paths.

As such, if you saved your `paths.php` file in the project root (and you installed using Composer) there's zero configuration; PathConfig searches up its installation path from its `vendor` folder for `paths.php` and when it finds it, considers that folder the base path.

You can then simply instantiate the PathConfig instance and load your paths like so:

```php
$paths = pathconfig\PathConfig::instance()->load();
```

### To load paths from a custom location

If you saved your paths file elsewhere, you'll need to tell PathConfig where both the base path and the configuration folder is: 
 
```php
$paths = pathconfig\PathConfig::instance()
    ->option('basepath', __DIR__ . '/../../')
    ->load('config/paths.php');
```

Note that base path *must* be absolute, but the config path:
 
 - can be absolute
 - can be relative from the base path location
 - can be just the folder reference (will default to `paths.php`)
 - can be the path to the filename such as `my-paths.php`

### Separate instances

Note that you can have separate instances of the PathConfig library by skipping the call to `instance()` and simply instantiating new instances.

By definition, each will be required to set their own `basepath` and load their own configuration:

```php
$otherPaths = new PathConfig('basepath', __DIR__ . '/../../')
    ->load('config/paths.php');
```

## Usage

Now you are up and running, you can use the library.


### Getting paths

Get paths directly from instance using the `get()` method:

```php
$config = $paths->get('config');
```
```
/home/vagrant/code/project.app/support/config/
```

To append an additional filepath (which also resolves any ../ references) add it as the second argument:

```php
$file = $paths->get('config', 'path/to/file.php');
```
```
/home/vagrant/code/project.app/support/config/path/to/file.php
```

Passing a single argument that is NOT an existing path key resolves the path from the base folder:

```php
$file = $paths->get('path/to/file/from/root.php');
```
```
/home/vagrant/code/project.app/path/to/file/from/root.php
```
Passing no arguments (or the word `base`) returns the base folder:

```php
$root = $paths->get();
```
```
/home/vagrant/code/project.app/
```

To get all paths, call `all()` method:

```php
$root = $paths->all();
```
```
Array
(
    [base] => /home/vagrant/code/project.app
    [app] => /home/vagrant/code/project.app/app
    [public] => /home/vagrant/code/project.app/public
    [routes.php] => /home/vagrant/code/project.app/app/Http/routes.php
    ...
)
```

Pass `false` to return the same array, but with relative paths.

### Setting new paths

Set additional paths new path using `set()`:

```php
$paths->set('plugins', 'support/plugins/');
```

You can also set multiple paths by passing in an array in the same format as the `paths.php` array:

```php
$paths->set(['foo' => 'a', 'bar' => 'a/b', 'baz' => 'a/b/c']);
```

## Options

There are various options to control the loading and conversion of paths.

By default they are set to mimic PHP's `realpath()` which outputs differently depending on the platform it's running on.

Note that `option()` chains, so you can easily set multiple parameters.

### Convert slashes

To automatically convert slashes (on by default):

```php
$paths->option('convertslashes', true);
```

There are 3 options which include conversion of the base path, configured paths, and generated paths:

1. **true** : convert all slashes to the platform type
2. **false** : don't convert any slashes
3. **'auto'** : convert all slashes to the first slash type found in the config file


### Trim trailing folder slashes

Some frameworks expect trailing slashes, some don't. To preserve trailing slashes passed in the config file (they are trimmed by default), call:

```php
$paths->option('trimslashes', false);
```

Note that none of the slash-related options will take effect after loading paths.

### Test paths exist

By default the library doesn't test passed in paths to see if they exist in the way that PHP's `realpath()` does. To mimic this behaviour call:

```php
$paths->option('testpaths', true);
```

### Allow paths to be set more than once

The library allows only allows you to set paths once, but you can override this by setting the `mutable` option to false:

```php
$paths->option('mutable', false);
```

### Set an alternative base path

To set a new base path:

```php
$paths->option('basepath', '/absolute/path/to/folder/');
```

Note that the base path may not be changed after paths have been loaded.


### Set an alternative base name

To set a new base path name, such as 'root':

```php
$paths->option('basename', 'root');
```

This will allow you to treat the basepath as being called `root` and call:

```php
$paths->get('root');
```
```
/absolute/path/to/root/
```

## Additional functionality 

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

