<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Catalog\Context;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Test\IntegrationTestsBundle\Fixture\EntityWithValue\Builder;
use Behat\Behat\Context\Context;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductValue implements Context
{
    private const IDENTIFIER_ATTRIBUTE = 'sku';

    /** @var ProductInterface */
    protected $updatedProduct;

    /** @var Builder\Product */
    private $productBuilder;

    /** @var SaverInterface */
    private $attributeSaver;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var ValidatorInterface */
    private $productValidator;

    /** @var SaverInterface */
    private $productSaver;

    public function __construct(
        SaverInterface $attributeSaver,
        Builder\Product $productBuilder,
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        SaverInterface $productSaver
    ) {
        $this->attributeSaver = $attributeSaver;
        $this->productBuilder = $productBuilder;
        $this->productRepository = $productRepository;
        $this->productUpdater = $productUpdater;
        $this->productValidator = $productValidator;
        $this->productSaver = $productSaver;
    }

    /**
     * @Given a product with an identifier :identifier
     */
    public function aProductWithAnIdentifier(string $identifier)
    {
        // Todo: Create/use a proper attribute builder
        $attribute = (new Attribute())
            ->setType(AttributeTypes::IDENTIFIER)
            ->setCode(self::IDENTIFIER_ATTRIBUTE);
        $this->attributeSaver->save($attribute);

        $product = $this->productBuilder->withIdentifier($identifier)->build();
        $this->productSaver->save($product);
    }

    /**
     * @When a product is created with identifier :identifier
     */
    public function aProductIsCreatedWithIdentifier($identifier)
    {
        $this->updatedProduct = $this->productBuilder->withIdentifier($identifier)->build();
    }

    /**
     * @Then an error should be raised because of :errorMessage
     */
    public function anErrorShouldBeRaisedBecauseOf($errorMessage)
    {
        $violations = $this->productValidator->validate($this->updatedProduct);

        $messages = [];
        $isFoundMessage = false;
        foreach ($violations as $violation) {
            $message = $violation->getMessage();
            $messages[] = $message;
            if ($message === $errorMessage) {
                $isFoundMessage = true;
            }
        }

        if (!$isFoundMessage) {
            throw new \Exception(
                sprintf(
                    'Expected error message "%s" was not found, %s given', $errorMessage,
                    implode(',', $messages)
                )
            );
        }
    }
}