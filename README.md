# PathConfig

PathConfig allows a single point of configuration for your application's paths.

The library's main purpose it to allow restructuring of applications, working around framework's existing limitations.

The animation below demonstrates restructuring a Laravel 5 project to silo less-used components to a "support" folder:

![path-config](https://cloud.githubusercontent.com/assets/132681/10691094/69c02270-797e-11e5-9207-a3bbf2edd40b.gif)

The library provides both helper classes and Application class stubs to facilitate this.

## Features

- A single root-level configuration file of project paths
- Named root-relative paths such as `config` or `storage`
- Ability to add additional paths programatically
- Integration with existing frameworks (Lumen and Laravel currently supported)

## Configuration

Create a `paths.php` file in your project's root folder.

Return from it a single array with as many key/path pairs as your application requires:

    return array
    (
        'foo'   => 'foo/',
        'bar'   => 'foo/bar/',
        'baz'   => 'foo/bar/baz/',
    );

All paths should be **relative** to the project root, which is determined separately.

An example configuration for the Lumen framework is available in the `templates/` folder.

For configuration-free instantiation, it is important that the config file remain in the root of the project!

## Usage

There are 2 ways to use PathConfig:
 
1. With Laravel or Lumen
2. Standalone


### With Laravel or Lumen

In your application's `bootstrap/app.php` file, swap the existing `Illuminate` Application class for a `pathconfig` one:

    $app = new pathconfig\apps\Lumen();

The `$app` instance loads your path configuration, and provides overriden path methods within the class to your configured paths.

There are various locations that **will** need to be updated if they reference any moved folders:
 
File > location| Search | Replace
:-- | :-- | :--
artisan | bootstrap/ | *path to bootstrap*
public/index.php | ../bootstrap/ | *path to bootstrap*
bootstrap/autoload.php | ../vendor/ | *path to vendor*
bootstrap/app.php | $app = new Illuminate\Foundation\Application(...); | $app = new pathconfig\apps\ &lt;Class&gt; ;
composer.json > autoload | database | *path to database*
composer.json > autoload-dev | tests/ | *path to tests*

Everything else remains the same.

### Standalone

Load the PathConfig instance like so:

    $paths = pathconfig\PathConfig::instance()->load();

See the next section on getting and setting paths.

## Getting and setting paths

The easiest way to get paths is to use the global helper function, passing the key to the path you require:

    $config = path('config');
    "/home/vagrant/code/project.com/support/config/"

You can also get paths directly from instance using the `get()` method:

    $config = $paths->get('config');
    
To resolve an additional filepath (which also resolves any ../ references) add it as the second argument:

	$file = path('config', 'path/to/file.php');
	"/home/vagrant/code/project.com/support/config/path/to/file.php"

Passing a single argument that is NOT a key resolves the path from the base folder:

	$file = path('path/to/file/from/root.php');
	"/home/vagrant/code/project.com/path/to/file/from/root.php"

Passing a no arguments returns the base folder:

	$root = path();
	"/home/vagrant/code/project.com/"

Set paths new path using `set()`:

    $paths->set('plugins', 'support/plugins/');

## How it works

One the library is loaded, it will:
 
- search up the folder tree from it's `vendor` folder for the `paths.php` configuration file
- add that folder to the config as the `base` path
- load the configured paths
- add them to the library, appending them to the base path

## Using a custom base path or config location

To configure the library with a custom base folder, or if you want to store the `paths.php` configuration file elsewhere, configure the PathConfig instance first.

This can be done when you first load it, in straight PHP or within the Application stubs:
 
    pathconfig\PathConfig::instance()->load($path_to_base, $path_to_config);

Both paths should be absolute.
