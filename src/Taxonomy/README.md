# Taxonomy Component

[![Latest Version](https://img.shields.io/github/release/RocketPropelledTortoise/Core.svg?style=flat-square)](https://github.com/RocketPropelledTortoise/Core/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/RocketPropelledTortoise/Core/blob/master/LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/rocket/core.svg?style=flat-square)](https://packagist.org/packages/rocket/core)
[![Sonar Quality Gate](https://img.shields.io/sonar/alert_status/RocketPropelledTortoise_Core?server=https%3A%2F%2Fsonarcloud.io&style=flat-square)](https://sonarcloud.io/dashboard?id=RocketPropelledTortoise_Core)
[![Sonar Coverage](https://img.shields.io/sonar/coverage/RocketPropelledTortoise_Core?server=https%3A%2F%2Fsonarcloud.io&style=flat-square)](https://sonarcloud.io/dashboard?id=RocketPropelledTortoise_Core)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/RocketPropelledTortoise/Core/PHP?style=flat-square)](https://github.com/RocketPropelledTortoise/Core/actions)

__This is a subtree split of RocketPropelledTortoise CMS - Core. Don't send pull requests here__

## What is it ?

Taxonomy is the art of classifying things. The Taxonomy Component is here to help you classify your content.

Create as many vocabularies and terms that you want and assign them to content.

Vocabularies can be Regions, Countries, Tags, Categories.<br />
A vocabulary contains terms, each term can have one or more sub-terms.

Taxonomy is a __Laravel 5__ module

## Example

```php
use Taxonomy;
use Model;

use Rocket\Taxonomy\TaxonomyTrait;
use Rocket\Taxonomy\Model\Vocabulary;
use Rocket\Translation\Model\Language;

class Post extends Model {

    // Add the taxonomy trait
    use TaxonomyTrait;

    public $fillable = ['content'];
}

// Create a vocabulary
Vocabulary::insert([
    'name' => 'Tag',
    'machine_name' => 'tag',
    'hierarchy' => Vocabulary::$HIERARCHY_FLAT, // Can be $HIERARCHY_FLAT, $HIERARCHY_SINGLE_PARENT or $HIERARCHY_MULTIPLE_PARENT
    'translatable' => true
]);

// Create a post
$post = new Post(['content' => 'a test post']);
$post->save();

// Add tags to it (They are automatically created if they don't exist)
$ids = T::getTermIds(['tag' => ['TDD', 'PHP', 'Add some tags']]);
$post->setTerms($ids);

// Get the tags from the Post
$terms = $post->getTerms('tag')

```

## Installing

Install with Composer

```bash
composer require rocket/taxonomy
```
