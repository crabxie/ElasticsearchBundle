<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchBundle\Tests\Functional\DSL\Query;

use ONGR\ElasticsearchBundle\DSL\Query\FuzzyLikeThisQuery;
use ONGR\ElasticsearchBundle\ORM\Repository;
use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;

/**
 * FuzzyLikeThis query functional test.
 */
class FuzzyLikeThisTest extends AbstractElasticsearchTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getDataArray()
    {
        return [
            'default' => [
                'product' => [
                    [
                        '_id' => 1,
                        'title' => 'foo',
                        'price' => 10,
                        'description' => 'Loram ipsum',
                    ],
                    [
                        '_id' => 2,
                        'title' => 'bar',
                        'price' => 100,
                        'description' => 'Lorem ipsum dolor sit amet...',
                    ],
                    [
                        '_id' => 3,
                        'title' => 'baz',
                        'price' => 1000,
                        'description' => 'Loruu ipsum dolor sit amet, consectetur adipisicing elit...',
                    ],
                ],
            ],
        ];
    }

    /**
     * Data provider for testFuzzyLikeThisFieldQuery().
     *
     * @return array
     */
    public function getTestFuzzyLikeThisFieldQueryData()
    {
        $out = [];
        $testData = $this->getDataArray();

        // FLT in single field
        $out[] = [
            'description',
            'Lorem',
            [
                'fuzziness' => 0.6,
            ],
            [
                $testData['default']['product'][0],
                $testData['default']['product'][1],
            ],
        ];

        // FLT in multiple fields
        $out[] = [
            'description,title',
            'Lorem bar',
            [],
            [
                $testData['default']['product'][0],
                $testData['default']['product'][1],
                $testData['default']['product'][2],
            ],
        ];

        return $out;
    }

    /**
     * Test FuzzyLikeThis query for expected search results.
     *
     * @param string $field
     * @param string $likeText
     * @param array  $parameters
     * @param array  $expected
     *
     * @dataProvider getTestFuzzyLikeThisFieldQueryData
     */
    public function testFuzzyLikeThisFieldQuery($field, $likeText, $parameters, $expected)
    {
        /** @var Repository $repo */
        $repo = $this->getManager()->getRepository('AcmeTestBundle:Product');
        $fuzzyLikeThisFieldQuery = new FuzzyLikeThisQuery($field, $likeText, $parameters);
        $search = $repo->createSearch()->addQuery($fuzzyLikeThisFieldQuery);
        $results = $repo->execute($search, Repository::RESULTS_ARRAY);
        foreach ($expected as &$document) {
            unset($document['_id']);
        }
        sort($expected);
        sort($results);
        $this->assertEquals($expected, $results);
    }
}
