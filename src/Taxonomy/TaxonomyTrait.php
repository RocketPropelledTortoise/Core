<?php

/**
 * Trait used to give Taxonomy abilities to another model.
 */
namespace Rocket\Taxonomy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Rocket\Taxonomy\Model\TermContainer;
use Rocket\Taxonomy\Support\Laravel5\Facade as T;

/**
 * This class is the link between a content and its taxonomies
 *
 * Including this trait in your model will enable it
 * to add and remove taxonomies from any vocabulary
 */
trait TaxonomyTrait
{
    /**
     * Define a polymorphic many-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $name
     * @param  string  $table
     * @param  string  $foreignPivotKey
     * @param  string  $relatedPivotKey
     * @param  string  $parentKey
     * @param  string  $relatedKey
     * @param  bool  $inverse
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    abstract public function morphToMany(
        $related,
        $name,
        $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null,
        $inverse = false
    );

    /**
     * Declared by Eloquent Model
     */
    abstract public function getTable();

    /**
     * Declared by Eloquent Model
     */
    abstract public function getKey();

    /**
     * The relation configuration
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function taxonomies()
    {
        return $this->morphToMany(Model\TermContainer::class, 'relationable', 'taxonomy_content', null, 'term_id');
    }

    /**
     * Filter the model to return a subset of entries matching the term ID
     *
     * @param Builder $query
     * @param int $term_id
     *
     * @return Builder
     */
    public function scopeGetAllByTermId(Builder $query, $term_id)
    {
        return $query->whereHas('taxonomies', function (Builder $q) use ($term_id) {
            $q->where('term_id', $term_id);
        });
    }

    /**
     * Get the terms from a content
     *
     * @param  int|string $vocabulary_id
     * @return Collection
     */
    public function getTerms($vocabulary_id)
    {
        if (!$data = Cache::get($this->getTaxonomyCacheKey())) {
            $data = $this->cacheTermsForContent();
        }

        if (!is_numeric($vocabulary_id)) {
            $vocabulary_id = T::vocabulary($vocabulary_id);
        }

        $results = new Collection();
        if (array_key_exists($vocabulary_id, $data)) {
            foreach ($data[$vocabulary_id] as $term) {
                $results[] = T::getTerm($term);
            }
        }

        return $results;
    }

    /**
     * Link a term to this content
     *
     * @param int $term_id
     */
    public function addTerm($term_id)
    {
        TermContainer::findOrFail($term_id);

        // Cancel if the user wants to add the same term again
        if ($this->taxonomies()->where('term_id', $term_id)->count()) {
            return;
        }

        $this->taxonomies()->attach($term_id);

        Cache::forget($this->getTaxonomyCacheKey());
    }

    /**
     * Set the terms to a content, removes the old ones (only for one vocabulary if specified)
     *
     * @param array<integer> $terms
     * @param int|string $vocabulary_id
     */
    public function setTerms($terms, $vocabulary_id = null)
    {
        $this->removeTerms($vocabulary_id);

        foreach ($terms as $term_id) {
            $this->addTerm($term_id);
        }
    }

    /**
     * Removes terms specified by a vocabulary, or all
     *
     * @param int|string $vocabulary_id
     * @return bool
     */
    public function removeTerms($vocabulary_id = null)
    {
        if ($vocabulary_id === null) {
            return $this->taxonomies()->detach();
        }

        if (!is_numeric($vocabulary_id)) {
            $vocabulary_id = T::vocabulary($vocabulary_id);
        }

        return $this->taxonomies()->detach(TermContainer::where('vocabulary_id', $vocabulary_id)->pluck('id'));
    }

    /**
     * Cache all terms of a content (only ids)
     *
     * @return array
     */
    private function cacheTermsForContent()
    {
        $term_container = (new TermContainer)->getTable();
        $terms = $this->taxonomies()
            ->select("$term_container.id", "$term_container.vocabulary_id")
            ->get();

        if (!count($terms)) {
            return [];
        }

        $results = [];
        foreach ($terms as $term) {
            $results[$term->vocabulary_id][] = $term->id;
        }

        // a whole week, because it's automatically recached
        Cache::put($this->getTaxonomyCacheKey(), $results, 60 * 24 * 7);

        return $results;
    }

    /**
     * Get the cache key for this content
     *
     * @return string
     */
    private function getTaxonomyCacheKey()
    {
        return 'Rocket::Taxonomy::Related::' . $this->getTable() . '::' . $this->getKey();
    }
}
