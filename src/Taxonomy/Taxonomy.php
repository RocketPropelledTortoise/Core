<?php namespace Rocket\Taxonomy;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Rocket\Taxonomy\Model\Hierarchy;
use Rocket\Taxonomy\Model\TermContainer;
use Rocket\Taxonomy\Model\TermData;
use Rocket\Taxonomy\Model\Vocabulary;
use Rocket\Taxonomy\Repositories\TermHierarchyRepositoryInterface as TermHieraRep;
use Rocket\Taxonomy\Repositories\TermRepositoryInterface as TermRep;
use Rocket\Translation\Support\Laravel5\Facade as I18N;

/**
 * The Taxonomy base class handles the cache and the creation/modification of terms
 */
class Taxonomy implements TaxonomyInterface
{
    /**
     * @var array Terms internal cache
     */
    public $terms = [];

    /**
     * @var array List of vocabularies by ID
     */
    protected $vocabularyById = [];

    /**
     * @var array List of vocabularies by Name
     */
    protected $vocabularyByName = [];

    /**
     * @var CacheRepository The repository to cache Terms
     */
    protected $cache;

    /**
     * @var TermRep The repository to load Terms
     */
    protected $termRepository;

    /**
     * @var TermHieraRep The repository to handle terms hierarchies
     */
    protected $termHierarchyRepository;

    /**
     * Initialize the Taxonomy system, Loads all existing vocabularies
     *
     * @param CacheRepository $cache The cache repository
     * @param TermRep $termRepository The term retrieval repository
     * @param TermHieraRep $termHierarchyRepository The term hierarchy repository
     */
    public function __construct(CacheRepository $cache, TermRep $termRepository, TermHieraRep $termHierarchyRepository)
    {
        $this->termRepository = $termRepository;
        $this->termHierarchyRepository = $termHierarchyRepository;
        $this->cache = $cache;

        // Get the list of vocabularies
        $vocs = $cache->remember('Rocket::Taxonomy::Vocabularies', 60 * 24 * 7, function () {
            return Vocabulary::all();
        });

        // Initialize the search for terms
        foreach ($vocs as $v) {
            $this->vocabularyByName[$v->machine_name] = $this->vocabularyById[$v->id] = $v;
        }
    }

    /**
     * Is this vocabulary translatable ?
     *
     * @param int|string $vid
     * @return bool
     */
    public function isTranslatable($vid)
    {
        if (!is_numeric($vid)) {
            $vid = $this->vocabulary($vid);
        }

        return $this->vocabularyById[$vid]->isTranslatable();
    }

    /**
     * Get the internal language for the vocabulary
     *
     * This will return the language_id if the vocabulary is translated or 1 if it's not
     *
     * @param int|string $vocabulary_id
     * @param int $language_id
     * @return int|null
     */
    public function getLanguage($vocabulary_id, $language_id = null)
    {
        if (!$this->isTranslatable($vocabulary_id)) {
            return 1;
        }

        if ($language_id === null) {
            return I18N::getCurrentId();
        }

        return $language_id;
    }

    /**
     * Get a vocabulary by name or ID
     *
     *     Taxonomy::vocabulary(1);
     *     returns 'tags'
     *
     *     Taxonomy::vocabulary('tags');
     *     returns 1
     *
     * @param int|string $key
     * @return mixed
     */
    public function vocabulary($key)
    {
        if (is_numeric($key)) {
            return $this->vocabularyById[$key]->machine_name;
        }

        return $this->vocabularyByName[$key]->id;
    }

    /**
     * Get all vocabularies with keys as id's
     *
     * @return array
     */
    public function vocabularies()
    {
        return $this->vocabularyById;
    }

    /**
     * Get a term with all translations
     *
     * @param int $term_id The term's ID
     * @param bool $from_cache Should we take this term from cache or request a fresh one ?
     * @return Term
     */
    public function getTerm($term_id, $from_cache = true)
    {
        if ($from_cache && array_key_exists($term_id, $this->terms)) {
            return $this->terms[$term_id];
        }

        $data = $this->termRepository->getTerm($term_id);
        $this->terms[$term_id] = $data;

        return $data;
    }

    /**
     * Remove a term from the cache
     *
     * @param int $term_id
     * @return bool
     */
    public function uncacheTerm($term_id)
    {
        if (array_key_exists($term_id, $this->terms)) {
            unset($this->terms[$term_id]);
        }

        return $this->termRepository->uncacheTerm($term_id);
    }

    /**
     * Get all paths for a term
     *
     * @param int $term_id
     * @return array<array<int>>
     */
    public function getAncestryPaths($term_id)
    {
        return $this->termHierarchyRepository->getAncestryPaths($term_id);
    }

    /**
     * Get all paths for a term
     *
     * @param int $term_id
     * @return array<array<int>>
     */
    public function getDescentPaths($term_id)
    {
        return $this->termHierarchyRepository->getDescentPaths($term_id);
    }

    /**
     * Get the complete graph
     * @param int $term_id
     * @return array
     */
    public function getAncestryGraph($term_id)
    {
        return $this->termHierarchyRepository->getAncestryGraph($term_id);
    }

    /**
     * Get the complete graph
     * @param int $term_id
     * @return array
     */
    public function getDescentGraph($term_id)
    {
        return $this->termHierarchyRepository->getDescentGraph($term_id);
    }

    /**
     * Add one parent to a term
     *
     * @param int $term_id
     * @param int $parent_id
     */
    public function addParent($term_id, $parent_id)
    {
        $this->testCanAddParents($term_id, 1);

        $this->termHierarchyRepository->addParent($term_id, $parent_id);
    }

    /**
     * Add a list of parents to a term
     *
     * @param int $term_id
     * @param array<integer> $parent_ids
     */
    public function addParents($term_id, array $parent_ids)
    {
        $this->testCanAddParents($term_id, count($parent_ids));

        foreach ($parent_ids as $id) {
            $this->termHierarchyRepository->addParent($term_id, $id);
        }
    }

    /**
     * Test if the term can have more parents
     *
     * @param int $term_id
     * @param int $count
     * @throws \RuntimeException
     */
    protected function testCanAddParents($term_id, $count)
    {
        $vocabulary = (new Vocabulary())->getTable();
        $term = (new TermContainer())->getTable();
        $v = Vocabulary::select('hierarchy', 'name')
            ->where("$term.id", $term_id)
            ->join($term, 'vocabulary_id', '=', "$vocabulary.id")
            ->first();

        if ($v->hierarchy == 0) {
            throw new \RuntimeException("Cannot add a parent in vocabulary '$v->name'");
        }

        if (($v->hierarchy == 1 && Hierarchy::where('term_id', $term_id)->count() > 0)
            || ($v->hierarchy == 1 && $count > 1)) {
            throw new \RuntimeException("Cannot have more than one parent in vocabulary '$v->name'");
        }
    }

    /**
     * Remove the parents form this term
     *
     * @param int $term_id
     * @return bool
     */
    public function unsetParents($term_id)
    {
        return $this->termHierarchyRepository->unsetParents($term_id);
    }

    /**
     * Get all the terms of a vocabulary
     *
     * @param int $vocabulary_id
     * @return array
     */
    public function getTermsForVocabulary($vocabulary_id)
    {
        return $this->cache->remember(
            'Rocket::Taxonomy::Terms::' . $vocabulary_id,
            60,
            function () use ($vocabulary_id) {
                $terms = TermContainer::where('vocabulary_id', $vocabulary_id)->get(['id']);

                $results = [];
                if (!empty($terms)) {
                    foreach ($terms as $term) {
                        $results[] = $term->id;
                    }
                }

                return $results;
            }
        );
    }

    /**
     * Search a specific term, if it doesn't exist, returns false
     *
     * @param  string $term
     * @param  int $vocabulary_id
     * @param  int $language_id
     * @param  array $exclude
     * @return int|null
     */
    public function searchTerm($term, $vocabulary_id, $language_id = null, $exclude = [])
    {
        $language_id = $this->getLanguage($vocabulary_id, $language_id);

        $term = trim($term);
        if ($term == '') {
            return;
        }

        $query = TermData::select('taxonomy_terms.id')
            ->join('taxonomy_terms', 'taxonomy_terms.id', '=', 'taxonomy_terms_data.term_id')
            ->where('taxonomy_terms.vocabulary_id', $vocabulary_id)
            ->where('taxonomy_terms_data.language_id', $language_id)
            ->where('taxonomy_terms_data.title', $term);

        if (count($exclude)) {
            $query->whereNotIn('taxonomy_terms.id', $exclude);
        }

        return $query->value('id');
    }

    /**
     * Returns the id of a term, if it doesn't exist, creates it.
     *
     * @param  string $title
     * @param  int $vocabulary_id
     * @param  int $language_id
     * @param  int $type
     * @return bool|int
     */
    public function getTermId($title, $vocabulary_id, $language_id = null, $type = 0)
    {
        $title = trim($title);
        if ($title == '') {
            return false;
        }

        if (!is_numeric($vocabulary_id)) {
            $vocabulary_id = $this->vocabulary($vocabulary_id);
        }

        $language_id = $this->getLanguage($vocabulary_id, $language_id);

        $search = $this->searchTerm($title, $vocabulary_id, $language_id);
        if ($search !== null) {
            return $search;
        }

        // Add term
        $term = new TermContainer(['vocabulary_id' => $vocabulary_id]);

        if ($type !== 0) {
            $term->type = $type;
        }
        $term->save();

        // Add translation
        $translation = [
            'language_id' => $language_id,
            'title' => $title,
        ];
        $term->translations()->save(new TermData($translation));

        return $term->id;
    }

    /**
     * Adds one or more tags and returns an array of id's
     *
     * @param array $taxonomies
     * @return array
     */
    public function getTermIds($taxonomies)
    {
        $tags = [];
        foreach ($taxonomies as $voc => $terms) {
            $vocabulary_id = $this->vocabulary($voc);
            $exploded = is_array($terms) ? $terms : explode(',', $terms);

            foreach ($exploded as $term) {
                $result = $this->getTermId($term, $vocabulary_id);
                if ($result) {
                    $tags[] = $result;
                }
            }
        }

        return $tags;
    }
}
