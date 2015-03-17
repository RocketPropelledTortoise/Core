<?php namespace Rocket\Taxonomy;

use Rocket\Taxonomy\Support\Laravel5\Facade as T;
use Rocket\Taxonomy\Model\Vocabulary;
use Rocket\Taxonomy\Utils\RecursiveQuery;
use Rocket\Taxonomy\Utils\CommonTableExpressionQuery;
use Rocket\Translation\Support\Laravel5\Facade as I18N;
use Rocket\Translation\Model\Language;
use Illuminate\Support\Facades\Cache;
use stdClass;

class RecursiveQueryTest extends \Rocket\Utilities\TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->packagesToTest(['translations', 'taxonomy']);

        Language::insert(['name' => 'Français', 'iso' => 'fr']);
        Language::insert(['name' => 'English', 'iso' => 'en']);
        Cache::flush();
    }

    protected function getPackageProviders($app)
    {
        return [
            '\Rocket\Taxonomy\Support\Laravel5\ServiceProvider',
            '\Rocket\Translation\Support\Laravel5\ServiceProvider'
        ];
    }

    protected function generateTestTree()
    {
        I18N::setLanguage('en');
        Vocabulary::insert(['name' => 'Family', 'machine_name' => 'family', 'hierarchy' => 2, 'translatable' => true]);
        $vid = T::vocabulary('family');

        $family = [
            'grandpa' => T::getTermId('Grandpa', $vid),
            'mom' => T::getTermId('Mom', $vid),
            'dad' => T::getTermId('Dad', $vid),
            'me' => T::getTermId('Me', $vid),
            'son' => T::getTermId('Son', $vid),
            'daughter' => T::getTermId('Daughter', $vid),
            'grandson' => T::getTermId('Grandson', $vid),
            'granddaughter' => T::getTermId('Dranddaughter', $vid)
        ];

        $relations = [
            ['mom', 'grandpa'],
            ['me','mom'],
            ['me', 'dad'],
            ['daughter', 'me'],
            ['son', 'me'],
            ['grandson', 'son'],
            ['granddaughter', 'son'],
        ];

        foreach ($relations as $rel) {
            T::addParent($family[$rel[0]], $family[$rel[1]]);
        }

        return $family;
    }

    public function generateComparison(array $family, array $relations) {

        $final = [];
        foreach ($relations as $rel) {
            $class = new stdClass();
            $class->term_id = $family[$rel[0]];
            $class->parent_id = $family[$rel[1]];
            $final[] = $class;
        }

        return $this->toComparable($final);
    }

    /**
     * Comparing objects is not possible, we need a readble format and in a precise order.
     *
     * @param $result
     * @return array|bool
     */
    public function toComparable($result)
    {
        $final = [];
        foreach ($result as $result) {
            $final[] = "parent_id => $result->parent_id, term_id => $result->term_id";
        }

        $final = natsort($final);

        return $final;
    }

    public function providerDescent()
    {
        return [
            ['granddaughter', []],
            ['grandson', []],
            ['son', [['grandson', 'son'], ['granddaughter', 'son']]],
            ['daughter', []],
            ['me', [['grandson', 'son'], ['granddaughter', 'son'],['daughter', 'me'], ['son', 'me']]],
            ['mom', [['me','mom'], ['daughter', 'me'], ['son', 'me'], ['grandson', 'son'], ['granddaughter', 'son']]],
            ['dad', [['me','dad'], ['daughter', 'me'], ['son', 'me'], ['grandson', 'son'], ['granddaughter', 'son']]],
            [
                'grandpa',
                [['mom', 'grandpa'], ['me','mom'], ['daughter', 'me'], ['son', 'me'], ['grandson', 'son'], ['granddaughter', 'son']]
            ],
        ];
    }

    /**
     * @dataProvider providerDescent
     */
    public function testDescentRecursiveQuery($origin, $result)
    {
        $retriever = new RecursiveQuery();

        $family = $this->generateTestTree();

        $relations = $this->generateComparison($family, $result);

        $this->assertEquals($relations, $this->toComparable($retriever->getDescent($family[$origin])));
    }

    /**
     * @dataProvider providerDescent
     */
    public function testDescentCommonTableExpressionQuery($origin, $result)
    {
		if (\DB::connection()->getDriverName() == 'sqlite' && \SQLite3::version()['versionNumber'] < 3008003) {
			$this->markTestSkipped('Sqlite 3.8.3 is require to run these tests');
		}

        $retriever = new CommonTableExpressionQuery();

        $family = $this->generateTestTree();

        $relations = $this->generateComparison($family, $result);

        $this->assertEquals($relations, $this->toComparable($retriever->getDescent($family[$origin])));
    }

    public function providerAncestry()
    {
        return [
            [
                'grandson',
                [['grandson', 'son'], ['mom', 'grandpa'], ['me','mom'], ['me', 'dad'], ['daughter', 'me'], ['son', 'me']]
            ],
            [
                'granddaughter',
                [['granddaughter', 'son'], ['mom', 'grandpa'], ['me','mom'], ['me', 'dad'], ['daughter', 'me'], ['son', 'me']]
            ],
            ['son', [['mom', 'grandpa'], ['me','mom'], ['me', 'dad'], ['daughter', 'me'], ['son', 'me'],]],
            ['daughter', [['mom', 'grandpa'], ['me','mom'], ['me', 'dad'], ['daughter', 'me']]],
            ['me', [['mom', 'grandpa'], ['me','mom'], ['me', 'dad']]],
            ['mom', [['mom', 'grandpa']]],
            ['dad', []],
            ['grandpa', []],
        ];
    }

    /**
     * @dataProvider providerAncestry
     */
    public function testAncestryRecursiveQuery($origin, $result)
    {
        $retriever = new RecursiveQuery();

        $family = $this->generateTestTree();

        $relations = $this->generateComparison($family, $result);

        $this->assertEquals($relations, $this->toComparable($retriever->getAncestry($family[$origin])));
    }

    /**
     * @dataProvider providerAncestry
     */
    public function testAncestryCommonTableExpressionQuery($origin, $result)
    {
		if (\DB::connection()->getDriverName() == 'sqlite' && \SQLite3::version()['versionNumber'] < 3008003) {
			$this->markTestSkipped('Sqlite 3.8.3 is require to run these tests');
		}

        $retriever = new CommonTableExpressionQuery();

        $family = $this->generateTestTree();

        $relations = $this->generateComparison($family, $result);

        $this->assertEquals($relations, $this->toComparable($retriever->getAncestry($family[$origin])));
    }
}
