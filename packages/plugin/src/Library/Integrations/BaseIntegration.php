<?php
/**
 * Freeform for Craft CMS.
 *
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2008-2022, Solspace, Inc.
 *
 * @see           https://docs.solspace.com/craft/freeform
 *
 * @license       https://docs.solspace.com/license-agreement
 */

namespace Solspace\Freeform\Library\Integrations;

use craft\helpers\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Solspace\Freeform\Attributes\Integration\Type;
use Solspace\Freeform\Events\Integrations\IntegrationResponseEvent;
use Solspace\Freeform\Freeform;
use yii\base\Event;

abstract class BaseIntegration implements IntegrationInterface
{
    public function __construct(
        private ?int $id,
        private bool $enabled,
        private string $handle,
        private string $name,
        private \DateTime $lastUpdate,
        private Type $typeDefinition,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getLastUpdate(): \DateTime
    {
        return $this->lastUpdate;
    }

    /**
     * Returns the MailingList service provider short name
     * i.e. - MailChimp, Constant Contact, etc...
     */
    public function getServiceProvider(): string
    {
        $reflection = (new \ReflectionClass($this));
        $type = $reflection->getAttributes(Type::class);
        $type = reset($type);

        if (!$type) {
            return $reflection->getShortName();
        }

        return $type->newInstance()->name;
    }

    /**
     * Perform anything necessary before this integration is saved.
     */
    public function onBeforeSave(): void
    {
    }

    public function getTypeDefinition(): Type
    {
        return $this->typeDefinition;
    }

    protected function triggerAfterResponseEvent(string $category, ResponseInterface $response): void
    {
        $event = new IntegrationResponseEvent($this, $category, $response);
        Event::trigger($this, self::EVENT_AFTER_RESPONSE, $event);
    }

    protected function getProcessedValue(mixed $value): bool|string|null
    {
        return App::parseEnv($value);
    }

    protected function getLogger(?string $category = null): LoggerInterface
    {
        return Freeform::getInstance()
            ->logger
            ->getLogger('Integration'.($category ? '.'.$category : ''))
        ;
    }

    /**
     * @throws \Exception
     */
    protected function processException(\Exception $exception, ?string $category = null): void
    {
        $this->getLogger($category)->error(
            $exception->getMessage(),
            ['exception' => $exception->getMessage()],
        );

        throw $exception;
    }
}
