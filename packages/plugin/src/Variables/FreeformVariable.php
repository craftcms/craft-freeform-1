<?php
/**
 * Freeform for Craft CMS.
 *
 * @author        Solspace, Inc.
 * @copyright     Copyright (c) 2008-2024, Solspace, Inc.
 *
 * @see           https://docs.solspace.com/craft/freeform
 *
 * @license       https://docs.solspace.com/license-agreement
 */

namespace Solspace\Freeform\Variables;

use craft\helpers\Template;
use craft\helpers\UrlHelper;
use Solspace\Freeform\Elements\Db\SubmissionQuery;
use Solspace\Freeform\Elements\Submission;
use Solspace\Freeform\Form\Form;
use Solspace\Freeform\Freeform;
use Solspace\Freeform\Library\Helpers\EditionHelper;
use Solspace\Freeform\Library\Helpers\SitesHelper;
use Solspace\Freeform\Models\Settings;
use Solspace\Freeform\Services\FormsService;
use Solspace\Freeform\Services\LoggerService;
use Solspace\Freeform\Services\NotificationsService;
use Solspace\Freeform\Services\SettingsService;
use Twig\Markup;
use yii\db\Exception;

class FreeformVariable
{
    public array $siteTemplatesDirectories = [];

    /**
     * @param int|string $handleOrId
     */
    public function form($handleOrId, ?array $properties = null): ?Form
    {
        $site = SitesHelper::getFrontendSiteHandle();
        $form = $this->getFormService()->getFormByHandleOrId($handleOrId, $site);
        if (!$form) {
            return null;
        }

        return $form->setProperties($properties);
    }

    /**
     * @return Form[]
     */
    public function forms(): array
    {
        $formService = $this->getFormService();

        $sites = SitesHelper::getSiteHandlesForFrontend();
        $forms = $formService->getAllForms(sites: $sites);

        return $forms ?: [];
    }

    public function submissionCount(Form $form): int
    {
        return Freeform::getInstance()->submissions->getSubmissionCount([$form->getId()]);
    }

    public function submissions(?array $attributes = null): SubmissionQuery
    {
        $query = Submission::find();

        if (isset($attributes['includeSpam'])) {
            $isSpam = $attributes['includeSpam'] ? null : false;
            unset($attributes['includeSpam']);
            $query->isSpam($isSpam);
        } else {
            $query->isSpam(false);
        }

        if ($attributes) {
            \Craft::configure($query, $attributes);
        }

        return $query;
    }

    /**
     * @throws Exception
     */
    public function deleteSubmissionByToken(string $token): bool
    {
        if (empty($token) || Submission::OPT_IN_DATA_TOKEN_LENGTH !== \strlen($token)) {
            return false;
        }

        $query = Submission::find()->limit(1)->token($token);

        return Freeform::getInstance()->submissions->delete($query, true);
    }

    public function getSettings(): Settings
    {
        return $this->getSettingsService()->getSettingsModel();
    }

    public function name(): string
    {
        return Freeform::getInstance()->name;
    }

    public function getSettingsNavigation(): array
    {
        return $this->getSettingsService()->getSettingsNavigation();
    }

    /**
     * @deprecated use the ::loadFreeformPlugin() method from now on
     */
    public function loadFreeformScripts(): Markup
    {
        return $this->loadFreeformPlugin();
    }

    public function loadFreeformPlugin(?string $attributes = null, ?string $styleAttributes = null): Markup
    {
        $jsHash = sha1_file($this->getSettingsService()->getPluginJsPath());
        $cssHash = sha1_file($this->getSettingsService()->getPluginCssPath());

        $js = UrlHelper::siteUrl('freeform/plugin.js', ['v' => $jsHash]);
        $css = UrlHelper::siteUrl('freeform/plugin.css', ['v' => $cssHash]);

        $output = '<script type="text/javascript" src="'.$js.'" '.$attributes.'></script>'.\PHP_EOL;
        $output .= '<link rel="stylesheet" href="'.$css.'" '.$styleAttributes.' />';

        return Template::raw($output);
    }

    public function getLoggerService(): LoggerService
    {
        return Freeform::getInstance()->logger;
    }

    public function isPro(): bool
    {
        return Freeform::getInstance()->isPro();
    }

    public function getEdition(): EditionHelper
    {
        return Freeform::getInstance()->edition();
    }

    public function getVersion(?int $marks = null): string
    {
        $version = Freeform::getInstance()->version;

        if (null === $marks) {
            return $version;
        }

        $points = explode('.', $version);
        $points = \array_slice($points, 0, $marks);

        return implode('.', $points);
    }

    public function isPossibleLoadingStaticScripts(): bool
    {
        return $this->getFormService()->isPossibleLoadingStaticScripts();
    }

    public function notifications(): NotificationsService
    {
        return Freeform::getInstance()->notifications;
    }

    public function getAllSiteTemplatesDirectories(): array
    {
        $this->siteTemplatesDirectories = [];

        $siteTemplatesPath = \Craft::$app->getPath()->getSiteTemplatesPath();

        $this->getSiteTemplatesDirectories($siteTemplatesPath, $siteTemplatesPath);

        return $this->siteTemplatesDirectories;
    }

    private function getSiteTemplatesDirectories(string $siteTemplatesPath, string $currentPath): void
    {
        foreach (new \DirectoryIterator($currentPath) as $fileInfo) {
            if (!$fileInfo->isDot()) {
                if ($fileInfo->isDir()) {
                    $this->siteTemplatesDirectories[] = str_replace($siteTemplatesPath.'/', '', $fileInfo->getPathname());

                    $this->getSiteTemplatesDirectories($siteTemplatesPath, $fileInfo->getPathname());
                }
            }
        }
    }

    private function getFormService(): FormsService
    {
        return Freeform::getInstance()->forms;
    }

    private function getSettingsService(): SettingsService
    {
        return Freeform::getInstance()->settings;
    }
}
