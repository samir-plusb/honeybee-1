<?php

class ShofiVerticalListRenderer extends DefaultListValueRenderer
{
    public function renderValue($value, $fieldname, array $data = array())
    {
        $displayVal = '';
        $categoryService = ShofiCategoriesWorkflowService::getInstance();
        $verticalService = ShofiVerticalsWorkflowService::getInstance();
        if ($value && ($category = $categoryService->fetchWorkflowItemById($value)))
        {
            $vertical = $category->getMasterRecord()->getVertical();
            if (isset($vertical['id']))
            {
                $vertical = $verticalService->fetchWorkflowItemById($vertical['id']);
                $displayVal = $vertical->getMasterRecord()->getName();
            }
        }
        return $displayVal;
    }
}
