<?php namespace Rocket\Taxonomy;

use Illuminate\Support\Facades\Cache;
use Rocket\Taxonomy\Model\Hierarchy;
use Rocket\Taxonomy\Model\Vocabulary;
use Rocket\Taxonomy\Support\Laravel5\Facade as T;
use Rocket\Translation\Model\Language;
use Rocket\Translation\Support\Laravel5\Facade as I18N;

class TermParent
{
    public function __construct($term, $parent)
    {
        $this->term_id = $term;
        $this->parent_id = $parent;
    }
}

class HierarchyTest extends \Rocket\Utilities\DBTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Language::insert(['name' => 'Français', 'iso' => 'fr']);
        Language::insert(['name' => 'English', 'iso' => 'en']);
        Cache::flush();
    }

    protected function getPackageProviders($app)
    {
        return [
            '\Rocket\Taxonomy\Support\Laravel5\ServiceProvider',
            '\Rocket\Translation\Support\Laravel5\ServiceProvider',
        ];
    }

    /**
     * Comparing objects is not possible, we need a readble format and in a precise order.
     *
     * @param $results
     * @return array|bool
     */
    protected function toComparable($results)
    {
        $final = [];
        foreach ($results as $result) {
            $final[] = implode(',', $result);
        }

        $final = natsort($final);

        return $final;
    }

    protected function assertSamePaths($expected, $actual)
    {
        $this->assertEquals($this->toComparable($expected), $this->toComparable($actual));
    }

    public function testAddParent()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Family', 'machine_name' => 'family', 'hierarchy' => 2, 'translatable' => true]);
        $vid = T::vocabulary('family');

        $family = [
            'dad' => T::getTermId('Dad', $vid),
            'me' => T::getTermId('Me', $vid),
        ];

        $me = T::getTerm($family['me']);
        $me->addParent($family['dad']);

        $this->assertSamePaths([[$family['dad'], $family['me']]], T::getAncestryPaths($family['me']));
        $this->assertSamePaths([[$family['me'], $family['dad']]], T::getDescentPaths($family['dad']));
    }

    public function testSetParent()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Family', 'machine_name' => 'family', 'hierarchy' => 2, 'translatable' => true]);
        $vid = T::vocabulary('family');

        $family = [
            'mom' => T::getTermId('Mom', $vid),
            'dad' => T::getTermId('Dad', $vid),
            'me' => T::getTermId('Me', $vid),
        ];

        $me = T::getTerm($family['me']);
        $me->addParent($family['dad']);

        $this->assertEquals([$family['dad']], Hierarchy::where('term_id', $family['me'])->pluck('parent_id')->toArray());

        // should remove "dad" as parent
        $me->setParent($family['mom']);
        $this->assertEquals([$family['mom']], Hierarchy::where('term_id', $family['me'])->pluck('parent_id')->toArray());

        // both should be present
        $me->addParent($family['dad']);
        $this->assertEquals([$family['mom'], $family['dad']], Hierarchy::where('term_id', $family['me'])->pluck('parent_id')->toArray());
    }

    public function testSetParents()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Family', 'machine_name' => 'family', 'hierarchy' => 2, 'translatable' => true]);
        $vid = T::vocabulary('family');

        $family = [
            'mom' => T::getTermId('Mom', $vid),
            'dad' => T::getTermId('Dad', $vid),
            'aunt' => T::getTermId('Aunt', $vid),
            'uncle' => T::getTermId('Uncle', $vid),
            'me' => T::getTermId('Me', $vid),
        ];

        $me = T::getTerm($family['me']);

        //first, add mom & dad
        $me->setParents([$family['mom'], $family['dad']]);
        $this->assertEquals([$family['mom'], $family['dad']], Hierarchy::where('term_id', $family['me'])->orderBy('parent_id')->pluck('parent_id')->toArray());

        //replace by aunt & uncle
        $me->setParents([$family['aunt'], $family['uncle']]);
        $this->assertEquals([$family['aunt'], $family['uncle']], Hierarchy::where('term_id', $family['me'])->orderBy('parent_id')->pluck('parent_id')->toArray());

        //add both parents again
        $me->addParents([$family['mom'], $family['dad']]);
        $this->assertEquals([$family['mom'], $family['dad'], $family['aunt'], $family['uncle']], Hierarchy::where('term_id', $family['me'])->orderBy('parent_id')->pluck('parent_id')->toArray());
    }

    public function testGetEmptyHierarchy()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Family', 'machine_name' => 'family', 'hierarchy' => 2, 'translatable' => true]);
        $vid = T::vocabulary('family');

        $id =  T::getTermId('Me', $vid);

        $this->assertEmpty(T::getAncestryPaths($id));
        $this->assertEmpty(T::getDescentPaths($id));
        $this->assertEquals([null, null], T::getAncestryGraph($id));
        $this->assertEquals([null, null], T::getDescentGraph($id));
    }

    public function testComplexPaths()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Family', 'machine_name' => 'family', 'hierarchy' => 2, 'translatable' => true]);
        $vid = T::vocabulary('family');

        $family = [
            'grandpa' => T::getTermId('Grandpa', $vid),
            'mom' => T::getTermId('Mom', $vid),
            'dad' => T::getTermId('Dad', $vid),
            'aunt' => T::getTermId('Aunt', $vid),
            'uncle' => T::getTermId('Uncle', $vid),
            'me' => T::getTermId('Me', $vid),
        ];

        $me = T::getTerm($family['me']);
        $me->addParents([$family['dad'], $family['mom']]);

        $mom = T::getTerm($family['mom']);
        $mom->addParent($family['grandpa']);

        // these should not appear ...
        T::getTerm($family['uncle'])->addParent($family['grandpa']);
        T::getTerm($family['aunt'])->addParent($family['grandpa']);

        $this->assertSamePaths(
            [
                [$family['grandpa'], $family['mom'], $family['me']],
                [$family['dad'], $family['me']],
            ],
            T::getAncestryPaths($family['me'])
        );

        $this->assertSamePaths(
            [
                [$family['me'], $family['dad']],
            ],
            T::getDescentPaths($family['dad'])
        );

        $this->assertSamePaths(
            [
                [$family['me'], $family['mom'], $family['grandpa']],
                [$family['uncle'], $family['grandpa']],
                [$family['aunt'], $family['grandpa']],

            ],
            T::getDescentPaths($family['grandpa'])
        );
    }

    public function testDetectLoopPathResolver()
    {
        $family = [
            'me' => '1',
            'dad' => '2',
        ];

        // Fake a cache entry so we wont touch the database
        Cache::put(
            "Rocket::Taxonomy::TermHierarchy::descent::$family[dad]",
            [
                new TermParent($family['me'], $family['dad']),
                new TermParent($family['dad'], $family['me']),
            ],
            60
        );

        $this->assertSamePaths([[$family['me'], $family['dad']]], T::getDescentPaths($family['dad']));

        // Fake a cache entry so we wont touch the database
        Cache::put(
            "Rocket::Taxonomy::TermHierarchy::ancestry::$family[me]",
            [
                new TermParent($family['me'], $family['dad']),
                new TermParent($family['dad'], $family['me']),
            ],
            60
        );
        $this->assertSamePaths([[$family['dad'], $family['me']]], T::getAncestryPaths($family['me']));
    }

    public function testDetectLoop()
    {
        $this->markTestSkipped('This goes to infinite loop, try to find a way to fix this (test when adding a parent?)');

        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Family', 'machine_name' => 'family', 'hierarchy' => 2, 'translatable' => true]);
        $vid = T::vocabulary('family');

        $family = [
            'mom' => T::getTermId('Mom', $vid),
            'dad' => T::getTermId('Dad', $vid),
            'me' => T::getTermId('Me', $vid),
        ];

        $me = T::getTerm($family['me']);
        $me->addParent($family['dad']);

        // Declare a circular reference, should not fail in code
        $me = T::getTerm($family['dad']);
        $me->addParent($family['me']);

        $this->assertSamePaths([[$family['dad'], $family['me']]], T::getAncestryPaths($family['me']));
        $this->assertSamePaths([[$family['me'], $family['dad']]], T::getDescentPaths($family['dad']));
    }

    public function testCannotAddParentBecauseVocabularyType()
    {
        $this->expectException(\RuntimeException::class);

        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Family', 'machine_name' => 'family', 'hierarchy' => 0, 'translatable' => true]);
        $vid = T::vocabulary('family');

        $me = T::getTerm(T::getTermId('Me', $vid));
        $me->addParent(T::getTermId('Dad', $vid));
    }

    public function testCannotAddMultipleParentBecauseVocabularyType()
    {
        $this->expectException(\RuntimeException::class);

        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Family', 'machine_name' => 'family', 'hierarchy' => 1, 'translatable' => true]);
        $vid = T::vocabulary('family');

        $me = T::getTerm(T::getTermId('Me', $vid));
        $me->addParent(T::getTermId('Mom', $vid));
        $me->addParent(T::getTermId('Dad', $vid));
    }
}
