# Utilities

__This is a subtree split of RocketPropelledTortoise CMS - Core. Don't send pull requests here__

__Work In Progress, do not use in production__

This repository contains all classes for RocketPropelledTortoise CMS that are not directly tied to a specific component.


## `ParentChildTree`

Transform a flat representation of a tree to a nested array

From

```php
 array(
    ['id' => 1, 'text' => 'parent'],
    ['id' => 2, 'text' => 'child', 'parent_id' => 1]
 );
```

To

```php
array(
    ['id' => 0, 'childs' => array(
        ['id' => 1, 'text' => 'parent', 'childs' => array(
            ['id' => 2, 'text' => 'child', 'parent_id' => 1]
        )],
    )
);
``
