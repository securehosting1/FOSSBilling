<?php
/**
 * Copyright 2022-2023 FOSSBilling
 * Copyright 2011-2021 BoxBilling, Inc.
 * SPDX-License-Identifier: Apache-2.0
 *
 * @copyright FOSSBilling (https://www.fossbilling.org)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 */

/**
 * Knowledge base API.
 */

namespace Box\Mod\Kb\Api;

class Guest extends \Api_Abstract
{
    /**
     * Get paginated list of knowledge base articles.
     * Returns only active articles.
     *
     * @return array
     */
    public function article_get_list($data)
    {
        $data['status'] = 'active';

        $status = $data['status'] ?? null;
        $search = $data['search'] ?? null;
        $cat = $data['kb_article_category_id'] ?? null;
        $per_page = $data['per_page'] ?? $this->di['pager']->getPer_page();
        $page = $data['page'] ?? null;

        $pager = $this->getService()->searchArticles($status, $search, $cat, $per_page, $page);

        foreach ($pager['list'] as $key => $item) {
            $article = $this->di['db']->getExistingModelById('KbArticle', $item['id'], 'KB Article not found');
            $pager['list'][$key] = $this->getService()->toApiArray($article);
        }

        return $pager;
    }

    /**
     * Get active knowledge base article.
     *
     * @param int    $id   - knowledge base article ID. Required only if SLUG is not passed.
     * @param string $slug - knowledge base article slug. Required only if ID is not passed.
     *
     * @return array
     */
    public function article_get($data)
    {
        if (!isset($data['id']) && !isset($data['slug'])) {
            throw new \Box_Exception('ID or slug is missing');
        }

        $id = $data['id'] ?? null;
        $slug = $data['slug'] ?? null;

        $model = false;
        if ($id) {
            $model = $this->getService()->findActiveArticleById($id);
        } else {
            $model = $this->getService()->findActiveArticleBySlug($slug);
        }

        if (!$model instanceof \Model_KbArticle) {
            throw new \Box_Exception('Article item not found');
        }
        $this->getService()->hitView($model);

        return $this->getService()->toApiArray($model, true, $this->getIdentity());
    }

    /**
     * Get paginated list of knowledge base categories.
     *
     * @return array
     */
    public function category_get_list($data)
    {
        $data['article_status'] = \Model_KbArticle::ACTIVE;
        [$query, $bindings] = $this->getService()->categoryGetSearchQuery($data);

        $per_page = $data['per_page'] ?? $this->di['pager']->getPer_page();
        $pager = $this->di['pager']->getAdvancedResultSet($query, $bindings, $per_page);

        $q = $data['q'] ?? null;

        foreach ($pager['list'] as $key => $item) {
            $category = $this->di['db']->getExistingModelById('KbArticleCategory', $item['id'], 'KB Article not found');
            $pager['list'][$key] = $this->getService()->categoryToApiArray($category, $this->getIdentity(), $q);
        }

        return $pager;
    }

    /**
     * Get knowledge base categories id, title pairs.
     *
     * @return array
     */
    public function category_get_pairs($data)
    {
        return $this->getService()->categoryGetPairs();
    }

    /**
     * Get knowledge base category by ID or SLUG.
     *
     * @param int    $id   - knowledge base category ID. Required only if SLUG is not passed.
     * @param string $slug - knowledge base category slug. Required only if ID is not passed.
     *
     * @return array
     */
    public function category_get($data)
    {
        if (!isset($data['id']) && !isset($data['slug'])) {
            throw new \Box_Exception('Category ID or slug is missing');
        }

        $id = $data['id'] ?? null;
        $slug = $data['slug'] ?? null;

        $model = false;
        if ($id) {
            $model = $this->getService()->findCategoryById($id);
        } else {
            $model = $this->getService()->findCategoryBySlug($slug);
        }

        if (!$model instanceof \Model_KbArticleCategory) {
            throw new \Box_Exception('Knowledge base category not found');
        }

        return $this->getService()->categoryToApiArray($model, $this->getIdentity());
    }
}
