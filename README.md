# SmallOrmBundle
Small ORM for symfony app

## Install

Add repository to your `composer.json` file.

```
"repositories": [
           {
               "type": "git",
               "url": "https://github.com/sebk69/SebkSmallOrmBundle.git"
           }
       ]
```

Add [`Sebk/SmallOrmBundle`]
to your `composer.json` file.

```
"Sebk/SmallUserBundle": "dev-master"
```

Register the bundle in `app/AppKernel.php`:

``` php
public function registerBundles()
{
    return array(
        // ...
        new Sebk\SmallOrmBundle\SebkSmallOrmBundle(),
    );
}
```

## Documentation

See [documentation]: http://iceberg-linux.net/smallorm.php
