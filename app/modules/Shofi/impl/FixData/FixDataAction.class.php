<?php

class Shofi_FixDataAction extends ShofiBaseAction
{
    protected $categoryStore;

    public function executeWrite(AgaviRequestDataHolder $parameters)
    {
        //$this->restoreBusinessEntries();
        //return 'Success';
        //$this->fixNamesForCategoryPlaces();
        //return 'Success';

        $this->categoryStore = new CouchDocumentStore(
            $this->getContext()->getDatabaseConnection('Shofi.Write')
        );
        $workflowService = ShofiWorkflowService::getInstance();
        $finder = ShofiFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi.list_config')
        ));
        $listState = ListState::fromArray(array(
            'limit' => 5000,
            'offset' => 0
        ));
        $entriesProcessed = 0;
		$errors = array();

        $cmExport = new ContentMachineHttpExport(
            AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_URL)
        );
		$finder->ignoreDeletedItems(FALSE);
        while (($result = $finder->find($listState)) && 0 < $result->getItemsCount())
        {
            $this->printMemUsage();
            echo "Processed " . $entriesProcessed . " places ..." . PHP_EOL;
            foreach ($result->getItems() as $item)
            {
                //if (! $this->localize($item))
				//{
				//	continue;
				//}
				//$this->fixProduct($item);
                //$this->fixName($item);
				$isDeleted = $item->getAttribute('marked_deleted', FALSE);
                if ($isDeleted)/*$this->fixPrimaryCategoryAssignment($item) || */
                {
                    //$workflowService->storeWorkflowItem($item);
                    $exportAllowed = (TRUE === AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_ENABLED));
                    if (TRUE === $exportAllowed)
                    {
                        if (! $cmExport->deleteEntity($item->getIdentifier(), 'place'))
                        {
                            echo "Error while deleting data to fe for item : " . $item->getIdentifier() . ', Error: ' . print_r($cmExport->getLastErrors(), TRUE) . PHP_EOL;
                        }
                	}
                }
                else
                {
                    //$workflowService->storeWorkflowItem($item);
                    //$exportAllowed = (TRUE === AgaviConfig::get(ContentMachineHttpExport::SETTING_EXPORT_ENABLED));
                    //if (TRUE === $exportAllowed)
                    //{
                    //    if (! $cmExport->exportShofiPlace($item))
                    //    {
                    //        echo "Error while sending data to fe for item : " . $item->getIdentifier() . ', Error: ' . print_r($cmExport->getLastErrors(), TRUE) . PHP_EOL;
                    //    }
                    //}
                }
            }
            $listState->setOffset(
                $listState->getOffset() + $listState->getLimit()
            );
            $entriesProcessed += $listState->getLimit();
        }
        return 'Success';
    }

    protected function restoreBusinessEntries()
    {
        $this->placesStore = new CouchDocumentStore(
            $this->getContext()->getDatabaseConnection('Shofi.Write')
        );
        $filePath = dirname(__FILE__) . '/business.places.json';
        $viewData = json_decode(file_get_contents($filePath), TRUE);
        $count = 0;
        foreach ($viewData['rows'] as $viewRow)
        {
            $placeData = $viewRow['value'];
            $detailData = $placeData['detailItem'];

            $placeItem = $this->placesStore->fetchByIdentifier($placeData['_id']);
            $detailItem = $placeItem->getDetailItem();
            $salesItem = $placeItem->getSalesItem();

            $detailItem->setAttributes($detailData['attributes']);
            $salesItem->setProduct('business');

            $this->placesStore->save($placeItem);

            echo "processed place: " . $placeItem->getIdentifier() . PHP_EOL;
        }
    }

    protected function localize(ShofiWorkflowItem $shofiItem)
    {
        $location = $shofiItem->getCoreItem()->getLocation();
		$coords = $location->getCoordinates();
		if ($coords && ! empty($coords['lon']) && ! empty($coords['lat']))
		{
			return false;
		}
        // create 'geoText' that we will query the localize api for.
        $geoText = $location->getName();
        $geoText .= ' ' . $location->getStreet();
        $geoText .= ' ' . $location->getHousenumber();
        $geoText .= ' ' . $location->getDetails();
        $geoText .= ' ' . $location->getCity();
        $geoText .= ' ' . $location->getPostalCode();
        $geoText .= '+Berlin';
        // prepare the api localize url
        $url = sprintf(
            '%s?string=%s&no-google=1',
            AgaviConfig::get('news.localize_api_url'),
            urlencode($geoText)
        );
        // init our curl handle
        $curl = ProjectCurl::create();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'X-Requested-With: XMLHttpRequest',
            'Content-Type:application/json',
            'Accept:application/json; charset=utf-8'
        ));
        // fire the request
        $apiResponse = curl_exec($curl);
        // and handle the response
        if (curl_error($curl))
        {
            echo "Failed localizing core item for geoText: " . $geoText . "and item: " . $shofiItem->getIdentifier() .
                 PHP_EOL . "Error: " . curl_error($curl);
			return false;
        }
        else
        {
            // if everyting worked hydrate the location data et voila
            $localizeResults = json_decode($apiResponse, TRUE);
            if (0 < $localizeResults['items_count'])
            {
                $locationData = $localizeResults[0];
                $location->applyValues(array(
                    'district' => $locationData['district'],
                    'administrativeDistrict' => $locationData['administrative district'],
                    'neighborhood' => isset($locationData['neighborhood']) ? $locationData['neighborhood'] : '',
                    //'postalCode' => isset($locationData['uzip']) ? $locationData['uzip'] : '',
                    //'street' => isset($locationData['street_name']) ? $locationData['street_name'] : '',
                    'coordinates' => array(
                        'lon' => $locationData['longitude'],
                        'lat' => $locationData['latitude']
                    )
                ));
                echo "Set location for item: " . $shofiItem->getIdentifier() . PHP_EOL;
            }
        }
		return true;
    }

    protected function fixCategoryAssignment(ShofiWorkflowItem $shofiItem)
    {
        $salesItem = $shofiItem->getSalesItem();
        $detailitem = $shofiItem->getDetailItem();

        $salesItem->setAdditionalCategories(
            $this->emptyBrokenCategoryList(
                $salesItem->getAdditionalCategories()
            )
        );
        $detailitem->setAdditionalCategories(
            $this->emptyBrokenCategoryList(
                $detailitem->getAdditionalCategories()
            )
        );
    }

    protected function emptyBrokenCategoryList(array $categoryIds)
    {
        foreach ($categoryIds as $categoryId)
        {
            $category = $this->categoryStore->fetchByIdentifier($categoryId);
            if (! $category)
            {
                return array();
            }
        }
        return $categoryIds;
    }

    protected function printMemUsage()
    {
        $mem_usage = memory_get_usage(true);
        if ($mem_usage < 1024)
            echo $mem_usage." bytes";
        elseif ($mem_usage < 1048576)
            echo round($mem_usage/1024,2)." kilobytes";
        else
            echo round($mem_usage/1048576,2)." megabytes";
        echo PHP_EOL;
    }

     /**
      * @see Ticket #6853
      */
    protected function fixNamesForCategoryPlaces()
    {
        $categoryIds = $this->fetchCategoriesForNameFixing();
        $shofiFinder = ShofiFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi.list_config')
        ));
        $service = $shofiFinder->getWorkflowService();
        $result = $shofiFinder->getByCategoryIds($categoryIds);
        foreach ($result->getItems() as $item)
        {
            $coreItem = $item->getCoreItem()->setName(NULL);
            $service->storeWorkflowItem($item);
            echo "Processed item: " . $item->getIdentifier() . PHP_EOL;
        }
    }

     /**
     * @see Ticket #6853
     */
    protected function fetchCategoriesForNameFixing()
    {
        $staticIds = array(
            '4f105ab1b4fc471f09440000',
            '4f105ab1b4fc471f09470000',
            '4f105ab1b4fc471f09430000',
            '4f700cbe8786c0160a000005',
            '4f105ab1b4fc471f09440000',
            '4f105ab1b4fc471f091e0000',
            '4f105ab1b4fc471f09450000',
            '4f105ab1b4fc471f09460000',
            '4f105ab1b4fc471f09710000',
            '4f105ab1b4fc471f095e0000',
            '4f105ab1b4fc471f09470000',
            '4f105ab2b4fc471f09780000',
            '4f105ab1b4fc471f09490000',
            '4f105ab1b4fc471f094b0000',
            '4f105ab2b4fc471f09600100',
            '4f105ab1b4fc471f094d0000',
            '4f105ab1b4fc471f094d0000',
            '4f105ab1b4fc471f094c0000',
            '4f105ab1b4fc471f094e0000',
            '4f105ab1b4fc471f09510000',
            '4f105ab1b4fc471f09510000',
            '4f105ab3b4fc471f09ef0200',
            '4f105ab1b4fc471f09520000',
            '4f700cbe8786c0160a000014',
            '4f700cbe8786c0160a00000d',
            '4f105ab1b4fc471f09540000',
            '4f105ab1b4fc471f09560000',
            '4f105ab1b4fc471f09540000',
            '4f700cbe8786c0160a00000f',
            '4f105ab1b4fc471f095b0000',
            '4f700cc38786c0160a000051',
            '4f105ab1b4fc471f09550000',
            '4f105ab1b4fc471f09560000',
            '4f105ab3b4fc471f09800300',
            '4f105ab1b4fc471f09580000',
            '4f105ab1b4fc471f09590000',
            '4f105ab1b4fc471f095c0000',
            '4f105ab1b4fc471f095d0000',
            '4f105ab1b4fc471f095f0000',
            '4f105ab1b4fc471f09500000',
            '4f105ab1b4fc471f09620000',
            '4f105ab1b4fc471f09650000',
            '4f105ab4b4fc471f097a0400',
            '4f105ab4b4fc471f097e0400',
            '4f105ab1b4fc471f09680000',
            '4f105ab1b4fc471f09670000',
            '4f105ab4b4fc471f097d0400',
            '4f105ab1b4fc471f09690000',
            '4f105ab1b4fc471f096b0000',
            '4f700cc38786c0160a000054',
            '4f105ab1b4fc471f096c0000',
            '4f105ab1b4fc471f096d0000',
            '4f105ab1b4fc471f09640000',
            '4f105ab1b4fc471f096f0000',
            '4f105ab1b4fc471f095a0000',
            '4f105ab5b4fc471f099f0600',
            '4f105ab5b4fc471f099f0600',
            '4f105ab5b4fc471f09a30600',
            '4f105ab5b4fc471f09a70600'
        );

        $categoryNames = array(
            'Chirurgie', 'Dermatologe', 'Gynäkologe',
            'Hals-Nasen-Ohren Heilkunde', 'Hausarzt', 'Innere Medizin',
            'Notdienste', 'Orthopäde', 'Orthopäden und Unfallchirurgen',
            'Physikalische Therapie', 'Psychotherapie', 'Venen-Heilkunde'
        );
        $queriedIds = $this->fetchCategoryIdsByNames($categoryNames);
        return array_merge($queriedIds, $staticIds);
    }

    /**
     * @see Ticket #6853
     */
    protected function fetchCategoryIdsByNames(array $categoryNames)
    {
        $finder = ShofiCategoriesFinder::create(ListConfig::fromArray(
            AgaviConfig::get('shofi_categories.list_config')
        ));
        $result = $finder->getCategoriesByNames($categoryNames);
        $categoryIds = array();
        foreach ($result->getItems() as $item)
        {
            $categoryIds[] = $item->getIdentifier();
        }
        return $categoryIds;
    }

    /**
     * @see Ticket #6895
     */
    protected function fixPrimaryCategoryAssignment(ShofiWorkflowItem $item)
    {
        $categoryId = $item->getDetailItem()->getCategory();
        if (! empty($categoryId))
        {
            $category = $this->categoryStore->fetchByIdentifier($categoryId);
            if (! $category)
            {
                $item->getDetailItem()->setCategory(NULL);
                $item->setAttribute('isHidden', '1');
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * @see Ticket #6895
     */
    protected function fixName(ShofiWorkflowItem $item)
    {
        $name = $item->getCoreItem()->getName();
        $pattern = '~\s(dr|prof)\.\s~is';
        if (preg_match($pattern, $name))
        {
            $item->getCoreItem()->setName(NULL);
        }
    }

    protected function fixProduct(ShofiWorkflowItem $shofiItem)
    {
        $supportedProducts = array('premium', 'buisiness');
        $salesItem = $shofiItem->getSalesItem();
        $product = $salesItem->getProduct();
        if (! in_array($product, $supportedProducts))
        {
            $product = 'no-product';
            $salesItem->setProduct($product);
        }
        if ('no-product' === $product)
        {
            $attributes = $salesItem->getAttributes();
            if (! empty($attributes))
            {
                $purgedData = array(
                    'id' => $shofiItem->getIdentifier(),
                    'attr' => $attributes
                );
                $salesItem->setAttributes(array());
                file_put_contents('purged_sales_attributes.json', json_encode($purgedData), FILE_APPEND);
            }
        }
    }

    public function isSecure()
    {
        return FALSE;
    }
}
