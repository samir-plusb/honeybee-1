<?php

class Shofi_UpdateCategoriesAction extends ShofiBaseAction
{
    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        $categoryMatcher = new ShofiCategoryMatcher(
            $this->getContext()->getDatabaseConnection('Shofi.Write')
        );
        $listConfig = ListConfig::fromArray(AgaviConfig::get('shofi.list_config'));
        $finder = ShofiFinder::create($listConfig);
        $service = ShofiWorkflowService::getInstance();

        foreach ($categoryMatcher->getMatchMappings() as $externalCategory => $categories)
        {
			if (empty($categories))
			{
				continue;
			}

            foreach ($finder->getWhereCategoryEmptyByCategorySource($externalCategory)->getItems() as $shofiItem)
            {
				$tmpCategories = $categories;
                $detailItem = $shofiItem->getDetailItem();
                $firstCategory = array_shift($tmpCategories);
                if (! empty($firstCategory))
                {
                    $detailItem->setCategory($firstCategory);
                }
                if (! empty($tmpCategories))
                {
                    $detailItem->setAdditionalCategories($tmpCategories);
                }
                $service->storeWorkflowItem($shofiItem);
            }
        }

        return AgaviView::NONE;
    }

    public function isSecure()
    {
        return FALSE;
    }
}
