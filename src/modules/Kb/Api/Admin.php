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

class Admin extends \Api_Abstract
{
    /**
     * Get paginated list of knowledge base articles.
     *
     * @return array
     */
    public function article_get_list($data)
    {
        $status =  $data['status'] ?? null;
        $search =  $data['search'] ?? null;
        $cat =  $data['cat'] ?? null;

        $pager = $this->getService()->searchArticles($status, $search, $cat);

        foreach ($pager['list'] as $key => $item) {
            $article = $this->di['db']->getExistingModelById('KbArticle', $item['id'], 'KB Article not found');
            $pager['list'][$key] = $this->getService()->toApiArray($article);
        }

        return $pager;
    }

    /**
     * Get knowledge base article.
     *
     * @param int $id - knowledge base article ID
     *
     * @return array
     */
    public function article_get($data)
    {
        $required = [
            'id' => 'Article id not passed',
        ];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $model = $this->di['db']->findOne('KbArticle', 'id = ?', [$data['id']]);

        if (!$model instanceof \Model_KbArticle) {
            throw new \Box_Exception('Article not found');
        }

        return $this->getService()->toApiArray($model, true, $this->getIdentity());
    }

    /**
     * Create new knowledge base article.
     *
     * @param int    $kb_article_category_id - knowledge base category ID
     * @param string $title                  - knowledge base article title
     *
     * @optional string $status - knowledge base article status
     * @optional string $content - knowledge base article content
     *
     * @return array
     */
    public function article_create($data)
    {
        $required = [
            'kb_article_category_id' => 'Article category id not passed',
            'title' => 'Article title not passed',
        ];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $articleCategoryId = $data['kb_article_category_id'];
        $title = $data['title'];
        $status = $data['status'] ?? \Model_KbArticle::DRAFT;
        $content = $data['content'] ?? null;

        return $this->getService()->createArticle($articleCategoryId, $title, $status, $content);
    }

    /**
     * Update knowledge base article.
     *
     * @param int $id - knowledge base article ID
     *
     * @optional string $title - knowledge base article title
     * @optional int $kb_article_category_id - knowledge base category ID
     * @optional string $slug - knowledge base article slug
     * @optional string $status - knowledge base article status
     * @optional string $content - knowledge base article content
     * @optional int $views - knowledge base article views counter
     *
     * @return bool
     */
    public function article_update($data)
    {
        $required = [
            'id' => 'Article ID not passed',
        ];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $articleCategoryId = $data['kb_article_category_id'] ?? null;
        $title = $data['title'] ?? null;
        $slug = $data['slug'] ?? null;
        $status = $data['status'] ?? null;
        $content = $data['content'] ?? null;
        $views = $data['views'] ?? null;

        return $this->getService()->updateArticle($data['id'], $articleCategoryId, $title, $slug, $status, $content, $views);
    }

    /**
     * Delete knowledge base article.
     *
     * @param int $id - knowledge base article ID
     *
     * @return bool
     */
    public function article_delete($data)
    {
        $required = [
            'id' => 'Article ID not passed',
        ];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $model = $this->di['db']->findOne('KbArticle', 'id = ?', [$data['id']]);

        if (!$model instanceof \Model_KbArticle) {
            throw new \Box_Exception('Article not found');
        }

        $this->getService()->rm($model);

        return true;
    }

    /**
     * Get paginated list of knowledge base categories.
     *
     * @return array
     */
    public function category_get_list($data)
    {
        [$sql, $bindings] = $this->getService()->categoryGetSearchQuery($data);
        $per_page = $data['per_page'] ?? $this->di['pager']->getPer_page();
        $pager = $this->di['pager']->getAdvancedResultSet($sql, $bindings, $per_page);

        foreach ($pager['list'] as $key => $item) {
            $category = $this->di['db']->getExistingModelById('KbArticleCategory', $item['id'], 'KB Article not found');
            $pager['list'][$key] = $this->getService()->categoryToApiArray($category, $this->getIdentity());
        }

        return $pager;
    }

    /**
     * Get knowledge base category.
     *
     * @param int $id - knowledge base category ID
     *
     * @return array
     */
    public function category_get($data)
    {
        $required = [
            'id' => 'Category ID not passed',
        ];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $model = $this->di['db']->findOne('KbArticleCategory', 'id = ?', [$data['id']]);

        if (!$model instanceof \Model_KbArticleCategory) {
            throw new \Box_Exception('Article Category not found');
        }

        return $this->getService()->categoryToApiArray($model);
    }

    /**
     * Create new knowledge base category.
     *
     * @param string $title - knowledge base category title
     *
     * @optional string $description - knowledge base category description
     *
     * @return array
     */
    public function category_create($data)
    {
        $required = [
            'title' => 'Category title not passed',
        ];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $title = $data['title'];
        $description = $data['description'] ?? null;

        return $this->getService()->createCategory($title, $description);
    }

    /**
     * Update knowledge base category.
     *
     * @param int $id - knowledge base category ID
     *
     * @optional string $title - knowledge base category title
     * @optional string $slug  - knowledge base category slug
     * @optional string $description - knowledge base category description
     *
     * @return array
     */
    public function category_update($data)
    {
        $required = [
            'id' => 'Category ID not passed',
        ];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $model = $this->di['db']->findOne('KbArticleCategory', 'id = ?', [$data['id']]);

        if (!$model instanceof \Model_KbArticleCategory) {
            throw new \Box_Exception('Article Category not found');
        }

        $title = $data['title'] ?? null;
        $slug = $data['slug'] ?? null;
        $description = $data['description'] ?? null;

        return $this->getService()->updateCategory($model, $title, $slug, $description);
    }

    /**
     * Delete knowledge base category.
     *
     * @param int $id - knowledge base category ID
     *
     * @return bool
     */
    public function category_delete($data)
    {
        $required = [
            'id' => 'Category ID not passed',
        ];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $model = $this->di['db']->findOne('KbArticleCategory', 'id = ?', [$data['id']]);

        if (!$model instanceof \Model_KbArticleCategory) {
            throw new \Box_Exception('Category not found');
        }

        return $this->getService()->categoryRm($model);
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
}
