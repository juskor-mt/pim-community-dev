<?php

namespace Pim\Bundle\EnrichBundle\tests\integration\PQB\Filter;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('foo', ['categories' => ['master']]);
        $this->createProduct('bar', ['categories' => ['master_clothing']]);
        $this->createProduct('baf', ['categories' => ['men']]);
        $this->createProduct('baz', []);

        $this->createProductModel([
            'code'           => 'model-shoes',
            'family_variant' => 'familyVariantA1',
            'categories'     => ['shoes'],
        ]);
        $this->createProductModel([
            'code'           => 'model-s',
            'family_variant' => 'familyVariantA1',
            'categories'     => [],
            'parent'         => 'model-shoes',
            'values'         => [
                'a_simple_select' => [
                    ['data' => 'optionA', 'locale' => null, 'scope' => null],
                ],
            ],
        ]);
        $this->createProductModel([
            'code'           => 'model-m',
            'family_variant' => 'familyVariantA1',
            'categories'     => [],
            'parent'         => 'model-shoes',
            'values'         => [
                'a_simple_select' => [
                    ['data' => 'optionB', 'locale' => null, 'scope' => null],
                ],
            ],
        ]);
        $this->createVariantProduct(
            'red-s',
            [
                'family'     => 'familyA',
                'parent'     => 'model-s',
                'categories' => ['women'],
                'values'     => [
                    'a_yes_no' => [
                        ['data' => false, 'locale' => null, 'scope' => null],
                    ],
                ],
            ]
        );
        $this->createVariantProduct(
            'blue-s',
            [
                'family'     => 'familyA',
                'parent'     => 'model-s',
                'categories' => ['men'],
                'values'     => [
                    'a_yes_no' => [
                        ['data' => true, 'locale' => null, 'scope' => null],
                    ],
                ],
            ]
        );
        $this->createVariantProduct(
            'red-m',
            [
                'family'     => 'familyA',
                'parent'     => 'model-m',
                'categories' => ['women'],
                'values'     => [
                    'a_yes_no' => [
                        ['data' => true, 'locale' => null, 'scope' => null],
                    ],
                ],
            ]
        );
        $this->createVariantProduct(
            'blue-m',
            [
                'family'     => 'familyA',
                'parent'     => 'model-m',
                'categories' => ['men'],
                'values'     => [
                    'a_yes_no' => [
                        ['data' => false, 'locale' => null, 'scope' => null],
                    ],
                ],
            ]
        );
    }

    /**
     * In the context of "Enrich" and the Datagrid, there is only one supported operator: "IN_LIST" used along only one
     * category value.
     */
    public function testOperatorIn()
    {
        $result = $this->executeFilter([['categories', Operators::IN_LIST, ['master_clothing']]]);
        $this->assert($result, ['bar']);

        $result = $this->executeFilter([['categories', Operators::IN_LIST, ['shoes']]]);
        $this->assert($result, ['model-shoes']);

        $result = $this->executeFilter([['categories', Operators::IN_LIST, ['men']]]);
        $this->assert($result, ['blue-s', 'blue-m', 'baf']);

        $result = $this->executeFilter([['categories', Operators::IN_LIST, ['women']]]);
        $this->assert($result, ['red-s', 'red-m']);
    }

    public function testUnsupportedOperatorThrowsAnException()
    {
    }

    /**
     * @param array $data
     */
    private function createProductModel(array $data)
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            $data
        );

        $violations = $this->get('validator')->validate($productModel);
        $this->assertEquals(0, $violations->count());

        $this->get('pim_catalog.saver.product_model')->save($productModel);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();
    }

    /**
     * @param string $identifier
     * @param array  $data
     */
    protected function createVariantProduct(string $identifier, array $data = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $constraintList = $this->get('pim_catalog.validator.product')->validate($product);
        $this->assertEquals(0, $constraintList->count());
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();
    }

    /**
     * @param array $filters
     *
     * @return CursorInterface
     */
    protected function executeFilter(array $filters)
    {
        $pqb = $this->get('pim_enrich.query.product_and_product_model_query_builder_from_size_factory')->create(
            ['limit' => 100]
        );

        foreach ($filters as $filter) {
            $context = isset($filter[3]) ? $filter[3] : [];
            $pqb->addFilter($filter[0], $filter[1], $filter[2], $context);
        }

        return $pqb->execute();
    }

    /**
     * @param CursorInterface $result
     * @param array           $expected
     */
    protected function assert(CursorInterface $result, array $expected)
    {
        $entities = [];
        foreach ($result as $entity) {
            if ($entity instanceof ProductInterface) {
                $entities[] = $entity->getIdentifier();
            } elseif ($entity instanceof ProductModelInterface) {
                $entities[] = $entity->getCode();
            }
        }

        sort($entities);
        sort($expected);

        $this->assertSame($expected, $entities);
    }
}
