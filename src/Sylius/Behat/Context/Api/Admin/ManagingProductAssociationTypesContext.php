<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Behat\Context\Api\Admin;

use Behat\Behat\Context\Context;
use Sylius\Behat\Client\ApiClientInterface;
use Sylius\Behat\Client\ResponseCheckerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Product\Model\ProductAssociationTypeInterface;
use Webmozart\Assert\Assert;

final class ManagingProductAssociationTypesContext implements Context
{
    /** @var ApiClientInterface */
    private $client;

    /** @var ResponseCheckerInterface */
    private $responseChecker;

    /** @var SharedStorageInterface */
    private $sharedStorage;

    public function __construct(
        ApiClientInterface $client,
        ResponseCheckerInterface $responseChecker,
        SharedStorageInterface $sharedStorage
    ) {
        $this->client = $client;
        $this->responseChecker = $responseChecker;
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @When I want to create a new product association type
     */
    public function iWantToCreateANewProductAssociationType(): void
    {
        $this->client->buildCreateRequest();
    }

    /**
     * @When I specify its code as :productAssociationTypeCode
     */
    public function iSpecifyItsCodeAs($productAssociationTypeCode): void
    {
        $this->client->addRequestData('code', $productAssociationTypeCode);
    }

    /**
     * @When I name it :productAssociationTypeName in :localeCode
     */
    public function iNameItIn(string $productAssociationTypeName, string $localeCode): void
    {
        $this->client->updateRequestData(['translations' => [$localeCode => ['name' => $productAssociationTypeName, 'locale' => $localeCode]]]);
    }

    /**
     * @When I add it
     * @When I try to add it
     */
    public function iAddIt(): void
    {
        $this->client->create();
    }

    /**
     * @Then I should be notified that it has been successfully created
     */
    public function iShouldBeNotifiedThatItHasBeenSuccessfullyCreated(): void
    {
        Assert::true(
            $this->responseChecker->isCreationSuccessful($this->client->getLastResponse()),
            'Product aAssociation type could not be created'
        );
    }

    /**
     * @Then the product association type :name should appear in the store
     */
    public function theProductAssociationTypeShouldAppearInTheStore(string $Name): void
    {
        Assert::true(
            $this->responseChecker->hasItemWithValue($this->client->index(), 'name', $Name),
            sprintf('There is no product association type with name "%s"', $Name)
        );
    }

    /**
     * @When I want to browse product association types
     */
    public function iWantToBrowseProductAssociationTypes(): void
    {
        $this->client->index();
    }

    /**
     * @Then I should see :count product association types in the list
     */
    public function iShouldSeeProductAssociationTypesInTheList(int $count): void
    {
        Assert::same($this->responseChecker->countCollectionItems($this->client->index()), $count);
    }

    /**
     * @Then I should see the product association type :name in the list
     */
    public function iShouldSeeTheProductAssociationTypeInTheList(string $name): void
    {
        Assert::true(
            $this->responseChecker->hasItemWithValue($this->client->index(), 'name', $name),
            sprintf('There is no product association type with name "%s"', $name)
        );
    }

    /**
     * @When I delete the :productAssociationType product association type
     */
    public function iDeleteTheProductAssociationType(ProductAssociationTypeInterface $productAssociationType): void
    {
        $this->sharedStorage->set('product_association_type_code', $productAssociationType->getCode());
        $this->client->delete($productAssociationType->getCode());
    }

    /**
     * @Then I should be notified that it has been successfully deleted
     */
    public function iShouldBeNotifiedThatItHasBeenSuccessfullyDeleted(): void
    {
        Assert::true($this->responseChecker->isDeletionSuccessful(
            $this->client->getLastResponse()),
            'Product association type could not be deleted'
        );
    }

    /**
     * @Then this product association type should no longer exist in the registry
     */
    public function thisProductAssociationTypeShouldNoLongerExistInTheRegistry(): void
    {
        $code = $this->sharedStorage->get('product_association_type_code');
        Assert::false(
            $this->responseChecker->hasItemWithValue($this->client->index(), 'code', $code),
            sprintf('Product association type with code %s exist', $code)
        );
    }
}
