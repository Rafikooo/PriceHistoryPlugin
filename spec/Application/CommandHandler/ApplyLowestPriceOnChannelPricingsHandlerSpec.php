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

namespace spec\Sylius\PriceHistoryPlugin\Application\CommandHandler;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PriceHistoryPlugin\Application\Command\ApplyLowestPriceOnChannelPricings;
use Sylius\PriceHistoryPlugin\Application\CommandHandler\ApplyLowestPriceOnChannelPricingsHandler;
use Sylius\PriceHistoryPlugin\Application\Processor\ProductLowestPriceBeforeDiscountProcessorInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelPricingInterface;

final class ApplyLowestPriceOnChannelPricingsHandlerSpec extends ObjectBehavior
{
    function let(
        ProductLowestPriceBeforeDiscountProcessorInterface $productLowestPriceBeforeDiscountProcessor,
        RepositoryInterface $channelPricingRepository,
    ): void {
        $this->beConstructedWith($productLowestPriceBeforeDiscountProcessor, $channelPricingRepository);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ApplyLowestPriceOnChannelPricingsHandler::class);
    }

    function it_applies_lowest_price_before_discount_on_all_given_channel_pricings(
        ProductLowestPriceBeforeDiscountProcessorInterface $productLowestPriceBeforeDiscountProcessor,
        RepositoryInterface $channelPricingRepository,
        ChannelPricingInterface $firstChannelPricing,
        ChannelPricingInterface $secondChannelPricing,
        ChannelPricingInterface $thirdChannelPricing,
    ): void {
        $channelPricingRepository
            ->findBy(['id' => [1, 3, 4]])
            ->willReturn([
                $firstChannelPricing->getWrappedObject(),
                $secondChannelPricing->getWrappedObject(),
                $thirdChannelPricing->getWrappedObject(),
            ])
        ;

        $productLowestPriceBeforeDiscountProcessor->process($firstChannelPricing)->shouldBeCalled();
        $productLowestPriceBeforeDiscountProcessor->process($secondChannelPricing)->shouldBeCalled();
        $productLowestPriceBeforeDiscountProcessor->process($thirdChannelPricing)->shouldBeCalled();
        $productLowestPriceBeforeDiscountProcessor->process(Argument::any())->shouldBeCalledTimes(3);

        $this(new ApplyLowestPriceOnChannelPricings([1, 3, 4]));
    }

    function it_does_not_apply_lowest_price_before_discount_on_any_channel_pricing_if_there_are_no_given_channel_pricings(
        ProductLowestPriceBeforeDiscountProcessorInterface $productLowestPriceBeforeDiscountProcessor,
        RepositoryInterface $channelPricingRepository,
    ): void {
        $channelPricingRepository
            ->findBy(['id' => []])
            ->willReturn([])
        ;

        $productLowestPriceBeforeDiscountProcessor->process(Argument::any())->shouldNotBeCalled();

        $this(new ApplyLowestPriceOnChannelPricings([]));
    }
}
