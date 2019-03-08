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

namespace OstExportModifier\Listeners\Core;

use Enlight_Hook_HookArgs as HookArgs;
use Shopware_Components_Modules as Modules;

class sExport
{
    /**
     * ...
     *
     * @var Modules
     */
    private $modules;

    /**
     * ...
     *
     * @param Modules $modules
     */
    public function __construct(Modules $modules)
    {
        // set params
        $this->modules = $modules;
    }

    /**
     * ...
     *
     * @param HookArgs $arguments
     *
     * @throws \SmartyException
     */
    public function afterInitSmarty(HookArgs $arguments)
    {
        // get the class
        /* @var $sExport \sExport */
        $sExport = $arguments->getSubject();

        // register our custom modifier
        $sExport->sSmarty->registerPlugin('modifier', 'filteredCategory', [&$this, 'modifierFilteredCategory']);
    }

    /**
     * ...
     *
     * @param int    $articleId
     * @param string $separator
     *
     * @return string
     */
    public function modifierFilteredCategory($articleId, $separator = ' > ')
    {
        if (empty($categoryID)) {
            $categoryID = $this->modules->Export()->sSettings['categoryID'];
        }
        $productCategoryId = $this->getCategoryIdByArticleId($articleId, $categoryID);
        $breadcrumb = array_reverse($this->modules->Categories()->sGetCategoriesByParent($productCategoryId));
        $breadcrumbs = [];
        foreach ($breadcrumb as $breadcrumbObj) {
            $breadcrumbs[] = $breadcrumbObj['name'];
        }
        return htmlspecialchars_decode(implode($separator, $breadcrumbs));
    }

    /**
     * ...
     *
     * @param int  $articleId Id of the product to look for
     * @param int  $parentId  Category subtree root id. If null, the shop category is used.
     * @param null $shopId
     *
     * @return int id of the leaf category, or 0 if none found
     */
    private function getCategoryIdByArticleId($articleId, $parentId = null, $shopId = null)
    {
        if ($parentId === null) {
            $parentId = (int) Shopware()->Shop()->get('parentID');
        }
        if ($shopId === null) {
            $shopId = Shopware()->Shop()->getId();
        }

        $id = (int) Shopware()->Db()->fetchOne(
            'SELECT category.category_id
             FROM s_articles_categories_seo AS category
             WHERE category.article_id = :articleId
             AND category.shop_id = :shopId',
            [':articleId' => $articleId, ':shopId' => $shopId]
        );

        if ($id > 0 && $this->isRestricted($id) === false) {
            return $id;
        }

        $query = '
           SELECT ac.categoryID AS id
            FROM s_articles_categories ac
                INNER JOIN s_categories c
                    ON  ac.categoryID = c.id
                    AND c.active = 1
                    AND c.path LIKE ?
                LEFT JOIN s_categories c2
                    ON c2.parent = c.id
            WHERE ac.articleID = ?
            AND c2.id IS NULL
            ORDER BY ac.id
            LIMIT 1
        ';
        $categories = Shopware()->Db()->fetchAll($query, [
            '%|' . $parentId . '|%',
            $articleId,
        ]);

        // loop every category
        foreach ($categories as $category) {
            // not restricted?
            if ($this->isRestricted((int) $category['id']) === false) {
                // all good
                return (int) $category['id'];
            }
        }

        // default
        return 0;
    }

    /**
     * ...
     *
     * @param int $categoryId
     *
     * @return bool
     */
    private function isRestricted($categoryId)
    {
        // ...
        $query = '
            SELECT category.id, category.parent, attribute.ost_export_modifier_blacklist
            FROM s_categories AS category
                LEFT JOIN s_categories_attributes AS attribute
                    ON category.id = attribute.categoryID
            WHERE category.id = :id
        ';
        $category = Shopware()->Db()->fetchRow($query, ['id' => $categoryId]);

        // restricted?!
        if ((int) $category['ost_export_modifier_blacklist'] === 1) {
            // yes
            return true;
        }

        // final top category?
        if ((int) $category['parent'] === 0) {
            // all good
            return false;
        }

        // check parent
        return $this->isRestricted((int) $category['parent']);
    }
}
