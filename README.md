# Puzzle API Page Bundle
**=========================**

Puzzle Page API

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

`composer require webundle/puzzle-api-page`

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
{
    $bundles = array(
    // ...

    new Puzzle\Api\PageBundle\PuzzleApiPageBundle(),
                    );

 // ...
}

 // ...
}
```

### Step 3: Register the Routes

Load the bundle's routing definition in the application (usually in the `app/config/routing.yml` file):

# app/config/routing.yml
```yaml
puzzle_api_page:
    resource: "@PuzzleApiPageBundle/Resources/config/routing.yml"
    prefix:   /v1
    host: '%host_apis%'
```

### Step 4: Enable services

Load the bundle's routing definition in the application (usually in the `app/config/config.yml` file):

# app/config/config.yml
```yaml
imports:
    ...
    - { resource: '@PuzzleApiPageBundle/Resources/config/services.yml' }
```