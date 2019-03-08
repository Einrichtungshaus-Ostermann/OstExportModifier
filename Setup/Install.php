<?php declare(strict_types=1);

/**
 * Einrichtungshaus Ostermann GmbH & Co. KG - Export Modifier
 *
 * @package   OstExportModifier
 *
 * @author    Eike Brandt-Warneke <e.brandt-warneke@ostermann.de>
 * @copyright 2019 Einrichtungshaus Ostermann GmbH & Co. KG
 * @license   proprietary
 */

namespace OstExportModifier\Setup;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;

class Install
{
    /**
     * ...
     *
     * @var array
     */
    public static $attributes = [
        's_categories_attributes' => [
            [
                'column' => 'ost_export_modifier_blacklist',
                'type'   => 'boolean',
                'data'   => [
                    'label'            => 'Nicht im Produktexport anzeigen',
                    'helpText'         => 'Diese Kategorie soll nicht im Produktexport angezeigt werden',
                    'translatable'     => false,
                    'displayInBackend' => true,
                    'custom'           => false,
                    'position'         => 180
                ]
            ]
        ]
    ];

    /**
     * Main bootstrap object.
     *
     * @var Plugin
     */
    protected $plugin;

    /**
     * ...
     *
     * @var InstallContext
     */
    protected $context;

    /**
     * ...
     *
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * ...
     *
     * @var CrudService
     */
    protected $crudService;

    /**
     * ...
     *
     * @param Plugin         $plugin
     * @param InstallContext $context
     * @param ModelManager   $modelManager
     * @param CrudService    $crudService
     */
    public function __construct(Plugin $plugin, InstallContext $context, ModelManager $modelManager, CrudService $crudService)
    {
        // set params
        $this->plugin = $plugin;
        $this->context = $context;
        $this->modelManager = $modelManager;
        $this->crudService = $crudService;
    }

    /**
     * ...
     *
     * @throws \Exception
     */
    public function install()
    {
    }
}
