<?php

class ShofiCategoryListRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $fieldname, array $data = array())
    {
        $displayVal = '';
        $categoryService = ShofiCategoriesWorkflowService::getInstance();
        if ($value && ($category = $categoryService->fetchWorkflowItemById($value)))
        {
            $displayVal = $category->getMasterRecord()->getName();
        }
        return $displayVal;
    }
}
